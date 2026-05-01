<?php

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$service = app(App\Services\Store\StoreService::class);

$stores = App\Models\Store::query()
    ->select(['id', 'branch_code', 'workday_starts_at', 'workday_ends_at', 'working_hours'])
    ->orderBy('branch_code')
    ->get();

$updated = 0;

foreach ($stores as $store) {
    $newHours = $service->formatWorkingHours(
        $store->workday_starts_at ? substr((string) $store->workday_starts_at, 0, 5) : null,
        $store->workday_ends_at ? substr((string) $store->workday_ends_at, 0, 5) : null
    );

    if ($newHours !== $store->working_hours) {
        $store->working_hours = $newHours;
        $store->save();
        $updated++;
        echo $store->branch_code.' -> '.($newHours ?? 'NULL').PHP_EOL;
    }
}

echo 'Updated stores: '.$updated.PHP_EOL;
