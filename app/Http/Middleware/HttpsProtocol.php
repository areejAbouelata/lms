<?php

namespace App\Http\Middleware;

use Closure;

class HttpsProtocol
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $whitelist = array(
                '127.0.0.1',
                '::1'
            );
        $my_url = $request->getPathInfo() . ($request->getQueryString() ? ('?' . $request->getQueryString()) : '');
        if (!$request->secure() && !in_array($request->ip(), $whitelist)) {
            return redirect()->secure($my_url);
        }

        return $next($request);
    }
}
