<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\DB;

function pad(string $value, int $len): string
{
    $value = (string) $value;
    if (strlen($value) >= $len) return $value;
    return $value . str_repeat(' ', $len - strlen($value));
}

$db = DB::connection()->getDatabaseName();
echo "DB: " . ($db ?: '(unknown)') . PHP_EOL;
echo PHP_EOL;

echo "Passwords:" . PHP_EOL;
echo "  - System Administrator: 123" . PHP_EOL;
echo "  - Manager: 123" . PHP_EOL;
echo "  - Employee: 321" . PHP_EOL;
echo PHP_EOL;

$users = User::query()
    ->select(['id', 'name', 'job_title'])
    ->orderByRaw("FIELD(job_title, 'system administrator', 'store_manager', 'store_employee') ASC")
    ->orderBy('name')
    ->get();

if ($users->isEmpty()) {
    echo "No users found." . PHP_EOL;
    exit(0);
}

$rows = $users->map(function ($u) {
    $role = match($u->job_title) {
        'system administrator' => 'System Administrator',
        'store_manager' => 'Manager',
        'store_employee' => 'Employee',
        default => (string) ($u->job_title ?? ''),
    };
    return [
        'id' => (string) $u->id,
        'role' => $role,
        'name' => (string) $u->name,
    ];
});

$wId = max(2, ...$rows->map(fn ($r) => strlen($r['id']))->all());
$wRole = max(4, ...$rows->map(fn ($r) => strlen($r['role']))->all());
$wName = max(4, ...$rows->map(fn ($r) => strlen($r['name']))->all());

echo pad('ID', $wId) . "  " . pad('ROLE', $wRole) . "  " . pad('NAME', $wName) . PHP_EOL;
echo str_repeat('-', $wId) . "  " . str_repeat('-', $wRole) . "  " . str_repeat('-', $wName) . PHP_EOL;

foreach ($rows as $r) {
    echo pad($r['id'], $wId) . "  " . pad($r['role'], $wRole) . "  " . pad($r['name'], $wName) . PHP_EOL;
}
