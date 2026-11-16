<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAttachmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('attach', $this->route('ticket')) ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'file' => ['required', 'file', 'max:10240', 'mimes:pdf,png,jpg,jpeg,txt,zip,doc,docx,xlsx,csv'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'file.required' => 'Wybierz plik.',
            'file.max' => 'Plik może mieć najwyżej 10 MB.',
            'file.mimes' => 'Dozwolone formaty: pdf, obrazy, txt, zip, doc, xlsx i csv.',
        ];
    }
}
