<?php

declare(strict_types=1);

use Illuminate\Contracts\Console\Kernel;

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$renameIfExists = function (App\Models\User $user, string $base, int $max = 50): string {
    $name = $base;
    $suffix = 2;
    while (App\Models\User::where('name', $name)->where('id', '!=', $user->id)->exists()) {
        $name = $base.' '.$suffix;
        $suffix++;
    }
    return mb_substr($name, 0, $max);
};

// Normalize manager names to fixed unique values (no Manager prefix).
$managerNameMap = [
    'manager.omar@gmail.com' => 'Lina Al Darwish',
    'manager.homs@gmail.com' => 'Nour Al Hakim',
    'manager.rodain@gmail.com' => 'Rodain Gouzlan',
    'manager.lattakia@gmail.com' => 'Hadi Al Latif',
    'manager.sweida@gmail.com' => 'Samira Al Nouri',
    'manager.hama@gmail.com' => 'Khalil Al Yasin',
];

App\Models\User::query()
    ->where('job_title', 'store_manager')
    ->get()
    ->each(function (App\Models\User $user) use ($managerNameMap) {
        $target = $managerNameMap[$user->email] ?? null;
        if ($target && $user->name !== $target) {
            $user->update(['name' => $target]);
        }
        if (is_string($user->name) && str_starts_with($user->name, 'Manager ')) {
            $user->update(['name' => trim(substr($user->name, 8))]);
        }
    });

// Ensure only one user keeps the exact name "Rodain Gouzlan".
$rodains = App\Models\User::where('name', 'Rodain Gouzlan')->orderBy('id')->get();
if ($rodains->count() > 1) {
    $keep = $rodains->shift();
    $taken = App\Models\User::query()->pluck('name')->filter()->values()->all();
    $fallbackFirstNames = [
        'Ahmad', 'Samir', 'Fares', 'Yahya', 'Bilal', 'Nabil', 'Rakan', 'Yazan',
        'Samer', 'Jad', 'Mazen', 'Rami', 'Nasser', 'Tarek', 'Waleed', 'Mahmoud',
    ];
    $fallbackLastNames = [
        'Al Saad', 'Al Qadi', 'Al Zayani', 'Al Karam', 'Al Najjar', 'Al Azzam',
        'Al Nabulsi', 'Al Sharif', 'Al Fares', 'Al Salman', 'Al Jaber', 'Al Sayegh',
    ];
    $fallbackNames = [];
    foreach ($fallbackFirstNames as $firstName) {
        foreach ($fallbackLastNames as $lastName) {
            $fallbackNames[] = $firstName.' '.$lastName;
        }
    }
    foreach ($rodains as $user) {
        $newName = null;
        foreach ($fallbackNames as $candidate) {
            if (!in_array($candidate, $taken, true)) {
                $newName = $candidate;
                $taken[] = $candidate;
                break;
            }
        }
        if (!$newName) {
            $newName = $renameIfExists($user, 'Employee');
        }
        $user->update(['name' => $newName]);
    }
}

// Ensure names are unique across managers and employees (keep first, rename duplicates).
$seen = [];
// Extra fallback names to replace duplicates (no numbering).
$fallbackFirstNames = [
    'Ahmad', 'Samir', 'Fares', 'Yahya', 'Bilal', 'Nabil', 'Rakan', 'Yazan',
    'Samer', 'Jad', 'Mazen', 'Rami', 'Nasser', 'Tarek', 'Waleed', 'Mahmoud',
];
$fallbackLastNames = [
    'Al Saad', 'Al Qadi', 'Al Zayani', 'Al Karam', 'Al Najjar', 'Al Azzam',
    'Al Nabulsi', 'Al Sharif', 'Al Fares', 'Al Salman', 'Al Jaber', 'Al Sayegh',
];
$fallbackNames = [];
foreach ($fallbackFirstNames as $firstName) {
    foreach ($fallbackLastNames as $lastName) {
        $fallbackNames[] = $firstName.' '.$lastName;
    }
}
$fallbackIndex = 0;
App\Models\User::query()
    ->whereIn('job_title', ['store_manager', 'store_employee'])
    ->orderBy('id')
    ->get()
    ->each(function (App\Models\User $user) use (&$seen, $renameIfExists, &$fallbackIndex, $fallbackNames) {
        $base = is_string($user->name) && trim($user->name) !== '' ? trim($user->name) : 'Employee';
        $hasNumericSuffix = (bool) preg_match('/\\s\\d+$/', $base);
        if (!$hasNumericSuffix && !isset($seen[$base])) {
            $seen[$base] = true;
            return;
        }
        while ($fallbackIndex < count($fallbackNames) && isset($seen[$fallbackNames[$fallbackIndex]])) {
            $fallbackIndex++;
        }
        $nextName = $fallbackIndex < count($fallbackNames)
            ? $fallbackNames[$fallbackIndex]
            : $renameIfExists($user, $base);
        $fallbackIndex++;
        $seen[$nextName] = true;
        if ($user->name !== $nextName) {
            $user->update(['name' => $nextName]);
        }
    });
