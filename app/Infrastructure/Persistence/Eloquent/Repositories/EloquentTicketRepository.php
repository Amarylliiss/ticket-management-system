<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Domain\Ticket\Entities\Ticket;
use App\Domain\Ticket\Exceptions\TicketNotFound;
use App\Domain\Ticket\Repositories\TicketRepository;
use App\Domain\Ticket\ValueObjects\TicketId;
use App\Domain\Ticket\ValueObjects\TicketReference;
use App\Infrastructure\Persistence\Eloquent\Mappers\TicketMapper;
use App\Infrastructure\Persistence\Eloquent\Models\TicketModel;
use Illuminate\Support\Facades\DB;

final class EloquentTicketRepository implements TicketRepository
{
    public function nextReference(): TicketReference
    {
        $counter = DB::table('ticket_counters')->where('id', 1)->lockForUpdate()->first();

        if ($counter === null) {
            DB::table('ticket_counters')->insert(['id' => 1, 'current' => 0]);
            $counter = (object) ['current' => 0];
        }

        $next = ((int) $counter->current) + 1;

        DB::table('ticket_counters')->where('id', 1)->update(['current' => $next]);

        return TicketReference::fromSequence($next);
    }

    public function save(Ticket $ticket): void
    {
        $model = TicketModel::query()->find($ticket->id()->value()) ?? new TicketModel;
        TicketMapper::fill($ticket, $model);
        $model->save();
    }

    public function find(TicketId $id): Ticket
    {
        $model = TicketModel::query()->find($id->value());

        if ($model === null) {
            throw TicketNotFound::withId($id);
        }

        return TicketMapper::toDomain($model);
    }
}
