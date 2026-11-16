<?php

declare(strict_types=1);

namespace App\Domain\Ticket\Enums;

enum CommentVisibility: string
{
    case Public = 'public';
    case Internal = 'internal';

    public function label(): string
    {
        return match ($this) {
            self::Public => 'Publiczny',
            self::Internal => 'Wewnętrzny',
        };
    }
}
