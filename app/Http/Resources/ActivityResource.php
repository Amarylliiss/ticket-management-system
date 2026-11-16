<?php

namespace App\Http\Resources;

use App\Infrastructure\Persistence\Eloquent\Models\ActivityModel;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin ActivityModel */
class ActivityResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => [
                'value' => $this->type->value,
                'label' => $this->type->label(),
            ],
            'summary' => $this->properties['summary'] ?? '',
            'actor' => $this->actor ? [
                'id' => $this->actor->id,
                'name' => $this->actor->name,
            ] : null,
            'created_at' => $this->created_at,
        ];
    }
}
