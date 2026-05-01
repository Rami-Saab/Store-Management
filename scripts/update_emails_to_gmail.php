<?php

declare(strict_types=1);

use Illuminate\Contracts\Console\Kernel;

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$normalize = static function (?string $email): ?string {
    $email = $email ?? '';
    if ($email === '') {
        return null;
    }
    if (str_ends_with($email, '@store.test')) {
        return str_replace('@store.test', '@gmail.com', $email);
    }
    return null;
};

App\Models\User::query()->get()->each(function ($user) use ($normalize) {
    $next = $normalize($user->email);
    if ($next && !App\Models\User::where('email', $next)->where('id', '!=', $user->id)->exists()) {
        $user->update(['email' => $next]);
    }
});

App\Models\Store::query()->get()->each(function ($store) use ($normalize) {
    $next = $normalize($store->email);
    if ($next && !App\Models\Store::where('email', $next)->where('id', '!=', $store->id)->exists()) {
        $store->update(['email' => $next]);
    }
});
