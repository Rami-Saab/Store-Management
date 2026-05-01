<?php // Name : Rodain Gouzlan Id:

/**
 * Migration: إنشاء جدول pivot product_store
 *
 * هذا الجدول يحقق علاقة many-to-many بين:
 * - المنتجات (products)
 * - الأفرع (stores)
 *
 * أي منتج يمكن أن يكون موجوداً في عدة أفرع، وأي فرع يمكن أن يبيع عدة منتجات.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // إنشاء جدول الربط بين المنتجات والأفرع.
        Schema::create('product_store', function (Blueprint $table) {
            // معرف فريد لكل صف في جدول الربط.
            $table->id();
            // معرف المنتج (FK) مع حذف تلقائي عند حذف المنتج.
            $table->foreignId('product_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            // معرف الفرع (FK) مع حذف تلقائي عند حذف الفرع.
            $table->foreignId('store_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            // created_at / updated_at لتتبع وقت الربط.
            $table->timestamps();

            // منع تكرار نفس الربط (نفس المنتج مع نفس الفرع).
            $table->unique(['product_id', 'store_id']);
            // فهارس لتحسين الاستعلامات حسب store أو حسب product.
            $table->index(['store_id'], 'product_store_store_id_idx');
            $table->index(['product_id'], 'product_store_product_id_idx');
        });
    }

    public function down(): void
    {
        // حذف جدول الربط عند التراجع.
        Schema::dropIfExists('product_store');
    }
};

// Summary: ينشئ جدول pivot product_store لربط المنتجات بالفروع مع قيود uniqueness وفهارس أداء.