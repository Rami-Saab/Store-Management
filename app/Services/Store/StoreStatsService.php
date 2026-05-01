<?php // Name : Rodain Gouzlan Id:

namespace App\Services\Store;

use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use App\Services\Access\StoreScopeService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class StoreStatsService
{
    private static ?bool $assignmentRoleColumnAvailable = null;

    public function dashboardData(?User $user = null, bool $ignoreScope = false): array
    {
        $scopeService = app(StoreScopeService::class);
        $scopedStoreIds = null;

        if (! $ignoreScope && $user && ! $user->hasRole('admin')) {
            $scopedStoreIds = $scopeService
                ->allowedStores($user)
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->values();
        }

        $cacheKey = $this->dashboardCacheKey($user, $ignoreScope, $scopedStoreIds);

        return Cache::remember($cacheKey, now()->addSeconds(60), function () use ($scopedStoreIds): array {
            $storeQuery = Store::query();
            if ($scopedStoreIds !== null) {
                $storeQuery->whereIn('id', $scopedStoreIds->all());
            }

            $storeCount = $storeQuery->count();
            $activeCount = (clone $storeQuery)->where('status', 'active')->count();
            $productCount = Product::query()->count();

            $assignedManagers = Store::query()
                ->when($scopedStoreIds !== null, fn ($query) => $query->whereIn('id', $scopedStoreIds->all()))
                ->whereNotNull('manager_id')
                ->distinct('manager_id')
                ->count('manager_id');

            $hasAssignmentRole = $this->assignmentRoleColumnAvailable();

            $employeeBase = DB::table('store_user')
                ->join('users', 'users.id', '=', 'store_user.user_id')
                ->when($scopedStoreIds !== null, fn ($query) => $query->whereIn('store_user.store_id', $scopedStoreIds->all()))
                ->when($hasAssignmentRole, fn ($query) => $query->where('store_user.assignment_role', 'employee'))
                ->whereIn('users.role', ['store_employee', 'employee']);

            $assignedEmployeesTotal = (clone $employeeBase)
                ->distinct('users.id')
                ->count('users.id');

            $assignedEmployeesActive = (clone $employeeBase)
                ->where('users.status', 'active')
                ->distinct('users.id')
                ->count('users.id');

            $employeeAssignments = (clone $employeeBase)
                ->where('users.status', 'active')
                ->count();

            $productLinksTotal = DB::table('product_store')
                ->when($scopedStoreIds !== null, fn ($query) => $query->whereIn('store_id', $scopedStoreIds->all()))
                ->count();

            $linkedProductsCount = DB::table('product_store')
                ->when($scopedStoreIds !== null, fn ($query) => $query->whereIn('store_id', $scopedStoreIds->all()))
                ->distinct('product_id')
                ->count('product_id');

            $activeRatio = $storeCount > 0 ? round(($activeCount / $storeCount) * 100) : 0;
            $productCoveragePercent = $productCount > 0 ? round(($linkedProductsCount / $productCount) * 100) : 0;
            $avgEmployeesPerStore = $storeCount > 0 ? round($employeeAssignments / $storeCount, 1) : 0;
            $avgProductsPerStore = $storeCount > 0 ? round($productLinksTotal / $storeCount, 1) : 0;
            $employeeActiveRatio = $assignedEmployeesTotal > 0
                ? round(($assignedEmployeesActive / $assignedEmployeesTotal) * 100)
                : 0;

            $perStoreEmployees = DB::table('store_user')
                ->select(DB::raw('store_user.store_id as store_id'), DB::raw('count(*) as cnt'))
                ->join('users', 'users.id', '=', 'store_user.user_id')
                ->when($scopedStoreIds !== null, fn ($query) => $query->whereIn('store_user.store_id', $scopedStoreIds->all()))
                ->when($hasAssignmentRole, fn ($query) => $query->where('store_user.assignment_role', 'employee'))
                ->whereIn('users.role', ['store_employee', 'employee'])
                ->where('users.status', 'active')
                ->groupBy('store_user.store_id')
                ->pluck('cnt', 'store_id');

            $productCountsByStore = DB::table('product_store')
                ->select('store_id', DB::raw('count(distinct product_id) as cnt'))
                ->when($scopedStoreIds !== null, fn ($query) => $query->whereIn('store_id', $scopedStoreIds->all()))
                ->groupBy('store_id')
                ->pluck('cnt', 'store_id');

            $storeIds = $scopedStoreIds ?? Store::query()->pluck('id');
            $employeeCounts = $storeIds
                ->map(fn ($storeId) => (int) ($perStoreEmployees[$storeId] ?? 0))
                ->values();

            $meanEmployees = $storeCount > 0 ? ($employeeCounts->sum() / $storeCount) : 0;
            $varianceEmployees = 0.0;
            if ($storeCount > 0) {
                foreach ($employeeCounts as $count) {
                    $varianceEmployees += ($count - $meanEmployees) ** 2;
                }

                $varianceEmployees /= $storeCount;
            }

            $stdEmployees = sqrt($varianceEmployees);
            $staffStabilityPercent = $meanEmployees > 0
                ? max(0, min(100, (int) round(100 - (($stdEmployees / $meanEmployees) * 100))))
                : 0;

            $recentStores = Store::query()
                ->select([
                    'id',
                    'name',
                    'branch_code',
                    'status',
                    'created_at',
                    'opening_date',
                    'address',
                    'city',
                    'phone',
                    'email',
                ])
                ->when($scopedStoreIds !== null, fn ($query) => $query->whereIn('id', $scopedStoreIds->all()))
                ->latest()
                ->limit(5)
                ->get();

            $hamaStore = Store::query()
                ->select([
                    'id',
                    'name',
                    'branch_code',
                    'status',
                    'created_at',
                    'opening_date',
                    'address',
                    'city',
                    'phone',
                    'email',
                ])
                ->where('branch_code', 'HMA-006')
                ->first();

            if (
                $hamaStore
                && ($scopedStoreIds === null || $scopedStoreIds->contains((int) $hamaStore->id))
                && ! $recentStores->contains('id', $hamaStore->id)
            ) {
                $recentStores = $recentStores->concat([$hamaStore]);
            }

            $recentStoreIds = $recentStores->pluck('id');
            $recentStoreEmployees = $recentStoreIds->isEmpty()
                ? []
                : $recentStoreIds->mapWithKeys(
                    fn ($id) => [$id => (int) ($perStoreEmployees[$id] ?? 0)]
                )->all();

            $recentStoreProducts = $recentStoreIds->isEmpty()
                ? []
                : $recentStoreIds->mapWithKeys(
                    fn ($id) => [$id => (int) ($productCountsByStore[$id] ?? 0)]
                )->all();

            return [
                'storeCount' => $storeCount,
                'activeCount' => $activeCount,
                'productCount' => $productCount,
                'managerCount' => $assignedManagers,
                'employeeCount' => $assignedEmployeesActive,
                'employeeActiveRatio' => $employeeActiveRatio,
                'activeRatio' => $activeRatio,
                'linkedProductsCount' => $linkedProductsCount,
                'productCoveragePercent' => $productCoveragePercent,
                'avgEmployeesPerStore' => $avgEmployeesPerStore,
                'avgProductsPerStore' => $avgProductsPerStore,
                'staffStabilityPercent' => $staffStabilityPercent,
                'recentStores' => $recentStores
                    ->map(function ($store) use ($recentStoreEmployees, $recentStoreProducts) {
                        return [
                            'id' => $store->id,
                            'name' => $store->name,
                            'branch_code' => $store->branch_code,
                            'status' => $store->status,
                            'employees' => (int) ($recentStoreEmployees[$store->id] ?? 0),
                            'created_at' => $store->created_at,
                            'opening_date' => $store->opening_date,
                            'address' => $store->address,
                            'city' => $store->city,
                            'phone' => $store->phone,
                            'email' => $store->email,
                            'products' => (int) ($recentStoreProducts[$store->id] ?? 0),
                        ];
                    })
                    ->values(),
                'navStore' => Store::query()->select('id')->orderBy('id')->first(),
            ];
        });
    }

    private function dashboardCacheKey(?User $user, bool $ignoreScope, ?Collection $scopedStoreIds): string
    {
        return 'store_dashboard_stats:v1:'.md5(json_encode([
            'ignore_scope' => $ignoreScope,
            'user_id' => $user?->id,
            'role' => $user?->role,
            'store_ids' => $scopedStoreIds?->all() ?? ['all'],
        ]));
    }

    private function assignmentRoleColumnAvailable(): bool
    {
        if (self::$assignmentRoleColumnAvailable !== null) {
            return self::$assignmentRoleColumnAvailable;
        }

        self::$assignmentRoleColumnAvailable = Schema::hasTable('store_user')
            && Schema::hasColumn('store_user', 'assignment_role');

        return self::$assignmentRoleColumnAvailable;
    }
}