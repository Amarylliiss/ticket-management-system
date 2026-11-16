<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Domain\Ticket\Enums\CommentVisibility;
use App\Domain\Ticket\Events\CommentAdded;
use App\Models\User;
use App\Notifications\CommentPostedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;

class NotifyAboutComment implements ShouldQueue
{
    public bool $afterCommit = true;

    public function handle(CommentAdded $event): void
    {
        if ($event->visibility === CommentVisibility::Internal->value) {
            return;
        }

        $recipientId = $event->authorIsStaff ? $event->customerId : $event->assigneeId;

        if ($recipientId === null || $recipientId === $event->authorId) {
            return;
        }

        $recipient = User::query()->find($recipientId);

        $message = $event->authorIsStaff
            ? "Pracownik dodał komentarz do zgłoszenia {$event->reference}."
            : "Klient dodał komentarz do zgłoszenia {$event->reference}.";

        $recipient?->notify(new CommentPostedNotification(
            $event->ticketId,
            $event->reference,
            $message,
        ));
    }
}
