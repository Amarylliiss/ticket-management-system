<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Mappers;

use App\Domain\Ticket\Entities\Ticket;
use App\Domain\Ticket\ValueObjects\TicketId;
use App\Domain\Ticket\ValueObjects\TicketReference;
use App\Infrastructure\Persistence\Eloquent\Models\TicketModel;
use DateTimeImmutable;
use DateTimeInterface;

final class TicketMapper
{
    public static function toDomain(TicketModel $model): Ticket
    {
        return Ticket::reconstitute(
            id: TicketId::fromString($model->id),
            reference: TicketReference::fromString($model->reference),
            title: $model->title,
            description: $model->description,
            categoryId: $model->category_id,
            priority: $model->priority,
            status: $model->status,
            customerId: $model->customer_id,
            assigneeId: $model->assignee_id,
            firstResponseAt: self::instant($model->first_response_at),
            resolvedAt: self::instant($model->resolved_at),
            closedAt: self::instant($model->closed_at),
            createdAt: self::instant($model->created_at) ?? new DateTimeImmutable,
        );
    }

    public static function fill(Ticket $ticket, TicketModel $model): void
    {
        $model->id = $ticket->id()->value();
        $model->reference = $ticket->reference()->value();
        $model->title = $ticket->title();
        $model->description = $ticket->description();
        $model->category_id = $ticket->categoryId();
        $model->priority = $ticket->priority();
        $model->status = $ticket->status();
        $model->customer_id = $ticket->customerId();
        $model->assignee_id = $ticket->assigneeId();
        $model->first_response_at = $ticket->firstResponseAt();
        $model->resolved_at = $ticket->resolvedAt();
        $model->closed_at = $ticket->closedAt();

        if (! $model->exists) {
            $model->created_at = $ticket->createdAt();
        }
    }

    private static function instant(mixed $value): ?DateTimeImmutable
    {
        if (! $value instanceof DateTimeInterface) {
            return null;
        }

        return DateTimeImmutable::createFromInterface($value);
    }
}
