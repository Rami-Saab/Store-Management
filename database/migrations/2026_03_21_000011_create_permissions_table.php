<?php // Name : Rodain Gouzlan Id:

// هذا الملف ينشئ جدول الصلاحيات (permissions) المستخدم للتحكم بما يمكن للأدوار تنفيذه.

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // تنفيذ عملية الإنشاء عند تشغيل php artisan migrate.
    public function up(): void
    {
        // إنشاء جدول permissions لتعريف الصلاحيات على مستوى النظام.
        Schema::create('permissions', function (Blueprint $table) {
            // المفتاح الأساسي للجدول.
            $table->id();
            // الاسم المقروء للصلاحية (وصف مختصر).
            $table->string('name', 120);
            // المعرّف النصي الفريد للصلاحية (يُستخدم في التحقق البرمجي).
            $table->string('slug', 120)->unique();
            // حقول تتبع وقت الإنشاء والتحديث.
            $table->timestamps();
        });
    }

    // التراجع عن الإنشاء عند تشغيل php artisan migrate:rollback.
    public function down(): void
    {
        // حذف الجدول إذا كان موجوداً.
        Schema::dropIfExists('permissions');
    }
};

// Summary: يضيف جدول الصلاحيات مع اسم ووصف مختصر ومعرّف slug فريد لدعم نظام الأدوار والصلاحيات.