<?php // Name : Rodain Gouzlan Id:

namespace Database\Seeders;

use App\Support\LockedUsers;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Seeder: Roles + Credentials for locked users.
 *
 * Uses config/locked_users.php as the single source of truth for:
 * - role (admin / store_manager / store_employee)
 * - job_title (legacy label)
 * - password (hashed)
 */
class UserRoleSeeder extends Seeder
{
    public function run(): void
    {
        LockedUsers::ensure();

        $roles = Role::query()->get()->keyBy('slug');
        if ($roles->isEmpty()) {
            return;
        }

        User::query()
            ->select(['id', 'role'])
            ->chunkById(200, function ($users) use ($roles) {
                foreach ($users as $user) {
                    $roleSlug = (string) ($user->role ?? '');
                    if ($roleSlug === 'branch_manager') {
                        $roleSlug = 'store_manager';
                        $user->role = 'store_manager';
                        $user->save();
                    }
                    if (in_array($roleSlug, ['employee', 'department_employee'], true)) {
                        $roleSlug = 'store_employee';
                        $user->role = 'store_employee';
                        $user->save();
                    }
                    if ($roleSlug === '' || ! $roles->has($roleSlug)) {
                        continue;
                    }
                    $roleId = $roles[$roleSlug]->id;
                    $user->roles()->sync([$roleId]);
                }
            });
    }
}