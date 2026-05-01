<?php

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$stores = App\Models\Store::query()
    ->with(['manager:id,name'])
    ->orderBy('branch_code')
    ->get(['id', 'branch_code', 'manager_id']);

foreach ($stores as $store) {
    $name = $store->manager?->name ?? '';
    echo 'code='.$store->branch_code.' manager_id='.(string) ($store->manager_id ?? '').' manager='.$name."\n";
}
