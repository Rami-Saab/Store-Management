<?php // Name : Rodain Gouzlan Id:

// هذا الملف يضيف حقل message إلى إشعارات طلبات التعيين لعرض تفاصيل القرار/الشرح.

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // إضافة عمود الرسالة.
    public function up(): void
    {
        Schema::table('assignment_request_notifications', function (Blueprint $table) {
            // نص توضيحي للإشعار (اختياري).
            $table->text('message')->nullable()->after('user_id');
        });
    }

    // إزالة عمود الرسالة عند التراجع.
    public function down(): void
    {
        Schema::table('assignment_request_notifications', function (Blueprint $table) {
            $table->dropColumn('message');
        });
    }
};

// Summary: يضيف عمود message لإشعارات طلبات التعيين لتخزين وصف القرار أو السبب.