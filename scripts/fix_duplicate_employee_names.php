<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

\App\Models\User::where('job_title', 'store_employee')
    ->whereIn('name', ['Ahmed Al Hassan 2', 'Mohammad Al Ali 2'])
    ->update([
        'name' => \DB::raw("CASE name
            WHEN 'Ahmed Al Hassan 2' THEN 'Sami Al Hariri'
            WHEN 'Mohammad Al Ali 2' THEN 'Nabil Al Mansour'
            END"),
    ]);

echo "done\n";
