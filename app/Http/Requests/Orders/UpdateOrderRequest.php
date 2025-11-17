<?php

namespace App\Http\Requests\Orders;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateOrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Validar que el usuario puede actualizar la orden
        return $this->route('order')->user_id === auth()->id();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:255'],
            'items_json' => ['required', 'string'],
            'is_scheduled' => ['nullable', Rule::in(['0', '1', 0, 1, true, false])],
            'scheduled_for' => ['nullable', 'date_format:Y-m-d\TH:i'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'El nombre del cliente es obligatorio.',
            'email.email' => 'El email debe ser una dirección válida.',
            'items_json.required' => 'El pedido debe tener al menos un producto.',
            'scheduled_for.date_format' => 'El formato de fecha no es válido.',
        ];
    }
}
