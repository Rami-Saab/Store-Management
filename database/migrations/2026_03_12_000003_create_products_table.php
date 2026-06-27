<?php // Name : Rodain Gouzlan Id:

/**
 * Migration: إنشاء جدول products
 *
 * يمثل منتجات النظام (الاسم، SKU، السعر، الحالة).
 * سيتم ربط المنتجات بالأفرع عبر pivot table (product_store) لتحقيق متطلبات Block 3.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // إنشاء جدول المنتجات.
        Schema::create('products', function (Blueprint $table) {
            // معرف فريد لكل منتج.
            $table->id();
            // اسم المنتج.
            $table->string('name');
            // SKU فريد للمنتج (كود تعريف).
            $table->string('sku')->unique();
            // السعر بدقة رقمين بعد الفاصلة.
            $table->decimal('price', 10, 2)->default(0);
            // حالة المنتج (available أو غير ذلك).
            $table->string('status', 30)->default('available');
            // created_at / updated_at.
            $table->timestamps();
            // فهرس لتحسين البحث بالاسم/الحالة.
            $table->index(['name', 'status']);
        });
    }

    public function down(): void
    {
        // حذف الجدول عند التراجع.
        Schema::dropIfExists('products');
    }
};

// Summary: ينشئ جدول المنتجات مع الحقول الأساسية وفهرس للبحث، تمهيداً لربطها بالفروع عبر pivot.