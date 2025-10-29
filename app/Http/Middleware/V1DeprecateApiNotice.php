<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class V1DeprecateApiNotice
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        $response->headers->set('X-API-Deprecated', 'true');
        $response->headers->set('X-API-Deprecation-Date', '2025-10-28');
        $response->headers->set('X-API-Sunset', 'https://example.com/docs/deprecations/v1');

        return $response;
    }
}
