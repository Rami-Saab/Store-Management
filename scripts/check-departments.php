<?php

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$departments = App\Models\Department::query()->orderBy('id')->get(['id', 'name', 'slug']);
echo 'count='.$departments->count()."\n";
foreach ($departments as $department) {
    echo 'id='.$department->id.' slug='.$department->slug.' name='.$department->name."\n";
}

$storeManagementId = App\Models\Department::query()->where('slug', 'store_management')->value('id');
echo 'store_management_id='.(string) $storeManagementId."\n";
