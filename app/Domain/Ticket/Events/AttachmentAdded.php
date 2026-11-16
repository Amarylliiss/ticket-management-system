<?php

declare(strict_types=1);

namespace App\Domain\Ticket\Events;

final class AttachmentAdded
{
    public function __construct(
        public string $ticketId,
        public string $attachmentId,
        public int $actorId,
        public string $originalName,
    ) {}
}
