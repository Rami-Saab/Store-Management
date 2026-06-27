<?php // Name : Rodain Gouzlan Id:

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

/**
 * Service Provider عام للتطبيق.
 *
 * في هذا المشروع لم نحتج لإضافات كبيرة هنا، لكن تركناه كجزء من هيكل Laravel القياسي.
 */
class AppServiceProvider extends ServiceProvider
{
    /**
     * تسجيل خدمات التطبيق (Service Container bindings) إن وجدت.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * تهيئة خدمات التطبيق عند الإقلاع (Bootstrapping) إن وجدت.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}