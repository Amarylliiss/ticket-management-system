<?php

declare(strict_types=1);

namespace App\Domain\Ticket\Repositories;

use App\Domain\Ticket\Entities\Ticket;
use App\Domain\Ticket\ValueObjects\TicketId;
use App\Domain\Ticket\ValueObjects\TicketReference;

interface TicketRepository
{
    public function nextReference(): TicketReference;

    public function save(Ticket $ticket): void;

    public function find(TicketId $id): Ticket;
}
