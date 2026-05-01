<?php // Name : Rodain Gouzlan Id:

namespace App\Providers;

use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

/**
 * Service Provider للأحداث (Events) والمستمعين (Listeners).
 *
 * هذا الملف جزء من هيكل Laravel القياسي.
 * في مشروعنا الحالي لا توجد أحداث خاصة بوحدة الأفرع، لكنه يبقى جاهزاً للتوسع.
 */
class EventServiceProvider extends ServiceProvider
{
    /**
     * ربط الأحداث (Events) بالمستمعين (Listeners).
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
    ];

    /**
     * تسجيل أحداث التطبيق إن وجدت.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}