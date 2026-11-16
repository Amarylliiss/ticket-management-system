<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Models;

use App\Domain\Ticket\Enums\CommentVisibility;
use App\Models\User;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['id', 'ticket_id', 'author_id', 'body', 'visibility'])]
class CommentModel extends Model
{
    public $incrementing = false;

    protected $keyType = 'string';

    protected $table = 'comments';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'visibility' => CommentVisibility::class,
        ];
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(TicketModel::class, 'ticket_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }
}
