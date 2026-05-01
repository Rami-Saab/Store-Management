<?php // Name : Rodain Gouzlan Id:

namespace App\Http\Middleware;

use Illuminate\Http\Middleware\TrustProxies as Middleware;
use Illuminate\Http\Request;

/**
 * Middleware: الثقة بالـ Proxies (مثل Cloudflare / Load Balancers)
 *
 * يحدد الـ headers التي يستخدمها Laravel لقراءة IP الحقيقي والبروتوكول عند وجود Proxy أمام التطبيق.
 */
class TrustProxies extends Middleware
{
    /**
     * الـ Proxies الموثوقة لهذا التطبيق (إن وُجدت).
     *
     * @var array<int, string>|string|null
     */
    protected $proxies;

    /**
     * الـ Headers التي يعتمد عليها Laravel لاكتشاف معلومات الـ Proxy.
     *
     * @var int
     */
    protected $headers =
        Request::HEADER_X_FORWARDED_FOR |
        Request::HEADER_X_FORWARDED_HOST |
        Request::HEADER_X_FORWARDED_PORT |
        Request::HEADER_X_FORWARDED_PROTO |
        Request::HEADER_X_FORWARDED_AWS_ELB;
}