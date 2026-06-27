<?php // Name : Rodain Gouzlan Id:

/**
 * Migration: إضافة حقول التدقيق (Audit) إلى جدول stores
 *
 * الهدف:
 * - created_by : من أنشأ الفرع
 * - updated_by : من آخر شخص عدّل الفرع
 *
 * هذا يساعد في عرض بطاقة "Last Updated" في صفحة تعديل/تفاصيل الفرع.
 *
 * ملاحظة:
 * - يتم تعيين قيم افتراضية للحقول الفارغة اعتماداً على المستخدم "Rodain" (إن وجد).
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\User;

return new class extends Migration
{
    public function up(): void
    {
        $hasCreatedBy = Schema::hasColumn('stores', 'created_by'); // هل العمود created_by موجود مسبقاً؟
        $hasUpdatedBy = Schema::hasColumn('stores', 'updated_by'); // هل العمود updated_by موجود مسبقاً؟

        if (! $hasCreatedBy || ! $hasUpdatedBy) {
            Schema::table('stores', function (Blueprint $table) use ($hasCreatedBy, $hasUpdatedBy) {
                if (! $hasCreatedBy) {
                    $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete()->after('brochure_path'); // إضافة created_by كـ FK إلى users
                }
                if (! $hasUpdatedBy) {
                    $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete()->after('created_by'); // إضافة updated_by كـ FK إلى users
                }
            });
        }

        // محاولة العثور على المستخدم "Rodain" لملء الحقول الفارغة (إن لم يوجد نأخذ أول مستخدم).
        $userId = User::query()->where('name', 'Rodain')->value('id')
            ?? User::query()->orderBy('id')->value('id');

        if ($userId) {
            if (Schema::hasColumn('stores', 'created_by')) {
                DB::table('stores')->whereNull('created_by')->update(['created_by' => $userId]); // تعبئة created_by للصفوف القديمة
            }
            if (Schema::hasColumn('stores', 'updated_by')) {
                DB::table('stores')->whereNull('updated_by')->update(['updated_by' => $userId]); // تعبئة updated_by للصفوف القديمة
            }
        }
    }

    public function down(): void
    {
        Schema::table('stores', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
            $table->dropForeign(['updated_by']);
            $table->dropColumn(['created_by', 'updated_by']);
        });
    }
};

// Summary: يضيف حقول التدقيق created_by وupdated_by للمتاجر مع تعبئة افتراضية من مستخدم محدد.