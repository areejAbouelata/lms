<?php

namespace App\Http\Middleware;

use Closure;

class DriverMiddleware
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
       if (auth('api')->check() && in_array(auth('api')->user()->user_type,['driver']) && ! auth('api')->user()->is_user_deactive) {
           return $next($request);
       }elseif (auth('api')->check() &&  auth('api')->user()->is_user_deactive) {
           return response()->json(['status' => 'fail','message'=> 'تم حظر حسابك رجاء التواصل مع الادارة للتفعيل','data' => null] ,403);
       }else{
            return response()->json(['status' => 'fail','message'=>'بيانات تسجيل الدخول غير صحيحة','data' => null] ,401);
       }

     }
}
