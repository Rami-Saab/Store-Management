<?php // Name : Rodain Gouzlan Id:

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\TrimStrings as Middleware;

/**
 * Middleware: إزالة المسافات الزائدة من المدخلات (Trim)
 *
 * يقوم Laravel بحذف المسافات من بداية/نهاية قيم الـ Request.
 * يمكن استثناء بعض الحقول الحساسة (كلمات المرور) من عملية الـ trim.
 */
class TrimStrings extends Middleware
{
    /**
     * أسماء الحقول التي لا نريد تطبيق trim عليها.
     *
     * @var array<int, string>
     */
    protected $except = [
        'current_password',
        'password',
        'password_confirmation',
    ];
}