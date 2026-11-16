<?php

declare(strict_types=1);

namespace App\Domain\Identity\Enums;

enum UserRole: string
{
    case Customer = 'customer';
    case Agent = 'agent';
    case Admin = 'admin';

    public function isStaff(): bool
    {
        return $this === self::Agent || $this === self::Admin;
    }

    public function label(): string
    {
        return match ($this) {
            self::Customer => 'Klient',
            self::Agent => 'Pracownik',
            self::Admin => 'Administrator',
        };
    }
}
