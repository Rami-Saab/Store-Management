<?php // Name : Rodain Gouzlan Id:

// هذا الملف يضيف حقول department_id و store_id للمستخدمين ويضيف department_id للمتاجر مع تعبئة بيانات قديمة.

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // تنفيذ الإضافة والتعبئة الخلفية للبيانات.
    public function up(): void
    {
        // إضافة department_id للمستخدمين إذا لم يكن موجوداً.
        if (! Schema::hasColumn('users', 'department_id')) {
            Schema::table('users', function (Blueprint $table) {
                // الربط بقسم اختياري مع حذف مرن (nullOnDelete).
                $table->foreignId('department_id')
                    ->nullable()
                    ->after('role')
                    ->constrained('departments')
                    ->nullOnDelete();
                // فهرس لتحسين البحث/الفلترة بالقسم.
                $table->index('department_id', 'users_department_id_idx');
            });
        }

        // إضافة store_id للمستخدمين إذا لم يكن موجوداً.
        if (! Schema::hasColumn('users', 'store_id')) {
            Schema::table('users', function (Blueprint $table) {
                // ربط المستخدم بفرع محدد بشكل اختياري.
                $table->foreignId('store_id')
                    ->nullable()
                    ->after('department_id')
                    ->constrained('stores')
                    ->nullOnDelete();
                // فهرس لتحسين الاستعلامات على الفرع.
                $table->index('store_id', 'users_store_id_idx');
            });
        }

        // إضافة department_id للمتاجر إذا لم يكن موجوداً.
        if (! Schema::hasColumn('stores', 'department_id')) {
            Schema::table('stores', function (Blueprint $table) {
                // ربط الفرع بقسم إداري بشكل اختياري.
                $table->foreignId('department_id')
                    ->nullable()
                    ->after('manager_id')
                    ->constrained('departments')
                    ->nullOnDelete();
                // فهرس لتحسين البحث حسب القسم.
                $table->index('department_id', 'stores_department_id_idx');
            });
        }

        // تعبئة department_id للمستخدمين اعتماداً على الحقل القديم النصي "department".
        if (Schema::hasTable('departments')) {
            // جلب الأقسام كخريطة (slug => id).
            $departments = DB::table('departments')->pluck('id', 'slug');
            if ($departments->isNotEmpty()) {
                foreach ($departments as $slug => $id) {
                    // تحديث المستخدمين الذين يملكون department نصي مطابق.
                    DB::table('users')
                        ->whereNull('department_id')
                        ->where('department', $slug)
                        ->update(['department_id' => $id]);
                }
            }

            // تعيين قسم افتراضي (store_management) عند غياب تعيين محدد.
            $storeManagementId = $departments['store_management'] ?? null;
            if ($storeManagementId) {
                DB::table('users')
                    ->whereNull('department_id')
                    ->update(['department_id' => $storeManagementId]);

                DB::table('stores')
                    ->whereNull('department_id')
                    ->update(['department_id' => $storeManagementId]);
            }
        }

        // مزامنة store_id لمديري الفروع من جدول stores.
        if (Schema::hasColumn('users', 'store_id') && Schema::hasColumn('stores', 'manager_id')) {
            DB::statement('
                UPDATE users
                INNER JOIN stores ON stores.manager_id = users.id
                SET users.store_id = stores.id
                WHERE users.store_id IS NULL
            ');
        }
    }

    public function down(): void
    {
        // إزالة department_id من جدول المتاجر.
        if (Schema::hasColumn('stores', 'department_id')) {
            Schema::table('stores', function (Blueprint $table) {
                $table->dropForeign(['department_id']);
                $table->dropIndex('stores_department_id_idx');
                $table->dropColumn('department_id');
            });
        }

        // إزالة store_id من جدول المستخدمين.
        if (Schema::hasColumn('users', 'store_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropForeign(['store_id']);
                $table->dropIndex('users_store_id_idx');
                $table->dropColumn('store_id');
            });
        }

        // إزالة department_id من جدول المستخدمين.
        if (Schema::hasColumn('users', 'department_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropForeign(['department_id']);
                $table->dropIndex('users_department_id_idx');
                $table->dropColumn('department_id');
            });
        }
    }
};

// Summary: يضيف أعمدة department_id/store_id مع مزامنة بيانات قديمة لضبط نطاق المستخدمين والفروع حسب الأقسام.