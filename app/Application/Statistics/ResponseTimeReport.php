<?php

declare(strict_types=1);

namespace App\Application\Statistics;

use App\Domain\Ticket\Enums\TicketPriority;
use App\Domain\Ticket\Enums\TicketStatus;
use App\Domain\Ticket\Services\ResponseTimePolicy;
use App\Infrastructure\Persistence\Eloquent\Models\CategoryModel;
use App\Infrastructure\Persistence\Eloquent\Models\TicketModel;
use App\Models\User;
use DateTimeImmutable;
use Illuminate\Support\Collection;

final class ResponseTimeReport
{
    public function __construct(private ResponseTimePolicy $policy) {}

    /**
     * @param  array{from?: string|null, to?: string|null, category_id?: int|null, priority?: string|null}  $filters
     * @return array<string, mixed>
     */
    public function handle(array $filters = []): array
    {
        $query = TicketModel::query()->with(['category:id,name', 'assignee:id,name']);

        if (! empty($filters['from'])) {
            $query->where('created_at', '>=', $filters['from'].' 00:00:00');
        }

        if (! empty($filters['to'])) {
            $query->where('created_at', '<=', $filters['to'].' 23:59:59');
        }

        if (! empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        if (! empty($filters['priority'])) {
            $query->where('priority', $filters['priority']);
        }

        /** @var Collection<int, TicketModel> $tickets */
        $tickets = $query->get();
        $now = new DateTimeImmutable;

        $responded = $tickets->filter(fn (TicketModel $ticket) => $ticket->first_response_at !== null);
        $resolved = $tickets->filter(fn (TicketModel $ticket) => $ticket->resolved_at !== null);
        $breaches = $tickets->filter(fn (TicketModel $ticket) => $this->isLate($ticket, $now));

        $byStatus = [];
        foreach (TicketStatus::cases() as $status) {
            $byStatus[] = [
                'status' => $status->value,
                'label' => $status->label(),
                'tickets' => $tickets->where('status', $status)->count(),
            ];
        }

        return [
            'totals' => [
                'tickets' => $tickets->count(),
                'awaiting_first_response' => $tickets->filter(
                    fn (TicketModel $ticket) => $ticket->first_response_at === null
                        && ! in_array($ticket->status, [TicketStatus::Resolved, TicketStatus::Closed], true)
                )->count(),
                'responded' => $responded->count(),
                'resolved' => $resolved->count(),
                'within_sla' => $responded->count() - $responded->filter(fn (TicketModel $ticket) => $this->isLate($ticket, $now))->count(),
                'breached_sla' => $breaches->count(),
            ],
            'average_first_response_seconds' => $this->average($responded, 'first_response_at'),
            'average_resolution_seconds' => $this->average($resolved, 'resolved_at'),
            'by_status' => $byStatus,
            'by_priority' => $this->byPriority($tickets, $now),
            'by_category' => $this->byCategory($tickets, $now),
            'by_agent' => $this->byAgent($tickets),
        ];
    }

    private function isLate(TicketModel $ticket, DateTimeImmutable $now): bool
    {
        return $this->policy->isFirstResponseLate(
            $ticket->priority,
            DateTimeImmutable::createFromInterface($ticket->created_at),
            $ticket->first_response_at ? DateTimeImmutable::createFromInterface($ticket->first_response_at) : null,
            $ticket->closed_at ? DateTimeImmutable::createFromInterface($ticket->closed_at) : null,
            $now,
        );
    }

    /**
     * @param  Collection<int, TicketModel>  $tickets
     */
    private function average(Collection $tickets, string $endColumn): ?int
    {
        if ($tickets->isEmpty()) {
            return null;
        }

        $total = $tickets->sum(function (TicketModel $ticket) use ($endColumn): int {
            return $ticket->{$endColumn}->getTimestamp() - $ticket->created_at->getTimestamp();
        });

        return (int) round($total / $tickets->count());
    }

    /**
     * @param  Collection<int, TicketModel>  $tickets
     * @return list<array<string, mixed>>
     */
    private function byPriority(Collection $tickets, DateTimeImmutable $now): array
    {
        $rows = [];

        foreach (TicketPriority::cases() as $priority) {
            $group = $tickets->where('priority', $priority);
            $responded = $group->filter(fn (TicketModel $ticket) => $ticket->first_response_at !== null);

            $rows[] = [
                'priority' => $priority->value,
                'label' => $priority->label(),
                'tickets' => $group->count(),
                'target_seconds' => $this->policy->targetSeconds($priority),
                'average_first_response_seconds' => $this->average($responded, 'first_response_at'),
                'breached_sla' => $group->filter(fn (TicketModel $ticket) => $this->isLate($ticket, $now))->count(),
            ];
        }

        return $rows;
    }

    /**
     * @param  Collection<int, TicketModel>  $tickets
     * @return list<array<string, mixed>>
     */
    private function byCategory(Collection $tickets, DateTimeImmutable $now): array
    {
        $names = CategoryModel::query()->pluck('name', 'id');

        return $tickets
            ->groupBy('category_id')
            ->map(function (Collection $group, int|string $categoryId) use ($names, $now): array {
                $responded = $group->filter(fn (TicketModel $ticket) => $ticket->first_response_at !== null);

                return [
                    'category_id' => (int) $categoryId,
                    'name' => $names[$categoryId] ?? 'Usunięta kategoria',
                    'tickets' => $group->count(),
                    'average_first_response_seconds' => $this->average($responded, 'first_response_at'),
                    'breached_sla' => $group->filter(fn (TicketModel $ticket) => $this->isLate($ticket, $now))->count(),
                ];
            })
            ->values()
            ->all();
    }

    /**
     * @param  Collection<int, TicketModel>  $tickets
     * @return list<array<string, mixed>>
     */
    private function byAgent(Collection $tickets): array
    {
        $assigned = $tickets->filter(fn (TicketModel $ticket) => $ticket->assignee_id !== null);
        $names = User::query()
            ->whereIn('id', $assigned->pluck('assignee_id')->unique()->all())
            ->pluck('name', 'id');

        return $assigned
            ->groupBy('assignee_id')
            ->map(function (Collection $group, int|string $agentId) use ($names): array {
                $responded = $group->filter(fn (TicketModel $ticket) => $ticket->first_response_at !== null);
                $resolved = $group->filter(fn (TicketModel $ticket) => $ticket->resolved_at !== null);

                return [
                    'id' => (int) $agentId,
                    'name' => $names[$agentId] ?? 'Pracownik',
                    'assigned' => $group->count(),
                    'resolved' => $resolved->count(),
                    'average_first_response_seconds' => $this->average($responded, 'first_response_at'),
                    'average_resolution_seconds' => $this->average($resolved, 'resolved_at'),
                ];
            })
            ->sortBy('name')
            ->values()
            ->all();
    }
}
