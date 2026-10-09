<?php

declare(strict_types=1);

namespace App\Domain\Evidence\Enums;

enum EvidenceAnswerOutcome: string
{
    case Answer = 'answer';
    case CannotProvide = 'cannot_provide';
}
