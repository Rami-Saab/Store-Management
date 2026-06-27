<?php

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$rows = App\Models\Store::query()
    ->select([
        'id',
        'name',
        'description',
        'branch_code',
        'province_id',
        'city',
        'address',
        'phone',
        'status',
        'opening_date',
        'working_hours',
        'workday_starts_at',
        'workday_ends_at',
        'brochure_path',
    ])
    ->with([
        'province:id,name,code',
        'manager:id,name',
    ])
    ->withCount(['employees', 'products'])
    ->latest()
    ->get()
    ->map(function (App\Models\Store $store) {
        return (object) [
            'id' => $store->id,
            'branch_code' => $store->branch_code,
            'manager_name' => $store->manager?->name,
        ];
    });

foreach ($rows as $row) {
    echo 'code='.$row->branch_code.' manager='.(string) ($row->manager_name ?? '')."\n";
}
