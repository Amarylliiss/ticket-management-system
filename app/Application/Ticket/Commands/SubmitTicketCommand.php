<?php

declare(strict_types=1);

namespace App\Application\Ticket\Commands;

final readonly class SubmitTicketCommand
{
    public function __construct(
        public int $customerId,
        public string $title,
        public string $description,
        public int $categoryId,
        public string $priority,
    ) {}
}
