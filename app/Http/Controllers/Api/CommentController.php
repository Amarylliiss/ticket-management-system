<?php

namespace App\Http\Controllers\Api;

use App\Application\Ticket\Commands\AddCommentCommand;
use App\Application\Ticket\Handlers\AddCommentHandler;
use App\Domain\Ticket\Enums\CommentVisibility;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCommentRequest;
use App\Http\Resources\CommentResource;
use App\Infrastructure\Persistence\Eloquent\Models\CommentModel;
use App\Infrastructure\Persistence\Eloquent\Models\TicketModel;
use Illuminate\Http\JsonResponse;

class CommentController extends Controller
{
    public function store(StoreCommentRequest $request, TicketModel $ticket, AddCommentHandler $handler): JsonResponse
    {
        $visibility = $request->input('visibility', CommentVisibility::Public->value);

        $commentId = $handler->handle(new AddCommentCommand(
            ticketId: $ticket->id,
            authorId: $request->user()->id,
            body: $request->string('body')->toString(),
            visibility: $visibility,
        ));

        $comment = CommentModel::query()->with('author')->findOrFail($commentId);

        return (new CommentResource($comment))->response()->setStatusCode(201);
    }
}
