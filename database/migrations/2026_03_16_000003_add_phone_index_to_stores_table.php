<?php // Name : Rodain Gouzlan Id:

/**
 * Migration: إضافة فهرس (Index) على حقل phone في جدول stores
 *
 * الهدف:
 * - تسريع البحث/الفلترة بالهاتف ضمن شاشة Grid و API Search.
 *
 * ملاحظة:
 * - يتم التحقق من وجود الجدول ومن وجود الفهرس مسبقاً (خصوصاً على MySQL).
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('stores')) {
            return;
        }

        if (DB::getDriverName() === 'mysql') {
            $existing = DB::select("SHOW INDEX FROM stores WHERE Key_name = 'stores_phone_idx'");
            if (! empty($existing)) {
                return;
            }
        }

        Schema::table('stores', function (Blueprint $table) {
            $table->index('phone', 'stores_phone_idx'); // إنشاء فهرس باسم stores_phone_idx على عمود phone
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('stores')) {
            return;
        }

        Schema::table('stores', function (Blueprint $table) {
            $table->dropIndex('stores_phone_idx'); // حذف الفهرس عند التراجع عن الـ migration
        });
    }
};

// Summary: يضيف فهرساً على رقم الهاتف في جدول stores لتسريع البحث ثم يزيله عند التراجع.