<?php

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$rows = DB::table('migrations')->where('migration', '2026_03_21_000003_update_store_search_procedure_manager_id')->get();
echo 'found='.$rows->count()."\n";
foreach ($rows as $row) {
    echo 'migration='.$row->migration.' batch='.$row->batch."\n";
}
