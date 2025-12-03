<?php

use App\Facades\Response;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
        api: __DIR__ . '/../routes/api.php',
    )
    ->withMiddleware(function (Middleware $middleware): void {
       $middleware->alias([
           'idempotency' => App\Http\Middleware\idempotency::class,
       ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {

        $exceptions->render(function (Throwable $e, $request) {
            
            if (! $request->expectsJson() && ! $request->is('api/*')) {
               
                return null; // let Laravel handle HTML
            }
           
            $additionals = [];
            // Determine the correct HTTP status
            $code = $e instanceof \Symfony\Component\HttpKernel\Exception\HttpException
                ? $e->getStatusCode()
                : ($e instanceof \Illuminate\Validation\ValidationException  ? 422 : 500);

            // Validation exception
            if ($e instanceof \Illuminate\Validation\ValidationException) {
                $message     = 'Validation Failed';
                $additionals = $e->errors();
            } elseif ($code >= 500) {
                $message = config('app.debug') ? $e->getMessage() : 'Server Error';

                if (config('app.debug') && config('app.env') === 'local') {
                    $additionals = [
                        'file' => $e->getFile(),
                        'line' => $e->getLine(),
                    ];
                }
            }
            // Other exceptions
            else {
                $message = $e->getMessage() ?: 'Error';
            }

            // Use your API Response facade properly
            $response = Response::successFalse()
                ->setMessage($message)
                ->additionals($additionals)
                ->toArray();

            return response()->json($response, $code);
        });
    })
    ->create();
