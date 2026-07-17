<?php

use App\Http\Controllers\PublicRegistration\CategoryStepController;
use App\Http\Controllers\PublicRegistration\ConfirmationStepController;
use App\Http\Controllers\PublicRegistration\EmailStepController;
use App\Http\Controllers\PublicRegistration\EntryController;
use App\Http\Controllers\PublicRegistration\InformationStepController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Online Registration Routes
|--------------------------------------------------------------------------
|
| Refresh-safe multi-step public registration wizard.
| After email capture, step URLs include an encrypted draft key ({reg}).
|
*/

Route::get('/{slug}', EntryController::class)
    ->name('online.registration');

Route::get('/{slug}/new', [EntryController::class, 'startNew'])
    ->name('online.registration.new');

Route::get('/{slug}/resume/{token}', [EmailStepController::class, 'resume'])
    ->middleware('throttle:registration-resume')
    ->name('online.registration.resume');

// Bootstrap / cookie-fallback routes (no encrypted reg yet)
Route::get('/{slug}/step/email', [EmailStepController::class, 'show'])
    ->name('online.registration.step.email');
Route::post('/{slug}/step/email', [EmailStepController::class, 'store'])
    ->middleware('throttle:registration-draft')
    ->name('online.registration.step.email.store');
Route::get('/{slug}/step/information', [InformationStepController::class, 'show'])
    ->name('online.registration.step.information');
Route::post('/{slug}/step/information', [InformationStepController::class, 'store'])
    ->middleware('throttle:registration-draft')
    ->name('online.registration.step.information.store');
Route::get('/{slug}/step/category', [CategoryStepController::class, 'show'])
    ->name('online.registration.step.category');
Route::post('/{slug}/step/category', [CategoryStepController::class, 'store'])
    ->middleware('throttle:registration-draft')
    ->name('online.registration.step.category.store');
Route::get('/{slug}/step/confirmation', [ConfirmationStepController::class, 'show'])
    ->name('online.registration.step.confirmation');
Route::post('/{slug}/step/confirmation', [ConfirmationStepController::class, 'store'])
    ->middleware('throttle:registration-complete')
    ->name('online.registration.step.confirmation.store');

// Draft-scoped wizard steps (encrypted reg key in the path)
Route::prefix('{slug}/{reg}')
    ->where(['reg' => '^(?!step$|resume$).+'])
    ->group(function () {
        Route::get('/step/email', [EmailStepController::class, 'show'])
            ->name('online.registration.reg.step.email');
        Route::post('/step/email', [EmailStepController::class, 'store'])
            ->middleware('throttle:registration-draft')
            ->name('online.registration.reg.step.email.store');
        Route::post('/step/email/verify', [EmailStepController::class, 'verify'])
            ->middleware('throttle:registration-otp-verify')
            ->name('online.registration.reg.step.email.verify');
        Route::post('/step/email/resend', [EmailStepController::class, 'resend'])
            ->middleware('throttle:registration-otp-send')
            ->name('online.registration.reg.step.email.resend');

        Route::get('/step/information', [InformationStepController::class, 'show'])
            ->name('online.registration.reg.step.information');
        Route::post('/step/information', [InformationStepController::class, 'store'])
            ->middleware('throttle:registration-draft')
            ->name('online.registration.reg.step.information.store');

        Route::get('/step/category', [CategoryStepController::class, 'show'])
            ->name('online.registration.reg.step.category');
        Route::post('/step/category', [CategoryStepController::class, 'store'])
            ->middleware('throttle:registration-draft')
            ->name('online.registration.reg.step.category.store');

        Route::get('/step/confirmation', [ConfirmationStepController::class, 'show'])
            ->name('online.registration.reg.step.confirmation');
        Route::post('/step/confirmation', [ConfirmationStepController::class, 'store'])
            ->middleware('throttle:registration-complete')
            ->name('online.registration.reg.step.confirmation.store');
    });
