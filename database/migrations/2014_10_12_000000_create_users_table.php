<?php // Name : Rodain Gouzlan Id:

/**
 * Migration: إنشاء جدول users
 *
 * هذا جدول Laravel الافتراضي للمستخدمين.
 * تم توسيعه لاحقاً في مigrations أخرى لإضافة حقول خاصة بوحدة إدارة الأفرع (department/job_title/phone/status).
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * تنفيذ الـ migration (إنشاء الجداول/الأعمدة).
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            // المفتاح الأساسي للمستخدم.
            $table->id();
            // اسم المستخدم.
            $table->string('name');
            // بريد إلكتروني فريد لتسجيل الدخول/التواصل.
            $table->string('email')->unique();
            // وقت التحقق من البريد (إن وجد).
            $table->timestamp('email_verified_at')->nullable();
            // كلمة المرور المشفرة.
            $table->string('password');
            // توكن تذكر الدخول.
            $table->rememberToken();
            // تواريخ الإنشاء والتحديث.
            $table->timestamps();
        });
    }

    /**
     * التراجع عن الـ migration (حذف ما تم إنشاؤه).
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
};

// Summary: ينشئ جدول المستخدمين الأساسي مع البريد وكلمة المرور وحقول التتبع القياسية.