<?php // Name : Rodain Gouzlan Id:

namespace App\Services\Currency;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class ExchangeRateService
{
    public function usdToSypRate(): ?float
    {
        $override = (float) config('currency.usd_to_syp_override', 0);
        if ($override > 0) {
            return $override;
        }

        $cacheKey = 'exchange_rate_usd_syp_v1';
        $cached = Cache::get($cacheKey);
        if (is_numeric($cached) && (float) $cached > 0) {
            return (float) $cached;
        }

        $url = (string) config('currency.usd_to_syp_url', '');
        $fallback = (float) config('currency.usd_to_syp_fallback', 0);
        $timeout = (int) config('currency.http_timeout', 4);
        $cacheMinutes = (int) config('currency.cache_minutes', 360);

        if ($url === '') {
            return $fallback > 0 ? $fallback : null;
        }

        try {
            $response = Http::timeout($timeout)->retry(1, 200)->get($url);
            if ($response->successful()) {
                $data = $response->json();
                $rate = data_get($data, 'rates.SYP');
                if (! is_numeric($rate)) {
                    $rate = data_get($data, 'conversion_rates.SYP');
                }
                if (is_numeric($rate) && (float) $rate > 0) {
                    Cache::put($cacheKey, (float) $rate, now()->addMinutes($cacheMinutes));
                    return (float) $rate;
                }
            }
        } catch (\Throwable $e) {
            // Keep silent and fall back if needed.
        }

        return $fallback > 0 ? $fallback : null;
    }
}