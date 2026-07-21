<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\Response;

/**
 * Keeps generated URLs aligned with the browser origin during local dev.
 * Also respects reverse proxies (Cloudflare Tunnel, ngrok) so HTTPS demos
 * do not emit http:// asset links that browsers block as mixed content.
 */
class ForceLocalRootUrl
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! app()->environment('local') || ! $request->getHttpHost()) {
            return $next($request);
        }

        $scheme = $this->resolveScheme($request);
        URL::forceRootUrl($scheme.'://'.$request->getHttpHost());

        if ($scheme === 'https') {
            URL::forceScheme('https');
        }

        return $next($request);
    }

    protected function resolveScheme(Request $request): string
    {
        if ($request->isSecure() || $request->header('X-Forwarded-Proto') === 'https') {
            return 'https';
        }

        $configured = (string) config('app.url');
        if (str_starts_with($configured, 'https://')) {
            $configuredHost = parse_url($configured, PHP_URL_HOST);
            if ($configuredHost && strcasecmp($request->getHost(), $configuredHost) === 0) {
                return 'https';
            }
        }

        return $request->getScheme() ?: 'http';
    }
}
