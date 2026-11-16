<?php

declare(strict_types=1);

namespace App\Application\Ticket\Commands;

final readonly class AddCommentCommand
{
    public function __construct(
        public string $ticketId,
        public int $authorId,
        public string $body,
        public string $visibility,
    ) {}
}
