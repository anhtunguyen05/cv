<?php

declare(strict_types=1);

namespace App\Presentation\Http\Controllers\Cv;

use App\Application\Cv\TemplatePresenter;
use App\Application\Cv\TemplateService;
use App\Presentation\Http\Controllers\Controller;
use App\Presentation\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;

final class TemplateController extends Controller
{
    public function __construct(private readonly TemplateService $templates) {}

    public function index(): JsonResponse
    {
        $data = array_map(
            fn ($template): array => TemplatePresenter::summary($template, $this->templates),
            $this->templates->available(),
        );

        return ApiResponse::data($data);
    }
}
