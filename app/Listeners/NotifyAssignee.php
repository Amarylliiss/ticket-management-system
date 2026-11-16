<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Domain\Ticket\Events\TicketAssigned;
use App\Models\User;
use App\Notifications\TicketAssignedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;

class NotifyAssignee implements ShouldQueue
{
    public bool $afterCommit = true;

    public function handle(TicketAssigned $event): void
    {
        $assignee = User::query()->find($event->assigneeId);

        $assignee?->notify(new TicketAssignedNotification(
            $event->ticketId,
            $event->reference,
            $event->title,
        ));
    }
}
