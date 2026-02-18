<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RegistrationController;

/*
|--------------------------------------------------------------------------
| Online Registration Routes
|--------------------------------------------------------------------------
|
| These routes handle public online registration via custom URLs.
| All routes are prefixed with /online/
|
*/

// Online registration via custom slug
Route::get('/{slug}', [RegistrationController::class, 'showForm'])
    ->name('online.registration');

// Store registration (same endpoint for all URLs)
Route::post('/{slug}', [RegistrationController::class, 'store'])
    ->name('online.registration.store');
