<?php // Name : Rodain Gouzlan Id:

// هذا الملف ينشئ جدول الربط بين الصلاحيات والأدوار (permission_role) لعلاقة Many-to-Many.

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // تنفيذ إنشاء جدول الربط.
    public function up(): void
    {
        // إنشاء جدول وسيط يربط كل دور بمجموعة من الصلاحيات.
        Schema::create('permission_role', function (Blueprint $table) {
            // مفتاح أجنبي يشير إلى الصلاحية.
            $table->unsignedBigInteger('permission_id');
            // مفتاح أجنبي يشير إلى الدور.
            $table->unsignedBigInteger('role_id');
            // تواريخ الإنشاء والتحديث للربط.
            $table->timestamps();

            // مفتاح مركب لضمان عدم تكرار نفس الربط.
            $table->primary(['permission_id', 'role_id']);
            // ربط الصلاحية مع جدول permissions وحذف آلي عند حذف الصلاحية.
            $table->foreign('permission_id')->references('id')->on('permissions')->cascadeOnDelete();
            // ربط الدور مع جدول roles وحذف آلي عند حذف الدور.
            $table->foreign('role_id')->references('id')->on('roles')->cascadeOnDelete();
        });
    }

    // التراجع عن إنشاء جدول الربط.
    public function down(): void
    {
        // حذف جدول الربط إذا كان موجوداً.
        Schema::dropIfExists('permission_role');
    }
};

// Summary: ينشئ جدول وسيط يربط الأدوار بالصلاحيات مع مفاتيح أجنبية ومفتاح مركب لمنع التكرار.