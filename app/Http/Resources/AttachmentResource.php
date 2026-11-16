<?php

namespace App\Http\Resources;

use App\Infrastructure\Persistence\Eloquent\Models\AttachmentModel;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin AttachmentModel */
class AttachmentResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'original_name' => $this->original_name,
            'mime_type' => $this->mime_type,
            'size' => $this->size,
            'uploader' => [
                'id' => $this->uploader?->id,
                'name' => $this->uploader?->name,
            ],
            'created_at' => $this->created_at,
        ];
    }
}
