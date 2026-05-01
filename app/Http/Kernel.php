<?php // Name : Rodain Gouzlan Id:

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

/**
 * Http Kernel
 *
 * هذا الملف يحدد Middleware الأساسية التي يمر بها كل طلب في Laravel.
 * لا يحتوي على منطق أعمال خاص بالمشروع، لكنه مهم لفهم دورة حياة الطلب (Request Lifecycle).
 */
class Kernel extends HttpKernel
{
    /**
     * قائمة الـ Middleware العامة (Global) التي تعمل مع كل طلب HTTP داخل التطبيق.
     *
     * هذه الـ Middleware يتم تنفيذها مع كل Request.
     *
     * @var array<int, class-string|string>
     */
    protected $middleware = [
        // Measure request duration + log slow requests (performance budget).
        \App\Http\Middleware\RequestTiming::class,
        // \App\Http\Middleware\TrustHosts::class,
        // التعامل مع البروكسيات وعناوين العميل الحقيقية.
        \App\Http\Middleware\TrustProxies::class,
        // تمكين CORS للطلبات القادمة من مصادر أخرى.
        \Fruitcake\Cors\HandleCors::class,
        // منع الطلبات أثناء وضع الصيانة.
        \App\Http\Middleware\PreventRequestsDuringMaintenance::class,
        // التحقق من حجم الـ POST لتجنّب طلبات كبيرة جداً.
        \Illuminate\Foundation\Http\Middleware\ValidatePostSize::class,
        // تنظيف النصوص من المسافات الزائدة.
        \App\Http\Middleware\TrimStrings::class,
        // تحويل النصوص الفارغة إلى null.
        \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
    ];

    /**
     * مجموعات الـ Middleware الخاصة بالمسارات (مثل web و api).
     *
     * @var array<string, array<int, class-string|string>>
     */
    protected $middlewareGroups = [
        'web' => [
            // تشفير/فك تشفير الكوكيز.
            \App\Http\Middleware\EncryptCookies::class,
            // إضافة الكوكيز المؤجلة إلى الاستجابة.
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            // تخصيص كوكيز الجلسة لكل تبويب (Tab Session).
            \App\Http\Middleware\TabSessionCookie::class,
            // بدء جلسة المستخدم.
            \Illuminate\Session\Middleware\StartSession::class,
            // \Illuminate\Session\Middleware\AuthenticateSession::class,
            // تمرير أخطاء التحقق للواجهات.
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            // حماية CSRF لطلبات النماذج.
            \App\Http\Middleware\VerifyCsrfToken::class,
            // ربط المسارات بالنماذج (Route Model Binding).
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],

        'api' => [
            // \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
            // حد السرعة الافتراضي لواجهات API.
            'throttle:api',
            // ربط المسارات بالنماذج في سياق API.
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],
    ];

    /**
     * Middleware يمكن استخدامها على مستوى Route بشكل فردي أو ضمن مجموعات.
     *
     * @var array<string, class-string|string>
     */
    protected $routeMiddleware = [
        // التحقق من تسجيل الدخول.
        'auth' => \App\Http\Middleware\Authenticate::class,
        // مصادقة Basic Auth.
        'auth.basic' => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
        // التحكم برؤوس التخزين المؤقت.
        'cache.headers' => \Illuminate\Http\Middleware\SetCacheHeaders::class,
        // السماح/المنع عبر Gate/Policy.
        'can' => \Illuminate\Auth\Middleware\Authorize::class,
        // إعادة التوجيه إذا كان المستخدم مسجلاً.
        'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
        // التحقق من الدور (Role-based).
        'role' => \App\Http\Middleware\EnsureRole::class,
        // التحقق من الروابط الموقعة.
        'signed' => \Illuminate\Routing\Middleware\ValidateSignature::class,
        // تحديد معدل الطلبات.
        'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,
        // التحقق من البريد الموثق.
        'verified' => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,
    ];
}

// Summary: يعرّف هذا الملف طبقات الـ Middleware العامة والمجمّعة ومسميات Middleware القابلة للاستخدام على المسارات.
