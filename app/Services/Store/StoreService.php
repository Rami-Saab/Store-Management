<?php // Name : Rodain Gouzlan Id:

namespace App\Services\Store;

/**
 * Service: منطق الأعمال الخاص بالأفرع (Stores Domain Service)
 *
 * هذا الملف يعتبر "قلب" منطق إدارة الفرع، ويُستخدم من عدة Controllers/Services.
 *
 * مسؤولياته الأساسية:
 * - استخراج بيانات الفرع من Request بعد التحقق (extractStoreData) مع دعم رفع البرشور PDF.
 * - مزامنة تعيينات الموظفين والمدير عبر pivot table store_user (syncAssignments).
 * - تنسيق ساعات العمل إلى صيغة قراءة سهلة (formatWorkingHours).
 * - تحديث updated_by + updated_at بشكل صحيح (touchStore).
 * - تفريغ الكاش المتعلق بالأفرع حتى تظهر النتائج الجديدة فوراً (flushStoreCaches).
 *
 * ملاحظة:
 * - تم استخدام Eloquent + DB Facade عند الحاجة لعمليات bulk / أداء أفضل.
 */

// الاستيرادات اللازمة للتعامل مع نموذج الفرع والمستخدم + خدمات مساعدة (Phone/Storage/DB...).
use App\Models\Store;
use App\Models\User;
use App\Support\UserContact;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class StoreService
{
    // القيم المسموح بها لحالة الفرع (تستخدم في الـ validation وفي واجهة المستخدم).
    public const STORE_STATUSES = [
        'active',
        'inactive',
        'under_maintenance',
    ];

    public function extractStoreData(Request $request, array $validated, ?Store $store = null): array
    {
        // بناء مصفوفة البيانات التي سيتم حفظها في جدول stores اعتماداً على البيانات التي تم التحقق منها.
        // عند التحديث: بعض الحقول قد لا تُرسل دائماً، لذلك نستخدم قيم الفرع الحالية كـ fallback عند الحاجة.
        $data = [
            // اسم الفرع (Branch name).
            'name' => $validated['name'],
            // كود الفرع (Unique).
            'branch_code' => $validated['branch_code'],
            // المحافظة (FK).
            'province_id' => $validated['province_id'],
            // المدينة: قد تكون اختيارية في بعض السيناريوهات، لذلك نستخدم قيمة الفرع الحالية إن لم تُرسل.
            'city' => $validated['city'] ?? ($store?->city ?? ''),
            // العنوان التفصيلي.
            'address' => $validated['address'],
            // الهاتف: يتم تطبيعه (Normalization) إلى صيغة قياسية (مثلاً ضمان 09..).
            'phone' => UserContact::phone($validated['phone'] ?? null, false),
            // وصف الفرع (اختياري).
            'description' => $validated['description'] ?? ($store?->description ?? null),
            // البريد الإلكتروني (اختياري).
            'email' => $validated['email'] ?? null,
            // حالة الفرع.
            'status' => $validated['status'],
            // نص جاهز للعرض عن ساعات العمل (مثل "From 09:00 AM to 10:00 PM").
            'working_hours' => $this->formatWorkingHours(
                $validated['workday_starts_at'] ?? null,
                $validated['workday_ends_at'] ?? null
            ),
            // وقت بداية الدوام (يُخزن كـ time).
            'workday_starts_at' => $validated['workday_starts_at'] ?? null,
            // وقت نهاية الدوام (يُخزن كـ time).
            'workday_ends_at' => $validated['workday_ends_at'] ?? null,
            // تاريخ افتتاح الفرع.
            'opening_date' => $validated['opening_date'],
        ];

        // معالجة رفع ملف البرشور (Brochure PDF) إن تم إرساله.
        if ($request->hasFile('brochure')) {
            // عند التحديث: إذا كان هناك ملف قديم، نحذفه أولاً لتجنب تراكم الملفات.
            if ($store?->brochure_path) {
                Storage::disk('public')->delete($store->brochure_path);
            }

            // حفظ الملف داخل disk = public حتى يمكن الوصول له عبر /storage.
            $data['brochure_path'] = $request->file('brochure')->store('brochures/stores', 'public');
        }

        // دعم رفع البرشور على شكل chunks عبر AJAX:
        // في هذه الحالة لا يصل الملف ضمن نفس طلب الحفظ (لتجنب 413)، بل يصل فقط مسار ملف تم تجميعه مسبقاً.
        $chunkedPath = trim((string) ($validated['brochure_path'] ?? $request->input('brochure_path', '')));
        if ($chunkedPath !== '' && Storage::disk('public')->exists($chunkedPath)) {
            // عند التحديث: إذا اختلف المسار عن الحالي، نحذف القديم ونستبدله بالجديد.
            if ($store?->brochure_path && $store->brochure_path !== $chunkedPath) {
                Storage::disk('public')->delete($store->brochure_path);
            }

            $data['brochure_path'] = $chunkedPath;
        }

        // إعادة بيانات الحفظ النهائية.
        return $data;
    }

    public function syncAssignments(Store $store, ?int $managerId, array $employeeIds): array
    {
        $transferMessages = [];

        $storeDepartmentId = (int) ($store->department_id ?? 0);
        $managerId = $managerId ? (int) $managerId : null;
        $employeeIds = collect($employeeIds)
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->unique()
            ->values()
            ->all();

        if ($managerId) {
            $employeeIds = array_values(array_diff($employeeIds, [$managerId]));
        }

        if ($managerId && $storeDepartmentId) {
            $managerDepartmentId = (int) (DB::table('users')->where('id', $managerId)->value('department_id') ?? 0);
            if ($managerDepartmentId && $managerDepartmentId !== $storeDepartmentId) {
                $managerId = null;
            }
        }

        if ($managerId && (int) $store->manager_id !== $managerId) {
            $previousManagedStore = Store::query()
                ->where('manager_id', $managerId)
                ->where('id', '!=', $store->id)
                ->first();

                if ($previousManagedStore) {
                    $managerName = User::query()->where('id', $managerId)->value('name') ?: 'Manager';
                    $fromStoreName = $previousManagedStore->name ?: ('Store #'.$previousManagedStore->id);
                    $toStoreName = $store->name ?: ('Store #'.$store->id);
                    $transferMessages[] = "Transferred Manager {$managerName} from branch {$fromStoreName} to branch {$toStoreName}.";
                    $previousManagedStore->update(['manager_id' => null]);
                    User::query()->where('id', $managerId)->update(['store_id' => null]);
                }

            $store->manager_id = $managerId;
            $store->save();
            User::query()->where('id', $managerId)->update(['store_id' => $store->id]);
        } elseif (! $managerId && $store->manager_id) {
            $previousManagerId = (int) $store->manager_id;
            $store->manager_id = null;
            $store->save();
            User::query()->where('id', $previousManagerId)->update(['store_id' => null]);
        }

        if ($employeeIds !== []) {
            if ($storeDepartmentId) {
                $employeeIds = DB::table('users')
                    ->whereIn('id', $employeeIds)
                    ->where('department_id', $storeDepartmentId)
                    ->pluck('id')
                    ->map(fn ($id) => (int) $id)
                    ->values()
                    ->all();
            }
            $previousAssignments = DB::table('store_user')
                ->join('stores', 'stores.id', '=', 'store_user.store_id')
                ->whereIn('store_user.user_id', $employeeIds)
                ->where('store_user.store_id', '!=', $store->id)
                ->select([
                    'store_user.user_id',
                    'store_user.store_id',
                    'stores.name as from_store_name',
                ])
                ->get()
                ->groupBy('user_id')
                ->map(fn ($rows) => $rows->first());

            DB::table('store_user')
                ->whereIn('user_id', $employeeIds)
                ->where('store_id', '!=', $store->id)
                ->delete();

            if ($previousAssignments->isNotEmpty()) {
                $userNames = User::query()
                    ->whereIn('id', $previousAssignments->keys()->all())
                    ->pluck('name', 'id');

                foreach ($previousAssignments as $userId => $row) {
                    $userName = $userNames[$userId] ?? ('User #'.$userId);
                    $fromStoreName = $row->from_store_name ?? ('Store #'.$row->store_id);
                    $toStoreName = $store->name ?: ('Store #'.$store->id);
                    $transferMessages[] = "Transferred Employee {$userName} from branch {$fromStoreName} to branch {$toStoreName}.";
                }
            }
        }

        $store->employees()->sync($employeeIds);

        return $transferMessages;
    }

    public function formatWorkingHours(?string $startsAt, ?string $endsAt): ?string
    {
        // إذا لم يوجد وقت بداية ولا نهاية، لا نعرض ساعات عمل (null).
        if (! $startsAt && ! $endsAt) {
            return null;
        }

        // دالة مساعدة لتحويل "H:i" إلى صيغة 12 ساعة "h:i A".
        $format = static function (?string $value): ?string {
            if (! $value) {
                return null;
            }
            // نأخذ HH:MM فقط (تجاهل الثواني إن وجدت).
            $value = substr((string) $value, 0, 5);
            try {
                return \Carbon\Carbon::createFromFormat('H:i', $value)->format('h:i A');
            } catch (\Throwable $e) {
                // في حال فشل التحويل لأي سبب (قيمة غير متوقعة)، نعيد القيمة كما هي بدل كسر النظام.
                return $value;
            }
        };

        // تنسيق القيم للعرض.
        $startLabel = $format($startsAt);
        $endLabel = $format($endsAt);

        // إذا توفر الوقتين نعيد صيغة "From ... to ...".
        if ($startLabel && $endLabel) {
            return "From {$startLabel} to {$endLabel}";
        }

        // إذا توفر وقت واحد فقط نعيده كما هو.
        return $startLabel ?: $endLabel;
    }

    public function touchStore(Store $store, ?int $actorId = null): void
    {
        // إذا كان لدينا عمود updated_by ونملك actorId، نحدثه مع تحديث updated_at (save()).
        if ($actorId && Schema::hasColumn('stores', 'updated_by')) {
            $store->forceFill(['updated_by' => $actorId])->save();
            return;
        }

        // fallback: تحديث updated_at فقط.
        $store->touch();
    }

    public function flushStoreCaches(): void
    {
        // ترقية نسخة كاش الداشبورد حتى تتجدد كل الإحصائيات فوراً لكل المستخدمين.
        Cache::forever('store_stats_version', (int) Cache::get('store_stats_version', 1) + 1);
        // ترقية نسخة كاش المنتجات المرتبطة بالأفرع حتى تظهر التعديلات بسرعة.
        Cache::forever('product_store_version', (int) Cache::get('product_store_version', 1) + 1);
        // مسح كاش القوائم المنسدلة في نموذج الفرع.
        Cache::forget('store_form_provinces_v1');
        Cache::forget('store_form_managers_v1');
        Cache::forget('store_form_employees_v1');
        // زيادة إصدار البحث لتوليد مفاتيح Cache جديدة تلقائياً (Cache busting).
        Cache::forever('store_search_version', (int) Cache::get('store_search_version', 1) + 1);
    }

}