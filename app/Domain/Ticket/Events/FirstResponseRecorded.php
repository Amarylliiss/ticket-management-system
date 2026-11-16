<?php

declare(strict_types=1);

namespace App\Domain\Ticket\Events;

final class FirstResponseRecorded
{
    public function __construct(
        public string $ticketId,
        public int $actorId,
        public string $respondedAt,
    ) {}
}
