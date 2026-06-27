<?php // Name : Rodain Gouzlan Id:

namespace Database\Seeders;

use App\Models\Province;
use Illuminate\Database\Seeder;

/**
 * Seeder: المحافظات (Provinces)
 *
 * يقوم بإضافة/تحديث قائمة المحافظات الأساسية مع الأكواد الخاصة بها.
 * تستخدم هذه البيانات في نموذج Store عبر province_id.
 */
class ProvinceSeeder extends Seeder
{
    public function run(): void
    {
        $provinces = [
            ['name' => 'Damascus', 'code' => 'DAM'],
            ['name' => 'Rural Damascus', 'code' => 'RDM'],
            ['name' => 'Quneitra', 'code' => 'QUN'],
            ['name' => 'Daraa', 'code' => 'DAR'],
            ['name' => 'As-Suwayda', 'code' => 'SWD'],
            ['name' => 'Homs', 'code' => 'HMS'],
            ['name' => 'Hama', 'code' => 'HMA'],
            ['name' => 'Tartus', 'code' => 'TAR'],
            ['name' => 'Latakia', 'code' => 'LAT'],
            ['name' => 'Idlib', 'code' => 'IDL'],
            ['name' => 'Aleppo', 'code' => 'ALP'],
            ['name' => 'Raqqa', 'code' => 'RAQ'],
            ['name' => 'Deir ez-Zor', 'code' => 'DEZ'],
            ['name' => 'Al-Hasakah', 'code' => 'HSK'],
        ];

        foreach ($provinces as $province) {
            Province::updateOrCreate(['code' => $province['code']], $province);
        }
    }
}