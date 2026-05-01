<?php

return [
    'usd_to_syp_override' => (float) env('USD_TO_SYP_OVERRIDE', 0),
    'usd_to_syp_url' => env('USD_TO_SYP_URL', 'https://open.er-api.com/v6/latest/USD'),
    'usd_to_syp_fallback' => (float) env('USD_TO_SYP_RATE', 0),
    'cache_minutes' => (int) env('USD_TO_SYP_CACHE_MINUTES', 360),
    'http_timeout' => (int) env('USD_TO_SYP_HTTP_TIMEOUT', 4),
];
