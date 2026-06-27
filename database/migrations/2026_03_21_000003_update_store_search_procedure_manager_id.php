<?php // Name : Rodain Gouzlan Id:

// هذا الملف يحدّث الإجراء المخزن للبحث عن الفروع ليعيد اسم المدير باستخدام manager_id.

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    // إعادة إنشاء الإجراء المخزن بالشكل الجديد.
    public function up(): void
    {
        // الإجراء المخزن مدعوم فقط في MySQL.
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        // حذف الإجراء القديم إن وجد لتجنب التعارض.
        DB::unprepared('DROP PROCEDURE IF EXISTS sp_search_store_branches');
        // إنشاء إجراء جديد يدعم اسم المدير والعدّادات.
        DB::unprepared("
            CREATE PROCEDURE sp_search_store_branches (
                IN p_name VARCHAR(255),
                IN p_status VARCHAR(30),
                IN p_province_id BIGINT,
                IN p_phone VARCHAR(50)
            )
            BEGIN
                SELECT
                    stores.id,
                    stores.name,
                    stores.branch_code,
                    provinces.name AS province_name,
                    stores.city,
                    stores.status,
                    stores.opening_date,
                    stores.brochure_path,
                    (
                        SELECT users.name
                        FROM users
                        WHERE users.id = stores.manager_id
                        LIMIT 1
                    ) AS manager_name,
                    (
                        SELECT COUNT(*)
                        FROM store_user
                        WHERE store_user.store_id = stores.id
                    ) AS employees_count,
                    (
                        SELECT COUNT(*)
                        FROM product_store
                        WHERE product_store.store_id = stores.id
                    ) AS products_count
                FROM stores
                INNER JOIN provinces ON provinces.id = stores.province_id
                WHERE (p_name IS NULL OR stores.name LIKE CONCAT('%', p_name, '%'))
                  AND (p_status IS NULL OR stores.status = p_status)
                  AND (p_province_id IS NULL OR stores.province_id = p_province_id)
                  AND (p_phone IS NULL OR stores.phone LIKE CONCAT('%', p_phone, '%'))
                ORDER BY stores.created_at DESC;
            END
        ");
    }

    // التراجع: حذف الإجراء المخزن.
    public function down(): void
    {
        // الإجراء المخزن مدعوم فقط في MySQL.
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        // حذف الإجراء في حالة التراجع.
        DB::unprepared('DROP PROCEDURE IF EXISTS sp_search_store_branches');
    }
};

// Summary: يعيد بناء إجراء البحث عن الفروع ليعرض اسم المدير عبر manager_id مع نفس فلاتر البحث.