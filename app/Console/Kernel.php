<?php // Name : Rodain Gouzlan Id:

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

/**
 * Console Kernel
 *
 * مسؤول عن:
 * - تفعيل الـ Schedule (إن تم استخدامه).
 * - تحميل أوامر Artisan المخصصة الموجودة في app/Console/Commands.
 */
class Kernel extends ConsoleKernel
{
    /**
     * تعريف جدول تشغيل الأوامر (Schedule) إن وُجد.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // مثال على جدولة أمر ليعمل كل ساعة (يمكن تفعيله عند الحاجة).
        // $schedule->command('inspire')->hourly();
    }

    /**
     * تسجيل أوامر Artisan الخاصة بالتطبيق.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}