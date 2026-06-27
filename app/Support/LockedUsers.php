<?php // Name : Rodain Gouzlan Id:

namespace App\Support;

use App\Models\Department;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class LockedUsers
{
    private const PASSWORD_HASH_CACHE_PREFIX = 'locked_users.password_hash.v1:';

    private static ?bool $emailNullableCache = null;
    private static ?bool $rolesPivotReadyCache = null;
    private static ?bool $departmentColumnReadyCache = null;

    public static function ensure(): void
    {
        $rawAccounts = (array) config('locked_users', []);
        if ($rawAccounts === []) {
            return;
        }

        $accounts = [];
        foreach ($rawAccounts as $account) {
            $name = trim((string) ($account['name'] ?? ''));
            $password = (string) ($account['password'] ?? '');
            if ($name === '' || $password === '') {
                continue;
            }

            $account['_name'] = $name;
            $account['_password'] = $password;
            $accounts[] = $account;
        }

        if ($accounts === []) {
            return;
        }

        $names = array_values(array_unique(array_map(
            static fn (array $account): string => $account['_name'],
            $accounts
        )));

        $rolesPivotReady = self::rolesPivotReady();
        $existingUsersQuery = User::query()
            ->whereIn('name', $names)
            ->orderBy('id');

        if ($rolesPivotReady) {
            $existingUsersQuery->with('roles:id,slug');
        }

        $existingByName = $existingUsersQuery->get()->groupBy('name');

        $departmentIds = collect();
        if (self::departmentColumnReady()) {
            $departmentSlugs = array_values(array_unique(array_filter(array_map(
                static fn (array $account): string => (string) ($account['department'] ?? ''),
                $accounts
            ))));

            if ($departmentSlugs !== []) {
                $departmentIds = Department::query()
                    ->whereIn('slug', $departmentSlugs)
                    ->pluck('id', 'slug');
            }
        }

        $roleModels = collect();
        if ($rolesPivotReady) {
            $roleSlugs = array_values(array_unique(array_filter(array_map(
                static fn (array $account): string => (string) ($account['role'] ?? ''),
                $accounts
            ))));

            if ($roleSlugs !== []) {
                $roleModels = Role::query()->whereIn('slug', $roleSlugs)->get()->keyBy('slug');
            }
        }

        foreach ($accounts as $account) {
            $name = $account['_name'];
            $password = $account['_password'];
            $matches = $existingByName->get($name, collect());

            /** @var User|null $user */
            $user = $matches->first();
            if (! $user) {
                $user = new User();
                $user->name = $name;
            }

            $email = trim((string) ($account['email'] ?? ''));
            if ($email === '') {
                $email = self::fallbackEmailFor($name, (string) ($account['phone'] ?? ''));
            }

            $currentEmail = (string) ($user->email ?? '');
            if (
                $currentEmail === ''
                || str_ends_with($currentEmail, '@locked.invalid')
                || str_ends_with($currentEmail, '@branch.com')
            ) {
                $user->email = $email;
            }

            $user->department = (string) ($account['department'] ?? $user->department ?? 'store_management');
            $user->job_title = (string) ($account['job_title'] ?? $user->job_title ?? 'store_employee');

            $role = (string) ($account['role'] ?? '');
            if ($role === '') {
                $role = match ($user->job_title) {
                    'system administrator' => 'admin',
                    'store_manager' => 'store_manager',
                    default => 'store_employee',
                };
            }

            $user->role = $role ?: ($user->role ?? 'store_employee');
            $user->phone = (string) ($account['phone'] ?? $user->phone ?? null);
            $user->status = (string) ($account['status'] ?? $user->status ?? 'active');

            if (self::departmentColumnReady()) {
                $departmentId = $departmentIds->get($user->department);
                if ($departmentId) {
                    $user->department_id = (int) $departmentId;
                }
            }

            self::syncPassword($user, $password);

            if (! $user->exists || $user->isDirty()) {
                $user->save();
            }

            if ($rolesPivotReady) {
                $roleModel = $roleModels->get($user->role);
                if (! $roleModel) {
                    $roleModel = Role::query()->where('slug', $user->role)->first();
                    if ($roleModel) {
                        $roleModels->put($user->role, $roleModel);
                    }
                }

                if ($roleModel && ! self::userHasOnlyRole($user, (int) $roleModel->id)) {
                    $user->roles()->sync([$roleModel->id]);
                    $user->setRelation('roles', collect([$roleModel]));
                }
            }

            if ($matches->count() > 1) {
                foreach ($matches->slice(1) as $dup) {
                    $dup->status = 'inactive';
                    $dup->email = self::fallbackEmailFor($dup->name.' '.$dup->id, (string) ($dup->phone ?? ''));
                    $dup->name = Str::limit($dup->name.' (disabled #'.$dup->id.')', 255, '');
                    $dup->save();
                }
            }

            if ($matches->isNotEmpty()) {
                $existingByName->put($name, $matches->map(
                    fn (User $item) => (int) $item->id === (int) $user->id ? $user : $item
                ));
            } else {
                $existingByName->put($name, collect([$user]));
            }
        }
    }

    private static function emailIsNullable(): bool
    {
        if (self::$emailNullableCache !== null) {
            return self::$emailNullableCache;
        }

        try {
            $column = DB::selectOne("SHOW COLUMNS FROM users LIKE 'email'");
            if (! $column || ! isset($column->Null)) {
                return self::$emailNullableCache = true;
            }

            return self::$emailNullableCache = strtoupper((string) $column->Null) === 'YES';
        } catch (\Throwable) {
            return self::$emailNullableCache = true;
        }
    }

    private static function fallbackEmailFor(string $name, string $phone = ''): string
    {
        $slug = Str::slug($name, '.');
        if ($slug === '') {
            $slug = 'user';
        }

        $suffix = '';
        $digits = preg_replace('/\D+/', '', $phone);
        if ($digits !== '') {
            $suffix = '.'.substr($digits, -3);
        }

        $localPart = substr($slug.$suffix, 0, 64);

        return $localPart.'@branch.com';
    }

    private static function syncPassword(User $user, string $password): void
    {
        $preferredHash = self::preferredPasswordHash($password);
        if ((string) ($user->password ?? '') !== $preferredHash) {
            $user->password = $preferredHash;
        }
    }

    private static function preferredPasswordHash(string $password): string
    {
        return Cache::rememberForever(
            self::PASSWORD_HASH_CACHE_PREFIX.sha1($password),
            static fn (): string => Hash::make($password)
        );
    }

    private static function userHasOnlyRole(User $user, int $roleId): bool
    {
        if (! $user->exists) {
            return false;
        }

        $roleIds = $user->relationLoaded('roles')
            ? $user->roles->pluck('id')->map(fn ($id) => (int) $id)->values()->all()
            : $user->roles()->pluck('roles.id')->map(fn ($id) => (int) $id)->values()->all();

        return $roleIds === [$roleId];
    }

    private static function rolesPivotReady(): bool
    {
        if (self::$rolesPivotReadyCache !== null) {
            return self::$rolesPivotReadyCache;
        }

        self::$rolesPivotReadyCache = Schema::hasTable('roles') && Schema::hasTable('role_user');

        return self::$rolesPivotReadyCache;
    }

    private static function departmentColumnReady(): bool
    {
        if (self::$departmentColumnReadyCache !== null) {
            return self::$departmentColumnReadyCache;
        }

        self::$departmentColumnReadyCache = Schema::hasColumn('users', 'department_id');

        return self::$departmentColumnReadyCache;
    }
}