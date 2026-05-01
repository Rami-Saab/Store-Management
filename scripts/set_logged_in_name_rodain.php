<?php

declare(strict_types=1);

use Illuminate\Contracts\Console\Kernel;

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$desired = 'Rodain Gouzlan';
$fallbackNames = [
    'Ahmad Al Saad',
    'Samir Al Saad',
    'Fares Al Saad',
    'Yahya Al Saad',
    'Bilal Al Saad',
    'Nabil Al Saad',
];

$pickFallback = function (array $taken) use ($fallbackNames): string {
    foreach ($fallbackNames as $name) {
        if (!in_array($name, $taken, true)) {
            return $name;
        }
    }
    return $fallbackNames[0];
};

$taken = App\Models\User::query()->pluck('name')->filter()->values()->all();

$rodains = App\Models\User::where('name', $desired)->orderBy('id')->get();
if ($rodains->count() > 1) {
    $keep = $rodains->shift();
    foreach ($rodains as $user) {
        $newName = $pickFallback($taken);
        $user->update(['name' => $newName]);
        $taken[] = $newName;
    }
}

$managerHoms = App\Models\User::where('email', 'manager.homs@gmail.com')->first();
if ($managerHoms && $managerHoms->name !== $desired) {
    if (App\Models\User::where('name', $desired)->where('id', '!=', $managerHoms->id)->exists()) {
        $conflict = App\Models\User::where('name', $desired)->where('id', '!=', $managerHoms->id)->first();
        if ($conflict) {
            $newName = $pickFallback($taken);
            $conflict->update(['name' => $newName]);
            $taken[] = $newName;
        }
    }
    $managerHoms->update(['name' => $desired]);
}

$current = App\Models\User::where('name', 'Ahmad Al Saad')->orderBy('id')->first();
if ($current && $current->name !== $desired) {
    if (App\Models\User::where('name', $desired)->where('id', '!=', $current->id)->exists()) {
        $conflict = App\Models\User::where('name', $desired)->where('id', '!=', $current->id)->first();
        if ($conflict) {
            $newName = $pickFallback($taken);
            $conflict->update(['name' => $newName]);
            $taken[] = $newName;
        }
    }
    $current->update(['name' => $desired]);
}
