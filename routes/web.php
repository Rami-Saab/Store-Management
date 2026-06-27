<?php // Name : Rodain Gouzlan Id:

// هذا الملف مسؤول عن تعريف مسارات الويب (Web routes) الخاصة بواجهات المستخدم.
// أي رابط تراه في المتصفح غالبًا يبدأ تعريفه من هنا.

/*
|--------------------------------------------------------------------------
| مسارات الويب (واجهة المستخدم)
|--------------------------------------------------------------------------
| هذا الملف يحتوي على جميع مسارات واجهة الويب الخاصة بالنظام.
|
| ملاحظات مهمة للطبيب/المراجع:
| - النظام يعتمد على تسجيل دخول بسيط (اسم المستخدم + كلمة المرور).
| - جميع صفحات "إدارة الأفرع/المتاجر" (Programming Block 3) موجودة ضمن prefix = stores.
| - تم فصل المهام إلى Controllers متخصصة: CRUD، المنتجات، التعيينات، لوحة المعلومات، والبحث.
| - تم حماية المسارات بواسطة Middleware: guest للمصادقة و auth لباقي الصفحات.
*/

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\StoreAuthController;
use App\Http\Controllers\Auth\TokenAuthController;
use App\Http\Controllers\Stores\StoreController;
use App\Http\Controllers\Stores\StoreDashboardController;
use App\Http\Controllers\Stores\StoreProductController;
use App\Http\Controllers\Products\ProductController;
use App\Http\Controllers\SearchController;

// مجموعة مسارات "الزائر" (غير المسجل دخول) مثل صفحة تسجيل الدخول.
// مجموعة مسارات للزوار غير المسجلين (guest).
Route::middleware('guest')->group(function () {
    // عرض صفحة تسجيل الدخول (GET /).
    Route::get('/', [StoreAuthController::class, 'login'])->name('login');
    // تنفيذ عملية تسجيل الدخول والتحقق من بيانات المستخدم (POST /login).
    Route::post('/login', [StoreAuthController::class, 'authenticate'])->name('login.attempt');
    // تسجيل دخول عبر Sanctum وإرجاع token (بدون إنشاء جلسة).
    Route::post('/auth/token/login', [TokenAuthController::class, 'login'])->name('auth.token.login');
});

// مجموعة مسارات "المستخدم المسجل" (تتطلب auth) لباقي صفحات النظام.
// مجموعة مسارات للمستخدمين المسجلين فقط (auth).
Route::middleware('auth')->group(function () {
    // إصدار token بناءً على جلسة الويب الحالية (للاستخدام داخل نفس التبويب).
    Route::get('/auth/token', [TokenAuthController::class, 'issueFromSession'])->name('auth.token.issue');
    // تسجيل الخروج (POST /logout) مع مسح جلسة المستخدم.
    Route::post('/logout', [StoreAuthController::class, 'logout'])->name('logout');
    // عرض لوحة المعلومات الرئيسية (Dashboard).
    // مسار عرض لوحة المعلومات الرئيسية (Dashboard).
    Route::get('/dashboard', [StoreDashboardController::class, 'dashboard'])->name('dashboard');
    // صفحة البحث العامة في النظام (بحث ذكي عن موظفين/منتجات/أفرع...).
    // مسار البحث العام داخل النظام (Search).
    Route::get('/search', [SearchController::class, 'index'])->name('search');
    // صفحة تفاصيل المنتج مع عرض الأفرع المرتبطة به.
    // مسار عرض تفاصيل منتج واحد مع الفروع المرتبطة به.
    Route::get('/products/{product}', [ProductController::class, 'show'])->name('products.show');

    // جميع مسارات إدارة الأفرع ضمن prefix = /stores و name prefix = stores.*
    // مجموعة مسارات إدارة الفروع (Store Management) مع صلاحيات الأدوار.
    Route::prefix('stores')->name('stores.')->middleware('role:admin,store_manager,store_employee')->group(function () {
        // صفحة قائمة الأفرع (Grid) مع فلاتر + بحث AJAX.
        Route::get('/', [StoreController::class, 'index'])->name('index');
        // صفحة إنشاء فرع جديد.
        Route::get('/create', [StoreController::class, 'create'])->name('create');
        // حفظ فرع جديد في قاعدة البيانات (Create -> Store).
        Route::post('/', [StoreController::class, 'store'])->name('store');

        // رفع البرشور على شكل chunks لتجنب 413 عند حدود رفع صغيرة في PHP/السيرفر.
        Route::post('/brochure/upload-chunk', [StoreController::class, 'uploadChunk'])
            ->name('brochure.uploadChunk');

        // عرض تفاصيل فرع معيّن.
        Route::get('/{store}', [StoreController::class, 'show'])->name('show');
        // صفحة تعديل فرع موجود.
        Route::get('/{store}/edit', [StoreController::class, 'edit'])->name('edit');
        // تحديث بيانات فرع موجود (Edit -> Update).
        Route::put('/{store}', [StoreController::class, 'update'])->name('update');
        // حذف فرع مع تطبيق قيود الحذف (إن كان مرتبط بمنتجات/مستودعات لا يُحذف).
        Route::delete('/{store}', [StoreController::class, 'destroy'])->name('destroy');

        // صفحة تعيينات الفرع (مدير + موظفين) لفرع محدد.
        Route::get('/{store}/assignments', [StoreController::class, 'assignments'])->name('assignments');
        // حفظ/تحديث تعيينات الفرع (مزامنة المدير والموظفين).
        Route::put('/{store}/assignments', [StoreController::class, 'updateAssignments'])->name('assignments.update');

        // صفحة ربط المنتجات بفرع محدد (Products Linked to Store).
        Route::get('/{store}/products', [StoreProductController::class, 'products'])->name('products');
        // حفظ/تحديث المنتجات المرتبطة بالفرع (Sync Products).
        Route::put('/{store}/products', [StoreProductController::class, 'updateProducts'])->name('products.update');

        // صفحة عرض البرشور (Brochure) داخل النظام (Viewer).
        Route::get('/{store}/brochure', [StoreController::class, 'viewBrochure'])->name('brochure.view');
        Route::get('/{store}/brochure/inline', [StoreController::class, 'inlineBrochure'])->name('brochure.inline');
        // تحميل ملف البرشور (Download) للفرع.
        Route::get('/{store}/brochure/download', [StoreController::class, 'downloadBrochure'])->name('brochure.download');
    });

});

// إلغاء token عبر Sanctum (لمن يستخدم Authorization Bearer).
Route::middleware('auth:sanctum')->post('/auth/token/logout', [TokenAuthController::class, 'logoutToken'])->name('auth.token.logout');

// Summary: يعرّف مسارات الويب الأساسية (تسجيل الدخول/الخروج، لوحة التحكم، البحث، المنتجات) ومسارات إدارة الفروع مع حماية الصلاحيات والتحميلات الخاصة بالبروشور.