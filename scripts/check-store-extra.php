<?php

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$ids = App\Models\Store::query()->pluck('id')->all();
$rows = DB::table('stores')
    ->leftJoin('users', 'users.id', '=', 'stores.manager_id')
    ->whereIn('stores.id', $ids)
    ->get([
        'stores.id',
        'stores.branch_code',
        'stores.manager_id',
        'users.name as manager_name',
    ])
    ->keyBy('id');

foreach ($rows as $id => $row) {
    echo 'id='.$id.' code='.$row->branch_code.' manager_id='.$row->manager_id.' manager_name='.(string) ($row->manager_name ?? '')."\n";
}
