<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Routing\Middleware\SubstituteBindings;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Event context must be applied after StartSession and before
        // SubstituteBindings so hashed route model binding can resolve.
        $middleware->web(
            append: [
                \App\Http\Middleware\SetEventContext::class,
                SubstituteBindings::class,
            ],
            remove: [
                SubstituteBindings::class,
            ],
        );

        // Register middleware aliases
        $middleware->alias([
            'event.admin' => \App\Http\Middleware\EventAdmin::class,
            'attendee.auth' => \App\Http\Middleware\AttendeeAuth::class,
            'submission.portal' => \App\Http\Middleware\SubmissionPortalAuthenticated::class,
            'submissions.enabled' => \App\Http\Middleware\SubmissionModuleEnabled::class,
            'submission.ability' => \App\Http\Middleware\RequireSubmissionAbility::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
