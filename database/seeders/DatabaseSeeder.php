<?php // Name : Rodain Gouzlan Id:

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Seeder الرئيسي لقاعدة البيانات.
 *
 * يقوم باستدعاء Seeders الخاصة بالمشروع:
 * - ProvinceSeeder: إدخال المحافظات الأساسية.
 * - StoreModuleSeeder: إدخال بيانات تجريبية/أساسية لوحدة إدارة الأفرع (Block 3).
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            DepartmentSeeder::class,
            ProvinceSeeder::class,
            StoreRolePermissionSeeder::class,
            UserRoleSeeder::class,
            StoreModuleSeeder::class,
            StoreAssignmentsSeeder::class,
        ]);
    }
}