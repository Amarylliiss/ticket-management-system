<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Models;

use App\Domain\Ticket\Enums\ActivityType;
use App\Models\User;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['ticket_id', 'actor_id', 'type', 'properties'])]
class ActivityModel extends Model
{
    protected $table = 'ticket_activities';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => ActivityType::class,
            'properties' => 'array',
        ];
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(TicketModel::class, 'ticket_id');
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}
