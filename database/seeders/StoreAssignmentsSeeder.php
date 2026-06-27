<?php // Name : Rodain Gouzlan Id:

namespace Database\Seeders;

use App\Models\Store;
use App\Models\User;
use App\Services\Store\StoreService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

// Seeder لتوزيع المدراء والموظفين على الفروع بعد إنشاء البيانات الأساسية.
// يساعد على تجهيز بيانات تجريبية واقعية لتعيينات الفروع.
class StoreAssignmentsSeeder extends Seeder
{
    private const MIN_EMPLOYEES_PER_STORE = 5;
    private const MAX_EMPLOYEES_PER_STORE = 5;

    public function run(): void
    {
        $stores = Store::query()
            ->orderBy('branch_code')
            ->get();

        if ($stores->isEmpty()) {
            return;
        }

        $managers = User::query()
            ->where('role', 'store_manager')
            ->where('status', 'active')
            ->orderBy('id')
            ->get()
            ->values();

        if ($managers->isEmpty()) {
            return;
        }

        $employees = User::query()
            ->whereIn('role', ['store_employee'])
            ->where('status', 'active')
            ->orderBy('id')
            ->get()
            ->values();

        $storeCount = $stores->count();
        $minNeeded = self::MIN_EMPLOYEES_PER_STORE * $storeCount;
        if ($employees->count() < $minNeeded) {
            $employees = $employees
                ->unique('id')
                ->values();
        }

        $managerIds = $managers->pluck('id')->map(fn ($id) => (int) $id)->values();
        $employees = $employees
            ->reject(fn (User $employee) => $managerIds->contains((int) $employee->id))
            ->values();

        if ($employees->isEmpty()) {
            return;
        }

        $employeeCount = $employees->count();
        $capacity = $storeCount * self::MAX_EMPLOYEES_PER_STORE;
        if ($employeeCount > $capacity) {
            $employees = $employees->take($capacity)->values();
            $employeeCount = $employees->count();
        }

        $priorityStore = $stores->first(function (Store $store) {
            return $store->branch_code === 'ALP-003' || str_contains(strtolower((string) $store->name), 'aleppo');
        });
        $priorityEmployees = collect();
        $priorityManager = null;

        if ($priorityStore) {
            $priorityEmployees = $employees->take(self::MIN_EMPLOYEES_PER_STORE)->values();
            $employees = $employees->slice(self::MIN_EMPLOYEES_PER_STORE)->values();
            $priorityManager = $managers->first();
        }

        $remainingStores = $priorityStore
            ? $stores->reject(fn (Store $store) => (int) $store->id === (int) $priorityStore->id)->values()
            : $stores;

        $counts = $this->buildDistribution($remainingStores->count(), $employees->count());
        if ($priorityStore && $counts->isEmpty() && $employees->isNotEmpty()) {
            $counts = collect(array_fill(0, $remainingStores->count(), 0));
        }
        if (! $priorityStore && $counts->isEmpty()) {
            return;
        }

        $storeIds = $stores->pluck('id')->map(fn ($id) => (int) $id)->values()->all();
        DB::table('store_user')->whereIn('store_id', $storeIds)->delete();

        Store::query()->whereIn('id', $storeIds)->update(['manager_id' => null]);
        User::query()->whereIn('id', $managerIds)->update(['store_id' => null]);

        $storeService = app(StoreService::class);
        $offset = 0;
        $managerIndex = 0;

        if ($priorityStore) {
            $manager = $priorityManager ?? $managers[$managerIndex % $managers->count()];
            $managerIndex++;
            $assignedEmployees = $priorityEmployees
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->values()
                ->all();

            $storeService->syncAssignments($priorityStore, (int) $manager->id, $assignedEmployees);
            if ($assignedEmployees !== []) {
                User::query()
                    ->whereIn('id', $assignedEmployees)
                    ->update(['store_id' => $priorityStore->id]);
            }
        }

        foreach ($remainingStores as $index => $store) {
            $manager = $managers[$managerIndex % $managers->count()];
            $managerIndex++;
            $count = (int) ($counts[$index] ?? 0);
            $assignedEmployees = $employees
                ->slice($offset, $count)
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->values()
                ->all();

            $storeService->syncAssignments($store, (int) $manager->id, $assignedEmployees);

            if ($assignedEmployees !== []) {
                User::query()
                    ->whereIn('id', $assignedEmployees)
                    ->update(['store_id' => $store->id]);
            }

            $offset += $count;
        }

        $storeService->flushStoreCaches();
    }

    private function buildDistribution(int $storeCount, int $employeeCount): Collection
    {
        if ($storeCount <= 0 || $employeeCount <= 0) {
            return collect();
        }

        $minPerStore = self::MIN_EMPLOYEES_PER_STORE;
        $maxPerStore = self::MAX_EMPLOYEES_PER_STORE;
        $minTotal = $minPerStore * $storeCount;

        if ($employeeCount < $minTotal) {
            $base = intdiv($employeeCount, $storeCount);
            $remainder = $employeeCount - ($base * $storeCount);
            $counts = array_fill(0, $storeCount, $base);
            for ($i = 0; $i < $remainder; $i++) {
                $counts[$i] += 1;
            }

            return collect($counts);
        }

        $counts = array_fill(0, $storeCount, $minPerStore);
        $remaining = $employeeCount - $minTotal;

        for ($i = 0; $i < $storeCount && $remaining > 0; $i++) {
            $available = $maxPerStore - $minPerStore;
            $add = min($available, $remaining);
            $counts[$i] += $add;
            $remaining -= $add;
        }

        return collect($counts);
    }
}