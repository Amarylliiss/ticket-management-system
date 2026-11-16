<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Domain\Ticket\Enums\ActivityType;
use App\Domain\Ticket\Enums\CommentVisibility;
use App\Domain\Ticket\Enums\TicketPriority;
use App\Domain\Ticket\Enums\TicketStatus;
use App\Domain\Ticket\Events\AttachmentAdded;
use App\Domain\Ticket\Events\CommentAdded;
use App\Domain\Ticket\Events\FirstResponseRecorded;
use App\Domain\Ticket\Events\TicketAssigned;
use App\Domain\Ticket\Events\TicketPriorityChanged;
use App\Domain\Ticket\Events\TicketStatusChanged;
use App\Domain\Ticket\Events\TicketSubmitted;
use App\Infrastructure\Persistence\Eloquent\Models\ActivityModel;
use App\Models\User;

class RecordTicketHistory
{
    public function handle(object $event): void
    {
        $entry = match ($event::class) {
            TicketSubmitted::class => $this->submitted($event),
            TicketAssigned::class => $this->assigned($event),
            TicketStatusChanged::class => $this->statusChanged($event),
            TicketPriorityChanged::class => $this->priorityChanged($event),
            CommentAdded::class => $this->commentAdded($event),
            AttachmentAdded::class => $this->attachmentAdded($event),
            FirstResponseRecorded::class => $this->firstResponse($event),
            default => null,
        };

        if ($entry === null) {
            return;
        }

        ActivityModel::query()->create($entry);
    }

    /**
     * @return array<string, mixed>
     */
    private function submitted(TicketSubmitted $event): array
    {
        return [
            'ticket_id' => $event->ticketId,
            'actor_id' => $event->customerId,
            'type' => ActivityType::Submitted,
            'properties' => [
                'summary' => 'Zarejestrowano zgłoszenie.',
                'priority' => $event->priority,
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function assigned(TicketAssigned $event): array
    {
        $name = $this->name($event->assigneeId);

        return [
            'ticket_id' => $event->ticketId,
            'actor_id' => $event->actorId,
            'type' => ActivityType::Assigned,
            'properties' => [
                'summary' => "Przypisano zgłoszenie do {$name}.",
                'assignee_id' => $event->assigneeId,
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function statusChanged(TicketStatusChanged $event): array
    {
        $from = TicketStatus::from($event->from)->label();
        $to = TicketStatus::from($event->to)->label();

        return [
            'ticket_id' => $event->ticketId,
            'actor_id' => $event->actorId,
            'type' => ActivityType::StatusChanged,
            'properties' => [
                'summary' => "Zmieniono status z „{$from}” na „{$to}”.",
                'from' => $event->from,
                'to' => $event->to,
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function priorityChanged(TicketPriorityChanged $event): array
    {
        $from = TicketPriority::from($event->from)->label();
        $to = TicketPriority::from($event->to)->label();

        return [
            'ticket_id' => $event->ticketId,
            'actor_id' => $event->actorId,
            'type' => ActivityType::PriorityChanged,
            'properties' => [
                'summary' => "Zmieniono priorytet z „{$from}” na „{$to}”.",
                'from' => $event->from,
                'to' => $event->to,
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function commentAdded(CommentAdded $event): array
    {
        $visibility = CommentVisibility::from($event->visibility);

        return [
            'ticket_id' => $event->ticketId,
            'actor_id' => $event->authorId,
            'type' => ActivityType::CommentAdded,
            'properties' => [
                'summary' => $visibility === CommentVisibility::Internal
                    ? 'Dodano komentarz wewnętrzny.'
                    : 'Dodano komentarz.',
                'comment_id' => $event->commentId,
                'visibility' => $event->visibility,
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function attachmentAdded(AttachmentAdded $event): array
    {
        return [
            'ticket_id' => $event->ticketId,
            'actor_id' => $event->actorId,
            'type' => ActivityType::AttachmentAdded,
            'properties' => [
                'summary' => "Dodano załącznik „{$event->originalName}”.",
                'attachment_id' => $event->attachmentId,
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function firstResponse(FirstResponseRecorded $event): array
    {
        return [
            'ticket_id' => $event->ticketId,
            'actor_id' => $event->actorId,
            'type' => ActivityType::FirstResponse,
            'properties' => [
                'summary' => 'Zapisano czas pierwszej odpowiedzi.',
                'responded_at' => $event->respondedAt,
            ],
        ];
    }

    private function name(int $userId): string
    {
        return User::query()->find($userId)?->name ?? 'pracownika';
    }
}
