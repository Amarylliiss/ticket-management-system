<?php

declare(strict_types=1);

namespace App\Application\Ticket\Handlers;

use App\Application\Ticket\Commands\AssignTicketCommand;
use App\Application\Ticket\Handlers\Concerns\SavesTickets;
use App\Domain\Ticket\Repositories\TicketRepository;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class AssignTicketHandler
{
    use SavesTickets;

    public function __construct(private TicketRepository $tickets) {}

    public function handle(AssignTicketCommand $command): void
    {
        $assignee = User::query()->find($command->assigneeId);

        if ($assignee === null || ! $assignee->isStaff()) {
            throw ValidationException::withMessages([
                'assignee_id' => 'Zgłoszenie można przypisać tylko pracownikowi.',
            ]);
        }

        DB::transaction(function () use ($command): void {
            $ticket = $this->findTicket($command->ticketId);
            $ticket->assign($command->assigneeId, $command->actorId);
            $this->saveAndDispatch($ticket);
        });
    }

    protected function tickets(): TicketRepository
    {
        return $this->tickets;
    }
}
