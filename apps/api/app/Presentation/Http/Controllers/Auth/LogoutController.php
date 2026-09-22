<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controllers\Auth;

use App\Presentation\Http\Controllers\Controller;
use App\Presentation\Http\Responses\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

final class LogoutController extends Controller
{
    public function __invoke(Request $request): Response|JsonResponse
    {
        $body = trim($request->getContent());
        if ($body !== '' && json_decode($body, true) !== []) {
            return ApiResponse::error('VALIDATION_FAILED', 'Body must be empty.', status: 422);
        }

        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->noContent();
    }
}
