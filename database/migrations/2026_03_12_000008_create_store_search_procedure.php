<?php // Name : Rodain Gouzlan Id:

/**
 * Migration: إنشاء Stored Procedure للبحث عن الأفرع (MySQL)
 *
 * المتطلب الأكاديمي:
 * - استخدام Stored Procedure مرة واحدة على الأقل ضمن هذا الـ Module.
 *
 * ما يفعله الإجراء المخزن:
 * - يبحث في جدول stores مع الانضمام إلى provinces.
 * - يعيد أيضاً اسم مدير الفرع وعدد الموظفين وعدد المنتجات المرتبطة (Counts) عبر Subqueries.
 *
 * ملاحظة:
 * - يتم إنشاء الإجراء فقط عندما تكون قاعدة البيانات MySQL.
 * - هذا الملف يمثل ما قد يُذكر في الشجرة كـ store_procedures.sql.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::unprepared('DROP PROCEDURE IF EXISTS sp_search_store_branches');
        DB::unprepared("
            CREATE PROCEDURE sp_search_store_branches (
                IN p_name VARCHAR(255),
                IN p_status VARCHAR(30),
                IN p_province_id BIGINT
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
                        FROM store_user
                        INNER JOIN users ON users.id = store_user.user_id
                        WHERE store_user.store_id = stores.id
                          AND store_user.assignment_role = 'manager'
                        LIMIT 1
                    ) AS manager_name,
                    (
                        SELECT COUNT(*)
                        FROM store_user
                        WHERE store_user.store_id = stores.id
                          AND store_user.assignment_role = 'employee'
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
                ORDER BY stores.created_at DESC;
            END
        ");
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::unprepared('DROP PROCEDURE IF EXISTS sp_search_store_branches');
    }
};

// Summary: ينشئ إجراء مخزن للبحث عن الفروع في MySQL مع فلاتر وحسابات للمدير والموظفين والمنتجات.