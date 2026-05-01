<?php // Name : Rodain Gouzlan Id:

namespace App\Services\Store;

/**
 * Service: تنفيذ عمليات CRUD للفرع داخل Transaction
 *
 * لماذا يوجد هذا الملف؟
 * - عمليات إنشاء/تحديث الفرع ليست مجرد تحديث حقول، بل تشمل:
 *   1) حفظ بيانات الفرع الأساسية.
 *   2) مزامنة تعيينات المدير/الموظفين.
 *   3) (اختياري) مزامنة ربط المنتجات.
 *
 * لذلك يتم تنفيذها ضمن DB::transaction لضمان:
 * - إما نجاح جميع الخطوات أو فشلها جميعاً (Atomicity).
 */

// الاستيرادات اللازمة للتعامل مع النماذج (Models) وطلبات HTTP وقاعدة البيانات.
use App\Exceptions\StoreDeletionBlockedException;
use App\Models\Store;
// خدمة إنشاء طلبات التعيين/الإشعارات لتوثيق تغييرات التعيين.
use App\Models\Department;
use App\Services\Access\StoreScopeService;
use App\Services\Store\StoreBrochureService;
// Request قياسي لاستقبال البيانات والملفات (مثل brochure).
use Illuminate\Http\Request;
// DB لتنفيذ معاملات Transaction.
use Illuminate\Support\Facades\DB;
// Schema للتحقق من وجود أعمدة (created_by/updated_by) بدون كسر التوافق مع أي مخطط سابق.
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class StoreCrudService
{
    public function __construct(
        // خدمة الدومين (StoreService) التي تحتوي منطق استخراج البيانات ومزامنة العلاقات.
        private StoreService $storeService,
        
        // خدمة البروشور (تحميل/عرض) + تنظيف كاش ملفات PDF المولدة عند تحديث بيانات الفرع.
        private StoreBrochureService $storeBrochureService,
    ) {
    }

    /**
     * @return array{store: Store, transfers: array<int, string>}
     */
    public function create(Request $request, array $validated, ?int $actorId, bool $notifyAssignments, bool $syncProducts = false): array
    {
        // تجميع رسائل "النقل" الناتجة عند نقل موظفين/مدير من فرع لآخر (لإظهارها في UI).
        $transferMessages = [];

        // تنفيذ كامل عملية الإنشاء داخل Transaction حتى تبقى البيانات متسقة (Stores + Assignments + Products).
        $store = DB::transaction(function () use ($request, $validated, $actorId, $notifyAssignments, $syncProducts, &$transferMessages): Store {
            // تجهيز بيانات الفرع من البيانات المُتحقّق منها، مع التعامل مع رفع ملف البرشور إن وُجد.
            $storeData = $this->storeService->extractStoreData($request, $validated);
            $departmentId = $this->resolveDepartmentId($request, null);
            if ($departmentId) {
                $storeData['department_id'] = $departmentId;
            }
            // دعم حقول التدقيق (Audit) إن كانت موجودة في الجدول.
            if ($actorId && Schema::hasColumn('stores', 'created_by')) {
                $storeData['created_by'] = $actorId;
            }
            // تخزين آخر من قام بالتعديل/الإنشاء داخل updated_by عند توفر العمود.
            if ($actorId && Schema::hasColumn('stores', 'updated_by')) {
                $storeData['updated_by'] = $actorId;
            }

            // إنشاء السجل الأساسي للفرع في جدول stores.
            $store = Store::create($storeData);

            // إذا كان المنفذ Admin (أو مفعل إشعارات التعيين)، ننشئ سجل "طلب تعيين" تلقائي وموافق عليه.
            // الهدف: إبقاء التعديلات قابلة للتتبع + إرسال إشعارات للأشخاص المنقولين/المعيّنين.
            // مزامنة تعيين المدير/الموظفين داخل pivot store_user.
            // هذه الخطوة قد تحذف تعيينات سابقة لهم في فروع أخرى (حسب قواعد النظام).
            $transferMessages = $this->storeService->syncAssignments(
                $store,
                $validated['manager_id'] ?? null,
                $validated['employee_ids'] ?? []
            );

            // (اختياري) مزامنة المنتجات المرتبطة بالفرع إذا كان هذا المسار مطلوباً.
            if ($syncProducts && array_key_exists('product_ids', $validated)) {
                $store->products()->sync($validated['product_ids'] ?? []);
            }

            // إرجاع كائن الفرع الذي تم إنشاؤه من داخل الـ Transaction.
            return $store;
        });

        // تفريغ الكاشات المرتبطة بالأفرع حتى تظهر النتائج الجديدة فوراً في القائمة/الداشبورد.
        $this->storeService->flushStoreCaches();

        // إرجاع بيانات مفيدة للـ Controller (الفرع + رسائل النقل).
        return [
            'store' => $store,
            'transfers' => $transferMessages,
        ];
    }

    /**
     * @return array{store: Store, transfers: array<int, string>, changed: bool}
     */
    public function update(Store $store, Request $request, array $validated, ?int $actorId, bool $notifyAssignments, bool $syncProducts = false): array
    {
        // نحتفظ بالكود القديم قبل التحديث حتى إذا تغيّر branch_code نزيل كاش PDF القديم أيضاً.
        $previousBranchCode = (string) $store->branch_code;

        // تحميل تعيينات الفرع الحالية للتأكد من إمكانية مقارنة القديم بالجديد.
        $store->loadMissing(['manager', 'employees']);

        // تجهيز البيانات القادمة للتحديث (تشمل البرشور عند وجود ملف مرفوع).
        $incomingData = $this->storeService->extractStoreData($request, $validated, $store);
        $departmentId = $this->resolveDepartmentId($request, $store);
        if ($departmentId) {
            $incomingData['department_id'] = $departmentId;
        }
        // تحديث حقل updated_by إن كان موجوداً.
        if ($actorId && Schema::hasColumn('stores', 'updated_by')) {
            $incomingData['updated_by'] = $actorId;
        }

        // اكتشاف إن كان هناك تغيير فعلي:
        // - coreChanged: تغير بيانات الفرع الأساسية
        // - assignmentsChanged: تغير المدير/الموظفين
        // - brochureChanged: تم رفع ملف برشور جديد
        [$coreChanged, $assignmentsChanged, $currentManagerId, $currentEmployeeIds] = $this->detectChanges($store, $incomingData, $validated, $request);
        $brochureChanged = $request->hasFile('brochure')
            || (array_key_exists('brochure_path', $incomingData) && (string) ($incomingData['brochure_path'] ?? '') !== (string) ($store->brochure_path ?? ''));

        // إذا لم يتغير أي شيء فعلاً، نعيد نتيجة واضحة حتى تعرض الواجهة رسالة "لا يوجد تغييرات".
        if (! $coreChanged && ! $assignmentsChanged && ! $brochureChanged) {
            return [
                'store' => $store,
                'transfers' => [],
                'changed' => false,
            ];
        }

        // تجميع رسائل النقل إن حدث نقل لمستخدمين بين الأفرع.
        $transferMessages = [];

        // تنفيذ التحديث داخل Transaction لضمان تماسك البيانات بين store + العلاقات.
        $store = DB::transaction(function () use ($store, $incomingData, $validated, $assignmentsChanged, $notifyAssignments, $actorId, $currentManagerId, $currentEmployeeIds, $syncProducts, &$transferMessages): Store {
            // تحديث بيانات الفرع الأساسية.
            $store->update($incomingData);

            // معالجة التعيينات فقط عند وجود تغيير لتقليل العمليات على قاعدة البيانات.
            if ($assignmentsChanged) {
                // مزامنة المدير/الموظفين وإرجاع رسائل النقل.
                // مزامنة المدير/الموظفين وإرجاع رسائل النقل.
                $transferMessages = $this->storeService->syncAssignments(
                    $store,
                    $validated['manager_id'] ?? null,
                    $validated['employee_ids'] ?? []
                );
            }

            // (اختياري) تحديث ربط المنتجات.
            if ($syncProducts && array_key_exists('product_ids', $validated)) {
                $store->products()->sync($validated['product_ids'] ?? []);
            }

            // إرجاع كائن الفرع بعد تحديثه.
            return $store;
        });

        // إذا كان لدينا PDF مولد سابقاً (cache) بنفس branch_code، نحذفه حتى يعاد توليده بالقيم الجديدة عند التحميل.
        // هذا يضمن أن أي تعديل على بيانات الفرع ينعكس مباشرة على البروشور المحمّل.
        $this->storeBrochureService->forgetGeneratedCacheByBranchCode($previousBranchCode);
        $this->storeBrochureService->forgetGeneratedCacheByBranchCode($store->branch_code);

        // تفريغ الكاش لإظهار التغيير مباشرة.
        $this->storeService->flushStoreCaches();

        // إرجاع نتيجة التحديث للـ Controller.
        return [
            'store' => $store,
            'transfers' => $transferMessages,
            'changed' => true,
        ];
    }

    public function delete(Store $store): void
    {
        $this->assertDeletable($store);

        // حذف ملف البرشور من التخزين إن وجد حتى لا تبقى ملفات يتيمة.
        if ($store->brochure_path) {
            Storage::disk('public')->delete($store->brochure_path);
        }

        // حذف تعيينات المستخدمين من pivot store_user قبل حذف الفرع.
        $store->users()->detach();
        // حذف سجل الفرع من جدول stores.
        $store->delete();

        // تفريغ الكاش بعد الحذف لتحديث الواجهة.
        $this->storeService->flushStoreCaches();
    }

    public function assertDeletable(Store $store): void
    {
        $blockers = $this->deletionBlockers($store);
        if ($blockers !== []) {
            throw StoreDeletionBlockedException::forBlockers($blockers);
        }
    }

    /**
     * @return array{0: bool, 1: bool, 2: int|null, 3: array<int, int>}
     */
    private function detectChanges(Store $store, array $incomingData, array $validated, Request $request): array
    {
        // توحيد تمثيل الوقت القادم من قاعدة البيانات/الطلب إلى صيغة HH:MM لسهولة المقارنة.
        $normalizeTime = static function ($value): ?string {
            if (! is_string($value)) {
                return null;
            }
            $value = substr($value, 0, 5);
            return preg_match('/^\\d{2}:\\d{2}$/', $value) ? $value : null;
        };

        // توحيد تمثيل التاريخ (قد يكون Carbon أو string) إلى صيغة Y-m-d.
        $normalizeDate = static function ($value): ?string {
            if ($value instanceof \Carbon\CarbonInterface) {
                return $value->format('Y-m-d');
            }
            if (is_string($value)) {
                return $value;
            }
            return null;
        };

        // أخذ "صورة" من بيانات الفرع الحالية (core fields) لمقارنتها مع البيانات القادمة.
        $currentCore = [
            'name' => (string) $store->name,
            'branch_code' => (string) $store->branch_code,
            'province_id' => (int) $store->province_id,
            'city' => (string) ($store->city ?? ''),
            'address' => (string) $store->address,
            'phone' => (string) $store->phone,
            'description' => $store->description ? (string) $store->description : null,
            'email' => $store->email ? (string) $store->email : null,
            'status' => (string) $store->status,
            'opening_date' => $normalizeDate($store->opening_date),
            'workday_starts_at' => $normalizeTime($store->workday_starts_at),
            'workday_ends_at' => $normalizeTime($store->workday_ends_at),
        ];

        // تجهيز "صورة" من البيانات القادمة (بعد التطبيع) لمقارنتها مع البيانات الحالية.
        $incomingCore = [
            'name' => (string) ($incomingData['name'] ?? ''),
            'branch_code' => (string) ($incomingData['branch_code'] ?? ''),
            'province_id' => (int) ($incomingData['province_id'] ?? 0),
            'city' => (string) ($incomingData['city'] ?? ''),
            'address' => (string) ($incomingData['address'] ?? ''),
            'phone' => (string) ($incomingData['phone'] ?? ''),
            'description' => ! empty($incomingData['description']) ? (string) $incomingData['description'] : null,
            'email' => ! empty($incomingData['email']) ? (string) $incomingData['email'] : null,
            'status' => (string) ($incomingData['status'] ?? ''),
            'opening_date' => $normalizeDate($incomingData['opening_date'] ?? null),
            'workday_starts_at' => $normalizeTime($incomingData['workday_starts_at'] ?? null),
            'workday_ends_at' => $normalizeTime($incomingData['workday_ends_at'] ?? null),
        ];

        // تحديد المدير الحالي والقادم.
        $currentManagerId = $store->manager?->id;
        $incomingManagerId = $validated['manager_id'] ?? null;
        // تحديد قائمة الموظفين الحالية والقادمة مع ترتيبها حتى تصبح المقارنة دقيقة.
        $currentEmployeeIds = $store->employees->pluck('id')->map(fn ($id) => (int) $id)->sort()->values()->all();
        $incomingEmployeeIds = collect($validated['employee_ids'] ?? [])->map(fn ($id) => (int) $id)->unique()->sort()->values()->all();

        // أي اختلاف في المدير أو الموظفين يعني أن التعيينات تغيرت.
        $assignmentsChanged =
            (int) ($currentManagerId ?? 0) !== (int) ($incomingManagerId ?? 0)
            || $currentEmployeeIds !== $incomingEmployeeIds;

        // اختلاف أي قيمة من core fields يعني أن بيانات الفرع تغيرت.
        $coreChanged = $currentCore !== $incomingCore;

        // إرجاع:
        // 1) هل core تغير؟
        // 2) هل assignments تغيرت؟
        // 3) المدير الحالي (للتوثيق والمقارنة)
        // 4) الموظفون الحاليون (للتوثيق والمقارنة)
        return [$coreChanged, $assignmentsChanged, $currentManagerId, $currentEmployeeIds];
    }

    /**
     * @return array<int, string>
     */
    private function deletionBlockers(Store $store): array
    {
        $blockers = [];

        if ($store->products()->exists()) {
            $blockers[] = 'products';
        }

        if (Schema::hasTable('store_warehouse') && DB::table('store_warehouse')->where('store_id', $store->id)->exists()) {
            $blockers[] = 'warehouses';
        }

        return $blockers;
    }

    private function resolveDepartmentId(Request $request, ?Store $store): ?int
    {
        if ($store && $store->department_id) {
            return (int) $store->department_id;
        }

        $storeManagementId = $this->resolveStoreManagementDepartmentId();
        if ($storeManagementId) {
            return $storeManagementId;
        }

        $user = $request->user();
        if (! $user) {
            return null;
        }

        return app(StoreScopeService::class)->resolveDepartmentId($user);
    }

    private function resolveStoreManagementDepartmentId(): ?int
    {
        if (! Schema::hasTable('departments')) {
            return null;
        }

        $id = Department::query()->where('slug', 'store_management')->value('id');
        return $id ? (int) $id : null;
    }
}