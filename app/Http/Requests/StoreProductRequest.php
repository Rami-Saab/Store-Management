<?php // Name : Rodain Gouzlan Id:

namespace App\Http\Requests;

/**
 * Form Request: ربط المنتجات بفرع
 *
 * الهدف:
 * - التأكد أن المستخدم لديه صلاحية استخدام وحدة إدارة الأفرع.
 * - التأكد أن product_ids (إن وُجدت) عبارة عن مصفوفة من معرفات منتجات صحيحة.
 */

use App\Traits\StoreAuthorization;
use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    use StoreAuthorization;

    public function authorize(): bool
    {
        // منع الوصول إذا لم يكن المستخدم ضمن وحدة إدارة الأفرع.
        $this->abortUnlessStoreModuleUser($this->user());
        return true;
    }

    public function rules(): array
    {
        // قواعد التحقق: product_ids مصفوفة، وكل عنصر id موجود في جدول المنتجات.
        return [
            'product_ids' => ['nullable', 'array'],
            'product_ids.*' => ['integer', 'exists:products,id'],
            'product_quantities' => ['nullable', 'array'],
            'product_quantities.*' => ['integer', 'min:0'],
        ];
    }
}