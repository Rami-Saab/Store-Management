<?php // Name : Rodain Gouzlan Id:

namespace Database\Seeders;

use App\Models\Department;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class StoreManagersSeeder extends Seeder
{
    public function run(): void
    {
        $names = [
            'Nour Hassan',
            'Omar Khaled',
            'Lina Al Darwish',
            'Samer Nasser',
            'Hiba Ahmad',
        ];

        $departmentSlug = 'store_management';
        $departmentId = null;
        if (Schema::hasTable('departments')) {
            $departmentId = Department::query()->where('slug', $departmentSlug)->value('id');
        }

        foreach ($names as $index => $name) {
            $email = 'manager.'.Str::slug($name, '.').'.'.($index + 1).'@branch.local';

            User::firstOrCreate(
                ['email' => $email],
                [
                    'name' => $name,
                    'password' => Hash::make(Str::random(24)),
                    'department' => $departmentSlug,
                    'department_id' => $departmentId,
                    'job_title' => 'store_manager',
                    'role' => 'store_manager',
                    'store_id' => null,
                    'phone' => null,
                    'status' => 'active',
                ]
            );
        }
    }
}