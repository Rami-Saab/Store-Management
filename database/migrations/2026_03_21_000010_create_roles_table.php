<?php // Name : Rodain Gouzlan Id:

// هذا الملف ينشئ جدول الأدوار (roles) المستخدم لتجميع الصلاحيات وربطها بالمستخدمين.

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // تنفيذ الإنشاء عند تشغيل الهجرة.
    public function up(): void
    {
        // إنشاء جدول الأدوار لتخزين أنواع المستخدمين (Admin, Manager, ...).
        Schema::create('roles', function (Blueprint $table) {
            // المفتاح الأساسي للجدول.
            $table->id();
            // الاسم المقروء للدور.
            $table->string('name', 80);
            // المعرّف النصي الفريد للدور (slug).
            $table->string('slug', 80)->unique();
            // تواريخ الإنشاء والتحديث.
            $table->timestamps();
        });
    }

    // التراجع عن الإنشاء.
    public function down(): void
    {
        // حذف الجدول عند التراجع.
        Schema::dropIfExists('roles');
    }
};

// Summary: يضيف جدول roles لتخزين الأدوار بأسماء و slugs فريدة لدعم نظام الصلاحيات.