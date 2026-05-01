<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\DB;

function line(string $text): void
{
    echo $text . PHP_EOL;
}

line('Fixing user roles...');

$driver = DB::connection()->getDriverName();
$dbName = DB::connection()->getDatabaseName();
line('Driver: ' . ($driver ?: '(unknown)'));
$dbName = DB::connection()->getDatabaseName();
line('DB: ' . ($dbName ?: '(unknown)'));

try {
    $tables = DB::select('SHOW TABLES');
    line('Tables: ' . count($tables));
} catch (Throwable $e) {
    line('SHOW TABLES failed: ' . $e->getMessage());
}
line('Users total: ' . User::count());

$before = User::query()
    ->selectRaw('job_title, COUNT(*) as c')
    ->groupBy('job_title')
    ->orderByRaw('c DESC')
    ->get()
    ->map(fn ($row) => ($row->job_title ?? '(null)') . ': ' . $row->c)
    ->values()
    ->all();

line('Before:');
foreach ($before as $row) {
    line('  - ' . $row);
}

// Normalize any legacy or inconsistent titles to our 3 roles.
$normalized = User::query()
    ->whereNotNull('job_title')
    ->get(['id', 'job_title'])
    ->map(function ($u) {
        $t = strtolower(trim((string) $u->job_title));
        $mapped = null;

        if (in_array($t, ['system administrator', 'system_admin', 'sysadmin', 'admin'], true)) {
            $mapped = 'system administrator';
        } elseif (in_array($t, ['store_manager', 'manager', 'store manager', 'branch manager'], true)) {
            $mapped = 'store_manager';
        } elseif (in_array($t, ['store_employee', 'employee', 'store employee', 'staff'], true)) {
            $mapped = 'store_employee';
        }

        return [$u->id, $mapped];
    })
    ->filter(fn ($pair) => $pair[1] !== null)
    ->values();

foreach ($normalized as [$id, $mapped]) {
    User::whereKey($id)->update(['job_title' => $mapped]);
}

// Enforce: Everyone except managers + system admins becomes store_employee.
$protectedIds = User::query()
    ->whereIn('job_title', ['system administrator', 'store_manager'])
    ->pluck('id')
    ->map(fn ($id) => (int) $id)
    ->all();

$updated = User::query()
    ->whereNotIn('id', $protectedIds)
    ->update(['job_title' => 'store_employee']);

line('Updated to store_employee (non-managers/admins): ' . $updated);

$after = User::query()
    ->selectRaw('job_title, COUNT(*) as c')
    ->groupBy('job_title')
    ->orderByRaw('c DESC')
    ->get()
    ->map(fn ($row) => ($row->job_title ?? '(null)') . ': ' . $row->c)
    ->values()
    ->all();

line('After:');
foreach ($after as $row) {
    line('  - ' . $row);
}
