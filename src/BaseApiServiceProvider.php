<?php
namespace Fenox\ApiBase;

use Fenox\ApiBase\Console\MakeApiModel;
use Fenox\ApiBase\Middleware\ForceJsonResponse;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Support\ServiceProvider;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Http\Request;
use Throwable;
use Illuminate\Database\QueryException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

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
        $this->app->afterResolving(
            Exceptions::class,
            function ($exceptions) {
                $exceptions->render(function (Throwable $e, Request $request) {
                    // Manejar excepciones y forzar respuestas en JSON
                    if ($e instanceof QueryException && $e->getCode() === '42S02') {
                        return response()->json([
                            'message' => 'The requested table or view does not exist in the database. Run php artisan:migrate?',
                        ], 500);
                    }

                    if ($e instanceof QueryException && $e->getCode() === 'HY000') {
                        return response()->json([
                            'message' => 'A required field is missing or has no default value. (Check your Request Rules)',
                        ], 422);
                    }

                    if ($e instanceof ValidationException) {
                        return response()->json([
                            'status' => 'error',
                            'message' => 'Validation failed',
                            'errors' => $e->errors(),
                        ], 422);
                    }

                    if ($e instanceof AuthenticationException) {
                        return response()->json([
                            'message' => 'Unauthenticated',
                        ], 401);
                    }

                    if ($e instanceof NotFoundHttpException) {
                        return response()->json([
                            'message' => 'Route not found',
                        ], 404);
                    }

                    if ($e instanceof QueryException && $e->errorInfo[1] == 1062) {
                        return response()->json([
                            'message' => 'The field has already been taken.',
                        ], 422);
                    }

                    return response()->json([
                        'message' => $e->getMessage(),
                    ], $e->getCode() ?: 500);
                });
            }
        );
    }
}
