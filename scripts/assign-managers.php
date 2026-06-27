<?php

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$stores = App\Models\Store::query()
    ->orderBy('branch_code')
    ->get();

$managers = App\Models\User::query()
    ->where('role', 'store_manager')
    ->where('status', 'active')
    ->orderBy('id')
    ->get();

if ($stores->isEmpty() || $managers->isEmpty()) {
    echo "No stores or no managers found.\n";
    exit(0);
}

$storeService = app(App\Services\Store\StoreService::class);
$managerCount = $managers->count();

foreach ($stores as $index => $store) {
    $manager = $managers[$index % $managerCount];
    $employeeIds = $store->employees()->pluck('users.id')->all();
    $storeService->syncAssignments($store, (int) $manager->id, $employeeIds);
    echo "Assigned manager {$manager->name} to {$store->branch_code}\n";
}
