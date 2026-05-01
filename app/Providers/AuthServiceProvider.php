<?php // Name : Rodain Gouzlan Id:

namespace App\Providers;

use App\Models\Store;
use App\Policies\StorePolicy;
use App\Services\Access\StoreScopeService;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

/**
 * Service Provider خاص بالصلاحيات (Policies/Gates).
 *
 * في هذا المشروع الاعتماد الأساسي للصلاحيات يتم عبر Trait (StoreAuthorization)
 * و Form Requests، لكن هذا الملف جزء من الهيكل القياسي لـ Laravel ويمكن توسيعه عند الحاجة.
 */
class AuthServiceProvider extends ServiceProvider
{
    /**
     * ربط الـ Policies مع الـ Models في حال تم استخدامها.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Store::class => StorePolicy::class,
    ];

    /**
     * تسجيل خدمات المصادقة/الصلاحيات (Auth/Authorization) إن وُجدت.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        Gate::define('store-scope', function ($user, Store $store) {
            return app(StoreScopeService::class)->canAccessStore($user, $store);
        });
    }
}