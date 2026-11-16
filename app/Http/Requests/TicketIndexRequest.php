<?php

namespace App\Http\Requests;

use App\Domain\Ticket\Enums\TicketPriority;
use App\Domain\Ticket\Enums\TicketStatus;
use App\Infrastructure\Persistence\Eloquent\Models\TicketModel;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TicketIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('viewAny', TicketModel::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'status' => ['nullable', Rule::enum(TicketStatus::class)],
            'priority' => ['nullable', Rule::enum(TicketPriority::class)],
            'category_id' => ['nullable', 'integer'],
            'assignee_id' => ['nullable', 'integer'],
            'unassigned' => ['nullable', 'boolean'],
            'q' => ['nullable', 'string', 'max:160'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }
}
