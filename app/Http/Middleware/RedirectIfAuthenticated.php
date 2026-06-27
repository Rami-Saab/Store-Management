<?php // Name : Rodain Gouzlan Id:

namespace App\Http\Middleware;

use App\Providers\RouteServiceProvider;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Middleware: إعادة توجيه المستخدم إذا كان مسجلاً للدخول
 *
 * الفكرة:
 * - إذا حاول مستخدم مسجّل الدخول فتح صفحة مخصصة للضيوف (مثل login)
 *   يتم توجيهه للصفحة الرئيسية بعد الدخول.
 */
class RedirectIfAuthenticated
{
    /**
     * معالجة طلب HTTP وارد.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @param  string|null  ...$guards
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next, ...$guards)
    {
        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                return redirect(RouteServiceProvider::HOME);
            }
        }

        return $next($request);
    }
}