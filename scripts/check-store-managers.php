<?php

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$stores = App\Models\Store::query()
    ->select(['id', 'name', 'branch_code', 'manager_id'])
    ->orderBy('branch_code')
    ->get();

foreach ($stores as $store) {
    $managerName = $store->manager_id
        ? App\Models\User::query()->where('id', $store->manager_id)->value('name')
        : null;
    echo 'id='.$store->id.' code='.$store->branch_code.' manager_id='.(string) ($store->manager_id ?? '')
        .' manager_name='.(string) ($managerName ?? '')
        ."\n";
}
