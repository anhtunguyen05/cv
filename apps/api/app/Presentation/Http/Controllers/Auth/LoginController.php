<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controllers\Auth;

use App\Presentation\Http\Controllers\Controller;
use App\Presentation\Http\Requests\LoginRequest;
use App\Presentation\Http\Resources\PublicUserResource;
use App\Presentation\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

final class LoginController extends Controller
{
    public function __invoke(LoginRequest $request): JsonResponse
    {
        $credentials = [
            'email' => $request->string('email')->toString(),
            'password' => $request->string('password')->toString(),
        ];

        if (! Auth::guard('web')->attempt($credentials)) {
            return ApiResponse::error(
                'INVALID_CREDENTIALS',
                'The email or password is incorrect.',
                status: 401,
            );
        }

        $request->session()->regenerate();
        $user = Auth::guard('web')->user();

        $response = response()->json([
            'data' => [
                'user' => (new PublicUserResource($user))->resolve($request),
            ],
        ]);
        $response->headers->set('Cache-Control', 'private, no-store');

        return $response;
    }
}
