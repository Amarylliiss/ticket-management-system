<?php

use App\Domain\Ticket\Enums\TicketPriority;
use App\Domain\Ticket\Services\ResponseTimePolicy;

it('treats an urgent ticket without a reply as late after fifteen minutes', function () {
    $policy = new ResponseTimePolicy;
    $created = new DateTimeImmutable('2026-11-16 10:00:00');

    expect($policy->isFirstResponseLate(
        TicketPriority::Urgent,
        $created,
        null,
        null,
        $created->modify('+16 minutes'),
    ))->toBeTrue();
});

it('keeps a fast reply inside the target', function () {
    $policy = new ResponseTimePolicy;
    $created = new DateTimeImmutable('2026-11-16 10:00:00');

    expect($policy->isFirstResponseLate(
        TicketPriority::High,
        $created,
        $created->modify('+20 minutes'),
        null,
        $created->modify('+3 hours'),
    ))->toBeFalse();
});

it('does not flag a ticket the customer closed before the deadline', function () {
    $policy = new ResponseTimePolicy;
    $created = new DateTimeImmutable('2026-11-16 10:00:00');

    expect($policy->isFirstResponseLate(
        TicketPriority::High,
        $created,
        null,
        $created->modify('+10 minutes'),
        $created->modify('+5 hours'),
    ))->toBeFalse();
});
