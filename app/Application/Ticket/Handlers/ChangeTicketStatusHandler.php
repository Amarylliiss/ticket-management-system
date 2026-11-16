<?php

declare(strict_types=1);

namespace App\Application\Ticket\Handlers;

use App\Application\Ticket\Commands\ChangeTicketStatusCommand;
use App\Application\Ticket\Handlers\Concerns\SavesTickets;
use App\Domain\Ticket\Enums\TicketStatus;
use App\Domain\Ticket\Repositories\TicketRepository;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class ChangeTicketStatusHandler
{
    use SavesTickets;

    public function __construct(private TicketRepository $tickets) {}

    public function handle(ChangeTicketStatusCommand $command): void
    {
        $actor = User::query()->findOrFail($command->actorId);
        $status = TicketStatus::from($command->status);

        DB::transaction(function () use ($command, $actor, $status): void {
            $ticket = $this->findTicket($command->ticketId);

            if ($ticket->customerId() !== $actor->id && ! $actor->isStaff()) {
                throw ValidationException::withMessages([
                    'status' => 'Nie możesz zmienić statusu tego zgłoszenia.',
                ]);
            }

            if (! $actor->isStaff() && $status !== TicketStatus::Closed) {
                throw ValidationException::withMessages([
                    'status' => 'Klient może tylko zamknąć własne zgłoszenie.',
                ]);
            }

            $ticket->changeStatus($status, $actor->id);
            $this->saveAndDispatch($ticket);
        });
    }

    protected function tickets(): TicketRepository
    {
        return $this->tickets;
    }
}
