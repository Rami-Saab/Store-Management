<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$admins = \App\Models\User::query()
    ->where('job_title', 'system administrator')
    ->orderBy('name')
    ->pluck('name')
    ->all();

$managers = \App\Models\User::query()
    ->where('department', 'store_management')
    ->where('status', 'active')
    ->where('job_title', 'store_manager')
    ->orderBy('name')
    ->pluck('name')
    ->all();

$employees = \App\Models\User::query()
    ->where('department', 'store_management')
    ->where('status', 'active')
    ->where('job_title', 'store_employee')
    ->orderBy('name')
    ->pluck('name')
    ->all();

echo "System Administrators:\n";
foreach ($admins as $name) {
    echo "- {$name}\n";
}

echo "\nManagers:\n";
foreach ($managers as $name) {
    echo "- {$name}\n";
}

echo "\nEmployees:\n";
foreach ($employees as $name) {
    echo "- {$name}\n";
}
