<?php // Name : Rodain Gouzlan Id:

namespace App\Http\Controllers\Stores;

/**
 * Controller: ربط المنتجات بالأفرع (Store Products Linking)
 *
 * هذه الصفحة تُمثّل العلاقة many-to-many بين Product و Store عبر الجدول pivot: product_store.
 *
 * ما يقدمه هذا الـ Controller:
 * - عرض المنتجات المتاحة (available) مع عرض الأفرع المرتبطة لكل منتج.
 * - حفظ المنتجات المرتبطة بالفرع باستخدام sync (استبدال روابط pivot حسب اختيار المستخدم).
 *
 * الصلاحيات:
 * - مسموح لمدير النظام ولمستخدمي القسم (Department level) ضمن نطاقهم.
 * - مدراء/موظفو الفرع يمكنهم عرض فرعهم فقط ولا يمكنهم تعديل روابط المنتجات.
 */

// الاستيرادات اللازمة لهذا الـ Controller.
use App\Http\Controllers\Controller;
// Form Request للتحقق من product_ids القادمة من النموذج.
use App\Http\Requests\StoreProductRequest;
// نموذج الفرع لاستخدام Route Model Binding.
use App\Models\Store;
use App\Services\Currency\ExchangeRateService;
// خدمة المنتجات لجلب المنتجات المتاحة مع معلومات الارتباط بالأفرع.
use App\Services\Product\ProductService;
// خدمة الفرع لتحديث بيانات التدقيق/الكاش بعد تعديل الروابط.
use App\Services\Store\StoreService;
// Trait يضم قواعد الصلاحيات الخاصة بوحدة إدارة الأفرع.
use App\Traits\StoreAuthorization;
// Redirect بعد حفظ التحديث.
use Illuminate\Http\RedirectResponse;
// Auth لإحضار المستخدم الحالي.
use Illuminate\Support\Facades\Auth;
// View لإرجاع صفحات Blade.
use Illuminate\View\View;

class StoreProductController extends Controller
{
    use StoreAuthorization;

    public function __construct(
        // StoreService: لمسح الكاش وتحديث حقول التدقيق عند تغيير الروابط.
        private StoreService $storeService,
        // ProductService: لجلب المنتجات المتاحة وعرضها في الواجهة.
        private ProductService $productService,
        private ExchangeRateService $exchangeRateService,
    ) {
    }

    public function products(Store $store): View
    {
        // Authorization is handled via StorePolicy.
        $this->authorize('manageProducts', $store);

        // تحميل المنتجات المرتبطة بالفرع (نحتاج id فقط لتحديد المحدد مسبقاً).
        $store->load(['products:id']);

        // عرض صفحة ربط المنتجات.
        return view('stores.products', [
            'store' => $store,
            // جلب المنتجات المتاحة للربط مع معلومات الأفرع التي تبيع كل منتج (لإظهارها في الجدول/القائمة).
            'products' => $this->productService->availableWithStores(['id', 'name', 'sku', 'price']),
            // قائمة الأفرع المسموح للمستخدم رؤيتها (قد تختلف حسب الدور).
            'stores' => $this->allowedStores(Auth::user()),
            'usdToSypRate' => $this->exchangeRateService->usdToSypRate(),
        ]);
    }

    public function updateProducts(StoreProductRequest $request, Store $store): RedirectResponse
    {
        // المستخدم الحالي لتحديد الصلاحيات والتدقيق.
        $currentUser = Auth::user();
        $this->authorize('manageProducts', $store);

        // البيانات التي اجتازت التحقق (product_ids).
        $validated = $request->validated();

        $productIds = collect($validated['product_ids'] ?? [])
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->values();
        $quantities = collect($validated['product_quantities'] ?? [])
            ->mapWithKeys(fn ($value, $key) => [(int) $key => max(0, (int) $value)]);

        // مزامنة روابط المنتجات مع الكميات (في حال لم تُرسل كمية نستخدم 0 افتراضياً).
        $syncPayload = $productIds
            ->mapWithKeys(fn ($id) => [$id => ['quantity' => (int) ($quantities[$id] ?? 0)]])
            ->all();

        $store->products()->sync($syncPayload);
        // تحديث معلومات "آخر تعديل" على الفرع بعد تغيير الروابط.
        $this->storeService->touchStore($store, $currentUser?->id);
        // تفريغ الكاش حتى تظهر التغييرات فوراً.
        $this->storeService->flushStoreCaches();

        // العودة لنفس الصفحة مع رسالة نجاح.
        return redirect()
            ->to(route('stores.products', $store, false))
            ->with('success', 'Product links saved successfully.');
    }
}