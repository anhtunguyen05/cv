<?php

use App\Presentation\Http\Controllers\Auth\CurrentAccountController;
use App\Presentation\Http\Controllers\Auth\LoginController;
use App\Presentation\Http\Controllers\Auth\LogoutController;
use App\Presentation\Http\Controllers\Auth\RegisterController;
use App\Presentation\Http\Controllers\HealthController;
use App\Presentation\Http\Middleware\RejectBearerToken;
use App\Presentation\Http\Middleware\RejectMalformedJson;
use App\Presentation\Http\Middleware\RequireCsrfToken;
use Illuminate\Support\Facades\Route;

Route::get('/health', HealthController::class);

Route::prefix('v1/auth')->group(function (): void {
    Route::post('/register', RegisterController::class)->middleware([
        'web',
        RejectBearerToken::class,
        RejectMalformedJson::class,
        RequireCsrfToken::class,
        'throttle:registration-ip',
        'throttle:registration-email',
    ]);
    Route::post('/login', LoginController::class)->middleware([
        'web',
        RejectBearerToken::class,
        RejectMalformedJson::class,
        RequireCsrfToken::class,
        'throttle:login-ip',
        'throttle:login-email',
    ]);
    Route::post('/logout', LogoutController::class)->middleware([
        'web',
        RejectBearerToken::class,
        RejectMalformedJson::class,
        RequireCsrfToken::class,
    ]);
    Route::get('/me', CurrentAccountController::class)->middleware([
        RejectBearerToken::class,
        'auth:sanctum',
    ]);
});
