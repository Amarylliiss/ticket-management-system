<?php

declare(strict_types=1);

namespace App\Application\Ticket\Commands;

final readonly class AssignTicketCommand
{
    public function __construct(
        public string $ticketId,
        public int $assigneeId,
        public int $actorId,
    ) {}
}
