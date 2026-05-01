<?php // Name : Rodain Gouzlan Id:

/**
 * Migration: إنشاء جدول personal_access_tokens
 *
 * هذا الجدول خاص بـ Laravel Sanctum لإدارة Access Tokens (إن تم استخدامها).
 * ليس جزءاً مباشراً من وحدة الأفرع، لكنه مطلوب ضمن حزمة Sanctum الموجودة بالمشروع.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * تنفيذ الـ migration.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('personal_access_tokens', function (Blueprint $table) {
            // المفتاح الأساسي.
            $table->id();
            // علاقة polymorphic لربط التوكن بأي نموذج.
            $table->morphs('tokenable');
            // اسم وصفي للتوكن.
            $table->string('name');
            // قيمة التوكن الفريدة.
            $table->string('token', 64)->unique();
            // صلاحيات التوكن (إن وُجدت).
            $table->text('abilities')->nullable();
            // آخر وقت استخدام للتوكن.
            $table->timestamp('last_used_at')->nullable();
            // تواريخ الإنشاء والتحديث.
            $table->timestamps();
        });
    }

    /**
     * التراجع عن الـ migration.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('personal_access_tokens');
    }
};

// Summary: ينشئ جدول personal_access_tokens القياسي لـ Sanctum لتخزين رموز الوصول وصلاحياتها.