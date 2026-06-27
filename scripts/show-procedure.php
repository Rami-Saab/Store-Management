<?php

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$row = DB::selectOne("SHOW CREATE PROCEDURE sp_search_store_branches");
if (! $row) {
    echo "procedure_not_found\n";
    exit(0);
}

echo (string) ($row->{'Create Procedure'} ?? '')."\n";
