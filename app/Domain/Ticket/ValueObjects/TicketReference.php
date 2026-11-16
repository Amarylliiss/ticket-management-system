<?php

declare(strict_types=1);

namespace App\Domain\Ticket\ValueObjects;

final readonly class TicketReference
{
    private function __construct(private string $value) {}

    public static function fromSequence(int $sequence): self
    {
        if ($sequence < 1) {
            throw new \InvalidArgumentException('Numer sekwencji zgłoszenia musi być dodatni.');
        }

        return new self(sprintf('TCK-%06d', $sequence));
    }

    public static function fromString(string $value): self
    {
        if (preg_match('/^TCK-\d{6}$/', $value) !== 1) {
            throw new \InvalidArgumentException('Niepoprawny numer zgłoszenia.');
        }

        return new self($value);
    }

    public function value(): string
    {
        return $this->value;
    }
}
