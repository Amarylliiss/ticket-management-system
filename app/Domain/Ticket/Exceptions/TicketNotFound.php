<?php

declare(strict_types=1);

namespace App\Domain\Ticket\Exceptions;

use App\Domain\Ticket\ValueObjects\TicketId;
use RuntimeException;

final class TicketNotFound extends RuntimeException
{
    public static function withId(TicketId $id): self
    {
        return new self(sprintf('Nie znaleziono zgłoszenia %s.', $id->value()));
    }
}
