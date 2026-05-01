<?php // Name : Rodain Gouzlan Id:

namespace App\Http\Controllers\Stores;

/**
 * Controller: إدارة الأفرع (Stores/Branches) - واجهة الويب
 *
 * هذا الـ Controller مسؤول عن صفحات إدارة الأفرع ضمن الكتلة البرمجية الثالثة (Programming Block 3).
 *
 * ما الذي يقدمه للمستخدم:
 * - عرض صفحة الشبكة (Grid) للأفرع مع فلاتر بحث تعمل عبر AJAX (بدون إعادة تحميل).
 * - إنشاء فرع جديد (Create) وحفظه.
 * - تعديل فرع موجود (Edit/Update) مع دعم اكتشاف "لا يوجد تغييرات" لإظهار رسالة منطقية.
 * - حذف فرع (Delete) مع شروط منع الحذف عند وجود روابط (منتجات/مستودعات).
 * - عرض تفاصيل فرع (Show) مع العلاقات (المحافظة، المدير، الموظفون، المنتجات).
 * - عرض/تحميل البرشور (Brochure) الخاص بالفرع.
 *
 * كيف تم تنظيم المنطق:
 * - هذا الـ Controller متعمد أن يكون "خفيف"؛ معظم منطق الأعمال موجود داخل Services و Form Requests.
 * - الصلاحيات يتم التحقق منها عبر StorePolicy (authorize) مع استخدام Trait للمساعدات.
 * - العمليات الحساسة (إنشاء/تحديث) تتم عبر StoreCrudService داخل Transaction لضمان سلامة البيانات.
 */

// الاستيرادات (Imports) المطلوبة لهذا الـ Controller.
use App\Http\Controllers\Controller;
// Form Request مخصص للتحقق من بيانات إنشاء الفرع.
use App\Http\Requests\StoreRequest;
// Form Request مخصص للتحقق من بيانات تحديث الفرع.
use App\Http\Requests\UpdateStoreRequest;
// نموذج المحافظة (Province) لإظهار قائمة المحافظات في نموذج الفرع.
use App\Models\Province;
// نموذج الفرع/المتجر (Store) لربط المسارات بالـ Model (Route Model Binding).
use App\Models\Store;
use App\Models\User;
// خدمة إدارة ملفات البرشور (عرض/تحميل).
use App\Services\Store\StoreBrochureService;
use App\Services\Store\StoreBrochureUploadService;
// خدمة CRUD التي تنفذ create/update/delete داخل معاملات (Transactions).
use App\Services\Store\StoreCrudService;
// خدمة البحث عن الأفرع (قد تستخدم Stored Procedure أو Query محسّن).
use App\Services\Store\StoreSearchService;
// خدمة منطق الأعمال العام للأفرع (قيود الحذف، ثوابت الحالات...).
use App\Services\Store\StoreService;
// خدمة المستخدمين لتجهيز قوائم المديرين والموظفين في النماذج.
use App\Services\User\UserService;
// Trait يضم قواعد الصلاحيات الخاصة بوحدة إدارة الأفرع.
use App\Traits\StoreAuthorization;
use Illuminate\Http\JsonResponse;
// نوع الاستجابة عند التحويل (Redirect) بعد حفظ/تحديث/حذف.
use Illuminate\Http\RedirectResponse;
// كائن Request القياسي لالتقاط فلاتر البحث وطلبات AJAX.
use Illuminate\Http\Request;
// Auth لإحضار المستخدم الحالي (المسجل).
use Illuminate\Support\Facades\Auth;
 // Cache لتخزين نتائج متكررة (مثل قائمة المحافظات).
 use Illuminate\Support\Facades\Cache;
// Storage للوصول لملفات البرشور داخل disk=public.
use Illuminate\Support\Facades\Storage;
// Str لتنظيف/تحويل اسم الملف إلى ASCII كـ fallback.
use Illuminate\Support\Str;
 // نوع View لصفحات Blade.
 use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

// Controller الويب الرئيسي لإدارة الفروع (CRUD + بحث + بروشور + تعيينات).
class StoreController extends Controller
{
    use StoreAuthorization;

    public function __construct(
        // حقن (DI) خدمة البحث لاستخدامها في صفحة القائمة.
        private StoreSearchService $searchService,
        // حقن خدمة CRUD للتعامل مع create/update/delete بشكل موحد.
        private StoreCrudService $storeCrudService,
        // حقن خدمة البرشور لتحميل/عرض ملفات PDF.
        private StoreBrochureService $storeBrochureService,
        private StoreBrochureUploadService $brochureUploadService,
        // حقن خدمة StoreService للوصول لقواعد منطقية (مثل قيود الحذف).
        private StoreService $storeService,
        // حقن خدمة المستخدمين لتجهيز قوائم الموظفين/المديرين.
        private UserService $userService,
    ) {
        // لا يوجد منطق إضافي هنا؛ فقط حفظ الخدمات كـ properties لاستخدامها لاحقاً.
    }

    public function index(Request $request): View
    {
        // Authorization is handled via StorePolicy.
        $currentUser = Auth::user();
        $this->authorize('viewAny', Store::class);

        // استخراج فلاتر البحث من الطلب أو من session (لتذكر آخر فلاتر استخدمت).
        $filters = $this->resolveFilters($request);
        // تنفيذ البحث وإرجاع صفوف جاهزة للعرض في الشبكة (Grid).
        $stores = $this->searchService->searchStoreRows($filters);
        if (! $this->isAdmin($currentUser)) {
            $allowedIds = $this->allowedStores($currentUser)->pluck('id')->map(fn ($id) => (int) $id)->all();
            $stores = $stores
                ->filter(fn ($row) => in_array((int) ($row->id ?? 0), $allowedIds, true))
                ->values();
        }
        // جلب المحافظات مع تخزينها مؤقتاً لتقليل ضغط قاعدة البيانات.
        $provinces = Cache::remember(
            'store_form_provinces_v1',
            now()->addMinutes(10),
            fn () => Province::query()->select(['id', 'name', 'code'])->orderBy('name')->get()
        );
        // تجهيز خريطة (Map) لتحويل اسم المحافظة من العربية/المدخلة إلى اسم إنجليزي للعرض.
        $provinceNameToEnglish = $provinces
            ->mapWithKeys(fn (Province $province) => [
                (string) $province->name => \App\Support\EnglishPlaceNames::provinceByCode($province->code) ?: (string) $province->name,
            ])
            ->all();

        // إذا كان الطلب AJAX نرجّع فقط جزء الشبكة (Partial) لتحديث النتائج بدون إعادة تحميل الصفحة.
        if ($request->ajax()) {
            return view('stores.partials.grid', compact('stores', 'provinceNameToEnglish'));
        }

        // العرض الطبيعي: صفحة القائمة كاملة مع الفلاتر والبيانات اللازمة.
        return view('stores.index', [
            'stores' => $stores,
            'filters' => $filters,
            'provinces' => $provinces,
            'statuses' => StoreService::STORE_STATUSES,
            'provinceNameToEnglish' => $provinceNameToEnglish,
        ]);
    }

    public function create(): View
    {
        // Authorization is handled via StorePolicy.
        $this->authorize('create', Store::class);

        // تمرير كائن Store جديد فارغ + خيارات النموذج (محافظات/حالات/مديرين/موظفين).
        return view('stores.create', array_merge(
            ['store' => new Store()],
            $this->storeFormOptions()
        ));
    }

    public function store(StoreRequest $request): RedirectResponse
    {
        $currentUser = Auth::user();
        $this->authorize('create', Store::class);
        // استخراج البيانات التي تم التحقق منها عبر StoreRequest.
        $validated = $request->validated();
        // المستخدم الحالي (قد يستخدم لتحديد من هو منفذ العملية).
        // في حال كان Admin نرسل إشعارات عند نقل/تعيين أشخاص.
        $notifyAssignments = $this->isAdmin($currentUser);
        // حفظ رقم/معرّف المستخدم المنفذ للعملية لكتابة سجل التعديل (updated_by).
        $actorId = $currentUser?->id;

        // تنفيذ عملية الإنشاء عبر خدمة CRUD (تقوم أيضاً بالمزامنة ورفع البرشور إن وجد).
        $result = $this->storeCrudService->create(
            $request,
            $validated,
            $actorId,
            $notifyAssignments,
            false
        );

        // Reset stored filters so the fresh branch list is visible after create.
        session()->forget('store_filters');

        // بعد الحفظ نعيد المستخدم لصفحة القائمة مع رسالة نجاح.
        $successMessage = 'Branch saved successfully.';
        if (! $this->isAdmin($currentUser)) {
            $successMessage = 'Branch saved. It will appear in Branch Directory after the system administrator assigns staff.';
        }
        $redirect = redirect()
            ->to(route('stores.index', [], false))
            ->with('success', $successMessage);

        // إن تم نقل موظفين/مدير من فرع لآخر نمرر رسائل النقل لعرضها للمستخدم.
        if (($result['transfers'] ?? []) !== []) {
            $redirect->with('transfers', $result['transfers']);
        }

        // تنفيذ التحويل النهائي.
        return $redirect;
    }

    public function show(Store $store): View
    {
        // Authorization is handled via StorePolicy.
        $this->authorize('view', $store);

        // تحميل العلاقات (Eager Loading) لمنع مشكلة N+1 عند العرض.
        $store->load(['province', 'manager', 'employees', 'products', 'warehouses']);

        // عرض صفحة تفاصيل الفرع.
        return view('stores.show', compact('store'));
    }

    public function edit(Store $store): View
    {
        // Authorization is handled via StorePolicy.
        $this->authorize('update', $store);

        // تحميل علاقات إضافية لعرض معلومات آخر تحديث (createdBy/updatedBy) + التعيينات الحالية.
        $store->load(['manager', 'employees', 'createdBy', 'updatedBy']);

        // تمرير بيانات الفرع + خيارات النموذج (المحافظات/الحالات/المديرين/الموظفين).
        return view('stores.edit', array_merge(
            ['store' => $store],
            $this->storeFormOptions()
        ));
    }

    public function update(UpdateStoreRequest $request, Store $store): RedirectResponse
    {
        $currentUser = Auth::user();
        $this->authorize('update', $store);
        // استخراج بيانات التحقق الخاصة بالتحديث.
        $validated = $request->validated();
        // المستخدم الحالي لتحديد الصلاحيات ومن هو منفذ التعديل.
        // إرسال إشعارات النقل عند Admin فقط.
        $notifyAssignments = $this->isAdmin($currentUser);
        // معرّف المنفذ ليتم تخزينه في updated_by.
        $actorId = $currentUser?->id;

        // تنفيذ عملية التحديث عبر خدمة CRUD.
        $result = $this->storeCrudService->update(
            $store,
            $request,
            $validated,
            $actorId,
            $notifyAssignments,
            false
        );

        // إذا لم يحدث أي تغيير فعلي نعرض تحذير منطقي بدل رسالة نجاح.
        if (! ($result['changed'] ?? true)) {
            return back()->with('warning', 'No changes were made.');
        }

        // عند نجاح التحديث نرجع لصفحة القائمة مع رسالة نجاح.
        $redirect = redirect()
            ->to(route('stores.index', [], false))
            ->with('success', 'Changes saved successfully.');

        // تمرير رسائل النقل (إن وجدت) لعرضها للمستخدم.
        if (($result['transfers'] ?? []) !== []) {
            $redirect->with('transfers', $result['transfers']);
        }

        // تنفيذ التحويل النهائي.
        return $redirect;
    }

    public function destroy(Store $store): RedirectResponse
    {
        // Authorization is handled via StorePolicy.
        $this->authorize('delete', $store);

        // المستخدم غير Admin لا يستطيع حذف فرع غير مرتبط بحسابه (لتجنب حذف أفرع أخرى).
        try {
            // تنفيذ الحذف الفعلي عبر خدمة CRUD (تتحقق من قيود الحذف داخلياً).
            $this->storeCrudService->delete($store);
        } catch (\App\Exceptions\StoreDeletionBlockedException $e) {
            return back()->with('warning', $e->getMessage());
        }

        // بعد الحذف نعود لصفحة القائمة مع رسالة نجاح.
        return redirect()
            ->to(route('stores.index', [], false))
            ->with('success', 'Branch deleted successfully.');
    }

    public function assignments(Store $store): View
    {
        // Ø§Ù„Ù…Ø³ØªØ®Ø¯Ù… Ø§Ù„Ø­Ø§Ù„ÙŠ Ù„ØªØ­Ø¯ÙŠØ¯ Ø§Ù„ØµÙ„Ø§Ø­ÙŠØ§Øª ÙˆÙ…Ù† ÙŠØ³ØªØ·ÙŠØ¹ Ø±Ø¤ÙŠØ©/ØªØ¹Ø¯ÙŠÙ„ ØµÙØ­Ø© Ø§Ù„ØªØ¹ÙŠÙŠÙ†Ø§Øª.
        $currentUser = Auth::user();
        $this->authorize('manageStaff', $store);

        // Ø³Ø«Ø§Ø°Ù‡ Ø§Ù„ØµÙØ­Ø© Ù…Ø®ØµØµØ© Ù„Ù…Ø¯ÙŠØ± Ø§Ù„Ù†Ø¸Ø§Ù… Ø£Ùˆ Ù…Ø¯ÙŠØ± Ø§Ù„Ù‚Ø³Ù… ÙÙ‚Ø·.
        // Ø¥Ø°Ø§ ÙƒØ§Ù† Ø§Ù„Ù…Ø³ØªØ®Ø¯Ù… "Ù…Ø¯ÙŠØ± ÙØ±Ø¹" Ù…Ø«Ù„Ø§Ù‹ØŒ Ù†Ø¹Ø±Ø¶ ØµÙØ­Ø© Ù…Ù‚ÙŠØ¯Ø© Ø¨Ø¯Ù„ ØªÙ†ÙØ° Ø¹Ù…Ù„ÙŠØ§Øª Ø§Ù„ØªØ¹ÙŠÙŠÙ†.
        $this->abortUnlessStoreModuleUser($currentUser);

        $canAssign = $this->hasStorePermission($currentUser, 'assign_staff_to_store');
        $canManage = $this->hasStorePermission($currentUser, 'manage_store_staff');
        $isAdmin = $this->isAdmin($currentUser);

        if (! $isAdmin && ! $canAssign && ! $canManage) {
            return view('stores.assignments-restricted', [
                'store' => $store,
                'message' => 'You do not have permission to manage staff assignments.',
            ]);
        }

        if (! $this->canAccessStore($store, $currentUser)) {
            $message = $this->isStoreManager($currentUser)
                ? 'You can only manage staff for your assigned store.'
                : 'You can only manage staff within your department.';
            return view('stores.assignments-restricted', [
                'store' => $store,
                'message' => $message,
            ]);
        }

        if ($canManage && ! $isAdmin && $this->isStoreManager($currentUser) && (int) $store->manager_id !== (int) ($currentUser?->id)) {
            return view('stores.assignments-restricted', [
                'store' => $store,
                'message' => 'You can only manage staff for your assigned branch.',
            ]);
        }

        // ØªØ­Ù…ÙŠÙ„ Ø§Ù„Ù…Ø¯ÙŠØ± Ø§Ù„Ø­Ø§Ù„ÙŠ ÙˆØ§Ù„Ù…ÙˆØ¸ÙÙŠÙ† Ø§Ù„Ø­Ø§Ù„ÙŠÙŠÙ† Ù„Ù„ÙØ±Ø¹ Ø­ØªÙ‰ ØªØ¸Ù‡Ø± Ø§Ù„Ù‚ÙŠÙ… Ù…Ø­Ø¯Ø¯Ø© Ù…Ø³Ø¨Ù‚Ø§Ù‹ ÙÙŠ ÙˆØ§Ø¬Ù‡Ø© Ø§Ù„Ù€ select.
        $store->load([
            'manager:id,name',
            'employees:id,name,email,phone,role',
        ]);

        // Ø¨Ù†Ø§Ø¡ Ø§Ø³ØªØ¹Ù„Ø§Ù…Ø§Øª Ø§Ù„Ù‚ÙˆØ§Ø¦Ù… Ø§Ù„Ù…Ù†Ø³Ø¯Ù„Ø©:
        // - Ù‚Ø§Ø¦Ù…Ø© Ø§Ù„Ø£ÙØ±Ø¹
        // - Ù‚Ø§Ø¦Ù…Ø© Ø§Ù„Ù…Ø¯Ø±Ø§Ø¡ Ø§Ù„Ù…Ø­ØªÙ…Ù„ÙŠÙ†
        // - Ù‚Ø§Ø¦Ù…Ø© Ø§Ù„Ù…ÙˆØ¸ÙÙŠÙ† Ø§Ù„Ù…Ø­ØªÙ…Ù„ÙŠÙ† Ù…Ø¹ Ù…Ø¹Ù„ÙˆÙ…Ø§Øª Ø§Ù„ÙØ±Ø¹ Ø§Ù„Ø­Ø§Ù„ÙŠ Ù„ÙƒÙ„ Ù…ÙˆØ¸Ù
        $storesQuery = Store::query()
            ->select(['id', 'name', 'branch_code'])
            ->orderBy('name');
        if (! $isAdmin) {
            $allowedIds = $this->allowedStores($currentUser)->pluck('id')->map(fn ($id) => (int) $id)->all();
            $storesQuery->whereIn('id', $allowedIds ?: [0]);
        }
        $storeDepartmentId = (int) ($store->department_id ?? 0);
        if ($storeDepartmentId) {
            $managersQuery = User::eligibleManagers()
                ->select(['id', 'name', 'store_id'])
                ->where('department_id', $storeDepartmentId)
                ->orderBy('name');
            $employeesQuery = User::storeEmployees()
                ->select(['id', 'name', 'email', 'phone', 'role'])
                ->where('department_id', $storeDepartmentId)
                ->with(['stores:id,branch_code,name', 'assignedStore:id,branch_code,name'])
                ->orderBy('name');
        } else {
            $managersQuery = $this->userService->queryStoreManagers(['id', 'name', 'store_id']);
            $employeesQuery = $this->userService->queryStoreEmployees(['id', 'name', 'email', 'phone', 'role'])
                ->with(['stores:id,branch_code,name', 'assignedStore:id,branch_code,name'])
                ->orderBy('name');
        }
        $managersQuery->with(['managedStore:id,branch_code,name,manager_id', 'assignedStore:id,branch_code,name']);
        $employees = $employeesQuery->get();

        // ØªÙ…Ø±ÙŠØ± Ø§Ù„Ø¨ÙŠØ§Ù†Ø§Øª Ù„Ù„ÙˆØ§Ø¬Ù‡Ø© Ù„Ø¹Ø±Ø¶Ù‡Ø§ Ø¶Ù…Ù† ØµÙØ­Ø© stores.assignments.
        return view('stores.assignments', [
            'store' => $store,
            'stores' => $storesQuery->get(),
            'managers' => $managersQuery->get(),
            'employees' => $employees,
        ]);
    }

    public function updateAssignments(StoreRequest $request, Store $store)
    {
        // Ø§Ù„Ù…Ø³ØªØ®Ø¯Ù… Ø§Ù„Ø­Ø§Ù„ÙŠ (Ù…Ù†ÙØ° Ø§Ù„Ø¹Ù…Ù„ÙŠØ©) Ù„ØªØ­Ø¯ÙŠØ¯ Ø§Ù„ØµÙ„Ø§Ø­ÙŠØ§Øª ÙˆØ¥Ø¶Ø§ÙØ© Ù…Ø¹Ù„ÙˆÙ…Ø§Øª Ø§Ù„ØªØ¯Ù‚ÙŠÙ‚.
        $currentUser = Auth::user();
        $this->authorize('manageStaff', $store);
        $isAdmin = $this->isAdmin($currentUser);
        $canAssign = $this->hasStorePermission($currentUser, 'assign_staff_to_store');
        $canManage = $this->hasStorePermission($currentUser, 'manage_store_staff');
        // Ø¨ÙŠØ§Ù†Ø§Øª Ø§Ù„Ø·Ù„Ø¨ Ø¨Ø¹Ø¯ Ø§Ø¬ØªÙŠØ§Ø² Ù‚ÙˆØ§Ø¹Ø¯ Ø§Ù„ØªØ­Ù‚Ù‚ (Validation).
        $validated = $request->validated();

        // ØªØ­Ù…ÙŠÙ„ Ø§Ù„Ø¨ÙŠØ§Ù†Ø§Øª Ø§Ù„Ø­Ø§Ù„ÙŠØ© Ù„Ù„ÙØ±Ø¹ (Ù…Ø¯ÙŠØ±/Ù…ÙˆØ¸ÙÙŠÙ†) Ø­ØªÙ‰ Ù†ØªÙ…ÙƒÙ† Ù…Ù† Ù…Ù‚Ø§Ø±Ù†Ø© Ø§Ù„Ù‚Ø¯ÙŠÙ… Ø¨Ø§Ù„Ø¬Ø¯ÙŠØ¯.
        $store->loadMissing(['manager', 'employees']);

        // Ø§Ù„Ù‚ÙŠÙ… Ø§Ù„Ù…Ø·Ù„ÙˆØ¨Ø© Ø¨Ø¹Ø¯ Ø§Ù„ØªØ¹Ø¯ÙŠÙ„ (Ù‚Ø¯ ØªÙƒÙˆÙ† null/[] Ø­Ø³Ø¨ Ù…Ø§ Ø£Ø±Ø³Ù„Ù‡ Ø§Ù„Ù…Ø³ØªØ®Ø¯Ù…).
        $managerId = $validated['manager_id'] ?? null;
        $employeeIds = $validated['employee_ids'] ?? [];
        $removedEmployeeIds = $validated['removed_employee_ids'] ?? [];

        if ($canManage && ! $isAdmin) {
            $managerId = $store->manager_id;
        }

        // Ù‚ÙŠÙˆØ¯ Ù…Ø¯ÙŠØ± Ø§Ù„Ù‚Ø³Ù… (ØºÙŠØ± admin):
        // - Ù„Ø§ ÙŠØ³Ù…Ø­ Ø¨ØªØºÙŠÙŠØ± Ø§Ù„Ù…Ø¯ÙŠØ±
        // - ÙŠØ³Ù…Ø­ ÙÙ‚Ø· Ø¨Ø¥Ø¶Ø§ÙØ©/Ø¥Ø²Ø§Ù„Ø© Ù…ÙˆØ¸ÙÙŠÙ† Ø¶Ù…Ù† Ù‚Ø§Ø¦Ù…Ø© Ø§Ù„Ù…ÙˆØ¸ÙÙŠÙ† Ø§Ù„Ø³Ù…ÙˆØ­ÙŠÙ† ÙˆØ¨Ù†Ø§Ø¡Ù‹ Ø¹Ù„Ù‰ Ø§Ù„ÙØ±Ø¹ Ø§Ù„Ø­Ø§Ù„ÙŠ
        if (($canAssign || $canManage) && ! $isAdmin) {
            // Ø§Ù„Ù…ÙˆØ¸ÙÙˆÙ† Ø§Ù„Ø­Ø§Ù„ÙŠÙˆÙ† ÙÙŠ Ø§Ù„ÙØ±Ø¹.
            $currentIds = $store->employees->pluck('id')->map(fn ($id) => (int) $id)->values()->all();
            // Ø¬Ù…ÙŠØ¹ Ø§Ù„Ù…ÙˆØ¸ÙÙŠÙ† Ø§Ù„Ù…Ø³Ù…ÙˆØ­ÙŠÙ† (Ø¨Ø­Ø§Ù„Ø© ÙØ¹Ø§Ù„Ø©).
            $allowedEmployees = $currentIds;
            if (! $canManage) {
                $allowedEmployees = User::storeEmployees()
                    ->pluck('id')
                    ->map(fn ($id) => (int) $id)
                    ->all();
            } else {
                $storeDepartmentId = (int) ($store->department_id ?? 0);
                $allowedEmployees = User::storeEmployees()
                    ->when($storeDepartmentId, fn ($query) => $query->where('department_id', $storeDepartmentId))
                    ->pluck('id')
                    ->map(fn ($id) => (int) $id)
                    ->all();
            }

            // ÙÙ„ØªØ±Ø© Ø§Ù„Ù…ÙˆØ¸ÙÙŠÙ† Ø§Ù„Ù…ÙØ±Ø³Ù„ÙŠÙ† Ø¨Ø­ÙŠØ« Ù„Ø§ Ù†Ù‚Ø¨Ù„ Ø¥Ù„Ø§ Ù…ÙˆØ¸ÙÙŠÙ† Ù…Ø³Ù…ÙˆØ­ÙŠÙ†.
            $employeeIds = array_values(array_filter(array_map('intval', $employeeIds), function ($id) use ($allowedEmployees) {
                return in_array($id, $allowedEmployees, true);
            }));

            // ÙÙ„ØªØ±Ø© Ù‚Ø§Ø¦Ù…Ø© Ø§Ù„Ø¥Ø²Ø§Ù„Ø© Ø¨Ø­ÙŠØ« Ù„Ø§ Ù†Ø²ÙŠÙ„ Ø¥Ù„Ø§ Ù…ÙˆØ¸ÙÙŠÙ† Ù…ÙˆØ¬ÙˆØ¯ÙŠÙ† Ø£Ø³Ø§Ø³Ø§Ù‹ ÙÙŠ Ù‡Ø°Ø§ Ø§Ù„ÙØ±Ø¹.
            $removedEmployeeIds = array_values(array_filter(array_map('intval', $removedEmployeeIds), function ($id) use ($currentIds) {
                return in_array($id, $currentIds, true);
            }));

            // Ø¯Ù…Ø¬ Ø§Ù„Ø¥Ø¶Ø§ÙØ§Øª Ù…Ø¹ Ø§Ù„Ø­Ø§Ù„ÙŠ Ø«Ù… ØªØ·Ø¨ÙŠÙ‚ Ø§Ù„Ø¥Ø²Ø§Ù„Ø© (Ø¥Ø°Ø§ ØªÙ… ØªØ­Ø¯ÙŠØ¯Ù‡Ø§).
            $employeeIds = array_values(array_unique(array_merge($currentIds, $employeeIds)));
            if ($removedEmployeeIds !== []) {
                $employeeIds = array_values(array_diff($employeeIds, $removedEmployeeIds));
            }
        }

        // ØªÙ†ÙÙŠØ° Ø§Ù„ØªØ¹ÙŠÙŠÙ†Ø§Øª ÙØ¹Ù„ÙŠØ§Ù‹ Ø¹Ù„Ù‰ pivot store_user (Ù‚Ø¯ ÙŠÙ†ØªØ¬ Ø¹Ù†Ù‡ Ù†Ù‚Ù„ Ù…ÙˆØ¸ÙÙŠÙ† Ù…Ù† Ø£ÙØ±Ø¹ Ø£Ø®Ø±Ù‰).
        $transferMessages = $this->storeService->syncAssignments($store, $managerId, $employeeIds);
        // ØªØ­Ù…ÙŠÙ„ Ø¨ÙŠØ§Ù†Ø§Øª Ø§Ù„ØªØ¯Ù‚ÙŠÙ‚ Ù„Ù„ÙØ±Ø¹ (updated_at/updated_by).
        $this->storeService->touchStore($store, $currentUser?->id);
        // ØªÙØ±ÙŠØº ÙƒØ§Ø´ Ø§Ù„Ø£ÙØ±Ø¹ Ø­ØªÙ‰ ØªØ¸Ù‡Ø± Ø§Ù„Ù†ØªØ§Ø¦Ø¬ ÙÙˆØ±Ø§Ù‹ ÙÙŠ Ø§Ù„ÙˆØ§Ø¬Ù‡Ø©.
        $this->storeService->flushStoreCaches();

        // redirect Ø§ÙØªØ±Ø§Ø¶ÙŠ Ù„ØµÙØ­Ø© Ø§Ù„ØªØ¹ÙŠÙŠÙ†Ø§Øª Ù…Ø¹ Ø±Ø³Ø§Ù„Ø© Ù†Ø¬Ø§Ø­.
        $redirect = redirect()
            ->to(route('stores.assignments', $store, false))
            ->with('success', 'Staff assignments saved successfully.');

        // ØªÙ…Ø±ÙŠØ± Ø±Ø³Ø§Ø¦Ù„ Ø§Ù„Ù†Ù‚Ù„ Ù„Ù„ÙˆØ§Ø¬Ù‡Ø© Ù„Ø¹Ø±Ø¶Ù‡Ø§ ÙƒØªÙ„Ù…ÙŠØ­Ø§Øª Ù„Ù„Ù…Ø³ØªØ®Ø¯Ù….
        if ($transferMessages !== []) {
            $redirect->with('transfers', $transferMessages);
        }

        // Ø¥Ø°Ø§ ÙƒØ§Ù†Øª Ø§Ù„ÙˆØ§Ø¬Ù‡Ø© ØªØªÙˆÙ‚Ø¹ JSON Ù†Ø¹ÙŠØ¯Ù‡ Ø¨Ù†ÙØ³ Ø§Ù„Ù…Ø¹Ø·ÙŠØ§Øª.
        if ($request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'message' => 'Staff assignments saved successfully.',
                'transfers' => $transferMessages,
            ]);
        }

        // Ø§Ù„Ø±Ø¯ Ø§Ù„Ø§ÙØªØ±Ø§Ø¶ÙŠ: redirect.
        return $redirect;
    }

    public function uploadChunk(StoreRequest $request): JsonResponse
    {
        $result = $this->brochureUploadService->uploadChunk(
            (string) $request->input('upload_id'),
            (int) $request->input('chunk_index'),
            (int) $request->input('total_chunks'),
            (string) $request->input('file_name'),
            $request->file('chunk')
        );

        return response()->json($result);
    }

    public function downloadBrochure(Store $store)
    {
        // Authorization is handled via StorePolicy.
        $this->authorize('view', $store);

        // استخدام خدمة البرشور لإرجاع Response تحميل الملف (Storage download).
        return $this->storeBrochureService->download($store);
    }

    public function viewBrochure(Store $store)
    {
        // Authorization is handled via StorePolicy.
        $this->authorize('view', $store);

        // إذا كان هناك ملف برشور مرفوع فعلياً، نعرضه داخل Viewer داخل النظام.
        $uploadedPath = (string) ($store->brochure_path ?? '');
        if ($uploadedPath !== '' && Storage::disk('public')->exists($uploadedPath)) {
            $v = $store->updated_at?->timestamp ?? time();

            return response()
                ->view('stores.brochure-file', [
                    'store' => $store,
                    // نضيف v لكسر كاش المتصفح عند استبدال الملف.
                    'inlineUrl' => route('stores.brochure.inline', $store, false).'?v='.$v,
                    'downloadUrl' => route('stores.brochure.download', $store, false).'?v='.$v,
                    'fileName' => basename($uploadedPath),
                ])
                ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
                ->header('Pragma', 'no-cache');
        }

        // إن لم يوجد ملف مرفوع، نعرض رسالة اعتذار واضحة.
        return response()
            ->view('stores.brochure-missing', [
                'store' => $store,
                'backUrl' => route('stores.index', [], false),
            ])
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache');
    }

    public function inlineBrochure(Store $store): BinaryFileResponse
    {
        // Authorization is handled via StorePolicy.
        $this->authorize('view', $store);

        $uploadedPath = (string) ($store->brochure_path ?? '');
        if ($uploadedPath === '' || ! Storage::disk('public')->exists($uploadedPath)) {
            abort(404);
        }

        $localPath = storage_path('app/public/'.ltrim($uploadedPath, '/\\'));

        // تجهيز اسم ملف لطيف للعرض داخل المتصفح.
        $branchName = trim((string) ($store->name ?? 'Branch'));
        $cleanName = preg_replace('/[^\\p{L}\\p{N}\\s\\-_().]/u', '', $branchName) ?: 'Branch';
        $fileName = $cleanName.' Brochure.pdf';
        $fallbackName = Str::ascii($fileName) ?: 'brochure.pdf';

        $response = new BinaryFileResponse($localPath);
        $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_INLINE, $fileName, $fallbackName);
        $response->headers->set('Content-Type', 'application/pdf');

        // منع كاش المتصفح حتى لا يعرض نسخة قديمة بعد استبدال الملف.
        $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
        $response->headers->set('Pragma', 'no-cache');

        return $response;
    }

    /**
     * @return array{provinces: \Illuminate\Support\Collection, statuses: array, managers: \Illuminate\Support\Collection, employees: \Illuminate\Support\Collection}
     */
    private function storeFormOptions(): array
    {
        // تجهيز بيانات القوائم المنسدلة في نموذج إنشاء/تعديل الفرع مع استخدام Cache لتحسين الأداء.
        return [
            // قائمة المحافظات (id/name/code) للـ select.
            'provinces' => Cache::remember(
                'store_form_provinces_v1',
                now()->addMinutes(10),
                fn () => Province::query()->select(['id', 'name', 'code'])->orderBy('name')->get()
            ),
            // قائمة الحالات المتاحة للفرع (ثابتة في StoreService).
            'statuses' => StoreService::STORE_STATUSES,
            // قائمة المدراء المحتملين لتعيينهم كمدير فرع.
            'managers' => Cache::remember(
                'store_form_managers_v1',
                now()->addSeconds(30),
                fn () => $this->userService->storeManagers(['id', 'name'])
            ),
            // قائمة الموظفين المحتملين لتعيينهم ضمن الفرع.
            'employees' => Cache::remember(
                'store_form_employees_v1',
                now()->addSeconds(30),
                fn () => $this->userService->storeEmployees(['id', 'name'])
            ),
        ];
    }

    private function resolveFilters(Request $request): array
    {
        // بناء مصفوفة الفلاتر من request (أو من session كقيمة سابقة) مع trimming للنصوص.
        $filters = [
            // فلتر اسم الفرع.
            'name' => trim((string) $request->input('name', session('store_filters.name', ''))),
            // فلتر الحالة (active/inactive/...).
            'status' => (string) $request->input('status', session('store_filters.status', '')),
            // فلتر المحافظة.
            'province_id' => $request->input('province_id', session('store_filters.province_id')),
            // فلتر رقم الهاتف.
            'phone' => trim((string) $request->input('phone', session('store_filters.phone', ''))),
        ];

        // حفظ الفلاتر في session ليتم تذكرها عند العودة للصفحة أو عند تحديثها عبر AJAX.
        session(['store_filters' => $filters]);

        // إعادة المصفوفة للـ index لاستخدامها في البحث وإعادة عرض الفلاتر.
        return $filters;
    }
}