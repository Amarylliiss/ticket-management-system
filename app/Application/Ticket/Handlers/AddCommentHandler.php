<?php

declare(strict_types=1);

namespace App\Application\Ticket\Handlers;

use App\Application\Ticket\Commands\AddCommentCommand;
use App\Application\Ticket\Handlers\Concerns\SavesTickets;
use App\Domain\Ticket\Enums\CommentVisibility;
use App\Domain\Ticket\Events\CommentAdded;
use App\Domain\Ticket\Repositories\TicketRepository;
use App\Infrastructure\Persistence\Eloquent\Models\CommentModel;
use App\Models\User;
use DateTimeImmutable;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;

final class AddCommentHandler
{
    use SavesTickets;

    public function __construct(private TicketRepository $tickets) {}

    public function handle(AddCommentCommand $command): string
    {
        $author = User::query()->findOrFail($command->authorId);
        $visibility = CommentVisibility::from($command->visibility);

        if ($visibility === CommentVisibility::Internal && ! $author->isStaff()) {
            throw new AuthorizationException('Komentarze wewnętrzne są dostępne tylko dla pracowników.');
        }

        return DB::transaction(function () use ($command, $author, $visibility): string {
            $ticket = $this->findTicket($command->ticketId);

            if ($visibility === CommentVisibility::Public && $author->isStaff()) {
                $ticket->markFirstResponse(new DateTimeImmutable, $author->id);
            }

            $commentId = Uuid::uuid4()->toString();

            CommentModel::query()->create([
                'id' => $commentId,
                'ticket_id' => $ticket->id()->value(),
                'author_id' => $author->id,
                'body' => trim($command->body),
                'visibility' => $visibility,
            ]);

            $this->saveAndDispatch($ticket);

            event(new CommentAdded(
                ticketId: $ticket->id()->value(),
                reference: $ticket->reference()->value(),
                commentId: $commentId,
                authorId: $author->id,
                authorIsStaff: $author->isStaff(),
                visibility: $visibility->value,
                customerId: $ticket->customerId(),
                assigneeId: $ticket->assigneeId(),
            ));

            return $commentId;
        });
    }

    protected function tickets(): TicketRepository
    {
        return $this->tickets;
    }
}
