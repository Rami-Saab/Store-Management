<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$dbName = DB::connection()->getDatabaseName();
echo "DB: ".($dbName ?: '(unknown)')."\n";

$name = $argv[1] ?? 'Rodain';
$password = $argv[2] ?? '123';

$user = User::query()->where('name', $name)->first();
if (! $user) {
    echo "User not found by exact name: {$name}\n";

    $sample = User::query()->select(['id', 'name'])->orderBy('id')->limit(10)->get();
    if ($sample->isEmpty()) {
        echo "No users in this DB.\n";
    } else {
        echo "First users:\n";
        foreach ($sample as $candidate) {
            $bytes = (string) $candidate->name;
            echo "  - #{$candidate->id} \"{$candidate->name}\" (len=".strlen($bytes).", hex=".bin2hex($bytes).")\n";
        }
    }

    $candidates = User::query()
        ->select(['id', 'name'])
        ->whereRaw('LOWER(name) = ?', [mb_strtolower($name)])
        ->get();
    if ($candidates->isNotEmpty()) {
        echo "Case-insensitive matches:\n";
        foreach ($candidates as $candidate) {
            $bytes = (string) $candidate->name;
            echo "  - #{$candidate->id} \"{$candidate->name}\" (len=".strlen($bytes).", hex=".bin2hex($bytes).")\n";
        }
    } else {
        $starts = User::query()
            ->select(['id', 'name'])
            ->where('name', 'like', $name.'%')
            ->limit(5)
            ->get();
        if ($starts->isNotEmpty()) {
            echo "Starts-with matches:\n";
            foreach ($starts as $candidate) {
                $bytes = (string) $candidate->name;
                echo "  - #{$candidate->id} \"{$candidate->name}\" (len=".strlen($bytes).", hex=".bin2hex($bytes).")\n";
            }
        }
    }
    exit(1);
}

echo "User: #{$user->id} name=\"{$user->name}\" status=\"{$user->status}\" job_title=\"{$user->job_title}\"\n";
echo "Password hash: ".substr((string) $user->password, 0, 10)."...\n";
echo "Hash::check: ".(Hash::check($password, (string) $user->password) ? 'true' : 'false')."\n";

$attempt = Auth::attempt(['name' => $name, 'password' => $password]);
echo "Auth::attempt(name+password): ".($attempt ? 'true' : 'false')."\n";
