<?php // Name : Rodain Gouzlan Id:

namespace App\Services\Access;

// خدمة تحديد نطاق الوصول للفروع (Store Scope).
// الهدف: توحيد منطق من يستطيع رؤية/الوصول إلى فرع معين حسب الدور أو القسم.
use App\Models\Department;
use App\Models\Store;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Service: StoreScopeService
 *
 * يستخدم لتقييد الاستعلامات والواجهات حسب صلاحيات المستخدم:
 * - Admin يرى كل الفروع
 * - مدير/موظف فرع يرى فروعه فقط
 * - مستخدم قسم (Department) يرى فروع قسمه
 */
class StoreScopeService
{
    private static array $resolvedStoreIds = [];
    private static array $resolvedDepartmentIds = [];

    private static ?bool $departmentsTableAvailable = null;
    private static ?bool $storesCreatedByColumnAvailable = null;
    // التحقق إن كان المستخدم يستطيع الوصول إلى فرع محدد.
    /**
     * يحدد إن كان المستخدم يملك صلاحية الوصول إلى فرع محدد.
     * يعتمد القرار على الدور (admin) أو نطاق الفرع أو نطاق القسم.
     */
    public function canAccessStore(?User $user, Store $store): bool
    {
        // لا مستخدم = لا وصول.
        if (! $user) {
            return false;
        }

        // المدير العام يرى جميع الفروع.
        if ($user->hasRole('admin')) {
            return true;
        }

        // المستخدم ضمن نطاق الفروع (مدير/موظف) -> تحقق من قائمة فروعه.
        if ($this->isStoreScoped($user)) {
            $storeIds = $this->resolveStoreIds($user);
            return $storeIds !== [] && in_array((int) $store->id, $storeIds, true);
        }

        // المستخدم ضمن نطاق قسم -> تحقق من قسم الفرع.
        $departmentId = $this->resolveDepartmentId($user);
        if (! $departmentId) {
            return false;
        }

        return (int) ($store->department_id ?? 0) === (int) $departmentId;
    }

    // تطبيق نطاق الوصول مباشرة على استعلام الفروع.
    /**
     * يطبق نطاق الوصول على استعلام الفروع مباشرة.
     */
    public function scopeStoreQuery(Builder $query, User $user): Builder
    {
        // admin: لا فلترة.
        if ($user->hasRole('admin')) {
            return $query;
        }

        // مدير/موظف فرع: فلترة على فروعه فقط.
        if ($this->isStoreScoped($user)) {
            $storeIds = $this->resolveStoreIds($user);
            return $storeIds === [] ? $query->whereRaw('1 = 0') : $query->whereIn('id', $storeIds);
        }

        // مستخدم قسم: فلترة على القسم.
        $departmentId = $this->resolveDepartmentId($user);
        if (! $departmentId) {
            return $query->whereRaw('1 = 0');
        }

        return $query->where('department_id', $departmentId);
    }

    // إرجاع قائمة الفروع المسموح بها للمستخدم.
    /**
     * يرجع الفروع المسموح للمستخدم التعامل معها (لعرضها في الواجهات).
     */
    public function allowedStores(?User $user): Collection
    {
        if (! $user) {
            return collect();
        }

        $version = (int) Cache::get('store_search_version', 1);
        $scopeKey = $user->hasRole('admin') ? 'admin' : 'user:'.(int) $user->id;
        $cacheKey = 'store_scope_allowed_stores:v2:'.$version.':'.$scopeKey;

        return Cache::remember($cacheKey, now()->addSeconds(90), fn () => $this->allowedStoresUncached($user));
    }

    private function allowedStoresUncached(?User $user): Collection
    {
        // لا مستخدم = لا فروع.
        if (! $user) {
            return collect();
        }

        // admin: جميع الفروع.
        if ($user->hasRole('admin')) {
            return Store::query()
                ->select(['id', 'name', 'branch_code'])
                ->orderBy('name')
                ->get();
        }

        // مدير/موظف فرع: فروعه فقط.
        if ($this->isStoreScoped($user)) {
            $storeIds = $this->resolveStoreIds($user);
            return Store::query()
                ->select(['id', 'name', 'branch_code'])
                ->when($storeIds !== [], fn ($query) => $query->whereIn('id', $storeIds))
                ->when($storeIds === [], fn ($query) => $query->whereRaw('1 = 0'))
                ->orderBy('name')
                ->get();
        }

        // مستخدم قسم: فروع القسم فقط.
        $departmentId = $this->resolveDepartmentId($user);
        if (! $departmentId) {
            return collect();
        }

        return Store::query()
            ->select(['id', 'name', 'branch_code'])
            ->where('department_id', $departmentId)
            ->orderBy('name')
            ->get();
    }

    // تحديد قسم المستخدم (department) بالاعتماد على ID أو slug النصي.
    /**
     * يحول قسم المستخدم إلى معرّف department_id.
     * يبحث أولاً عن العمود المباشر ثم عن slug النصي.
     */
    public function resolveDepartmentId(User $user): ?int
    {
        if ($user->department_id) {
            return (int) $user->department_id;
        }

        $slug = trim((string) $user->department);
        if ($slug === '') {
            return $this->resolveDepartmentIdUncached($user);
        }

        if (array_key_exists($slug, self::$resolvedDepartmentIds)) {
            return self::$resolvedDepartmentIds[$slug];
        }

        $id = Cache::remember(
            'department_slug_to_id:v2:'.sha1($slug),
            now()->addMinutes(30),
            fn () => $this->resolveDepartmentIdUncached($user)
        );

        $resolved = $id ? (int) $id : null;
        self::$resolvedDepartmentIds[$slug] = $resolved;

        return $resolved;
    }

    private function resolveDepartmentIdUncached(User $user): ?int
    {
        // حماية عند غياب جدول الأقسام.
        if (! Schema::hasTable('departments')) {
            return null;
        }

        // استخدام department_id إن كان موجوداً.
        if ($user->department_id) {
            return (int) $user->department_id;
        }

        // fallback إلى slug النصي.
        $slug = trim((string) $user->department);
        if ($slug === '') {
            return null;
        }

        $id = Department::query()->where('slug', $slug)->value('id');
        return $id ? (int) $id : null;
    }

    // استخراج معرف فرع المدير (حسب store_id أو manager_id).
    /**
     * يستخرج فرع المدير إما من store_id أو من جدول stores.
     */
    private function resolveManagerStoreId(User $user): ?int
    {
        // store_id المباشر أولاً.
        if ($user->store_id) {
            return (int) $user->store_id;
        }

        // بحث عبر manager_id في جدول stores.
        $storeId = Store::query()->where('manager_id', $user->id)->value('id');
        return $storeId ? (int) $storeId : null;
    }

    // حساب قائمة معرفات الفروع المرتبطة بالمستخدم (مدير/موظف).
    /**
     * يحسب جميع فروع المستخدم (مدير/موظف).
     */
    private function resolveStoreIds(User $user): array
    {
        $version = (int) Cache::get('store_search_version', 1);
        $cacheKey = 'store_scope_store_ids:v2:'.$version.':user:'.(int) $user->id;

        if (array_key_exists($cacheKey, self::$resolvedStoreIds)) {
            return self::$resolvedStoreIds[$cacheKey];
        }

        $ids = Cache::remember(
            $cacheKey,
            now()->addSeconds(90),
            fn () => $this->resolveStoreIdsUncached($user)
        );

        $normalized = array_values(array_unique(array_map(fn ($id) => (int) $id, (array) $ids)));
        self::$resolvedStoreIds[$cacheKey] = $normalized;

        return $normalized;
    }

    private function resolveStoreIdsUncached(User $user): array
    {
        // مدير الفرع: فرع واحد غالباً.
        if ($user->hasRole('store_manager')) {
            $storeId = $this->resolveManagerStoreId($user);
            return $storeId ? [(int) $storeId] : [];
        }

        // موظف فرع: قد يملك عدة روابط.
        if ($user->hasRole('store_employee') || $user->hasRole('employee')) {
            $ids = [];

            // store_id المباشر.
            if ($user->store_id) {
                $ids[] = (int) $user->store_id;
            }

            // روابط pivot store_user.
            $pivotIds = DB::table('store_user')
                ->where('user_id', $user->id)
                ->pluck('store_id')
                ->map(fn ($id) => (int) $id)
                ->values()
                ->all();

            $ids = array_merge($ids, $pivotIds);

            // فروع أنشأها المستخدم (إن كان العمود موجوداً).
            if (Schema::hasColumn('stores', 'created_by')) {
                $createdIds = Store::query()
                    ->where('created_by', $user->id)
                    ->pluck('id')
                    ->map(fn ($id) => (int) $id)
                    ->values()
                    ->all();
                $ids = array_merge($ids, $createdIds);
            }

            return array_values(array_unique($ids));
        }

        return [];
    }

    // هل المستخدم ضمن نطاق الفروع (Manager/Employee)؟
    /**
     * هل المستخدم محسوب ضمن نطاق الفروع (مدير/موظف)؟
     */
    private function isStoreScoped(User $user): bool
    {
        return $user->hasRole('store_manager')
            || $user->hasRole('store_employee')
            || $user->hasRole('employee');
    }
}

// Summary: خدمة تحدد نطاق رؤية الفروع للمستخدم وتطبّق هذا النطاق في الاستعلامات والقوائم.
