<?php

namespace App\Http\Resources;

use App\Infrastructure\Persistence\Eloquent\Models\TicketModel;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin TicketModel */
class TicketResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'reference' => $this->reference,
            'title' => $this->title,
            'description' => $this->description,
            'status' => [
                'value' => $this->status->value,
                'label' => $this->status->label(),
            ],
            'priority' => [
                'value' => $this->priority->value,
                'label' => $this->priority->label(),
                'target_minutes' => $this->priority->firstResponseTargetMinutes(),
            ],
            'category' => $this->category ? [
                'id' => $this->category->id,
                'name' => $this->category->name,
            ] : null,
            'customer' => $this->customer ? [
                'id' => $this->customer->id,
                'name' => $this->customer->name,
                'email' => $this->customer->email,
            ] : null,
            'assignee' => $this->assignee ? [
                'id' => $this->assignee->id,
                'name' => $this->assignee->name,
            ] : null,
            'first_response_at' => $this->first_response_at,
            'resolved_at' => $this->resolved_at,
            'closed_at' => $this->closed_at,
            'first_response_seconds' => $this->first_response_at
                ? $this->first_response_at->getTimestamp() - $this->created_at->getTimestamp()
                : null,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'comments_count' => $this->whenCounted('comments'),
            'attachments_count' => $this->whenCounted('attachments'),
            'comments' => CommentResource::collection($this->whenLoaded('comments')),
            'attachments' => AttachmentResource::collection($this->whenLoaded('attachments')),
            'activities' => ActivityResource::collection($this->whenLoaded('activities')),
        ];
    }
}
