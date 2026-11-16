<?php

declare(strict_types=1);

namespace App\Domain\Ticket\Events;

final class TicketAssigned
{
    public function __construct(
        public string $ticketId,
        public string $reference,
        public string $title,
        public int $assigneeId,
        public int $actorId,
        public int $customerId,
    ) {}
}
