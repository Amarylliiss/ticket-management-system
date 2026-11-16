<?php

namespace App\Http\Resources;

use App\Infrastructure\Persistence\Eloquent\Models\CommentModel;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin CommentModel */
class CommentResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'body' => $this->body,
            'visibility' => [
                'value' => $this->visibility->value,
                'label' => $this->visibility->label(),
            ],
            'author' => [
                'id' => $this->author?->id,
                'name' => $this->author?->name,
                'role' => $this->author?->role->value,
            ],
            'created_at' => $this->created_at,
        ];
    }
}
