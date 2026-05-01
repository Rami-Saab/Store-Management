<?php // Name : Rodain Gouzlan Id:

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

/*
|--------------------------------------------------------------------------
| مسارات/أوامر الطرفية (Console Commands)
|--------------------------------------------------------------------------
|
| هذا الملف مخصص لتعريف أوامر Artisan البسيطة (بالاعتماد على Closure).
| كل Closure يتم ربطه مع كائن أمر (Command) للسماح بالتعامل مع دخل/خرج الطرفية.
|
*/

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Summary: يعرّف أوامر Artisan المبسطة عبر Closures (مثل أمر inspire الافتراضي).