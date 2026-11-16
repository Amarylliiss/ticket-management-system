<?php

declare(strict_types=1);

namespace App\Domain\Ticket\Enums;

enum TicketPriority: string
{
    case Low = 'low';
    case Medium = 'medium';
    case High = 'high';
    case Urgent = 'urgent';

    public function label(): string
    {
        return match ($this) {
            self::Low => 'Niski',
            self::Medium => 'Średni',
            self::High => 'Wysoki',
            self::Urgent => 'Pilny',
        };
    }

    public function firstResponseTargetMinutes(): int
    {
        return match ($this) {
            self::Low => 480,
            self::Medium => 240,
            self::High => 60,
            self::Urgent => 15,
        };
    }
}
