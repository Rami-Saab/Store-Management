<?php // Name : Rodain Gouzlan Id:

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;

/**
 * Middleware: التأكد من تسجيل الدخول (Authentication)
 *
 * إذا لم يكن المستخدم مسجلاً للدخول:
 * - في طلبات الويب يتم توجيهه إلى صفحة login.
 * - في طلبات JSON/API يتم ترك Laravel يتعامل مع الاستجابة المناسبة.
 */
class Authenticate extends Middleware
{
    /**
     * إرجاع المسار الذي يجب توجيه المستخدم إليه عند عدم المصادقة.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    protected function redirectTo($request)
    {
        if (! $request->expectsJson()) {
            return route('login', [], false);
        }
    }
}