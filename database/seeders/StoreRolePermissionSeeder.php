<?php // Name : Rodain Gouzlan Id:

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class StoreRolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $rolesConfig = (array) config('store_permissions.roles', []);
        $permissionsConfig = (array) config('store_permissions.permissions', []);
        $rolePermissions = (array) config('store_permissions.role_permissions', []);

        $roles = collect($rolesConfig)->map(function (string $name, string $slug) {
            return Role::updateOrCreate(
                ['slug' => $slug],
                ['name' => $name]
            );
        })->keyBy('slug');

        $permissions = collect($permissionsConfig)->map(function (string $name, string $slug) {
            return Permission::updateOrCreate(
                ['slug' => $slug],
                ['name' => $name]
            );
        })->keyBy('slug');

        $allPermissionIds = $permissions->pluck('id')->values()->all();

        foreach ($roles as $slug => $role) {
            $assigned = $rolePermissions[$slug] ?? [];
            if (in_array('*', $assigned, true)) {
                $role->permissions()->sync($allPermissionIds);
                continue;
            }

            $permissionIds = collect($assigned)
                ->map(fn ($permissionSlug) => $permissions[$permissionSlug]->id ?? null)
                ->filter()
                ->values()
                ->all();

            $role->permissions()->sync($permissionIds);
        }
    }
}