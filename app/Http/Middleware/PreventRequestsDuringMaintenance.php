<?php // Name : Rodain Gouzlan Id:

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\PreventRequestsDuringMaintenance as Middleware;

/**
 * Middleware: منع الطلبات أثناء وضع الصيانة (Maintenance Mode)
 *
 * عندما يكون التطبيق في وضع الصيانة، يمنع Laravel الوصول لمعظم الصفحات.
 * يمكن السماح لبعض المسارات بالوصول عبر $except (إن لزم).
 */
class PreventRequestsDuringMaintenance extends Middleware
{
    /**
     * المسارات (URIs) المسموح بالوصول لها أثناء وضع الصيانة.
     *
     * @var array<int, string>
     */
    protected $except = [
        //
    ];
}