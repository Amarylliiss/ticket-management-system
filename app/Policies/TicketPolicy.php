<?php

declare(strict_types=1);

namespace App\Policies;

use App\Domain\Ticket\Enums\TicketStatus;
use App\Infrastructure\Persistence\Eloquent\Models\TicketModel;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class TicketPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, TicketModel $ticket): Response
    {
        return $user->isStaff() || $user->id === $ticket->customer_id
            ? Response::allow()
            : Response::deny('Nie masz dostępu do tego zgłoszenia.');
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function assign(User $user, TicketModel $ticket): Response
    {
        return $user->isStaff()
            ? Response::allow()
            : Response::deny('Tylko pracownik może przypisać zgłoszenie.');
    }

    public function changeStatus(User $user, TicketModel $ticket): Response
    {
        if ($user->isStaff() || $user->id === $ticket->customer_id) {
            return Response::allow();
        }

        return Response::deny('Nie możesz zmienić statusu tego zgłoszenia.');
    }

    public function changePriority(User $user, TicketModel $ticket): Response
    {
        return $user->isStaff()
            ? Response::allow()
            : Response::deny('Tylko pracownik może zmienić priorytet.');
    }

    public function comment(User $user, TicketModel $ticket): Response
    {
        if (! ($user->isStaff() || $user->id === $ticket->customer_id)) {
            return Response::deny('Nie masz dostępu do tego zgłoszenia.');
        }

        if ($ticket->status === TicketStatus::Closed && ! $user->isStaff()) {
            return Response::deny('Zamknięte zgłoszenie nie przyjmuje komentarzy klienta.');
        }

        return Response::allow();
    }

    public function attach(User $user, TicketModel $ticket): Response
    {
        return $this->comment($user, $ticket);
    }
}
