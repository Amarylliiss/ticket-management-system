<?php

declare(strict_types=1);

namespace App\Application\Ticket\Commands;

final readonly class ChangeTicketPriorityCommand
{
    public function __construct(
        public string $ticketId,
        public string $priority,
        public int $actorId,
    ) {}
}
