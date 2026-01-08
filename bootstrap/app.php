<?php

declare(strict_types=1);

use Sentry\Laravel\Integration;
use Illuminate\Foundation\Application;
use App\Http\Middleware\HandleInertiaRequests;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // IMPORTANTE:
        // Se o cookie XSRF-TOKEN for criptografado, o valor muda a cada response (IV aleatório),
        // o frontend não consegue reutilizar o token e você vê 419 (TokenMismatch) de forma intermitente.
        // O padrão do Laravel é NÃO criptografar o XSRF-TOKEN.
        $middleware->encryptCookies(except: [
            'XSRF-TOKEN',
        ]);

        $middleware->web(append: [
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
        ]);
        $middleware->validateCsrfTokens(except: [
            'stripe/*',
            'prism/*',
        ]);
        $middleware->trustProxies(at: '*');
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        Integration::handles($exceptions); // Intgrate Sentry with Application
    })->create();
