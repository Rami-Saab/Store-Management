<?php // Name : Rodain Gouzlan Id:

/**
 * Migration: إنشاء جدول pivot store_user
 *
 * هذا الجدول يحقق علاقة many-to-many بين:
 * - الأفرع (stores)
 * - المستخدمين (users)
 *
 * ويحتوي على الحقل assignment_role لتحديد نوع التعيين:
 * - manager  : مدير الفرع (واحد لكل فرع)
 * - employee : موظف فرع (عدة لكل فرع)
 */
// ملاحظة: هذا الجدول هو البديل العملي لما قد يُسمّى create_store_employee_table،
// لأن تعيين الموظفين والمدراء يتم هنا عبر الحقل assignment_role.

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // إنشاء جدول الربط بين المستخدمين والأفرع (Assignments).
        Schema::create('store_user', function (Blueprint $table) {
            // معرف فريد لكل صف ربط.
            $table->id();
            // معرف الفرع (FK).
            $table->foreignId('store_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            // معرف المستخدم (FK).
            $table->foreignId('user_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            // دور التعيين داخل الفرع (manager/employee).
            $table->string('assignment_role', 30);
            // created_at / updated_at لتتبع وقت التعيين.
            $table->timestamps();

            // منع تكرار نفس التعيين (نفس المستخدم في نفس الفرع).
            $table->unique(['store_id', 'user_id']);
            // فهارس لتحسين الاستعلامات حسب المستخدم أو حسب الفرع + الدور.
            $table->index(['user_id'], 'store_user_user_id_idx');
            $table->index(['store_id', 'assignment_role'], 'store_user_store_role_idx');
            $table->index(['assignment_role'], 'store_user_role_idx');
        });
    }

    public function down(): void
    {
        // حذف الجدول عند التراجع.
        Schema::dropIfExists('store_user');
    }
};

// Summary: ينشئ جدول store_user لتعيين المستخدمين للفروع مع دور التعيين وفهارس للبحث.