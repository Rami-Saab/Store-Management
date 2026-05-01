<?php // Name : Rodain Gouzlan Id:

// هذا الملف مسؤول عن Controller لوحة معلومات الفروع (Dashboard).
// وظيفته: استدعاء Service الإحصائيات وتمريرها إلى الواجهة.

namespace App\Http\Controllers\Stores;

/**
 * Controller: لوحة معلومات إدارة الأفرع (Dashboard)
 *
 * هذه الصفحة تعرض إحصائيات عامة تساعد في متابعة حالة الأفرع:
 * - عدد الأفرع الكلي والنشطة
 * - عدد المنتجات المتاحة
 * - عدد المدراء/الموظفين المرتبطين
 * - متوسطات وروابط المنتجات للفرع ... إلخ
 *
 * تم وضع التجميعات الثقيلة داخل StoreStatsService حتى لا تصبح الواجهة أو الـ Controller مزدحمة.
 */

// الاستيرادات اللازمة.
use App\Http\Controllers\Controller;
// خدمة الإحصائيات التي تجمع البيانات اللازمة للداشبورد.
use App\Services\Store\StoreStatsService;
use Illuminate\Support\Facades\Auth;
// View لإرجاع صفحة Blade.
use Illuminate\Http\Response;

class StoreDashboardController extends Controller
{
    // نحقن خدمة الإحصائيات عبر الـ DI حتى يبقى الـ Controller خفيفًا ومركزًا.
    public function __construct(private StoreStatsService $statsService)
    {
        // حقن خدمة الإحصائيات عبر DI.
    }

    public function dashboard(): Response
    {
        // عرض صفحة الداشبورد بإحصائيات شاملة لكل الأفرع.
        return response()
            ->view('stores.dashboard', $this->statsService->dashboardData(Auth::user()))
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache');
    }
}