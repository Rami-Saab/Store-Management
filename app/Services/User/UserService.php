<?php // Name : Rodain Gouzlan Id:

namespace App\Services\User;

/**
 * Service: خدمات المستخدمين ضمن إدارة الأفرع
 *
 * الهدف:
 * - توحيد استعلامات جلب المدراء والموظفين المرتبطين بقسم إدارة الأفرع (store_management).
 * - الإبقاء على Controllers بسيطة عبر استدعاء دوال جاهزة (queryStoreManagers/queryStoreEmployees).
 */

use App\Models\User;
use App\Models\Department;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class UserService
{
    private static ?int $storeDepartmentIdCache = null;
    public function queryStoreManagers(array $columns = ['id', 'name']): Builder
    {
        // Query Builder جاهز للمدراء ضمن قسم إدارة الأفرع (يعتمد على scopeStoreManagers في نموذج User).
        $departmentId = $this->storeDepartmentId();
        return User::eligibleManagers()
            ->select($columns)
            ->when($departmentId, fn ($query) => $query->where('department_id', $departmentId))
            ->orderBy('name');
    }

    public function queryStoreEmployees(array $columns = ['id', 'name']): Builder
    {
        // Query Builder جاهز لموظفي الأفرع ضمن نفس القسم (يعتمد على scopeStoreEmployees).
        $departmentId = $this->storeDepartmentId();
        return User::storeEmployees()
            ->select($columns)
            ->when($departmentId, fn ($query) => $query->where('department_id', $departmentId))
            ->orderBy('name');
    }

    public function storeManagers(array $columns = ['id', 'name']): Collection
    {
        // تنفيذ استعلام المدراء وإرجاع النتائج كـ Collection.
        return $this->queryStoreManagers($columns)->get();
    }

    public function storeEmployees(array $columns = ['id', 'name']): Collection
    {
        // تنفيذ استعلام الموظفين وإرجاع النتائج كـ Collection.
        return $this->queryStoreEmployees($columns)->get();
    }

    private function storeDepartmentId(): ?int
    {
        if (self::$storeDepartmentIdCache !== null) {
            return self::$storeDepartmentIdCache > 0 ? self::$storeDepartmentIdCache : null;
        }

        if (! Schema::hasTable('departments')) {
            self::$storeDepartmentIdCache = 0;
            return null;
        }

        $id = Cache::remember(
            'store_management_department_id:v1',
            now()->addMinutes(30),
            fn () => Department::query()->where('slug', 'store_management')->value('id')
        );

        self::$storeDepartmentIdCache = $id ? (int) $id : 0;

        return self::$storeDepartmentIdCache > 0 ? self::$storeDepartmentIdCache : null;
    }
}
