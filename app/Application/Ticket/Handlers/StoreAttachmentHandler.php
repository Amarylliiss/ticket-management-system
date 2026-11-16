<?php

declare(strict_types=1);

namespace App\Application\Ticket\Handlers;

use App\Application\Ticket\Commands\StoreAttachmentCommand;
use App\Application\Ticket\Handlers\Concerns\SavesTickets;
use App\Domain\Ticket\Events\AttachmentAdded;
use App\Domain\Ticket\Repositories\TicketRepository;
use App\Infrastructure\Persistence\Eloquent\Models\AttachmentModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Ramsey\Uuid\Uuid;
use Throwable;

final class StoreAttachmentHandler
{
    use SavesTickets;

    public function __construct(private TicketRepository $tickets) {}

    public function handle(StoreAttachmentCommand $command): string
    {
        $ticket = $this->findTicket($command->ticketId);
        $path = $command->file->store('tickets/'.$ticket->id()->value(), 'attachments');

        try {
            return DB::transaction(function () use ($command, $ticket, $path): string {
                $attachmentId = Uuid::uuid4()->toString();

                AttachmentModel::query()->create([
                    'id' => $attachmentId,
                    'ticket_id' => $ticket->id()->value(),
                    'uploaded_by' => $command->actorId,
                    'original_name' => $command->file->getClientOriginalName(),
                    'path' => $path,
                    'mime_type' => $command->file->getClientMimeType() ?: 'application/octet-stream',
                    'size' => $command->file->getSize(),
                ]);

                event(new AttachmentAdded(
                    ticketId: $ticket->id()->value(),
                    attachmentId: $attachmentId,
                    actorId: $command->actorId,
                    originalName: $command->file->getClientOriginalName(),
                ));

                return $attachmentId;
            });
        } catch (Throwable $exception) {
            Storage::disk('attachments')->delete($path);

            throw $exception;
        }
    }

    protected function tickets(): TicketRepository
    {
        return $this->tickets;
    }
}
