<?php

use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Middleware\NoCacheHeaders;
use App\Http\Middleware\UpdateLastActiveAt;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleMiddleware;
use Spatie\Permission\Middleware\RoleOrPermissionMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Trust all proxies (handle HTTPS behind reverse proxy)
        $middleware->trustProxies(at: '*');

        $middleware->web([
            HandleInertiaRequests::class,
            NoCacheHeaders::class,
            UpdateLastActiveAt::class,
        ]);

        $middleware->alias([
            'role' => RoleMiddleware::class,
            'permission' => PermissionMiddleware::class,
            'role_or_permission' => RoleOrPermissionMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Custom redirect for unauthenticated users based on the URL they were accessing
        $exceptions->render(function (AuthenticationException $e, Request $request) {
            $path = $request->path();
            $intendedUrl = $request->fullUrl();

            // Determine which login page to redirect to based on the URL path
            if (str_starts_with($path, 'admin')) {
                // Admin routes -> redirect to admin login
                return redirect()->guest(route('login'));
            }

            if (str_starts_with($path, 'seller')) {
                // Seller routes -> redirect to seller login
                return redirect()->guest(route('seller.login'));
            }

            // All other routes (customer, cart, orders, public pages requiring auth)
            // -> redirect to customer login with intended URL
            $loginUrl = route('customer.login');
            if ($intendedUrl && $intendedUrl !== url('/')) {
                $loginUrl .= '?intended='.urlencode($intendedUrl);
            }

            return redirect()->guest($loginUrl);
        });
    })->create();
