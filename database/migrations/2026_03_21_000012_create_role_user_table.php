<?php // Name : Rodain Gouzlan Id:

// هذا الملف ينشئ جدول الربط بين المستخدمين والأدوار (role_user) لعلاقة Many-to-Many.

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // تنفيذ إنشاء جدول الربط.
    public function up(): void
    {
        // إنشاء جدول وسيط يربط المستخدمين بالأدوار.
        Schema::create('role_user', function (Blueprint $table) {
            // مفتاح أجنبي يشير إلى الدور.
            $table->unsignedBigInteger('role_id');
            // مفتاح أجنبي يشير إلى المستخدم.
            $table->unsignedBigInteger('user_id');
            // تواريخ الإنشاء والتحديث لعلاقة الربط.
            $table->timestamps();

            // مفتاح مركب لضمان عدم تكرار نفس العلاقة.
            $table->primary(['role_id', 'user_id']);
            // ربط الدور مع جدول roles والحذف التتابعي.
            $table->foreign('role_id')->references('id')->on('roles')->cascadeOnDelete();
            // ربط المستخدم مع جدول users والحذف التتابعي.
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }

    // التراجع عن الإنشاء.
    public function down(): void
    {
        // حذف جدول الربط إذا كان موجوداً.
        Schema::dropIfExists('role_user');
    }
};

// Summary: ينشئ جدول وسيط لربط المستخدمين بالأدوار مع مفاتيح أجنبية ومفتاح مركب لمنع التكرار.