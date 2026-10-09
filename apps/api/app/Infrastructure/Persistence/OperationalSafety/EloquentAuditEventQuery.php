<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\OperationalSafety;

use App\Infrastructure\Persistence\OperationalSafety\Eloquent\Models\OperationalAuditEvent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use InvalidArgumentException;

final class EloquentAuditEventQuery
{
    private const MAX_PER_PAGE = 100;

    /** @var array<int,string> */
    private const OPERATIONS = ['generate-patch', 'analyze-job-description', 'validate-match'];

    public function paginate(?string $operation = null, ?string $correlationId = null, int $page = 1, int $perPage = 50): LengthAwarePaginator
    {
        if ($page < 1 || $perPage < 1 || $perPage > self::MAX_PER_PAGE) {
            throw new InvalidArgumentException('AUDIT_QUERY_BOUNDS');
        }
        if ($operation !== null && ! in_array($operation, self::OPERATIONS, true)) {
            throw new InvalidArgumentException('AUDIT_QUERY_OPERATION');
        }
        if ($correlationId !== null && preg_match('/^[A-Za-z0-9][A-Za-z0-9._:-]{0,127}$/', $correlationId) !== 1) {
            throw new InvalidArgumentException('AUDIT_QUERY_CORRELATION');
        }

        $query = OperationalAuditEvent::query()
            ->select(OperationalAuditEvent::query()->getModel()->getFillable())
            ->orderByDesc('occurred_at')
            ->orderByDesc('id');
        $this->applyFilters($query, $operation, $correlationId);

        return $query->paginate($perPage, ['*'], 'page', $page);
    }

    private function applyFilters(Builder $query, ?string $operation, ?string $correlationId): void
    {
        if ($operation !== null) {
            $query->where('operation', $operation);
        }
        if ($correlationId !== null) {
            $query->where('correlation_id', $correlationId);
        }
    }
}
