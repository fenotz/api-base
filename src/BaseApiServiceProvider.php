<?php

namespace Fenox\ApiBase;

use Fenox\ApiBase\Console\MakeApiModel;
use Fenox\ApiBase\Middleware\ForceJsonResponse;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Support\ServiceProvider;
use Illuminate\Http\Request;
use Throwable;
use Illuminate\Database\QueryException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Foundation\Configuration\Exceptions;


class BaseApiServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                MakeApiModel::class,
            ]);
        }

        // Registrar el middleware de JSON
        $kernel = $this->app->make(Kernel::class);
        $kernel->pushMiddleware(ForceJsonResponse::class);

        // Registrar el manejo de excepciones para respuestas JSON
        // $this->app->singleton(
        //    \Illuminate\Contracts\Debug\ExceptionHandler::class,
        //    \App\Exceptions\Handler::class
        // );
        $this->registerExceptionHandler();
    }

    protected function registerExceptionHandler(): void
    {
        //dd("hola");
        $this->app->make('Illuminate\Contracts\Debug\ExceptionHandler')->renderable(function (Throwable $e, Request $request) {
            if ($e instanceof \Illuminate\Auth\AuthenticationException) {
                return response()->json(['message' => 'Unauthenticated'], 401);
            }

            if ($e instanceof \Illuminate\Auth\Access\AuthorizationException) {
                return response()->json(['message' => 'Forbidden'], 403);
            }

            if ($e instanceof \Symfony\Component\HttpKernel\Exception\NotFoundHttpException) {
                return response()->json(['message' => 'Route not found'], 404);
            }

            if ($e instanceof \Illuminate\Validation\ValidationException) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $e->errors(),
                ], 422);
            }

            if ($e instanceof \Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException) {
                return response()->json(['message' => 'Method not allowed'], 405);
            }

            if ($e instanceof \Illuminate\Http\Exceptions\ThrottleRequestsException) {
                return response()->json(['message' => 'Too many requests'], 429);
            }

            if ($e instanceof \Symfony\Component\HttpKernel\Exception\BadRequestHttpException) {
                return response()->json(['message' => 'Bad request'], 400);
            }

            // Fallback para otros errores
            return response()->json(['message' => 'Server Error'], 500);
        });
    }
}
