<?php // Name : Rodain Gouzlan Id:

// هذا الملف يعيد هيكلة أدوار المستخدمين وتعيينات الفروع لتوحيد آلية إدارة المديرين والموظفين.

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // تنفيذ التعديلات والترحيل للبيانات القديمة.
    public function up(): void
    {
        // إضافة عمود role للمستخدمين إذا لم يكن موجوداً.
        $hasRole = Schema::hasColumn('users', 'role');
        if (! $hasRole) {
            Schema::table('users', function (Blueprint $table) {
                // role يحدد نوع المستخدم بشكل مبسط (admin/manager/employee).
                $table->string('role', 40)->default('employee')->after('job_title');
                // فهرس لتحسين الاستعلامات حسب الدور.
                $table->index('role', 'users_role_idx');
            });
        }

        // تحويل بيانات الدور اعتماداً على job_title القديمة عند توفرها.
        if (Schema::hasColumn('users', 'job_title')) {
            DB::table('users')->whereNull('role')->update(['role' => 'employee']);
            DB::statement("
                UPDATE users
                SET role = CASE
                    WHEN job_title = 'system administrator' THEN 'admin'
                    WHEN job_title = 'store_manager' THEN 'store_manager'
                    WHEN job_title = 'store_employee' THEN 'employee'
                    ELSE role
                END
            ");
        }

        // إضافة manager_id للمتاجر إذا لم يكن موجوداً.
        if (! Schema::hasColumn('stores', 'manager_id')) {
            Schema::table('stores', function (Blueprint $table) {
                // ربط المتجر بمدير (مستخدم) مع حذف مرن عند حذف المدير.
                $table->foreignId('manager_id')
                    ->nullable()
                    ->after('province_id')
                    ->constrained('users')
                    ->nullOnDelete();
                // فهرس لتحسين البحث بمدير الفرع.
                $table->index('manager_id', 'stores_manager_id_idx');
            });
        }

        // دالة مساعدة لفحص وجود الفهارس قبل حذفها.
        $indexExists = function (string $table, string $index): bool {
            try {
                $rows = DB::select('SHOW INDEX FROM '.$table.' WHERE Key_name = ?', [$index]);
                return ! empty($rows);
            } catch (\Throwable $e) {
                return false;
            }
        };

        // ترحيل تعيينات المديرين من جدول store_user إلى stores.manager_id.
        if (Schema::hasTable('store_user') && Schema::hasColumn('store_user', 'assignment_role')) {
            try {
                DB::statement("
                    UPDATE stores
                    INNER JOIN store_user ON store_user.store_id = stores.id
                    SET stores.manager_id = store_user.user_id
                    WHERE store_user.assignment_role = 'manager'
                ");
            } catch (\Throwable $e) {
                // تجاهل الخطأ في حال عدم دعم الاستعلام (مثلاً عند استخدام Driver غير MySQL).
            }

            // حذف صفوف المديرين القديمة من جدول الربط بعد نقلها.
            DB::table('store_user')
                ->where('assignment_role', 'manager')
                ->delete();

            // حذف الفهارس والأعمدة القديمة المرتبطة بـ assignment_role.
            $dropStoreRoleIdx = $indexExists('store_user', 'store_user_store_role_idx');
            $dropRoleIdx = $indexExists('store_user', 'store_user_role_idx');
            Schema::table('store_user', function (Blueprint $table) use ($dropStoreRoleIdx, $dropRoleIdx) {
                if ($dropStoreRoleIdx) {
                    $table->dropIndex('store_user_store_role_idx');
                }
                if ($dropRoleIdx) {
                    $table->dropIndex('store_user_role_idx');
                }
                if (Schema::hasColumn('store_user', 'assignment_role')) {
                    $table->dropColumn('assignment_role');
                }
            });
        }
    }

    public function down(): void
    {
        // دالة مساعدة لفحص وجود الفهارس قبل حذفها/إضافتها.
        $indexExists = function (string $table, string $index): bool {
            try {
                $rows = DB::select('SHOW INDEX FROM '.$table.' WHERE Key_name = ?', [$index]);
                return ! empty($rows);
            } catch (\Throwable $e) {
                return false;
            }
        };

        // إزالة manager_id من جدول المتاجر.
        if (Schema::hasColumn('stores', 'manager_id')) {
            Schema::table('stores', function (Blueprint $table) {
                $table->dropForeign(['manager_id']);
                if (Schema::hasColumn('stores', 'manager_id')) {
                    $table->dropIndex('stores_manager_id_idx');
                }
                $table->dropColumn('manager_id');
            });
        }

        // إعادة عمود assignment_role لجدول store_user في حالة التراجع.
        if (Schema::hasTable('store_user') && ! Schema::hasColumn('store_user', 'assignment_role')) {
            Schema::table('store_user', function (Blueprint $table) {
                // استرجاع الدور الافتراضي للموظف.
                $table->string('assignment_role', 30)->default('employee');
                // فهارس لاستعلامات الدور.
                $table->index(['store_id', 'assignment_role'], 'store_user_store_role_idx');
                $table->index(['assignment_role'], 'store_user_role_idx');
            });
        }

        // إزالة عمود role من المستخدمين عند التراجع.
        if (Schema::hasColumn('users', 'role')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropIndex('users_role_idx');
                $table->dropColumn('role');
            });
        }
    }
};

// Summary: يعيد تنظيم أدوار المستخدمين ويحوّل تعيين المديرين إلى manager_id داخل stores مع تنظيف الحقول القديمة.