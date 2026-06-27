<?php // Name : Rodain Gouzlan Id:

/**
 * Migration: إنشاء جداول طلبات التعيين + إشعارات الطلبات
 *
 * جداول هذه الـ migration تحقق Workflow المراجعة:
 * - assignment_requests: طلب تغيير تعيينات الموظفين/المدير لفرع ما.
 * - assignment_request_notifications: إشعارات للمستخدمين المتأثرين عند الموافقة/الرفض.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assignment_requests', function (Blueprint $table) {
            $table->id(); // معرّف فريد لكل طلب
            $table->foreignId('store_id')->constrained('stores')->cascadeOnDelete(); // الفرع المطلوب تعديل تعييناته
            $table->foreignId('requested_by')->constrained('users')->cascadeOnDelete(); // صاحب الطلب (المستخدم الذي أنشأ الطلب)
            $table->string('requested_role', 50)->nullable(); // دور صاحب الطلب وقت الإنشاء (للعرض/التدقيق)
            $table->foreignId('manager_id')->nullable()->constrained('users')->nullOnDelete(); // المدير المقترح للفرع (إن وُجد)
            $table->json('employee_ids')->nullable(); // قائمة الموظفين المطلوبة (IDs) بصيغة JSON
            $table->json('baseline_employee_ids')->nullable(); // قائمة الموظفين قبل التغيير (للمقارنة وحساب الفروقات)
            $table->json('transfer_employee_ids')->nullable(); // قائمة الموظفين الذين سيتم "نقلهم" من فروع أخرى (Transfer)
            $table->string('status', 20)->default('pending'); // حالة الطلب (pending/approved/rejected)
            $table->string('decision_note', 255)->nullable(); // ملاحظة قرار مدير النظام (تلخيص الإضافات/الحذف/النقل)
            $table->foreignId('decided_by')->nullable()->constrained('users')->nullOnDelete(); // من قام بالموافقة/الرفض (مدير النظام)
            $table->timestamp('decided_at')->nullable(); // وقت اتخاذ القرار
            $table->timestamps(); // created_at / updated_at

            $table->index(['requested_by', 'status'], 'assign_req_requested_by_status_idx'); // فهرس لتسريع فلترة الطلبات حسب صاحب الطلب/الحالة
            $table->index(['store_id', 'status'], 'assign_req_store_status_idx'); // فهرس لتسريع فلترة الطلبات حسب الفرع/الحالة
        });

        Schema::create('assignment_request_notifications', function (Blueprint $table) {
            $table->id(); // معرّف فريد لكل إشعار
            $table->foreignId('assignment_request_id')->constrained('assignment_requests')->cascadeOnDelete(); // الطلب المرتبط بالإشعار
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete(); // المستخدم الذي يستلم الإشعار
            $table->timestamp('read_at')->nullable(); // وقت قراءة الإشعار (NULL = غير مقروء)
            $table->timestamps(); // created_at / updated_at

            // استخدام اسم فهرس قصير لتجنب تجاوز حد طول أسماء المعرفات في MySQL.
            $table->unique(['assignment_request_id', 'user_id'], 'assign_req_notif_unique'); // منع تكرار نفس الإشعار لنفس المستخدم لنفس الطلب
            $table->index(['user_id', 'read_at'], 'assign_notif_user_read_idx'); // فهرس لتسريع جلب إشعارات المستخدم غير المقروءة
            $table->index(['user_id', 'created_at'], 'assign_notif_user_created_idx'); // فهرس لتسريع عرض الإشعارات حسب الأحدث
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assignment_request_notifications');
        Schema::dropIfExists('assignment_requests');
    }
};

// Summary: ينشئ جداول طلبات التعيين وإشعاراتها لدعم Workflow مراجعة التعيينات.