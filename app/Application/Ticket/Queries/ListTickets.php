<?php

declare(strict_types=1);

namespace App\Application\Ticket\Queries;

use App\Domain\Ticket\Enums\CommentVisibility;
use App\Infrastructure\Persistence\Eloquent\Models\TicketModel;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final class ListTickets
{
    /**
     * @param  array{status?: string|null, priority?: string|null, category_id?: int|null, assignee_id?: int|null, unassigned?: bool, q?: string|null, per_page?: int}  $filters
     */
    public function handle(User $actor, array $filters): LengthAwarePaginator
    {
        $query = TicketModel::query()
            ->with(['category:id,name,slug', 'customer:id,name,email', 'assignee:id,name,email'])
            ->withCount([
                'attachments',
                'comments' => function ($comments) use ($actor): void {
                    if (! $actor->isStaff()) {
                        $comments->where('visibility', CommentVisibility::Public->value);
                    }
                },
            ])
            ->latest();

        if (! $actor->isStaff()) {
            $query->where('customer_id', $actor->id);
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['priority'])) {
            $query->where('priority', $filters['priority']);
        }

        if (! empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        if (! empty($filters['assignee_id'])) {
            $query->where('assignee_id', $filters['assignee_id']);
        }

        if (! empty($filters['unassigned'])) {
            $query->whereNull('assignee_id');
        }

        if (! empty($filters['q'])) {
            $term = '%'.$filters['q'].'%';
            $query->where(function ($inner) use ($term): void {
                $inner->where('title', 'like', $term)
                    ->orWhere('reference', 'like', $term)
                    ->orWhere('description', 'like', $term);
            });
        }

        return $query->paginate($filters['per_page'] ?? 15);
    }
}
