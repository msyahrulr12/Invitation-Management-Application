<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->report(function (\Throwable $e) {
            // Skip logging standard validation/HTTP/Auth exceptions as system anomalies
            if ($e instanceof \Illuminate\Validation\ValidationException ||
                $e instanceof \Illuminate\Auth\Access\AuthorizationException ||
                $e instanceof \Symfony\Component\HttpKernel\Exception\HttpException) {
                return;
            }

            try {
                \Modules\LogManagement\Models\LogModel::create([
                    'level' => 'error',
                    'message' => 'System Anomaly: ' . substr($e->getMessage() ?: get_class($e), 0, 200),
                    'context' => json_encode([
                        'exception' => get_class($e),
                        'message' => substr($e->getMessage(), 0, 1000),
                        'file' => $e->getFile(),
                        'line' => $e->getLine(),
                        'trace' => substr($e->getTraceAsString(), 0, 2000), 
                        'url' => app()->runningInConsole() ? 'CLI' : request()->fullUrl(),
                        'method' => app()->runningInConsole() ? 'CLI' : request()->method(),
                        'ip' => app()->runningInConsole() ? '127.0.0.1' : request()->ip(),
                    ]),
                    'user_id' => \Illuminate\Support\Facades\Auth::check() ? \Illuminate\Support\Facades\Auth::id() : null,
                ]);
            } catch (\Throwable $dbEx) {
                // Fail silently or fallback to standard log to prevent infinite loops if database is down
                \Illuminate\Support\Facades\Log::error('Failed logging exception to database: ' . $dbEx->getMessage());
            }
        });
    })->create();
