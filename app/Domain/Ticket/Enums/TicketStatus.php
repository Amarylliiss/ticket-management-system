<?php

declare(strict_types=1);

namespace App\Domain\Ticket\Enums;

enum TicketStatus: string
{
    case Open = 'open';
    case InProgress = 'in_progress';
    case WaitingForCustomer = 'waiting_for_customer';
    case Resolved = 'resolved';
    case Closed = 'closed';

    public function label(): string
    {
        return match ($this) {
            self::Open => 'Otwarte',
            self::InProgress => 'W trakcie',
            self::WaitingForCustomer => 'Oczekuje na klienta',
            self::Resolved => 'Rozwiązane',
            self::Closed => 'Zamknięte',
        };
    }

    /**
     * @return list<self>
     */
    public function allowedTransitions(): array
    {
        return match ($this) {
            self::Open => [self::InProgress, self::Closed],
            self::InProgress => [self::WaitingForCustomer, self::Resolved, self::Closed],
            self::WaitingForCustomer => [self::InProgress, self::Resolved, self::Closed],
            self::Resolved => [self::InProgress, self::Closed],
            self::Closed => [self::InProgress],
        };
    }

    public function canTransitionTo(self $next): bool
    {
        return in_array($next, $this->allowedTransitions(), true);
    }
}
