<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Invitation;

class RegisterWithKeyRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Permitir que cualquiera pueda intentar registro, la validación de key ocurre en rules/after
        return true;
    }

    public function rules(): array
    {
        return [
            'invitation_key' => 'required|string',
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|confirmed|min:8',
        ];
    }

    public function messages(): array
    {
        return [
            'invitation_key.required' => 'Debes ingresar la clave de invitación.',
        ];
    }
}
