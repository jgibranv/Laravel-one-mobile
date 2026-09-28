<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTicketEstatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'estatus' => ['required', Rule::in(['abierto', 'en_proceso', 'resuelto'])],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'estatus.required' => 'Indica el nuevo estatus del ticket.',
            'estatus.in' => 'El estatus debe ser abierto, en_proceso o resuelto.',
        ];
    }
}
