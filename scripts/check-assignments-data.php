<?php

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$storeManagementId = App\Models\Department::query()->where('slug', 'store_management')->value('id');
echo 'store_management_id='.(string) $storeManagementId.PHP_EOL;

$stores = App\Models\Store::query()
    ->select(['id', 'branch_code', 'department_id'])
    ->orderBy('branch_code')
    ->get();

foreach ($stores as $store) {
    $deptId = (int) ($store->department_id ?? 0);
    $employeeCount = App\Models\User::query()
        ->where('role', 'employee')
        ->where('status', 'active')
        ->when($deptId > 0, fn ($q) => $q->where('department_id', $deptId))
        ->count();
    echo $store->branch_code.' dept_id='.$deptId.' employee_count='.$employeeCount.PHP_EOL;
}
