<?php // Name : Rodain Gouzlan Id:

namespace App\Http\Resources;

// هذا المورد (Resource) يحول نموذج Store إلى JSON منسق للاستخدام في الـ API.
// الهدف هو توحيد شكل البيانات حتى يكون ثابتاً في جميع الاستجابات.
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Resource: Store
 *
 * يجمع الحقول الأساسية والعلاقات المحمّلة مسبقاً (whenLoaded).
 */
class StoreResource extends JsonResource
{
    /**
     * تحويل كائن Store إلى مصفوفة مناسبة لواجهات الـ API.
     */
    public function toArray($request): array
    {
        // تجهيز بيانات المدير فقط إذا كانت علاقة manager محمّلة مسبقاً.
        // سنبني بيانات المدير فقط إذا كانت العلاقة محمّلة مسبقاً.
        $manager = null;
        if ($this->relationLoaded('manager')) {
            $managerModel = $this->manager;
            if ($managerModel) {
                $manager = [
                    'id' => $managerModel->id,
                    'name' => $managerModel->name,
                ];
            }
        }

        // مصفوفة الاستجابة القياسية للفرع.
        // مصفوفة الاستجابة الأساسية للفرع.
        return [
            // الحقول الأساسية.
            'id' => $this->id,
            'name' => $this->name,
            'branch_code' => $this->branch_code,
            'status' => $this->status,
            'province_id' => $this->province_id,
            'city' => $this->city,
            'address' => $this->address,
            'phone' => $this->phone,
            'description' => $this->description,
            'email' => $this->email,
            'working_hours' => $this->working_hours,
            'workday_starts_at' => $this->workday_starts_at,
            'workday_ends_at' => $this->workday_ends_at,
            'opening_date' => optional($this->opening_date)->format('Y-m-d'),
            'brochure_path' => $this->brochure_path,
            // بيانات المحافظة عند تحميل العلاقة.
            // المحافظة تُعاد فقط إن كانت محمّلة لتجنب استعلامات إضافية.
            'province' => $this->whenLoaded('province', function () {
                return [
                    'id' => $this->province?->id,
                    'name' => $this->province?->name,
                    'code' => $this->province?->code,
                ];
            }),
            // بيانات المدير إن وُجدت.
            'manager' => $manager,
            // الموظفون المرتبطون بالفرع (عند تحميلهم).
            // الموظفون (إن تم تحميلهم) نعيدهم كمصفوفة مبسطة.
            'employees' => $this->whenLoaded('employees', function () {
                return $this->employees->map(fn ($employee) => [
                    'id' => $employee->id,
                    'name' => $employee->name,
                ])->values();
            }),
            // المنتجات (إن تم تحميلها) نعيدها مع كمية الـ pivot.
            // المنتجات المرتبطة بالفرع (مع كمية الـ pivot).
            'products' => $this->whenLoaded('products', function () {
                return $this->products->map(fn ($product) => [
                    'id' => $product->id,
                    'name' => $product->name,
                    'sku' => $product->sku,
                    'quantity' => $product->pivot?->quantity,
                ])->values();
            }),
            // المستودعات (إن تم تحميلها).
            // المستودعات المرتبطة بالفرع (إن تم تحميلها).
            'warehouses' => $this->whenLoaded('warehouses', function () {
                return $this->warehouses->map(fn ($warehouse) => [
                    'id' => $warehouse->id,
                    'name' => $warehouse->name,
                ])->values();
            }),
            // العدادات تُعاد فقط إن كانت موجودة في الـ query (withCount).
            // عدادات محسوبة من withCount إن وُجدت.
            'employees_count' => $this->when(
                property_exists($this->resource, 'employees_count'),
                (int) $this->employees_count
            ),
            'products_count' => $this->when(
                property_exists($this->resource, 'products_count'),
                (int) $this->products_count
            ),
        ];
    }
}

// Summary: يوفّر تمثيلاً ثابتاً ومفهومًا لبيانات الفرع في استجابات الـ API مع دعم العلاقات والعدادات.