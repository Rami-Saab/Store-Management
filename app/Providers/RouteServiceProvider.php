<?php // Name : Rodain Gouzlan Id:

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

/**
 * Service Provider لمسارات التطبيق (Routes).
 *
 * هذا الملف يحدد:
 * - مسار HOME بعد تسجيل الدخول
 * - تحميل ملفات المسارات: routes/web.php و routes/api.php
 * - ضبط Rate Limiting الخاص بالـ API
 */
class RouteServiceProvider extends ServiceProvider
{
    /**
     * مسار الصفحة الرئيسية بعد تسجيل الدخول.
     *
     * يستخدمه Laravel لإعادة توجيه المستخدم بعد نجاح تسجيل الدخول.
     *
     * @var string
     */
    public const HOME = '/dashboard';

    /**
     * namespace الافتراضي للـ Controllers (اختياري).
     *
     * عند تفعيله، يتم إضافة هذا الـ namespace تلقائياً عند تعريف Routes بالأسلوب القديم.
     *
     * @var string|null
     */
    // protected $namespace = 'App\\Http\\Controllers';

    /**
     * تعريف bindings الخاصة بالـ Route Model Binding أو أي إعدادات للمسارات.
     *
     * @return void
     */
    public function boot()
    {
        $this->configureRateLimiting();

        $this->routes(function () {
            Route::prefix('api')
                ->middleware('api')
                ->namespace($this->namespace)
                ->group(base_path('routes/api.php'));

            Route::middleware('web')
                ->namespace($this->namespace)
                ->group(base_path('routes/web.php'));
        });
    }

    /**
     * إعداد Rate Limiting للـ API لحماية التطبيق من الطلبات الزائدة.
     *
     * @return void
     */
    protected function configureRateLimiting()
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });
    }
}