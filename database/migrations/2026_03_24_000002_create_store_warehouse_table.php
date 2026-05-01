<?php // Name : Rodain Gouzlan Id:

/**
 * Migration: إنشاء جدول pivot store_warehouse
 *
 * يربط الأفرع بالمستودعات (many-to-many).
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // عدم إعادة إنشاء الجدول إن كان موجوداً.
        if (Schema::hasTable('store_warehouse')) {
            return;
        }

        Schema::create('store_warehouse', function (Blueprint $table) {
            // المفتاح الأساسي للصف.
            $table->id();
            // ربط الفرع بالمستودع.
            $table->foreignId('store_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            // ربط المستودع بالفرع.
            $table->foreignId('warehouse_id')->constrained('warehouses')->cascadeOnUpdate()->cascadeOnDelete();
            // تواريخ الإنشاء والتحديث.
            $table->timestamps();

            // منع تكرار نفس الربط.
            $table->unique(['store_id', 'warehouse_id']);
            // فهارس لتحسين الاستعلامات حسب الفرع/المستودع.
            $table->index(['store_id'], 'store_warehouse_store_id_idx');
            $table->index(['warehouse_id'], 'store_warehouse_warehouse_id_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('store_warehouse');
    }
};

// Summary: ينشئ جدول pivot يربط الفروع بالمستودعات مع قيود uniqueness وفهارس.