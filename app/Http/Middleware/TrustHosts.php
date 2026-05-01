<?php // Name : Rodain Gouzlan Id:

namespace App\Http\Middleware;

use Illuminate\Http\Middleware\TrustHosts as Middleware;

/**
 * Middleware: الثقة بالمضيفين (Trusted Hosts)
 *
 * تُستخدم لتحديد أنماط أسماء النطاقات (hosts) التي نعتبرها موثوقة.
 * غالباً تبقى كما هي في المشاريع الصغيرة/الجامعية.
 */
class TrustHosts extends Middleware
{
    /**
     * إرجاع أنماط الـ host الموثوقة.
     *
     * @return array<int, string|null>
     */
    public function hosts()
    {
        return [
            $this->allSubdomainsOfApplicationUrl(),
        ];
    }
}