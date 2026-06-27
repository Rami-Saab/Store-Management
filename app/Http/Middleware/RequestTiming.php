<?php // Name : Rodain Gouzlan Id:

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class RequestTiming
{
    public function handle(Request $request, Closure $next): Response
    {
        $start = hrtime(true);

        /** @var \Symfony\Component\HttpFoundation\Response $response */
        $response = $next($request);

        $durationMs = (hrtime(true) - $start) / 1_000_000;

        $response->headers->set('X-Response-Time-Ms', (string) (int) round($durationMs));

        $existingServerTiming = (string) ($response->headers->get('Server-Timing') ?? '');
        $serverTimingEntry = sprintf('app;dur=%.1f', $durationMs);
        $response->headers->set(
            'Server-Timing',
            $existingServerTiming !== '' ? ($existingServerTiming.', '.$serverTimingEntry) : $serverTimingEntry
        );

        $thresholdMs = (int) config('performance.slow_request_ms', 2000);
        if ($thresholdMs > 0 && $durationMs >= $thresholdMs) {
            Log::warning('Slow request (>= threshold)', [
                'method' => $request->method(),
                'path' => '/'.$request->path(),
                'route' => $request->route()?->getName(),
                'status' => method_exists($response, 'getStatusCode') ? $response->getStatusCode() : null,
                'duration_ms' => round($durationMs, 1),
                'user_id' => $request->user()?->id,
            ]);
        }

        return $response;
    }
}

