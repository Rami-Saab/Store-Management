<?php // Name : Rodain Gouzlan Id:

/**
 * Migration: إنشاء جدول warehouses (تكامل مع وحدة المستودعات)
 *
 * الهدف: توفير جدول أساسي يسمح بربط الفروع بالمستودعات ضمن Block 3
 * دون بناء وحدة المستودعات بالكامل.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // حماية من الإنشاء المكرر.
        if (Schema::hasTable('warehouses')) {
            return;
        }

        Schema::create('warehouses', function (Blueprint $table) {
            // المفتاح الأساسي للمستودع.
            $table->id();
            // اسم المستودع (مع فهرس).
            $table->string('name')->index();
            // الموقع النصي (اختياري).
            $table->string('location')->nullable();
            // حالة المستودع (افتراضي active) مع فهرس.
            $table->string('status')->default('active')->index();
            // تواريخ الإنشاء والتحديث.
            $table->timestamps();
        });
    }

    public function down(): void
    {
        // حذف الجدول عند التراجع.
        Schema::dropIfExists('warehouses');
    }
};

// Summary: ينشئ جدول warehouses الأساسي (اسم، موقع، حالة) لدعم ربط الفروع بالمستودعات.