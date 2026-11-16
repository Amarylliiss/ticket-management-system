<?php

declare(strict_types=1);

namespace App\Domain\Ticket\Services;

use App\Domain\Ticket\Enums\TicketPriority;
use DateTimeImmutable;

final class ResponseTimePolicy
{
    public function targetSeconds(TicketPriority $priority): int
    {
        return $priority->firstResponseTargetMinutes() * 60;
    }

    public function elapsedSeconds(DateTimeImmutable $start, DateTimeImmutable $end): int
    {
        return max(0, $end->getTimestamp() - $start->getTimestamp());
    }

    public function isFirstResponseLate(
        TicketPriority $priority,
        DateTimeImmutable $createdAt,
        ?DateTimeImmutable $firstResponseAt,
        ?DateTimeImmutable $closedAt,
        DateTimeImmutable $now,
    ): bool {
        $end = $firstResponseAt ?? $closedAt ?? $now;

        return $this->elapsedSeconds($createdAt, $end) > $this->targetSeconds($priority);
    }
}
