<?php

namespace App\Exceptions;

use App\Traits\ApiResponser;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Throwable;
use Illuminate\Http\Response;
use Illuminate\Http\Request;

class Handler extends ExceptionHandler
{
    use ApiResponser;
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
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    /**
     * @param Request $request
     * @param Throwable $e
     * @return JsonResponse
     * @throws Throwable
     */
    public function render($request, Throwable $e): JsonResponse
    {
        return $this->handleThrowable($request, $e);
    }

    /**
     * @param $request
     * @param Throwable $exception
     * @return JsonResponse
     */
    private function handleThrowable($request, Throwable $exception)
    {
        switch(class_basename($exception)) {
            case 'QueryException':
                return $this->errorResponse('SQL complaint: '.$exception->getCode().' - '.$exception->getMessage(), 500);
            case 'ModelNotFoundException':
                $model = explode('\\', $exception->getModel());
                $model = end($model);
                $message = strtolower($model)."_not_found";
                return $this->errorResponse($message, Response::HTTP_NOT_FOUND);
                break;
            case 'NotFoundHttpException':
                return $this->errorResponse('route_not_found', Response::HTTP_NOT_FOUND);
                break;
            case 'AuthorizationException':
                return $this->errorResponse('only_json_content_type', Response::HTTP_BAD_REQUEST);
                break;
            case 'AccessDeniedHttpException':
                return $this->errorResponse('denied_access', Response::HTTP_UNAUTHORIZED);
                break;
            case 'ValidationException':
                $errors = $exception->validator->errors()->getMessages();
                return  $this->errorResponse($errors, Response::HTTP_UNPROCESSABLE_ENTITY);
            case 'Exception':
                $code = (is_null($exception->getCode()) || $exception->getCode() == 0)? 500 : $exception->getCode() ;
                return  $this->errorResponse($exception->getMessage(), $code);
                break;
            case 'Error':
            default:
                if (method_exists($exception, 'render')){
                    return $exception->render($request);
                } else {
                    if (method_exists($exception, 'getStatusCode')) {
                        $code = $exception->getStatusCode();
                        $code = (is_null($code) || $code == 0)? 500 : $exception->getStatusCode();
                    } else {
                        $code = (is_null($exception->getCode()) || $exception->getCode() == 0)? 500 : $exception->getCode() ;
                    }
                    return  $this->errorResponse([$exception->getMessage(), $exception->getTrace()], $code);
                }
        }
    }
}
