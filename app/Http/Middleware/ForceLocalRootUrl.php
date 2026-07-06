<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\Response;

/**
 * When APP_URL omits the dev-server port (e.g. http://localhost while
 * `php artisan serve --port=8001` is running), absolute redirects and
 * generated links jump to the wrong origin and the session appears lost.
 */
class ForceLocalRootUrl
{
    public function handle(Request $request, Closure $next): Response
    {
        if (app()->environment('local') && $request->getHttpHost()) {
            URL::forceRootUrl($request->getSchemeAndHttpHost());
        }

        return $next($request);
    }
}
