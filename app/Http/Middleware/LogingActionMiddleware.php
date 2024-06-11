<?php

namespace App\Http\Middleware;

use App\Events\LogActionEvent;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\Routing\Route;

class LogingActionMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        // dd(request()->method()); DELETE GET POST  PUT|PATCH  , GET|HEAD
        //    dd(request()->url());
        if (auth()->check()) {
            $user = auth()->user();
            $title = request()->route()->getName();
            $method = request()->method();
            $url = request()->url();
             LogActionEvent::dispatch($user, $title, $method, $url);
            return $next($request);
        }
        return $next($request);
    }
}