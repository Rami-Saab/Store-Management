<?php // Name : Rodain Gouzlan Id:

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\StoreApiController;

/*
|--------------------------------------------------------------------------
| مسارات الـ API (واجهة برمجية)
|--------------------------------------------------------------------------
| هذا الملف يعرّف نقاط النهاية (Endpoints) الخاصة بـ Programming Block 3.
|
| الهدف:
| - توفير CRUD للأفرع عبر REST API
| - توفير بحث (Search) عبر /api/stores/search
|
| ملاحظة:
| - تم الإبقاء على أسماء المسارات (route names) كما هي حتى لا يتأثر أي كود
|   يعتمد عليها (سواء في الواجهة أو في الاختبارات).
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    // إرجاع معلومات المستخدم الحالي (Sanctum) لاستخدامها في تطبيقات واجهة أمامية عند الحاجة.
    return $request->user();
});

// تجميع مسارات إدارة الأفرع ضمن /api/stores للحفاظ على RESTful API.
Route::prefix('stores')->name('api.stores.')->group(function () {
    // GET /api/stores -> إرجاع قائمة الأفرع (قد تكون مع pagination/filters حسب التنفيذ داخل الـ Controller).
    Route::get('/', [StoreApiController::class, 'apiIndex'])->name('index');
    // GET /api/stores/search -> بحث ديناميكي عن الأفرع بواسطة فلاتر (اسم/محافظة/هاتف...).
    Route::get('/search', [StoreApiController::class, 'apiSearch'])->name('search');
    // POST /api/stores -> إنشاء فرع جديد عبر API.
    Route::post('/', [StoreApiController::class, 'apiStore'])->name('store');
    // GET /api/stores/{store} -> إرجاع تفاصيل فرع واحد عبر API.
    Route::get('/{store}', [StoreApiController::class, 'apiShow'])->name('show');
    // PUT /api/stores/{store} -> تحديث بيانات فرع موجود عبر API.
    Route::put('/{store}', [StoreApiController::class, 'apiUpdate'])->name('update');
    // DELETE /api/stores/{store} -> حذف فرع عبر API مع تطبيق قيود الحذف.
    Route::delete('/{store}', [StoreApiController::class, 'apiDestroy'])->name('destroy');
});

// Summary: يعرّف واجهات API الخاصة بالفروع (CRUD + بحث) تحت /api/stores مع الحفاظ على أسماء المسارات.