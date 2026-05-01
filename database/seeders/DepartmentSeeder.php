<?php // Name : Rodain Gouzlan Id:

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $departments = [
            ['name' => 'Store Management', 'slug' => 'store_management'],
            ['name' => 'Administration', 'slug' => 'administration'],
            ['name' => 'Human Resources', 'slug' => 'hr'],
            ['name' => 'Marketing', 'slug' => 'marketing'],
        ];

        foreach ($departments as $department) {
            DB::table('departments')->updateOrInsert(
                ['slug' => $department['slug']],
                ['name' => $department['name']]
            );
        }

        // Backfill department_id for users/stores when the column exists.
        $departmentIds = DB::table('departments')->pluck('id', 'slug');
        if ($departmentIds->isNotEmpty()) {
            if (Schema::hasTable('users') && Schema::hasColumn('users', 'department_id')) {
                foreach ($departmentIds as $slug => $id) {
                    DB::table('users')
                        ->whereNull('department_id')
                        ->where('department', $slug)
                        ->update(['department_id' => $id]);
                }
            }

            $storeManagementId = $departmentIds['store_management'] ?? null;
            if ($storeManagementId) {
                if (Schema::hasTable('users') && Schema::hasColumn('users', 'department_id')) {
                    DB::table('users')
                        ->whereNull('department_id')
                        ->update(['department_id' => $storeManagementId]);
                }

                if (Schema::hasTable('stores') && Schema::hasColumn('stores', 'department_id')) {
                    DB::table('stores')
                        ->whereNull('department_id')
                        ->update(['department_id' => $storeManagementId]);
                }
            }
        }
    }
}