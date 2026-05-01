<?php // Name : Rodain Gouzlan Id:

/**
 * Migration: إنشاء جدول provinces
 *
 * هذا الجدول يخزن المحافظات (Name + Code) لاستخدامها ضمن نموذج Store.
 * - code يستخدم للربط المنطقي وعرض أسماء إنجليزية موحدة (EnglishPlaceNames).
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // إنشاء جدول المحافظات لاستخدامه كمرجع موحد للأفرع.
        Schema::create('provinces', function (Blueprint $table) {
            // معرف فريد لكل محافظة.
            $table->id();
            // اسم المحافظة (قد يكون بالعربية أو الإنجليزية حسب بيانات seeder).
            $table->string('name');
            // كود المحافظة (قصير) ومميز (unique) لاستخدامه في تحويل الاسم للإنجليزية.
            $table->string('code', 10)->unique();
            // created_at / updated_at.
            $table->timestamps();
        });
    }

    public function down(): void
    {
        // حذف الجدول عند التراجع عن migration.
        Schema::dropIfExists('provinces');
    }
};

// Summary: ينشئ جدول المحافظات مع الاسم والكود الفريد لتوحيد اختيار المحافظة في الفروع.