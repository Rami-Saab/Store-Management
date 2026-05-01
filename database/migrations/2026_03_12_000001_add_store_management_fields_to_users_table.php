<?php // Name : Rodain Gouzlan Id:

/**
 * Migration: توسيع جدول users ليتوافق مع نظام إدارة الأفرع
 *
 * الإضافات الأساسية:
 * - department : لتحديد القسم (مثل store_management)
 * - job_title  : لتحديد الدور (system administrator / store_manager / store_employee)
 * - phone      : رقم هاتف المستخدم
 * - status     : حالة المستخدم (active/inactive)
 *
 * ملاحظة:
 * - تم أيضاً جعل email قابلاً لأن يكون NULL لأن المشروع لا يعتمد على البريد بشكل أساسي.
 * - تم إضافة فهارس (indexes) لتحسين الاستعلامات الخاصة بجلب المدراء/الموظفين.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // فحص الأعمدة الحالية لتجنب محاولة إنشاء أعمدة موجودة (يدعم إعادة تشغيل migrations في بيئات مختلفة).
        $hasDepartment = Schema::hasColumn('users', 'department');
        $hasJobTitle = Schema::hasColumn('users', 'job_title');
        $hasPhone = Schema::hasColumn('users', 'phone');
        $hasStatus = Schema::hasColumn('users', 'status');

        // دالة صغيرة لفحص وجود index معين على جدول users داخل MySQL.
        $indexExists = function (string $indexName): bool {
            try {
                $rows = DB::select('SHOW INDEX FROM users WHERE Key_name = ?', [$indexName]);
                return ! empty($rows);
            } catch (\Throwable $e) {
                return false;
            }
        };

        // تحديد ما إذا كانت الفهارس المطلوبة موجودة أم يجب إنشاؤها.
        $needsDeptJobIndex = ! $indexExists('users_department_job_title_index');
        $needsDeptJobStatusIndex = ! $indexExists('users_dept_job_status_idx');

        // إذا كان أي عنصر مفقود (عمود/فهرس)، نقوم بالتعديل على جدول users.
        if (! $hasDepartment || ! $hasJobTitle || ! $hasPhone || ! $hasStatus || $needsDeptJobIndex || $needsDeptJobStatusIndex) {
            Schema::table('users', function (Blueprint $table) use (
                $hasDepartment,
                $hasJobTitle,
                $hasPhone,
                $hasStatus,
                $needsDeptJobIndex,
                $needsDeptJobStatusIndex
            ) {
                // إضافة الأعمدة المفقودة مع قيم افتراضية مناسبة للمشروع.
                if (! $hasDepartment) {
                    $table->string('department', 100)->default('store_management')->after('password');
                }
                if (! $hasJobTitle) {
                    $table->string('job_title', 100)->default('store_employee')->after('department');
                }
                if (! $hasPhone) {
                    $table->string('phone', 30)->nullable()->after('job_title');
                }
                if (! $hasStatus) {
                    $table->string('status', 30)->default('active')->after('phone');
                }

                // فهارس لتسريع استعلامات جلب المدراء/الموظفين حسب القسم/الدور/الحالة.
                if ($needsDeptJobIndex) {
                    $table->index(['department', 'job_title']);
                }
                if ($needsDeptJobStatusIndex) {
                    $table->index(['department', 'job_title', 'status'], 'users_dept_job_status_idx');
                }
            });
        }

        // جعل email اختيارياً (NULL) لأن نظام الدخول في هذا المشروع يعتمد على name + password.
        DB::statement('ALTER TABLE users MODIFY email VARCHAR(255) NULL');

        // إزالة جداول إعادة تعيين كلمة المرور (غير مستخدمة في هذا المشروع) لتقليل التشويش.
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('password_resets');
    }

    public function down(): void
    {
        // التراجع: حذف الأعمدة والفهارس التي تمت إضافتها.
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('users_dept_job_status_idx');
            $table->dropIndex(['department', 'job_title']);
            $table->dropColumn(['department', 'job_title', 'phone', 'status']);
        });

        // إعادة email لوضع NOT NULL كما في Laravel الافتراضي.
        DB::statement('ALTER TABLE users MODIFY email VARCHAR(255) NOT NULL');

        // إعادة إنشاء جدول password_resets (للتوافق مع الحالة الافتراضية إن احتاجت).
        Schema::create('password_resets', function (Blueprint $table) {
            $table->string('email')->index();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });
    }
};

// Summary: يوسّع جدول users لإدارة الفروع بإضافة حقول القسم والدور والهاتف والحالة وتعديل البريد والفهارس.