<?php // Name : Rodain Gouzlan Id:

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Province;
use App\Models\Store;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// Seeder بيانات وحدة إدارة الفروع (Block 3).
// يقوم بإدخال متاجر ومنتجات وتعيينات أساسية للتجربة.
// هذا الملف يمثل وظيفة "StoreSeeder" المذكورة في شجرة المشروع.
class StoreModuleSeeder extends Seeder
{
    public function run(): void
    {
        // التأكد من وجود البيانات الأساسية (المحافظات).
        (new ProvinceSeeder())->run();

        $byNameOrFail = function (string $name): User {
            $user = User::query()->where('name', $name)->first();
            if (! $user) {
                throw new \RuntimeException("Locked user missing: {$name}");
            }
            return $user;
        };

        $systemAdmin = User::query()->where('role', 'admin')->orderBy('id')->first() ?: $byNameOrFail('Rodain');
        $storeDepartmentId = DB::table('departments')->where('slug', 'store_management')->value('id');
        $auditFields = [];
        if (Schema::hasColumn('stores', 'created_by')) {
            $auditFields['created_by'] = $systemAdmin->id;
        }
        if (Schema::hasColumn('stores', 'updated_by')) {
            $auditFields['updated_by'] = $systemAdmin->id;
        }
        if ($storeDepartmentId) {
            $auditFields['department_id'] = $storeDepartmentId;
        }
        $employees = User::query()
            ->where('role', 'store_employee')
            ->when($storeDepartmentId, fn ($query) => $query->where('department_id', $storeDepartmentId))
            ->orderBy('id')
            ->get();

        // إدخال 56 منتجاً واقعياً (أسماء شبيهة بالعلامات التجارية) لتجربة متطلبات النظام.
        $productsCatalog = collect([
            ['name' => 'Samsung Galaxy S24 Ultra', 'sku' => 'CAT-001', 'price' => 12500000],
            ['name' => 'Apple iPhone 15 Pro', 'sku' => 'CAT-002', 'price' => 13500000],
            ['name' => 'Apple iPad 10th Gen', 'sku' => 'CAT-003', 'price' => 6200000],
            ['name' => 'Samsung Galaxy Tab S9', 'sku' => 'CAT-004', 'price' => 7400000],
            ['name' => 'Apple Watch Series 9', 'sku' => 'CAT-005', 'price' => 3200000],
            ['name' => 'Samsung Galaxy Watch6', 'sku' => 'CAT-006', 'price' => 2400000],
            ['name' => 'Sony WH-1000XM5 Headphones', 'sku' => 'CAT-007', 'price' => 1900000],
            ['name' => 'PlayStation 5 Console', 'sku' => 'CAT-008', 'price' => 6200000],
            ['name' => 'ASUS ROG Strix G16 Laptop', 'sku' => 'CAT-009', 'price' => 9800000],

            ['name' => 'Apple AirPods Pro 2', 'sku' => 'PRD-010', 'price' => 1650000],
            ['name' => 'JBL Tune 760NC', 'sku' => 'PRD-011', 'price' => 520000],
            ['name' => 'Sony WF-1000XM5 Earbuds', 'sku' => 'PRD-012', 'price' => 1750000],
            ['name' => 'Samsung Galaxy Buds2 Pro', 'sku' => 'PRD-013', 'price' => 980000],
            ['name' => 'Beats Studio Buds', 'sku' => 'PRD-014', 'price' => 820000],
            ['name' => 'Anker Soundcore Q45', 'sku' => 'PRD-015', 'price' => 690000],
            ['name' => 'JBL Live Pro 2', 'sku' => 'PRD-016', 'price' => 860000],
            ['name' => 'Apple AirPods 3', 'sku' => 'PRD-017', 'price' => 1050000],
            ['name' => 'Sony WH-CH720N', 'sku' => 'PRD-018', 'price' => 620000],

            ['name' => 'Samsung Galaxy A55', 'sku' => 'PRD-019', 'price' => 3600000],
            ['name' => 'Samsung Galaxy S23', 'sku' => 'PRD-020', 'price' => 8200000],
            ['name' => 'Apple iPhone 14', 'sku' => 'PRD-021', 'price' => 9800000],
            ['name' => 'Apple iPhone 15', 'sku' => 'PRD-022', 'price' => 11200000],
            ['name' => 'Google Pixel 8', 'sku' => 'PRD-023', 'price' => 7600000],
            ['name' => 'Xiaomi 13T', 'sku' => 'PRD-024', 'price' => 4900000],
            ['name' => 'OnePlus 12', 'sku' => 'PRD-025', 'price' => 7100000],
            ['name' => 'Oppo Reno 11', 'sku' => 'PRD-026', 'price' => 4200000],
            ['name' => 'Huawei Nova 12', 'sku' => 'PRD-027', 'price' => 4100000],
            ['name' => 'Samsung Galaxy Z Flip5', 'sku' => 'PRD-028', 'price' => 11500000],
            ['name' => 'Apple iPhone 15 Pro Max', 'sku' => 'PRD-029', 'price' => 15200000],

            ['name' => 'PlayStation 4 Console', 'sku' => 'PRD-030', 'price' => 2800000],
            ['name' => 'Xbox One S Console', 'sku' => 'PRD-031', 'price' => 2600000],
            ['name' => 'Nintendo Switch Console', 'sku' => 'PRD-032', 'price' => 3200000],
            ['name' => 'PlayStation 5 Slim Console', 'sku' => 'PRD-033', 'price' => 6400000],
            ['name' => 'PS5 DualSense Controller', 'sku' => 'PRD-034', 'price' => 520000],
            ['name' => 'Xbox Wireless Controller', 'sku' => 'PRD-035', 'price' => 480000],

            ['name' => 'ASUS ROG Zephyrus G14', 'sku' => 'PRD-036', 'price' => 10500000],
            ['name' => 'Lenovo Legion 7', 'sku' => 'PRD-037', 'price' => 9900000],
            ['name' => 'Dell XPS 13 Laptop', 'sku' => 'PRD-038', 'price' => 9200000],
            ['name' => 'Dell G15 Gaming Laptop', 'sku' => 'PRD-039', 'price' => 7800000],
            ['name' => 'HP Victus 16', 'sku' => 'PRD-040', 'price' => 7400000],
            ['name' => 'Acer Nitro 5', 'sku' => 'PRD-041', 'price' => 6900000],
            ['name' => 'MSI Katana 15', 'sku' => 'PRD-042', 'price' => 7600000],
            ['name' => 'MacBook Air M3', 'sku' => 'PRD-043', 'price' => 14500000],
            ['name' => 'MacBook Pro 14 M3', 'sku' => 'PRD-044', 'price' => 18500000],
            ['name' => 'Lenovo ThinkPad E14', 'sku' => 'PRD-045', 'price' => 6100000],

            ['name' => 'Canon EOS R50 Camera', 'sku' => 'PRD-046', 'price' => 7200000],
            ['name' => 'Sony Alpha a6400 Camera', 'sku' => 'PRD-047', 'price' => 8600000],
            ['name' => 'GoPro HERO12 Black', 'sku' => 'PRD-048', 'price' => 4300000],

            ['name' => 'Samsung Smart TV 55\" 4K', 'sku' => 'PRD-049', 'price' => 8400000],
            ['name' => 'LG OLED TV 55\" C3', 'sku' => 'PRD-050', 'price' => 12600000],
            ['name' => 'Sony Bravia 65\" 4K', 'sku' => 'PRD-051', 'price' => 13900000],
            ['name' => 'TCL QLED TV 55\" 4K', 'sku' => 'PRD-052', 'price' => 7200000],
            ['name' => 'Xiaomi Smart TV 50\" 4K', 'sku' => 'PRD-053', 'price' => 5900000],

            ['name' => 'Anker PowerCore 20000 Power Bank', 'sku' => 'PRD-054', 'price' => 310000],
            ['name' => 'USB-C Fast Charger 65W', 'sku' => 'PRD-055', 'price' => 170000],
            ['name' => 'USB-C Cable 2m (Braided)', 'sku' => 'PRD-056', 'price' => 65000],
        ]);

        $products = $productsCatalog->map(function (array $product) {
            return Product::updateOrCreate(
                ['sku' => $product['sku']],
                $product + ['status' => 'available']
            );
        });

        // الحفاظ على اتساق كتالوج النظام: فقط هذه الـ SKUs تبقى بحالة available (56 منتج).
        // أي منتجات قديمة خارج الكتالوج تصبح inactive حتى لا تظهر في شاشة ربط المنتجات.
        $catalogSkus = $productsCatalog->pluck('sku')->values()->all();
        Product::whereNotIn('sku', $catalogSkus)->update(['status' => 'inactive']);

        $damascus = Store::updateOrCreate(
            ['branch_code' => 'DAM-001'],
            [
                'name' => 'Damascus Branch (Main)',
                'province_id' => Province::where('code', 'DAM')->value('id'),
                'city' => 'Damascus',
                'address' => 'Al-Malki - Al-Jalaa Street',
                'phone' => '0987111001',
                'email' => 'damascus.branch@gmail.com',
                'status' => 'active',
                'opening_date' => '2025-09-01',
                'workday_starts_at' => '09:00',
                'workday_ends_at' => '17:00',
            ] + $auditFields
        );

        $homs = Store::updateOrCreate(
            ['branch_code' => 'HMS-002'],
            [
                'name' => 'Homs Branch',
                'province_id' => Province::where('code', 'HMS')->value('id'),
                'city' => 'Homs',
                'address' => 'Al-Waer District - Al-Hadara Street',
                'phone' => '0987333002',
                'email' => 'homs.branch@gmail.com',
                'status' => 'active',
                'opening_date' => '2025-10-15',
                'workday_starts_at' => '09:00',
                'workday_ends_at' => '17:00',
            ] + $auditFields
        );

        $aleppo = Store::updateOrCreate(
            ['branch_code' => 'ALP-003'],
            [
                'name' => 'Aleppo Branch',
                'province_id' => Province::where('code', 'ALP')->value('id'),
                'city' => 'Aleppo',
                'address' => 'Al-Jamiliyah - Baron Street',
                'phone' => '0987222003',
                'email' => 'aleppo.branch@gmail.com',
                'status' => 'active',
                'opening_date' => '2025-11-05',
                'workday_starts_at' => '09:00',
                'workday_ends_at' => '17:00',
            ] + $auditFields
        );

        $lattakia = Store::updateOrCreate(
            ['branch_code' => 'LAT-004'],
            [
                'name' => 'Latakia Branch',
                'province_id' => Province::where('code', 'LAT')->value('id'),
                'city' => 'Latakia',
                'address' => 'Al-Salibeh - Baghdad Street',
                'phone' => '0987444004',
                'email' => 'lattakia.branch@gmail.com',
                'status' => 'active',
                'opening_date' => '2025-12-01',
                'workday_starts_at' => '09:00',
                'workday_ends_at' => '17:00',
            ] + $auditFields
        );

        $sweida = Store::updateOrCreate(
            ['branch_code' => 'SWD-005'],
            [
                'name' => 'As-Suwayda Branch',
                'province_id' => Province::where('code', 'SWD')->value('id'),
                'city' => 'As-Suwayda',
                'address' => 'Al-Qarya Road - Corniche Street',
                'phone' => '0987555005',
                'email' => 'sweida.branch@gmail.com',
                'status' => 'active',
                'opening_date' => '2025-12-10',
                'workday_starts_at' => '08:30',
                'workday_ends_at' => '16:30',
            ] + $auditFields
        );

        $hama = Store::updateOrCreate(
            ['branch_code' => 'HMA-006'],
            [
                'name' => 'Hama Branch',
                'province_id' => Province::where('code', 'HMA')->value('id'),
                'city' => 'Hama',
                'address' => 'Al-Hader - Al-Murabit Street',
                'phone' => '0987666006',
                'email' => 'hama.branch@gmail.com',
                'status' => 'active',
                'opening_date' => '2025-12-20',
                'workday_starts_at' => '09:30',
                'workday_ends_at' => '18:00',
            ] + $auditFields
        );

        $staffStores = collect([$damascus, $aleppo, $homs, $lattakia, $sweida, $hama])
            ->filter()
            ->sortBy(fn (Store $store) => (string) $store->branch_code)
            ->values();

        $seedAssignments = $staffStores->isNotEmpty()
            && $staffStores->every(fn (Store $store) => ! $store->employees()->exists() && ! $store->manager_id);

        if ($seedAssignments) {
        $eligibleManagers = User::query()
                ->where('role', 'store_manager')
                ->when($storeDepartmentId, fn ($query) => $query->where('department_id', $storeDepartmentId))
                ->orderBy('id')
                ->get()
                ->values();

            $managerCount = $eligibleManagers->count();
            $managerIdsByStore = [];
            foreach ($staffStores as $index => $store) {
                if ($managerCount === 0) {
                    break;
                }

                $manager = $eligibleManagers[$index % $managerCount];
                $store->update(['manager_id' => $manager->id]);
                User::query()->where('id', $manager->id)->update(['store_id' => $store->id]);
                $managerIdsByStore[$store->id] = $manager->id;
            }

            $employeePool = $employees
                ->reject(fn (User $employee) => in_array($employee->id, $managerIdsByStore, true))
                ->values();

            $storeCount = $staffStores->count();
            $employeesByStore = [];
            foreach ($employeePool as $index => $employee) {
                $store = $staffStores[$index % $storeCount];
                $employeesByStore[$store->id][] = $employee->id;
            }

            foreach ($staffStores as $store) {
                $store->employees()->sync($employeesByStore[$store->id] ?? []);
            }
        }

        // توزيع المنتجات على جميع الأفرع بشكل متوازن وثابت (Deterministic).
        // كل منتج يتم ربطه بفرعين لزيادة التغطية مع الحفاظ على توازن الأعداد.
        $storesForProducts = collect([$damascus, $aleppo, $homs, $lattakia, $sweida, $hama])
            ->filter()
            ->values();

        if ($storesForProducts->isNotEmpty() && $products->isNotEmpty()) {
            $storeCount = $storesForProducts->count();
            $storeProductMap = [];
            foreach ($storesForProducts as $store) {
                $storeProductMap[$store->id] = [];
            }

            $sortedProducts = $products->sortBy('sku')->values();
            foreach ($sortedProducts as $index => $product) {
                $storeA = $storesForProducts[$index % $storeCount];
                $storeB = $storesForProducts[($index + 2) % $storeCount];
                $storeProductMap[$storeA->id][] = $product->id;
                $storeProductMap[$storeB->id][] = $product->id;
            }

            foreach ($storesForProducts as $store) {
                $ids = collect($storeProductMap[$store->id] ?? [])->unique()->values()->all();
                $store->products()->sync($ids);
            }
        }

        // متطلب المشروع: البريد الإلكتروني ليس ضرورياً للمستخدمين في هذا النظام.
        User::query()->whereNotNull('email')->update(['email' => null]);
    }
}