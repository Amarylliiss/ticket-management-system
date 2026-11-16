<?php

use App\Domain\Ticket\Entities\Ticket;
use App\Domain\Ticket\Enums\TicketPriority;
use App\Domain\Ticket\Enums\TicketStatus;
use App\Domain\Ticket\Events\FirstResponseRecorded;
use App\Domain\Ticket\Events\TicketSubmitted;
use App\Domain\Ticket\Exceptions\InvalidTicketTransition;
use App\Domain\Ticket\ValueObjects\TicketId;
use App\Domain\Ticket\ValueObjects\TicketReference;

function sampleTicket(): Ticket
{
    return Ticket::submit(
        TicketId::generate(),
        TicketReference::fromSequence(1),
        'Drukarka nie drukuje',
        'Od rana stoi w błędzie papieru.',
        1,
        TicketPriority::High,
        5,
        new DateTimeImmutable('2026-11-16 08:00:00'),
    );
}

it('opens a ticket and records that it was submitted', function () {
    $ticket = sampleTicket();

    expect($ticket->status())->toBe(TicketStatus::Open)
        ->and($ticket->reference()->value())->toBe('TCK-000001')
        ->and($ticket->releaseEvents()[0])->toBeInstanceOf(TicketSubmitted::class);
});

it('rejects a status that skips the workflow', function () {
    $ticket = sampleTicket();
    $ticket->releaseEvents();

    expect(fn () => $ticket->changeStatus(TicketStatus::Resolved, 9))
        ->toThrow(InvalidTicketTransition::class);
});

it('moves an open ticket to in progress when it is assigned', function () {
    $ticket = sampleTicket();
    $ticket->releaseEvents();

    $ticket->assign(12, 9);

    expect($ticket->status())->toBe(TicketStatus::InProgress)
        ->and($ticket->assigneeId())->toBe(12);
});

it('records the first response only once', function () {
    $ticket = sampleTicket();
    $ticket->releaseEvents();

    $ticket->markFirstResponse(new DateTimeImmutable('2026-11-16 08:20:00'), 9);
    $ticket->markFirstResponse(new DateTimeImmutable('2026-11-16 09:00:00'), 9);

    $events = $ticket->releaseEvents();

    expect($events)->toHaveCount(1)
        ->and($events[0])->toBeInstanceOf(FirstResponseRecorded::class)
        ->and($ticket->firstResponseAt()?->format('H:i'))->toBe('08:20');
});

it('clears the resolution time when a ticket is reopened', function () {
    $ticket = sampleTicket();
    $ticket->assign(12, 9);
    $ticket->changeStatus(TicketStatus::Resolved, 9);
    $ticket->changeStatus(TicketStatus::InProgress, 9);

    expect($ticket->status())->toBe(TicketStatus::InProgress)
        ->and($ticket->resolvedAt())->toBeNull();
});

it('refuses a malformed reference', function () {
    expect(fn () => TicketReference::fromString('HD-1'))
        ->toThrow(InvalidArgumentException::class);
});
