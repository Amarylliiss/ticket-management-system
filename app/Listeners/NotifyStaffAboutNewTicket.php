<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Domain\Identity\Enums\UserRole;
use App\Domain\Ticket\Events\TicketSubmitted;
use App\Models\User;
use App\Notifications\TicketSubmittedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Notification;

class NotifyStaffAboutNewTicket implements ShouldQueue
{
    public bool $afterCommit = true;

    public function handle(TicketSubmitted $event): void
    {
        $staff = User::query()
            ->whereIn('role', [UserRole::Agent->value, UserRole::Admin->value])
            ->get();

        Notification::send($staff, new TicketSubmittedNotification(
            $event->ticketId,
            $event->reference,
            $event->title,
            $event->priority,
        ));
    }
}
