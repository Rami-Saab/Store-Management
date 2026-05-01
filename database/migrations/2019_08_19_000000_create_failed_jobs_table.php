<?php // Name : Rodain Gouzlan Id:

/**
 * Migration: إنشاء جدول failed_jobs
 *
 * جدول افتراضي من Laravel لتخزين تفاصيل المهام (Jobs) التي فشلت (إن تم استخدام الـ Queue).
 * ليس جزءاً مباشراً من الكتلة البرمجية الثالثة لكنه ضمن بنية Laravel القياسية.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * تنفيذ الـ migration.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('failed_jobs', function (Blueprint $table) {
            // المفتاح الأساسي.
            $table->id();
            // معرف فريد لكل مهمة فاشلة.
            $table->string('uuid')->unique();
            // اسم الاتصال المستخدم في الـ Queue.
            $table->text('connection');
            // اسم الطابور (queue) الذي فشلت فيه المهمة.
            $table->text('queue');
            // محتوى المهمة نفسه (payload).
            $table->longText('payload');
            // تفاصيل الاستثناء الذي سبب الفشل.
            $table->longText('exception');
            // وقت الفشل مع قيمة افتراضية للوقت الحالي.
            $table->timestamp('failed_at')->useCurrent();
        });
    }

    /**
     * التراجع عن الـ migration.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('failed_jobs');
    }
};

// Summary: ينشئ جدول failed_jobs الافتراضي لتخزين تفاصيل مهام الـ Queue التي فشلت.