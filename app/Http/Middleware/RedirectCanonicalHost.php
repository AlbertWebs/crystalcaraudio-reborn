<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectCanonicalHost
{
    public function handle(Request $request, Closure $next): Response
    {
        $canonicalUrl = rtrim((string) config('app.url'), '/');
        $canonicalHost = parse_url($canonicalUrl, PHP_URL_HOST);

        if (app()->environment('production') && is_string($canonicalHost) && $request->getHost() !== $canonicalHost) {
            return redirect()->to($canonicalUrl.$request->getRequestUri(), 301);
        }

        return $next($request);
    }
}
