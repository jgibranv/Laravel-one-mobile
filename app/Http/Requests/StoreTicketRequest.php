<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTicketRequest extends FormRequest
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
            'titulo' => ['required', 'string', 'min:5', 'max:100'],
            'descripcion' => ['required', 'string', 'min:10', 'max:500'],
            'sucursal_id' => ['required', 'integer', 'min:1', 'max:9999'],
            'solicitante' => ['required', 'string', 'min:3', 'max:80'],
            'severidad' => ['required', Rule::in(['baja', 'media', 'alta'])],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'titulo.required' => 'El titulo del ticket es obligatorio.',
            'titulo.min' => 'El titulo debe tener al menos 5 caracteres.',
            'titulo.max' => 'El titulo no debe exceder 100 caracteres.',
            'descripcion.required' => 'La descripcion es obligatoria.',
            'descripcion.min' => 'Describe el problema con al menos 10 caracteres.',
            'descripcion.max' => 'La descripcion no debe exceder 500 caracteres.',
            'sucursal_id.required' => 'Indica la sucursal donde ocurre el problema.',
            'sucursal_id.integer' => 'La sucursal debe ser un numero entero.',
            'sucursal_id.min' => 'El numero de sucursal no es valido.',
            'solicitante.required' => 'Indica quien reporta el ticket.',
            'solicitante.min' => 'El nombre del solicitante debe tener al menos 3 caracteres.',
            'solicitante.max' => 'El nombre del solicitante no debe exceder 80 caracteres.',
            'severidad.required' => 'Indica la severidad del ticket.',
            'severidad.in' => 'La severidad debe ser baja, media o alta.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'titulo' => 'titulo',
            'descripcion' => 'descripcion',
            'sucursal_id' => 'sucursal',
            'solicitante' => 'solicitante',
            'severidad' => 'severidad',
        ];
    }
}
