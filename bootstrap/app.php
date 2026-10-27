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
<<<<<<< HEAD
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
=======
        $middleware->redirectUsersTo(fn (\Illuminate\Http\Request $request): string => route($request->user()->landingRoute()));
        $middleware->web(append: [\App\Http\Middleware\EnsureAccountIsActive::class, \App\Http\Middleware\HandleInertiaRequests::class]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->respond(function (\Symfony\Component\HttpFoundation\Response $response) {
            $status = $response->getStatusCode();
            if (! request()->expectsJson() && in_array($status, [401, 403, 404, 409, 419, 429, 500, 503], true)) {
                return \Inertia\Inertia::render('Error', [
                    'status' => $status,
                    'message' => match ($status) {
                        403 => 'Vous n’avez pas accès à cette page.',
                        404 => 'Cette page est introuvable.',
                        409 => 'Le dossier a été modifié. Actualisez pour continuer.',
                        419 => 'Votre session a expiré.',
                        429 => 'Trop de demandes. Patientez avant de réessayer.',
                        default => 'Le service est temporairement indisponible.',
                    },
                ])->toResponse(request())->setStatusCode($status);
            }

            return $response;
        });
>>>>>>> version1
    })->create();
