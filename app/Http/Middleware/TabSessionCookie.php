<?php // Name : Rodain Gouzlan Id:

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Assign a unique session cookie per browser tab using a tab id.
 *
 * The tab id is passed via the "tab" query parameter (or hidden form input)
 * and stored in sessionStorage on the client. This enables multiple accounts
 * to be used in different tabs of the same browser without session collisions.
 */
class TabSessionCookie
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): \Symfony\Component\HttpFoundation\Response  $next
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        $tabId = $this->resolveTabId($request);

        if ($tabId === null && $this->shouldBootstrapTab($request)) {
            return $this->tabBootstrapResponse();
        }

        if ($tabId !== null) {
            config(['session.cookie' => 'rg_tab_' . $tabId]);
            $request->attributes->set('tabId', $tabId);
        }

        $response = $next($request);

        if ($tabId !== null && $response instanceof RedirectResponse) {
            $response->setTargetUrl($this->appendTabToUrl($response->getTargetUrl(), $tabId, $request));
        }

        return $response;
    }

    /**
     * Resolve the tab id from query/body/header.
     */
    private function resolveTabId(Request $request): ?string
    {
        $tabId = $request->query('tab');
        if (!is_string($tabId) || $tabId === '') {
            $tabId = $request->input('tab');
        }
        if (!is_string($tabId) || $tabId === '') {
            $tabId = $request->header('X-Tab-Id');
        }

        if (!is_string($tabId)) {
            return null;
        }

        $tabId = trim($tabId);
        if ($tabId === '') {
            return null;
        }

        if (!preg_match('/^[A-Za-z0-9_-]{6,64}$/', $tabId)) {
            return null;
        }

        return $tabId;
    }

    /**
     * Ensure the tab id persists across redirects.
     */
    private function appendTabToUrl(string $url, string $tabId, Request $request): string
    {
        if ($tabId === '') {
            return $url;
        }

        $parts = parse_url($url);
        if ($parts === false) {
            return $url;
        }

        $targetHost = $parts['host'] ?? '';
        if ($targetHost !== '' && $targetHost !== $request->getHost()) {
            return $url;
        }

        $query = [];
        if (!empty($parts['query'])) {
            parse_str($parts['query'], $query);
        }

        if (isset($query['tab'])) {
            return $url;
        }

        $query['tab'] = $tabId;
        $queryString = http_build_query($query);

        $scheme = $parts['scheme'] ?? '';
        $host = $parts['host'] ?? '';
        $port = isset($parts['port']) ? ':' . $parts['port'] : '';
        $path = $parts['path'] ?? '';
        $fragment = isset($parts['fragment']) ? '#' . $parts['fragment'] : '';

        $authority = $host !== '' ? $scheme . '://' . $host . $port : '';
        $base = $authority . $path;

        return $base . ($queryString !== '' ? '?' . $queryString : '') . $fragment;
    }

    /**
     * Determine if we should return a bootstrap page to attach a tab id.
     */
    private function shouldBootstrapTab(Request $request): bool
    {
        if (!in_array($request->method(), ['GET', 'HEAD'], true)) {
            return false;
        }

        if ($request->expectsJson() || $request->wantsJson() || $request->ajax()) {
            return false;
        }

        $accept = strtolower((string) $request->header('Accept', ''));
        if ($accept !== '' && !str_contains($accept, 'text/html') && !str_contains($accept, '*/*')) {
            return false;
        }

        $path = strtolower((string) $request->path());
        if (preg_match('/\.(css|js|png|jpg|jpeg|gif|svg|ico|webp|pdf|map)$/', $path)) {
            return false;
        }

        return true;
    }

    /**
     * Return a minimal HTML page that sets the tab id then reloads with it.
     */
    private function tabBootstrapResponse(): Response
    {
        $html = <<<'HTML'
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Loading...</title>
    <style>
        html, body { height: 100%; margin: 0; font-family: system-ui, -apple-system, "Segoe UI", sans-serif; color: #0f172a; background: #f8fafc; }
        .wrap { height: 100%; display: grid; place-items: center; }
        .spinner {
            width: 44px;
            height: 44px;
            border-radius: 999px;
            border: 4px solid rgba(148, 163, 184, 0.35);
            border-top-color: #3b82f6;
            box-shadow: 0 10px 18px rgba(15, 23, 42, 0.12);
            background: #ffffff;
            animation: spin 0.75s linear infinite;
        }
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
<div class="wrap">
    <div class="spinner" role="status" aria-live="polite" aria-label="Loading"></div>
</div>
<script>
    (function () {
        var TAB_PARAM = 'tab';
        var STORAGE_KEY = 'rg_tab_id';

        function sanitize(value) {
            if (typeof value !== 'string') return null;
            var trimmed = value.trim();
            if (!trimmed) return null;
            if (!/^[A-Za-z0-9_-]{6,64}$/.test(trimmed)) return null;
            return trimmed;
        }

        function generateTabId() {
            var bytes = new Uint8Array(8);
            if (window.crypto && window.crypto.getRandomValues) {
                window.crypto.getRandomValues(bytes);
            } else {
                for (var i = 0; i < bytes.length; i += 1) {
                    bytes[i] = Math.floor(Math.random() * 256);
                }
            }
            return Array.from(bytes).map(function (b) { return b.toString(16).padStart(2, '0'); }).join('');
        }

        var url = new URL(window.location.href);
        var fromUrl = sanitize(url.searchParams.get(TAB_PARAM));
        var fromStorage = sanitize(sessionStorage.getItem(STORAGE_KEY));
        var tabId = fromUrl || fromStorage || generateTabId();

        if (tabId) {
            sessionStorage.setItem(STORAGE_KEY, tabId);
        }

        if (!url.searchParams.has(TAB_PARAM) && tabId) {
            url.searchParams.set(TAB_PARAM, tabId);
            window.location.replace(url.toString());
        }
    })();
</script>
<noscript>Please enable JavaScript to continue.</noscript>
</body>
</html>
HTML;

        return new Response($html, 200, ['Content-Type' => 'text/html; charset=UTF-8']);
    }
}