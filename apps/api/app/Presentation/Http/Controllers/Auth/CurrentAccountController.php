<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controllers\Auth;

use App\Presentation\Http\Controllers\Controller;
use App\Presentation\Http\Resources\PublicUserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class CurrentAccountController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $response = response()->json([
            'data' => [
                'user' => (new PublicUserResource($request->user()))->resolve($request),
            ],
        ]);
        $response->headers->set('Cache-Control', 'private, no-store');

        return $response;
    }
}
