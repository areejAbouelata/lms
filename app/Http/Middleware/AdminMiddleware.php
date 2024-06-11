<?php

namespace App\Http\Middleware;

use Closure;
use Request;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {

        // info(Request::route()->getName()) ;

        if (auth('api')->check() && auth('api')->user()->user_type == 'superadmin') {
            return $next($request);
        } elseif (auth('api')->check() && auth('api')->user()->role()->exists() && auth('api')->user()->user_type == 'admin') {
            if ($request->internal && $request->internal == 1) {
                return $next($request);
            }
            $url = Request::url();
            $method = Request::method();
            info($method);
            $incoming_permission = trim(str_replace('https://onboarding.phpv8.aait-d.com/api/dashboard/', '', $url));
            $has_number = filter_var($incoming_permission, FILTER_SANITIZE_NUMBER_INT);
            $has_uuid = preg_match('/\w{8}-\w{4}-\w{4}-\w{4}-\w{12}/', trim(str_replace('https://onboarding.phpv8.aait-d.com/api/dashboard/', '', $url)), $matches);
            $custom = $incoming_permission . "/." . $method;
            if ($has_number && !$has_uuid) {
                $incoming_permission = $has_number ? trim(str_replace($has_number, '*', $incoming_permission)) : null;
                $custom = $incoming_permission . "/." . $method;
                info($custom);
            } elseif ($has_uuid) {
                $incoming_permission = $has_uuid ? preg_replace("/\w{8}-\w{4}-\w{4}-\w{4}-\w{12}/", "*", $incoming_permission) : null;
                $custom = $incoming_permission . "/." . $method;
                info($custom);
            }
            $internal_apis = [
                "statistics",
            ];
            $role = auth()->user()->role;

            if (!$role->permissions()->where('back_route_name', $custom)->exists() && !in_array($incoming_permission, $internal_apis)) {
                return response()->json(['status' => 'fail', 'message' => 'not authorized', 'data' => null], 403);
            }
            return $next($request);
        }
        return response()->json(['status' => 'fail', 'message' => 'not authorized', 'data' => null], 403);
    }
}
