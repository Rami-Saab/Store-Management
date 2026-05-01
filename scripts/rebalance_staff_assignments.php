<?php

declare(strict_types=1);

use App\Models\Store;
use App\Models\User;
use App\Support\LockedUsers;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

LockedUsers::ensure();

$stores = Store::query()
    ->orderBy('branch_code')
    ->get();

if ($stores->isEmpty()) {
    echo "No stores found.\n";
    exit(0);
}

$managers = User::query()
    ->where('department', 'store_management')
    ->where('job_title', 'store_manager')
    ->where('status', 'active')
    ->orderBy('id')
    ->get();

$employees = User::query()
    ->where('department', 'store_management')
    ->where('job_title', 'store_employee')
    ->where('status', 'active')
    ->orderBy('id')
    ->get();

$staffIds = $managers->pluck('id')
    ->merge($employees->pluck('id'))
    ->unique()
    ->values()
    ->all();

if ($staffIds === []) {
    echo "No staff found to distribute.\n";
    exit(0);
}

DB::table('store_user')->whereIn('user_id', $staffIds)->delete();

$storeCount = $stores->count();
$syncByStoreId = [];

foreach ($stores as $index => $store) {
    $syncByStoreId[$store->id] = [];
    $manager = $managers[$index] ?? null;
    if ($manager) {
        $syncByStoreId[$store->id][$manager->id] = ['assignment_role' => 'manager'];
    }
}

foreach ($employees as $index => $employee) {
    $store = $stores[$index % $storeCount];
    $syncByStoreId[$store->id][$employee->id] = ['assignment_role' => 'employee'];
}

foreach ($stores as $store) {
    $store->users()->syncWithoutDetaching($syncByStoreId[$store->id] ?? []);
}

echo "Rebalanced staff across {$storeCount} stores.\n\n";

foreach ($stores as $store) {
    $managerCount = DB::table('store_user')->where('store_id', $store->id)->where('assignment_role', 'manager')->count();
    $employeeCount = DB::table('store_user')->where('store_id', $store->id)->where('assignment_role', 'employee')->count();
    echo "{$store->branch_code} | {$store->name} => managers: {$managerCount}, employees: {$employeeCount}\n";
}

