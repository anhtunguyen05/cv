<?php

return [
    'patch_provider' => env('AI_PATCH_PROVIDER', 'fake'),
    'service_url' => env('AI_SERVICE_URL', ''),
    'service_token' => env('AI_SERVICE_TOKEN', ''),
    'timeout_seconds' => (int) env('AI_SERVICE_TIMEOUT_SECONDS', 10),
];
