<?php

namespace App\Http\Controllers\Api;

use App\Application\Ticket\Commands\AssignTicketCommand;
use App\Application\Ticket\Commands\ChangeTicketPriorityCommand;
use App\Application\Ticket\Commands\ChangeTicketStatusCommand;
use App\Application\Ticket\Commands\SubmitTicketCommand;
use App\Application\Ticket\Handlers\AssignTicketHandler;
use App\Application\Ticket\Handlers\ChangeTicketPriorityHandler;
use App\Application\Ticket\Handlers\ChangeTicketStatusHandler;
use App\Application\Ticket\Handlers\SubmitTicketHandler;
use App\Application\Ticket\Queries\ListTickets;
use App\Domain\Ticket\Enums\ActivityType;
use App\Domain\Ticket\Enums\CommentVisibility;
use App\Http\Controllers\Controller;
use App\Http\Requests\AssignTicketRequest;
use App\Http\Requests\ChangePriorityRequest;
use App\Http\Requests\ChangeStatusRequest;
use App\Http\Requests\StoreTicketRequest;
use App\Http\Requests\TicketIndexRequest;
use App\Http\Resources\TicketResource;
use App\Infrastructure\Persistence\Eloquent\Models\TicketModel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class TicketController extends Controller
{
    public function index(TicketIndexRequest $request, ListTickets $query): AnonymousResourceCollection
    {
        $tickets = $query->handle($request->user(), [
            'status' => $request->input('status'),
            'priority' => $request->input('priority'),
            'category_id' => $request->integer('category_id') ?: null,
            'assignee_id' => $request->integer('assignee_id') ?: null,
            'unassigned' => $request->boolean('unassigned'),
            'q' => $request->input('q'),
            'per_page' => $request->integer('per_page') ?: 15,
        ]);

        return TicketResource::collection($tickets);
    }

    public function store(StoreTicketRequest $request, SubmitTicketHandler $handler): JsonResponse
    {
        $ticket = $handler->handle(new SubmitTicketCommand(
            customerId: $request->user()->id,
            title: $request->string('title')->toString(),
            description: $request->string('description')->toString(),
            categoryId: $request->integer('category_id'),
            priority: $request->string('priority')->toString(),
        ));

        return (new TicketResource($this->load($ticket->id()->value())))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Request $request, TicketModel $ticket): TicketResource
    {
        $this->authorize('view', $ticket);

        $ticket->load([
            'category',
            'customer',
            'assignee',
            'attachments.uploader',
            'comments' => function ($query) use ($request): void {
                $query->with('author')->oldest();

                if (! $request->user()->isStaff()) {
                    $query->where('visibility', CommentVisibility::Public->value);
                }
            },
            'activities' => function ($query) use ($request): void {
                $query->with('actor')->oldest();

                if (! $request->user()->isStaff()) {
                    $query->where(function ($inner): void {
                        $inner->where('type', '!=', ActivityType::CommentAdded->value)
                            ->orWhere('properties->visibility', CommentVisibility::Public->value);
                    });
                }
            },
        ]);

        return new TicketResource($ticket);
    }

    public function assign(AssignTicketRequest $request, TicketModel $ticket, AssignTicketHandler $handler): TicketResource
    {
        $handler->handle(new AssignTicketCommand(
            ticketId: $ticket->id,
            assigneeId: $request->integer('assignee_id'),
            actorId: $request->user()->id,
        ));

        return new TicketResource($this->load($ticket->id));
    }

    public function updateStatus(ChangeStatusRequest $request, TicketModel $ticket, ChangeTicketStatusHandler $handler): TicketResource
    {
        $handler->handle(new ChangeTicketStatusCommand(
            ticketId: $ticket->id,
            status: $request->string('status')->toString(),
            actorId: $request->user()->id,
        ));

        return new TicketResource($this->load($ticket->id));
    }

    public function updatePriority(ChangePriorityRequest $request, TicketModel $ticket, ChangeTicketPriorityHandler $handler): TicketResource
    {
        $handler->handle(new ChangeTicketPriorityCommand(
            ticketId: $ticket->id,
            priority: $request->string('priority')->toString(),
            actorId: $request->user()->id,
        ));

        return new TicketResource($this->load($ticket->id));
    }

    private function load(string $id): TicketModel
    {
        return TicketModel::query()
            ->with(['category', 'customer', 'assignee'])
            ->findOrFail($id);
    }
}
