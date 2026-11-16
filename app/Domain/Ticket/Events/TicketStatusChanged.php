<?php

declare(strict_types=1);

namespace App\Domain\Ticket\Events;

final class TicketStatusChanged
{
    public function __construct(
        public string $ticketId,
        public string $reference,
        public string $title,
        public string $from,
        public string $to,
        public int $actorId,
        public int $customerId,
    ) {}
}
