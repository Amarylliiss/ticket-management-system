<?php

declare(strict_types=1);

namespace App\Domain\Ticket\Events;

final class TicketSubmitted
{
    public function __construct(
        public string $ticketId,
        public string $reference,
        public string $title,
        public int $customerId,
        public int $categoryId,
        public string $priority,
    ) {}
}
