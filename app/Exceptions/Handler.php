<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Database\Eloquent\ModelNotFoundException as ModelNotFoundException;
use Illuminate\Auth\AuthenticationException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception.
     *
     * @param  \Exception  $exception
     * @return void
     *
     * @throws \Exception
     */
    public function report(Throwable $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $exception
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Exception
     */
    public function render($request, Throwable $exception)
    {
        if ($this->isHttpException($exception) && !$request->wantsJson()) {
            $code = $exception->getStatusCode();
            switch ($code) {
                case '404':
                    return response()->json([ '404 notauth'], 404);
                    break;
                case '403':
                    return response()->view('dashboard.error.403', [], 403);
                    break;
                default:
                    abort(404);
                    break;
            }
        }

        if ($this->isHttpException($exception) && $request->wantsJson()) {
            $code = $exception->getStatusCode();
            switch ($code) {
                case '404':
                    return response()->json(['status' => 'fail','message' => 'الصفحة غير موجودة' , 'data' => null],404);
                    break;
                case '403':
                    return response()->json(['status' => 'fail','message' => 'ليس لديك صلاحيات الدخول' , 'data' => null],403);
                    break;
                default:
                    return response()->json(['status' => 'fail','message' => 'الصفحة غير موجودة' , 'data' => null],403);
                    break;
            }
        }

        if ($exception instanceof \Illuminate\Foundation\Http\Exceptions\MaintenanceModeException) {
                return response()
                    ->view('dashboard.error.503')
                    ->header('Content-Type', 'text/html; charset=utf-8');
        }

        if ($exception instanceof ModelNotFoundException && auth()->check() && in_array(auth()->user()->user_type,['admin','superadmin']) && ! $request->ajax() && ! $request->wantsJson()) {
            return response()->view('dashboard.error.404', [], 404);
        }

        if ($exception instanceof ModelNotFoundException && $request->wantsJson()) {
          return response()->json(['status' => 'fail','message' => "لم يتم العثور علي بيانات",'data'=> null ],404);
        }

        if ($exception instanceof AuthenticationException && $request->wantsJson()) {
            return response()->json(['status' => 'fail','message' => 'قم بتسجيل الدخول أولا' , 'data' => null ],401);
        }

        return parent::render($request, $exception);
    }
}
