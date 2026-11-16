<?php

declare(strict_types=1);

namespace App\Application\Ticket\Commands;

final readonly class ChangeTicketStatusCommand
{
    public function __construct(
        public string $ticketId,
        public string $status,
        public int $actorId,
    ) {}
}
