<?php // Name : Rodain Gouzlan Id:

namespace App\Models;

// نموذج المستخدمين في النظام (مدير النظام، مدير فرع، موظف فرع، وغيرهم).
// يتم استخدامه أيضاً كبديل عن نموذج Employee في سياق تعيينات الفروع.
use App\Support\UserContact;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\Schema;

/**
 * Model: User
 *
 * يمثل حسابات المستخدمين ويربطهم بالفروع والأدوار والصلاحيات.
 * يعتمد على:
 * - العلاقة مع Store عبر store_user
 * - الدور role المباشر أو أدوار Pivot (roles)
 */
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    // كاش داخلي لنتائج التحقق من الدور والصلاحيات داخل نفس الطلب.
    /**
     * Cache results of role/permission checks within the current request.
     */
    protected array $roleCheckCache = [];
    protected array $permissionCheckCache = [];

    // كاش لحالة توفر جداول الأدوار/الصلاحيات لتفادي استعلامات متكررة.
    /**
     * Cache schema table availability within the current request.
     */
    protected static ?bool $rolesPivotAvailable = null;
    protected static ?bool $permissionsPivotAvailable = null;

    // الحقول القابلة للتعبئة الجماعية (Mass Assignment).
    /**
     * @var array<int, string>
     */
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

    // الحقول الحساسة التي لا يجب إرجاعها في JSON.
    /**
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    // تحويلات تلقائية للحقول عند القراءة/الكتابة.
    /**
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    // معرّف الموظف لواجهات العرض (alias للـ id).
    public function getStaffIdAttribute(): ?string
    {
        if (! $this->getKey()) {
            return null;
        }

        return (string) $this->getKey();
    }

    // تنسيق رقم الهاتف عند القراءة (عرض موحد).
    public function getPhoneAttribute($value): string
    {
        return UserContact::phone($value);
    }

    // تنسيق رقم الهاتف عند الحفظ (إزالة الزوائد).
    public function setPhoneAttribute($value): void
    {
        $this->attributes['phone'] = UserContact::phone($value, false);
    }

    // علاقة المستخدم بالفروع عبر pivot store_user.
    public function stores(): BelongsToMany
    {
        return $this->belongsToMany(Store::class)->withTimestamps();
    }

    // علاقة المستخدم بقسمه (Department).
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    // الفرع المعين للمستخدم بشكل مباشر عبر store_id.
    public function assignedStore(): BelongsTo
    {
        return $this->belongsTo(Store::class, 'store_id');
    }

    // أدوار المستخدم عبر جدول role_user (إن كان موجوداً).
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class)->withTimestamps();
    }

    // الفرع الذي يديره المستخدم كمدير فرع.
    public function managedStore(): HasOne
    {
        return $this->hasOne(Store::class, 'manager_id');
    }

    // التحقق من امتلاك المستخدم لدور معين مع استخدام الكاش.
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

    // التحقق من امتلاك المستخدم لصلاحية معينة مع مراعاة الأدوار والقيود.
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

    // فحص توفر جداول الأدوار في قاعدة البيانات.
    private static function rolesPivotReady(): bool
    {
        if (self::$rolesPivotAvailable !== null) {
            return self::$rolesPivotAvailable;
        }

        self::$rolesPivotAvailable = Schema::hasTable('roles') && Schema::hasTable('role_user');

        return self::$rolesPivotAvailable;
    }

    // فحص توفر جداول الصلاحيات في قاعدة البيانات.
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

    // تحميل أدوار وصلاحيات المستخدم عند الحاجة فقط.
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

    // Scope: مدراء الفروع المؤهلون (نشطون).
    public function scopeEligibleManagers(Builder $query): Builder
    {
        return $query
            ->where('role', 'store_manager')
            ->where('status', 'active');
    }

    // Scope: موظفو الفروع النشطون.
    public function scopeStoreEmployees(Builder $query): Builder
    {
        return $query
            ->whereIn('role', ['store_employee', 'employee'])
            ->where('status', 'active');
    }
}