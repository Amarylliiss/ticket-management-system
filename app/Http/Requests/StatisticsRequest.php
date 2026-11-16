<?php

namespace App\Http\Requests;

use App\Domain\Ticket\Enums\TicketPriority;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StatisticsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('viewStatistics') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
            'category_id' => ['nullable', 'integer'],
            'priority' => ['nullable', Rule::enum(TicketPriority::class)],
        ];
    }
}
