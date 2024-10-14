<?php

namespace Fenox\ApiBase;

use Fenox\ApiBase\Console\MakeApiModel;
use Fenox\ApiBase\Middleware\ForceJsonResponse;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Support\ServiceProvider;
use Illuminate\Http\Request;
use Throwable;
use Illuminate\Database\Eloquent\ModelNotFoundException;


class BaseApiServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                MakeApiModel::class,
            ]);
        }

        $kernel = $this->app->make(Kernel::class);
        $kernel->pushMiddleware(ForceJsonResponse::class);
        $this->registerExceptionHandler();
        $this->publishes([
            __DIR__.'/Requests/LoginRequest.php' => app_path('Http/Requests/FenoxApiRequests/User/LoginRequest.php'),
            __DIR__.'/Requests/StoreUserRequest.php' => app_path('Http/Requests/FenoxApiRequests/User/StoreUserRequest.php'),
            __DIR__.'/Requests/UpdateUserRequest.php' => app_path('Http/Requests/FenoxApiRequests/User/UpdateUserRequest.php'),
            __DIR__.'/Controllers/AuthController.php' => app_path('Http/Controllers/FenoxApiControllers/AuthController.php'),
        ], 'fenox-api-auth'); // Un solo tag para todas las publicaciones
    }

    protected function registerExceptionHandler(): void
    {
        $this->app->make('Illuminate\Contracts\Debug\ExceptionHandler')->renderable(function (Throwable $e, Request $request) {
            $statusCode = 500; // Código de estado por defecto
            $message = 'An unexpected error occurred. Please try again later.'; // Mensaje por defecto

            // Manejo de excepciones personalizadas
            if ($e instanceof ModelNotFoundException) {
                $model = class_basename($e->getModel());
                $message = "{$model} not found.";
                $statusCode = 404;
            } elseif ($e instanceof \Symfony\Component\HttpKernel\Exception\NotFoundHttpException) {
                // dd($e);
                if (str_contains($e->getMessage(), 'No query results for model') !== false) {
                    return response()->json([
                        'message' => 'The requested resource was not found.',
                    ], 404);
                }
                $message = 'The requested URL was not found.';
                $statusCode = 404;
            } elseif ($e instanceof \Illuminate\Auth\AuthenticationException) {
                $message = 'You need to log in to access this resource.';
                $statusCode = 401;
            } elseif ($e instanceof \Illuminate\Auth\Access\AuthorizationException) {
                $message = 'You do not have permission to perform this action.';
                $statusCode = 403;
            } elseif ($e instanceof \Illuminate\Validation\ValidationException) {
                $errors = $e->errors();
                $message = 'Validation failed';
                $statusCode = 422;
                return response()->json([
                    'status' => 'error',
                    'message' => $message,
                    'errors' => $errors,
                ], $statusCode);
            } elseif ($e instanceof \Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException) {
                $message = 'The method is not allowed for this route.';
                $statusCode = 405;
            } elseif ($e instanceof \Illuminate\Http\Exceptions\ThrottleRequestsException) {
                $message = 'You are making too many requests. Please slow down.';
                $statusCode = 429;
            } elseif ($e instanceof \Symfony\Component\HttpKernel\Exception\BadRequestHttpException) {
                $message = 'The request could not be understood by the server.';
                $statusCode = 400;
            }

            return response()->json(['message' => $message], $statusCode);
        });
    }


}
