<?php

namespace App\Http\Requests;

use App\Domain\Ticket\Enums\TicketPriority;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ChangePriorityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('changePriority', $this->route('ticket')) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'priority' => ['required', Rule::enum(TicketPriority::class)],
        ];
    }
}
