<?php

namespace App\Http\Controllers\Api;

use App\Application\Ticket\Commands\StoreAttachmentCommand;
use App\Application\Ticket\Handlers\StoreAttachmentHandler;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAttachmentRequest;
use App\Http\Resources\AttachmentResource;
use App\Infrastructure\Persistence\Eloquent\Models\AttachmentModel;
use App\Infrastructure\Persistence\Eloquent\Models\TicketModel;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AttachmentController extends Controller
{
    public function store(StoreAttachmentRequest $request, TicketModel $ticket, StoreAttachmentHandler $handler): JsonResponse
    {
        $attachmentId = $handler->handle(new StoreAttachmentCommand(
            ticketId: $ticket->id,
            actorId: $request->user()->id,
            file: $request->file('file'),
        ));

        $attachment = AttachmentModel::query()->with('uploader')->findOrFail($attachmentId);

        return (new AttachmentResource($attachment))->response()->setStatusCode(201);
    }

    public function download(AttachmentModel $attachment): StreamedResponse
    {
        $this->authorize('download', $attachment);

        return Storage::disk('attachments')->download($attachment->path, $attachment->original_name);
    }
}
