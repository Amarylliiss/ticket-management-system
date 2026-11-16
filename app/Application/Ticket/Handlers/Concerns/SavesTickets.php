<?php

declare(strict_types=1);

namespace App\Application\Ticket\Handlers\Concerns;

use App\Domain\Ticket\Entities\Ticket;
use App\Domain\Ticket\Repositories\TicketRepository;
use App\Domain\Ticket\ValueObjects\TicketId;

trait SavesTickets
{
    abstract protected function tickets(): TicketRepository;

    protected function findTicket(string $id): Ticket
    {
        return $this->tickets()->find(TicketId::fromString($id));
    }

    protected function saveAndDispatch(Ticket $ticket): void
    {
        $this->tickets()->save($ticket);

        foreach ($ticket->releaseEvents() as $event) {
            event($event);
        }
    }
}
