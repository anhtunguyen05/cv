<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controllers\Auth;

use App\Application\Auth\Data\RegisterUserData;
use App\Application\Auth\Exceptions\UserAlreadyExists;
use App\Application\Auth\RegisterUser;
use App\Presentation\Http\Controllers\Controller;
use App\Presentation\Http\Requests\RegisterRequest;
use App\Presentation\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

final class RegisterController extends Controller
{
    public function __invoke(RegisterRequest $request, RegisterUser $registerUser): JsonResponse
    {
        if (Auth::check()) {
            return ApiResponse::error(
                'AUTHENTICATED_REGISTRATION_FORBIDDEN',
                'An authenticated account cannot register another account.',
                status: 409,
            );
        }

        $data = new RegisterUserData(
            name: $request->string('name')->toString(),
            email: $request->string('email')->toString(),
            password: $request->string('password')->toString(),
        );

        try {
            $user = $registerUser->handle($data);
        } catch (UserAlreadyExists) {
            return $this->duplicateEmailResponse();
        }

        Auth::loginUsingId($user->id);
        $request->session()->regenerate();

        $response = response()->json([
            'data' => [
                'user' => [
                    'id' => (string) $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ],
            ],
        ], 201);
        $response->headers->set('Cache-Control', 'private, no-store');

        return $response;
    }

    private function duplicateEmailResponse(): JsonResponse
    {
        return ApiResponse::error(
            'VALIDATION_FAILED',
            'One or more fields are invalid.',
            ['email' => [['code' => 'INVALID', 'message' => 'The email address is unavailable.']]],
            422,
        );
    }
}
