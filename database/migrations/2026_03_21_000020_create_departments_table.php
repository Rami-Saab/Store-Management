<?php // Name : Rodain Gouzlan Id:

// هذا الملف ينشئ جدول الأقسام (departments) الذي يحدد جهات/إدارات العمل داخل النظام.

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // تنفيذ إنشاء جدول الأقسام.
    public function up(): void
    {
        // حماية إضافية: إذا كان الجدول موجوداً لا نعيد إنشاؤه.
        if (Schema::hasTable('departments')) {
            return;
        }

        // إنشاء جدول departments بأسماء وأكواد فريدة للأقسام.
        Schema::create('departments', function (Blueprint $table) {
            // المفتاح الأساسي.
            $table->id();
            // الاسم المقروء للقسم.
            $table->string('name', 120);
            // المعرّف النصي الفريد للقسم (slug).
            $table->string('slug', 120)->unique();
            // تواريخ الإنشاء والتحديث.
            $table->timestamps();
        });
    }

    // التراجع عن إنشاء الجدول.
    public function down(): void
    {
        // حذف جدول الأقسام إذا كان موجوداً.
        Schema::dropIfExists('departments');
    }
};

// Summary: ينشئ جدول الأقسام مع اسم و slug فريد لدعم تصنيف المستخدمين والفروع حسب الإدارة.