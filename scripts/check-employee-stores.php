<?php

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$total = App\Models\User::query()->where('role', 'employee')->count();
$withStores = App\Models\User::query()->where('role', 'employee')->whereHas('stores')->count();
$withStoreId = App\Models\User::query()->where('role', 'employee')->whereNotNull('store_id')->count();

echo 'employees='.$total.' withStores='.$withStores.' withStoreId='.$withStoreId.PHP_EOL;
