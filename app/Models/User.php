<?php

declare(strict_types=1);

namespace App\Models;

use App\Support\UserContact;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\Schema;

/**
 * User Model
 *
 * Represents user accounts in the system (admin, store managers, employees).
 * Handles role-based access control and store assignments.
 *
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $password
 * @property string|null $department
 * @property int|null $department_id
 * @property string|null $job_title
 * @property string $role
 * @property int|null $store_id
 * @property string|null $phone
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 *
 * @property-read string|null $staff_id
 * @property-read string $phone
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Store> $stores
 * @property-read Department|null $department
 * @property-read Store|null $assignedStore
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Role> $roles
 * @property-read Store|null $managedStore
 */
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected array $roleCheckCache = [];
    protected array $permissionCheckCache = [];

    protected static ?bool $rolesPivotAvailable = null;
    protected static ?bool $permissionsPivotAvailable = null;

    protected $fillable = [
        'name',
        'email',
        'password',
        'department',
        'department_id',
        'job_title',
        'role',
        'store_id',
        'phone',
        'status',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function getStaffIdAttribute(): ?string
    {
        if (! $this->getKey()) {
            return null;
        }

        return (string) $this->getKey();
    }

    public function getPhoneAttribute(?string $value): string
    {
        return UserContact::phone($value);
    }

    public function setPhoneAttribute(string|null $value): void
    {
        $this->attributes['phone'] = UserContact::phone($value, false);
    }

    public function stores(): BelongsToMany
    {
        return $this->belongsToMany(Store::class)->withTimestamps();
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function assignedStore(): BelongsTo
    {
        return $this->belongsTo(Store::class, 'store_id');
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class)->withTimestamps();
    }

    public function managedStore(): HasOne
    {
        return $this->hasOne(Store::class, 'manager_id');
    }

    public function hasRole(string $slug): bool
    {
        if (array_key_exists($slug, $this->roleCheckCache)) {
            return $this->roleCheckCache[$slug];
        }

        $columnRole = (string) $this->role;

        $canCheckPivot = self::rolesPivotReady();
        if ($canCheckPivot) {
            if ($this->relationLoaded('roles')) {
                return $this->roleCheckCache[$slug] = $this->roles->contains('slug', $slug);
            }

            if ($this->roles()->where('slug', $slug)->exists()) {
                return $this->roleCheckCache[$slug] = true;
            }
        }

        return $this->roleCheckCache[$slug] = ($columnRole === $slug);
    }

    public function hasPermission(string $slug): bool
    {
        if (array_key_exists($slug, $this->permissionCheckCache)) {
            return $this->permissionCheckCache[$slug];
        }

        if ($this->hasRole('admin')) {
            return $this->permissionCheckCache[$slug] = true;
        }

        if ($this->hasRole('store_manager')) {
            $blocked = [
                'create_store',
                'edit_store',
                'assign_staff_to_store',
                'manage_store_staff',
            ];
            if (in_array($slug, $blocked, true)) {
                return $this->permissionCheckCache[$slug] = false;
            }
        }

        if ($this->hasRole('store_employee')) {
            $blocked = [
                'delete_store',
                'assign_staff_to_store',
                'manage_store_staff',
                'manage_store_warehouses',
            ];
            if (in_array($slug, $blocked, true)) {
                return $this->permissionCheckCache[$slug] = false;
            }
        }

        $canCheckPermissions = self::permissionsPivotReady();

        if ($canCheckPermissions) {
            $this->loadRolePermissionsIfNeeded();
            $hasDatabasePermission = false;
            if ($this->relationLoaded('roles')) {
                $hasDatabasePermission = $this->roles->contains(function (Role $role) use ($slug) {
                    return $role->relationLoaded('permissions')
                        ? $role->permissions->contains('slug', $slug)
                        : $role->permissions()->where('slug', $slug)->exists();
                });
            } else {
                $hasDatabasePermission = $this->roles()
                    ->whereHas('permissions', fn (Builder $query) => $query->where('slug', $slug))
                    ->exists();
            }

            if ($hasDatabasePermission) {
                return $this->permissionCheckCache[$slug] = true;
            }
        }

        $roleSlug = (string) $this->role;
        $rolePermissions = (array) config('store_permissions.role_permissions', []);
        if ($roleSlug === 'employee' && isset($rolePermissions['store_employee'])) {
            $roleSlug = 'store_employee';
        }
        if ($roleSlug !== '' && isset($rolePermissions[$roleSlug])) {
            $assigned = (array) $rolePermissions[$roleSlug];
            if (in_array('*', $assigned, true)) {
                return $this->permissionCheckCache[$slug] = true;
            }

            return $this->permissionCheckCache[$slug] = in_array($slug, $assigned, true);
        }

        return $this->permissionCheckCache[$slug] = false;
    }

    private static function rolesPivotReady(): bool
    {
        if (self::$rolesPivotAvailable !== null) {
            return self::$rolesPivotAvailable;
        }

        self::$rolesPivotAvailable = Schema::hasTable('roles') && Schema::hasTable('role_user');

        return self::$rolesPivotAvailable;
    }

    private static function permissionsPivotReady(): bool
    {
        if (self::$permissionsPivotAvailable !== null) {
            return self::$permissionsPivotAvailable;
        }

        self::$permissionsPivotAvailable = self::rolesPivotReady()
            && Schema::hasTable('permissions')
            && Schema::hasTable('permission_role');

        return self::$permissionsPivotAvailable;
    }

    private function loadRolePermissionsIfNeeded(): void
    {
        if (! self::permissionsPivotReady()) {
            return;
        }

        $rolesLoaded = $this->relationLoaded('roles');
        $permissionsLoaded = $rolesLoaded
            && $this->roles->every(fn (Role $role) => $role->relationLoaded('permissions'));

        if (! $permissionsLoaded) {
            $this->loadMissing(['roles.permissions']);
        }
    }

    public function scopeEligibleManagers(Builder $query): Builder
    {
        return $query
            ->where('role', 'store_manager')
            ->where('status', 'active');
    }

    public function scopeStoreEmployees(Builder $query): Builder
    {
        return $query
            ->whereIn('role', ['store_employee', 'employee'])
            ->where('status', 'active');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    public function scopeInactive(Builder $query): Builder
    {
        return $query->where('status', 'inactive');
    }
}