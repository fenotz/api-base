<?php
namespace Fenox\ApiBase;

use Fenox\ApiBase\Console\MakeApiModel;
use Illuminate\Support\ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class BaseApiServiceProvider extends ServiceProvider
{
    public function boot()
    {

        if ($this->app->runningInConsole()) {
            $this->commands([
                MakeApiModel::class,
            ]);
        }
        // Registrar el middleware de JSON
        $this->app['router']->aliasMiddleware('force.json', \Fenox\ApiBase\Middleware\ForceJsonResponse::class);

        // Registrar la lógica de excepciones
        $this->registerExceptionHandling();
    }

    /**
     * Registra la lógica de manejo de excepciones
     */
    protected function registerExceptionHandling()
    {
        $this->app->make('Illuminate\Contracts\Debug\ExceptionHandler')->renderable(function ($exception, Request $request) {
            // Manejo de errores
            if ($exception instanceof QueryException && $exception->getCode() === '42S02') {
                return response()->json([
                    'message' => 'The requested table or view does not exist in the database. Run php artisan:migrate?',
                ], 500);
            }

            if ($exception instanceof QueryException) {
                if ($exception->getCode() === 'HY000') {
                    return response()->json([
                        'message' => 'A required field is missing or has no default value. (Check your Request Rules)',
                    ], 422);
                }
            }

            if ($exception instanceof ValidationException) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $exception->errors(),
                ], 422);
            }

            if ($exception instanceof AuthenticationException) {
                return response()->json([
                    'message' => 'Unauthenticated',
                ], 401);
            }

            if ($exception instanceof NotFoundHttpException) {
                return response()->json([
                    'message' => 'Route not found',
                ], 404);
            }

            if ($exception instanceof QueryException && $exception->errorInfo[1] == 1062) {
                return response()->json([
                    'message' => 'The field has already been taken.',
                ], 422);
            }

            return response()->json([
                'message' => $exception->getMessage(),
            ], $exception->getCode() ?: 500);
        });
    }
}

