<?php // Name : Rodain Gouzlan Id:

namespace App\Providers;

use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\ServiceProvider;

/**
 * Service Provider خاص بالبث (Broadcasting).
 *
 * في حال كان هناك استخدام لقنوات البث في النظام، يتم تفعيل مسارات البث هنا
 * وتحميل تعريف القنوات من routes/channels.php.
 */
class BroadcastServiceProvider extends ServiceProvider
{
    /**
     * تهيئة خدمات البث عند الإقلاع.
     *
     * @return void
     */
    public function boot()
    {
        Broadcast::routes();

        require base_path('routes/channels.php');
    }
}