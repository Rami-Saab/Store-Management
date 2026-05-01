<?php // Name : Rodain Gouzlan Id:

/**
 * Migration: إنشاء جدول stores (الأفرع/المتاجر)
 *
 * هذا الجدول يمثل الفرع ضمن الكتلة البرمجية الثالثة.
 * يحتوي على البيانات الأساسية للفرع + ساعات الدوام + مسار البرشور + فهارس للبحث.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // إنشاء جدول stores الذي يمثل الأفرع/المتاجر داخل النظام.
        Schema::create('stores', function (Blueprint $table) {
            // معرف (ID) فريد لكل فرع.
            $table->id();
            // اسم الفرع (Branch name).
            $table->string('name');
            // كود الفرع الفريد (مثل DAM-001).
            $table->string('branch_code')->unique();
            // المحافظة المرتبطة بالفرع (FK إلى جدول provinces).
            $table->foreignId('province_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            // المدينة (قد تُستخدم في البحث والعرض).
            $table->string('city');
            // العنوان التفصيلي (مع طول أكبر).
            $table->string('address', 500);
            // رقم الهاتف (نص لأن بعض الصيغ قد تحتوي أصفار في البداية).
            $table->string('phone', 30);
            // وصف اختياري للفرع (نص طويل).
            $table->text('description')->nullable();
            // البريد الإلكتروني (اختياري).
            $table->string('email')->nullable();
            // حالة الفرع (active/inactive/under_maintenance).
            $table->string('status', 30)->default('active');
            // نص جاهز للعرض لساعات العمل (مثل From 09:00 AM to 10:00 PM).
            $table->string('working_hours', 255)->nullable();
            // وقت بداية الدوام (time) - يمكن أن يكون null إذا لم يُحدد بعد.
            $table->time('workday_starts_at')->nullable();
            // وقت نهاية الدوام (time) - يمكن أن يكون null إذا لم يُحدد بعد.
            $table->time('workday_ends_at')->nullable();
            // تاريخ افتتاح الفرع.
            $table->date('opening_date');
            // مسار ملف PDF للبرشور داخل storage/public.
            $table->string('brochure_path')->nullable();
            // created_at / updated_at.
            $table->timestamps();

            // فهارس لتحسين البحث بالاسم/الحالة/المحافظة/المدينة.
            $table->index(['name', 'status', 'province_id', 'city']);
            // فهرس مركب للحالة + المحافظة (يستخدم بكثرة في الفلاتر).
            $table->index(['status', 'province_id'], 'stores_status_province_idx');
            // فهرس للكود (للبحث/التحقق من unique بسرعة).
            $table->index(['branch_code'], 'stores_branch_code_idx');
        });
    }

    public function down(): void
    {
        // حذف الجدول عند التراجع عن migration.
        Schema::dropIfExists('stores');
    }
};

// Summary: ينشئ جدول stores بكامل بيانات الفروع وساعات العمل والبروشور مع فهارس للبحث السريع.