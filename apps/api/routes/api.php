<?php

use App\Presentation\Http\Controllers\Auth\CurrentAccountController;
use App\Presentation\Http\Controllers\Auth\LoginController;
use App\Presentation\Http\Controllers\Auth\LogoutController;
use App\Presentation\Http\Controllers\Auth\RegisterController;
use App\Presentation\Http\Controllers\Cv\ProfileController;
use App\Presentation\Http\Controllers\Cv\VersionController;
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
        'web',
        RejectBearerToken::class,
        'auth:sanctum',
    ]);
});

Route::middleware([
    'web',
    RejectBearerToken::class,
    'auth:sanctum',
])->prefix('v1')->group(function (): void {
    Route::get('/cv-profiles', [ProfileController::class, 'index']);
    Route::get('/cv-profiles/{profile}', [ProfileController::class, 'show']);
    Route::get('/cv-versions', [VersionController::class, 'index']);
    Route::get('/cv-versions/{version}', [VersionController::class, 'show']);

    Route::post('/cv-profiles', [ProfileController::class, 'store'])->middleware([
        RejectMalformedJson::class,
        RequireCsrfToken::class,
    ]);
    Route::put('/cv-profiles/{profile}/personal-information', [ProfileController::class, 'updatePersonal'])->middleware([
        RejectMalformedJson::class,
        RequireCsrfToken::class,
    ]);
    Route::put('/cv-profiles/{profile}/title', [ProfileController::class, 'updateTitle'])->middleware([
        RejectMalformedJson::class,
        RequireCsrfToken::class,
    ]);
    Route::put('/cv-profiles/{profile}/{section}', [ProfileController::class, 'updateSection'])->middleware([
        RejectMalformedJson::class,
        RequireCsrfToken::class,
    ]);
    Route::post('/cv-profiles/{profile}/versions', [VersionController::class, 'store'])->middleware([
        RejectMalformedJson::class,
        RequireCsrfToken::class,
    ]);
});
