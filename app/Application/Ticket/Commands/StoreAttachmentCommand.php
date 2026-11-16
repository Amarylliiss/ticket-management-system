<?php

declare(strict_types=1);

namespace App\Application\Ticket\Commands;

use Illuminate\Http\UploadedFile;

final readonly class StoreAttachmentCommand
{
    public function __construct(
        public string $ticketId,
        public int $actorId,
        public UploadedFile $file,
    ) {}
}
