<?php

use App\Http\Middleware\RateLimitMiddleware;
use App\Http\Middleware\ValidateClientKey;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        
        $middleware->alias([
            'client.key' => ValidateClientKey::class,
            'rate.limit' => RateLimitMiddleware::class,
        ]);
        
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (ValidationException $e, $request) {
            return response()->json([
                'success' => false,
                'status'  => 422,
                'message' => 'Validation failed.',
                'errors'  => $e->errors(),
            ], 422);
        });

        $exceptions->render(function (ModelNotFoundException $e, $request) {
            return response()->json([
                'success' => false,
                'status'  => 404,
                'message' => 'Record not found.',
            ], 404);
        });


        $exceptions->render(function (UnauthorizedHttpException $e, $request) {
            return response()->json([
                'success' => false,
                'status'  => 401,
                'message' => 'Unauthorized.',
            ], 401);
        });

        $exceptions->render(function (Throwable $e, $request) {
            return response()->json([
                'success' => false,
                'status'  => 500,
                'message' => 'Internal server error.',
            ], 500);
        });
    })->create();
