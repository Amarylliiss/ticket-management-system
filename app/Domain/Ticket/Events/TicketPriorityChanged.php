<?php

declare(strict_types=1);

namespace App\Domain\Ticket\Events;

final class TicketPriorityChanged
{
    public function __construct(
        public string $ticketId,
        public string $reference,
        public string $from,
        public string $to,
        public int $actorId,
    ) {}
}
