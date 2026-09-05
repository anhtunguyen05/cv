<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controllers;

use Illuminate\Http\JsonResponse;

final class HealthController extends Controller
{
    public function __invoke(): JsonResponse
    {
        return response()->json([
            'status' => 'ok',
            'service' => config('app.name'),
            'timestamp' => now()->toIso8601String(),
        ]);
    }
}
