<?php

declare(strict_types=1);

namespace App\Application\Ticket\Handlers;

use App\Application\Ticket\Commands\SubmitTicketCommand;
use App\Application\Ticket\Handlers\Concerns\SavesTickets;
use App\Domain\Ticket\Entities\Ticket;
use App\Domain\Ticket\Enums\TicketPriority;
use App\Domain\Ticket\Repositories\TicketRepository;
use App\Domain\Ticket\ValueObjects\TicketId;
use App\Infrastructure\Persistence\Eloquent\Models\CategoryModel;
use DateTimeImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class SubmitTicketHandler
{
    use SavesTickets;

    public function __construct(private TicketRepository $tickets) {}

    public function handle(SubmitTicketCommand $command): Ticket
    {
        if (! CategoryModel::query()->whereKey($command->categoryId)->where('is_active', true)->exists()) {
            throw ValidationException::withMessages([
                'category_id' => 'Wybierz aktywną kategorię.',
            ]);
        }

        return DB::transaction(function () use ($command): Ticket {
            $ticket = Ticket::submit(
                id: TicketId::generate(),
                reference: $this->tickets->nextReference(),
                title: $command->title,
                description: $command->description,
                categoryId: $command->categoryId,
                priority: TicketPriority::from($command->priority),
                customerId: $command->customerId,
                createdAt: new DateTimeImmutable,
            );

            $this->saveAndDispatch($ticket);

            return $ticket;
        });
    }

    protected function tickets(): TicketRepository
    {
        return $this->tickets;
    }
}
