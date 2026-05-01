<?php // Name : Rodain Gouzlan Id:

namespace App\Http\Controllers\Products;

// هذا الـ Controller مسؤول عن عرض تفاصيل المنتج والربط مع الفروع من منظور واجهة الويب.
// الاستيرادات الأساسية للعرض والتحقق من صلاحيات وحدة الفروع.
use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Traits\StoreAuthorization;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Controller: عرض تفاصيل المنتج المرتبط بالفروع
 *
 * يستخدمه النظام لعرض قائمة الفروع المرتبطة بمنتج محدد في صفحة واحدة.
 */
class ProductController extends Controller
{
    use StoreAuthorization;

    public function show(Product $product): View
    {
        // السماح فقط للمستخدمين المرتبطين بوحدة إدارة الفروع.
        $this->abortUnlessStoreModuleUser(Auth::user());

        // تحميل العلاقات المطلوبة للعرض (الفروع + المحافظة + مدير الفرع).
        $product->load(['stores.province', 'stores.manager']);

        // عرض صفحة المنتج مع تفاصيل الربط.
        return view('products.show', compact('product'));
    }
}