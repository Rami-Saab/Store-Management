<?php // Name : Rodain Gouzlan Id:

/**
 * Migration: إضافة حقل quantity إلى جدول product_store
 *
 * هذا الحقل يحقق متطلب الكمية لكل منتج داخل الفرع.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // التأكد من وجود جدول الربط قبل التعديل.
        if (! Schema::hasTable('product_store')) {
            return;
        }

        // إضافة العمود إذا لم يكن موجوداً.
        if (! Schema::hasColumn('product_store', 'quantity')) {
            Schema::table('product_store', function (Blueprint $table) {
                // كمية المنتج في هذا الفرع.
                $table->unsignedInteger('quantity')->default(0)->after('store_id');
            });
        }
    }

    public function down(): void
    {
        // إزالة العمود عند التراجع.
        if (Schema::hasTable('product_store') && Schema::hasColumn('product_store', 'quantity')) {
            Schema::table('product_store', function (Blueprint $table) {
                $table->dropColumn('quantity');
            });
        }
    }
};

// Summary: يضيف عمود quantity لربط كمية المنتج بالفرع داخل product_store.