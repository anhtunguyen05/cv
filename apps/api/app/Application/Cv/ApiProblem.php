<?php

declare(strict_types=1);

namespace App\Application\Cv;

use RuntimeException;

final class ApiProblem extends RuntimeException
{
    public function __construct(
        public readonly string $errorCode,
        string $message,
        public readonly int $status,
        public readonly array $details = [],
    ) {
        parent::__construct($message);
    }
}
