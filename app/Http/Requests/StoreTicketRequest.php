<?php

namespace App\Http\Requests;

use App\Domain\Ticket\Enums\TicketPriority;
use App\Infrastructure\Persistence\Eloquent\Models\TicketModel;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTicketRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('create', TicketModel::class) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:160'],
            'description' => ['required', 'string', 'max:10000'],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'priority' => ['required', Rule::enum(TicketPriority::class)],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'title.required' => 'Podaj tytuł zgłoszenia.',
            'description.required' => 'Opisz problem.',
            'category_id.required' => 'Wybierz kategorię.',
            'priority.required' => 'Wybierz priorytet.',
        ];
    }
}
