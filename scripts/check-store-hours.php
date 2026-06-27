<?php

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$stores = App\Models\Store::query()
    ->select(['id', 'branch_code', 'workday_starts_at', 'workday_ends_at', 'working_hours'])
    ->orderBy('branch_code')
    ->get();

foreach ($stores as $store) {
    echo $store->branch_code
        .' start='.(string) ($store->workday_starts_at ?? '')
        .' end='.(string) ($store->workday_ends_at ?? '')
        .' working_hours='.(string) ($store->working_hours ?? '')
        ."\n";
}
