<?php // Name : Rodain Gouzlan Id:

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

/**
 * Middleware: التحقق من CSRF Token
 *
 * يحمي التطبيق من هجمات تزوير الطلبات (Cross-Site Request Forgery) لطلبات POST/PUT/DELETE.
 * يمكن استثناء بعض المسارات من التحقق عبر $except (غير مستحسن إلا عند الضرورة).
 */
class VerifyCsrfToken extends Middleware
{
    /**
     * المسارات (URIs) المستثناة من التحقق من CSRF (إن وُجدت).
     *
     * @var array<int, string>
     */
    protected $except = [
        //
    ];
}