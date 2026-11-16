<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Models;

use App\Domain\Ticket\Enums\TicketPriority;
use App\Domain\Ticket\Enums\TicketStatus;
use App\Models\User;
use Database\Factories\TicketModelFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'id',
    'reference',
    'title',
    'description',
    'category_id',
    'priority',
    'status',
    'customer_id',
    'assignee_id',
    'first_response_at',
    'resolved_at',
    'closed_at',
])]
class TicketModel extends Model
{
    /** @use HasFactory<TicketModelFactory> */
    use HasFactory;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $table = 'tickets';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'priority' => TicketPriority::class,
            'status' => TicketStatus::class,
            'first_response_at' => 'datetime',
            'resolved_at' => 'datetime',
            'closed_at' => 'datetime',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(CategoryModel::class, 'category_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assignee_id');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(CommentModel::class, 'ticket_id');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(AttachmentModel::class, 'ticket_id');
    }

    public function activities(): HasMany
    {
        return $this->hasMany(ActivityModel::class, 'ticket_id');
    }

    protected static function newFactory(): TicketModelFactory
    {
        return TicketModelFactory::new();
    }
}
