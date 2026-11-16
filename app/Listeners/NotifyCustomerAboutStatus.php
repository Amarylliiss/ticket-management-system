<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Domain\Ticket\Events\TicketStatusChanged;
use App\Models\User;
use App\Notifications\TicketStatusChangedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;

class NotifyCustomerAboutStatus implements ShouldQueue
{
    public bool $afterCommit = true;

    public function handle(TicketStatusChanged $event): void
    {
        if ($event->actorId === $event->customerId) {
            return;
        }

        $customer = User::query()->find($event->customerId);

        $customer?->notify(new TicketStatusChangedNotification(
            $event->ticketId,
            $event->reference,
            $event->title,
            $event->to,
        ));
    }
}
