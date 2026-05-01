<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$user = \App\Models\User::query()
    ->where('email', 'manager.rodain@gmail.com')
    ->first();

if (! $user) {
    echo "not found\n";
    return;
}

echo $user->name.' | '.$user->email.' | '.$user->status."\n";
