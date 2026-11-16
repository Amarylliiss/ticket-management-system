<?php

declare(strict_types=1);

namespace App\Domain\Ticket\Events;

final class CommentAdded
{
    public function __construct(
        public string $ticketId,
        public string $reference,
        public string $commentId,
        public int $authorId,
        public bool $authorIsStaff,
        public string $visibility,
        public int $customerId,
        public ?int $assigneeId,
    ) {}
}
