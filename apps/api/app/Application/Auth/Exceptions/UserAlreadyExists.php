<?php

declare(strict_types=1);

namespace App\Application\Auth\Exceptions;

use RuntimeException;

final class UserAlreadyExists extends RuntimeException {}
