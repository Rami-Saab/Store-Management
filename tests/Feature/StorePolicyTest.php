<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Product;
use App\Models\Province;
use App\Models\Role;
use App\Models\Store;
use App\Models\User;
use Database\Seeders\StoreRolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StorePolicyTest extends TestCase
{
    use RefreshDatabase;

    private Department $department;
    private Province $province;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(StoreRolePermissionSeeder::class);

        $this->department = Department::create([
            'name' => 'Store Management',
            'slug' => 'store_management',
        ]);

        $this->province = Province::create([
            'name' => 'Damascus',
            'code' => 'DAM',
        ]);
    }

    public function test_store_employee_can_create_update_assigned_store_but_cannot_delete(): void
    {
        $employee = $this->makeUser('store_employee');
        $store = $this->makeStore(['department_id' => $this->department->id]);
        $store->employees()->attach($employee->id);

        $this->assertTrue($employee->can('viewAny', Store::class));
        $this->assertTrue($employee->can('view', $store));
        $this->assertTrue($employee->can('create', Store::class));
        $this->assertTrue($employee->can('update', $store));
        $this->assertFalse($employee->can('delete', $store));
    }

    public function test_store_employee_cannot_view_store_not_assigned(): void
    {
        $employee = $this->makeUser('store_employee');
        $store = $this->makeStore(['department_id' => $this->department->id]);

        $this->assertFalse($employee->can('view', $store));
        $this->assertFalse($employee->can('update', $store));
    }

    public function test_store_manager_can_view_only_assigned_store(): void
    {
        $manager = $this->makeUser('store_manager');
        $assignedStore = $this->makeStore([
            'manager_id' => $manager->id,
            'department_id' => $this->department->id,
        ]);
        $manager->update(['store_id' => $assignedStore->id]);
        $otherStore = $this->makeStore(['department_id' => $this->department->id]);

        $this->assertTrue($manager->can('view', $assignedStore));
        $this->assertFalse($manager->can('view', $otherStore));
        $this->assertFalse($manager->can('create', Store::class));
        $this->assertFalse($manager->can('update', $assignedStore));
        $this->assertTrue($manager->can('delete', $assignedStore));
    }

    public function test_store_manager_cannot_delete_when_linked_to_products(): void
    {
        $manager = $this->makeUser('store_manager');
        $store = $this->makeStore([
            'manager_id' => $manager->id,
            'department_id' => $this->department->id,
        ]);
        $manager->update(['store_id' => $store->id]);
        $product = Product::create([
            'name' => 'Sample Product',
            'sku' => 'SKU-001',
            'price' => 100,
            'status' => 'active',
        ]);
        $store->products()->attach($product->id);

        $this->assertFalse($manager->can('delete', $store));
    }

    public function test_admin_can_manage_any_store_even_when_linked(): void
    {
        $admin = $this->makeUser('admin');
        $store = $this->makeStore(['department_id' => $this->department->id]);

        $this->assertTrue($admin->can('view', $store));
        $this->assertTrue($admin->can('create', Store::class));
        $this->assertTrue($admin->can('update', $store));
        $this->assertTrue($admin->can('delete', $store));

        $product = Product::create([
            'name' => 'Locked Product',
            'sku' => 'SKU-LOCK',
            'price' => 50,
            'status' => 'active',
        ]);
        $store->products()->attach($product->id);

        $this->assertTrue($admin->can('delete', $store));
    }

    private function makeUser(string $role, array $attributes = []): User
    {
        $user = User::factory()->create(array_merge([
            'role' => $role,
            'status' => 'active',
            'department_id' => $this->department->id,
        ], $attributes));

        $roleModel = Role::where('slug', $role)->first();
        if ($roleModel) {
            $user->roles()->sync([$roleModel->id]);
        }

        return $user;
    }

    private function makeStore(array $attributes = []): Store
    {
        $counter = Store::count() + 1;

        return Store::create(array_merge([
            'name' => 'Store '.$counter,
            'branch_code' => 'BR-'.$counter,
            'province_id' => $this->province->id,
            'city' => 'Damascus',
            'address' => 'Main Street',
            'phone' => '0990000000',
            'status' => 'active',
            'opening_date' => now()->subDay()->toDateString(),
            'department_id' => $this->department->id,
        ], $attributes));
    }
}
