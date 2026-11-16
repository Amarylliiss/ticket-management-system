<?php

declare(strict_types=1);

namespace App\Application\Ticket\Handlers;

use App\Application\Ticket\Commands\ChangeTicketPriorityCommand;
use App\Application\Ticket\Handlers\Concerns\SavesTickets;
use App\Domain\Ticket\Enums\TicketPriority;
use App\Domain\Ticket\Repositories\TicketRepository;
use Illuminate\Support\Facades\DB;

final class ChangeTicketPriorityHandler
{
    use SavesTickets;

    public function __construct(private TicketRepository $tickets) {}

    public function handle(ChangeTicketPriorityCommand $command): void
    {
        DB::transaction(function () use ($command): void {
            $ticket = $this->findTicket($command->ticketId);
            $ticket->changePriority(TicketPriority::from($command->priority), $command->actorId);
            $this->saveAndDispatch($ticket);
        });
    }

    protected function tickets(): TicketRepository
    {
        return $this->tickets;
    }
}
