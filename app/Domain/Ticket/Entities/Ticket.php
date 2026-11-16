<?php

declare(strict_types=1);

namespace App\Domain\Ticket\Entities;

use App\Domain\Ticket\Enums\TicketPriority;
use App\Domain\Ticket\Enums\TicketStatus;
use App\Domain\Ticket\Events\FirstResponseRecorded;
use App\Domain\Ticket\Events\TicketAssigned;
use App\Domain\Ticket\Events\TicketPriorityChanged;
use App\Domain\Ticket\Events\TicketStatusChanged;
use App\Domain\Ticket\Events\TicketSubmitted;
use App\Domain\Ticket\Exceptions\InvalidTicketTransition;
use App\Domain\Ticket\ValueObjects\TicketId;
use App\Domain\Ticket\ValueObjects\TicketReference;
use DateTimeImmutable;

final class Ticket
{
    /** @var list<object> */
    private array $events = [];

    private function __construct(
        private TicketId $id,
        private TicketReference $reference,
        private string $title,
        private string $description,
        private int $categoryId,
        private TicketPriority $priority,
        private TicketStatus $status,
        private int $customerId,
        private ?int $assigneeId,
        private ?DateTimeImmutable $firstResponseAt,
        private ?DateTimeImmutable $resolvedAt,
        private ?DateTimeImmutable $closedAt,
        private DateTimeImmutable $createdAt,
    ) {}

    public static function submit(
        TicketId $id,
        TicketReference $reference,
        string $title,
        string $description,
        int $categoryId,
        TicketPriority $priority,
        int $customerId,
        DateTimeImmutable $createdAt,
    ): self {
        $ticket = new self(
            id: $id,
            reference: $reference,
            title: self::requiredText($title, 'Tytuł zgłoszenia musi mieć od 1 do 160 znaków.', 160),
            description: self::requiredText($description, 'Opis zgłoszenia jest wymagany.', 10000),
            categoryId: self::positive($categoryId, 'Kategoria zgłoszenia jest wymagana.'),
            priority: $priority,
            status: TicketStatus::Open,
            customerId: self::positive($customerId, 'Zgłoszenie musi mieć autora.'),
            assigneeId: null,
            firstResponseAt: null,
            resolvedAt: null,
            closedAt: null,
            createdAt: $createdAt,
        );

        $ticket->record(new TicketSubmitted(
            ticketId: $id->value(),
            reference: $reference->value(),
            title: $ticket->title,
            customerId: $customerId,
            categoryId: $categoryId,
            priority: $priority->value,
        ));

        return $ticket;
    }

    public static function reconstitute(
        TicketId $id,
        TicketReference $reference,
        string $title,
        string $description,
        int $categoryId,
        TicketPriority $priority,
        TicketStatus $status,
        int $customerId,
        ?int $assigneeId,
        ?DateTimeImmutable $firstResponseAt,
        ?DateTimeImmutable $resolvedAt,
        ?DateTimeImmutable $closedAt,
        DateTimeImmutable $createdAt,
    ): self {
        return new self(
            $id,
            $reference,
            $title,
            $description,
            $categoryId,
            $priority,
            $status,
            $customerId,
            $assigneeId,
            $firstResponseAt,
            $resolvedAt,
            $closedAt,
            $createdAt,
        );
    }

    public function assign(int $assigneeId, int $actorId): void
    {
        if ($this->status === TicketStatus::Closed) {
            throw new InvalidTicketTransition('Zamkniętego zgłoszenia nie można przypisać. Najpierw otwórz je ponownie.');
        }

        $assigneeId = self::positive($assigneeId, 'Wskaż pracownika, któremu przypisujesz zgłoszenie.');

        if ($this->assigneeId === $assigneeId && $this->status !== TicketStatus::Open) {
            return;
        }

        if ($this->status === TicketStatus::Open) {
            $this->moveTo(TicketStatus::InProgress, $actorId);
        }

        $this->assigneeId = $assigneeId;
        $this->record(new TicketAssigned(
            ticketId: $this->id->value(),
            reference: $this->reference->value(),
            title: $this->title,
            assigneeId: $assigneeId,
            actorId: $actorId,
            customerId: $this->customerId,
        ));
    }

    public function changeStatus(TicketStatus $next, int $actorId): void
    {
        if ($this->status === $next) {
            return;
        }

        $this->moveTo($next, $actorId);
    }

    public function changePriority(TicketPriority $priority, int $actorId): void
    {
        if ($this->priority === $priority) {
            return;
        }

        $from = $this->priority;
        $this->priority = $priority;
        $this->record(new TicketPriorityChanged(
            ticketId: $this->id->value(),
            reference: $this->reference->value(),
            from: $from->value,
            to: $priority->value,
            actorId: $actorId,
        ));
    }

    public function markFirstResponse(DateTimeImmutable $at, int $actorId): void
    {
        if ($this->firstResponseAt !== null) {
            return;
        }

        $this->firstResponseAt = $at;
        $this->record(new FirstResponseRecorded(
            ticketId: $this->id->value(),
            actorId: $actorId,
            respondedAt: $at->format(DATE_ATOM),
        ));
    }

    /**
     * @return list<object>
     */
    public function releaseEvents(): array
    {
        $events = $this->events;
        $this->events = [];

        return $events;
    }

    public function id(): TicketId
    {
        return $this->id;
    }

    public function reference(): TicketReference
    {
        return $this->reference;
    }

    public function title(): string
    {
        return $this->title;
    }

    public function description(): string
    {
        return $this->description;
    }

    public function categoryId(): int
    {
        return $this->categoryId;
    }

    public function priority(): TicketPriority
    {
        return $this->priority;
    }

    public function status(): TicketStatus
    {
        return $this->status;
    }

    public function customerId(): int
    {
        return $this->customerId;
    }

    public function assigneeId(): ?int
    {
        return $this->assigneeId;
    }

    public function firstResponseAt(): ?DateTimeImmutable
    {
        return $this->firstResponseAt;
    }

    public function resolvedAt(): ?DateTimeImmutable
    {
        return $this->resolvedAt;
    }

    public function closedAt(): ?DateTimeImmutable
    {
        return $this->closedAt;
    }

    public function createdAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    private function moveTo(TicketStatus $next, int $actorId): void
    {
        if (! $this->status->canTransitionTo($next)) {
            throw new InvalidTicketTransition(sprintf(
                'Nie można zmienić statusu z „%s” na „%s”.',
                $this->status->label(),
                $next->label(),
            ));
        }

        $from = $this->status;
        $this->status = $next;

        if ($next === TicketStatus::Resolved) {
            $this->resolvedAt = new DateTimeImmutable;
            $this->closedAt = null;
        }

        if ($next === TicketStatus::Closed) {
            $this->closedAt = new DateTimeImmutable;
        }

        if ($next === TicketStatus::InProgress && in_array($from, [TicketStatus::Resolved, TicketStatus::Closed], true)) {
            $this->resolvedAt = null;
            $this->closedAt = null;
        }

        $this->record(new TicketStatusChanged(
            ticketId: $this->id->value(),
            reference: $this->reference->value(),
            title: $this->title,
            from: $from->value,
            to: $next->value,
            actorId: $actorId,
            customerId: $this->customerId,
        ));
    }

    private function record(object $event): void
    {
        $this->events[] = $event;
    }

    private static function requiredText(string $value, string $message, int $max): string
    {
        $value = trim($value);

        if ($value === '' || mb_strlen($value) > $max) {
            throw new \InvalidArgumentException($message);
        }

        return $value;
    }

    private static function positive(int $value, string $message): int
    {
        if ($value < 1) {
            throw new \InvalidArgumentException($message);
        }

        return $value;
    }
}
