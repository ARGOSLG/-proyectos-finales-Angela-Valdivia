<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withBroadcasting(
        __DIR__.'/../routes/channels.php',
        ['middleware' => ['auth:sanctum']],
    )
    ->withMiddleware(function (Middleware $middleware) {
    // Registrar middleware de roles
    $middleware->alias([
        'role' => \App\Http\Middleware\CheckRole::class,
         'device.token' => \App\Http\Middleware\DeviceTokenMiddleware::class,
    ]);
     // Para APIs — devolver JSON en lugar de redirigir al login
    $middleware->redirectGuestsTo(fn() => response()->json([
        'message' => 'No autenticado'
    ], 401));
})
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->is('broadcasting/*'),
        );
    })->create();