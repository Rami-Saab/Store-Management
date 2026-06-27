<?php // Name : Rodain Gouzlan Id:

// هذا الملف ينشئ جدول personal_access_tokens الخاص بـ Laravel Sanctum لتخزين رموز الوصول.

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // تنفيذ إنشاء جدول الرموز الشخصية إذا لم يكن موجوداً.
    public function up(): void
    {
        // حماية من الإنشاء المكرر.
        if (Schema::hasTable('personal_access_tokens')) {
            return;
        }

        // إنشاء جدول الرموز (tokens) المرتبطة بالمستخدمين/النماذج.
        Schema::create('personal_access_tokens', function (Blueprint $table) {
            // المفتاح الأساسي.
            $table->id();
            // علاقة polymorphic لربط التوكن بأي نموذج (User وغيره).
            $table->morphs('tokenable');
            // اسم وصفي للتوكن.
            $table->string('name');
            // قيمة التوكن نفسها (فريدة).
            $table->string('token', 64)->unique();
            // الصلاحيات الممنوحة للتوكن (إن وُجدت).
            $table->text('abilities')->nullable();
            // آخر وقت استخدام للتوكن.
            $table->timestamp('last_used_at')->nullable();
            // وقت انتهاء صلاحية التوكن (اختياري).
            $table->timestamp('expires_at')->nullable();
            // تواريخ الإنشاء والتحديث.
            $table->timestamps();
        });
    }

    // التراجع عن الإنشاء.
    public function down(): void
    {
        // حذف الجدول عند التراجع.
        Schema::dropIfExists('personal_access_tokens');
    }
};

// Summary: ينشئ جدول personal_access_tokens لتخزين رموز Sanctum مع علاقة polymorphic وصلاحيات ووقت انتهاء.