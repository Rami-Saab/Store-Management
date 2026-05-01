<?php // Name : Rodain Gouzlan Id:

namespace App\Http\Controllers;

/**
 * Controller الأساسي في Laravel.
 *
 * يتم توريثه من بقية Controllers لإتاحة Traits الافتراضية:
 * - AuthorizesRequests: صلاحيات Gate/Policy
 * - DispatchesJobs: إرسال Jobs
 * - ValidatesRequests: أدوات التحقق
 */

// Traits افتراضية من Laravel تساعد في الصلاحيات (Policies) وإرسال المهام (Jobs) والتحقق (Validation).
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
// الـ BaseController الرسمي الذي يوفر أساسيات الـ Controller داخل Laravel.
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    // تضمين الـ Traits داخل الـ Controller لتصبح متاحة لكل Controllers التي ترث منه.
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
}