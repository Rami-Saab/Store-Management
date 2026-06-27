<?php // Name : Rodain Gouzlan Id:

namespace App\Services\Store;

/**
 * Service: أدوات مساعدة لتعيينات الموظفين (Assignments Helpers)
 *
 * يحتوي على وظائف صغيرة تساعد في اكتشاف حالات النقل (Transfer):
 * - عندما يتم إضافة موظف لفرع جديد وهو مرتبط بفرع آخر، يعتبر ذلك "Transfer".
 * - هذه المعلومة تستخدم لعرض رسائل واضحة للمستخدم (مثلاً: تم نقل الموظف من فرع إلى آخر).
 */

use App\Models\Store;
use App\Models\User;

class AssignmentService
{
    public function resolveTransferEmployeeIds(Store $store, array $requestedEmployeeIds): array
    {
        // إذا لم يتم اختيار أي موظفين فلا يوجد نقل.
        if ($requestedEmployeeIds === []) {
            return [];
        }

        // تحويل القيم إلى أرقام صحيحة وتطبيعها.
        $requestedIds = collect($requestedEmployeeIds)->map(fn ($id) => (int) $id)->values()->all();
        // الموظفون الحاليون في الفرع (baseline).
        $baselineIds = $store->employees->pluck('id')->map(fn ($id) => (int) $id)->values()->all();
        // الإضافات فقط هي التي يمكن أن تكون "Transfer".
        $additions = array_values(array_diff($requestedIds, $baselineIds));
        if ($additions === []) {
            return [];
        }

        // الموظف يعتبر Transfer إذا كان مرتبطاً بفرع آخر غير الفرع الحالي.
        $transferIds = User::whereIn('id', $additions)
            ->whereHas('stores', function ($query) use ($store) {
                $query->where('stores.id', '!=', $store->id);
            })
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        // إزالة التكرار وإرجاع النتيجة.
        return array_values(array_unique($transferIds));
    }
}