<?php

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$filters = [
    'name' => null,
    'status' => null,
    'province_id' => null,
    'phone' => null,
];

$raw = DB::select(
    'CALL sp_search_store_branches(?, ?, ?, ?)',
    [$filters['name'], $filters['status'], $filters['province_id'], $filters['phone']]
);

if ($raw === []) {
    echo "no_rows\n";
    exit(0);
}

var_export($raw[0]);
echo "\n";
