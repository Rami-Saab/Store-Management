<?php // Name : Rodain Gouzlan Id:

namespace App\Http\Middleware;

use Illuminate\Cookie\Middleware\EncryptCookies as Middleware;

/**
 * Middleware: تشفير ملفات تعريف الارتباط (Cookies)
 *
 * Laravel يقوم بتشفير cookies بشكل افتراضي لحماية البيانات.
 * يمكن استثناء بعض أسماء cookies من التشفير عبر $except عند الحاجة.
 */
class EncryptCookies extends Middleware
{
    /**
     * أسماء cookies التي لا يجب تشفيرها (إن وُجدت).
     *
     * @var array<int, string>
     */
    protected $except = [
        //
    ];
}