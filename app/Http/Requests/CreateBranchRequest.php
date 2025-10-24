<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\User;
use App\DTOs\CreateBranchDTO;
use App\DTOs\UpdateBranchDTO;

class CreateBranchRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        return $user && ($user->isMaster() || $user->isCompany());
    }

    public function rules(): array
    {
        $rules = [
            // Datos del Branch
            'name' => 'required|string|max:255',
            'address' => 'nullable|string|max:500',
            'phone' => 'nullable|string|max:30',
            'contact_email' => 'nullable|email|max:255',
            
            // Datos del User (para autenticación)
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'user_limit' => 'nullable|integer|min:0',
            'is_active' => 'sometimes|boolean',
            'use_company_inventory' => 'sometimes|boolean',
        ];

        if ($this->user()->isMaster()) {
            $rules['company_id'] = [
                'required',
                Rule::exists('users', 'id')->where(function ($query) {
                    $query->where('hierarchy_level', User::HIERARCHY_COMPANY);
                })
            ];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'name.required' => 'El nombre de la sucursal es obligatorio.',
            'email.required' => 'El email es obligatorio.',
            'email.email' => 'El email debe tener un formato válido.',
            'email.unique' => 'Este email ya está en uso.',
            'password.required' => 'La contraseña es obligatoria.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'password.confirmed' => 'La confirmación de contraseña no coincide.',
            'company_id.required' => 'Debe seleccionar una empresa.',
            'company_id.exists' => 'La empresa seleccionada no es válida.',
        ];
    }

    public function toDTO(): CreateBranchDTO
    {
        return CreateBranchDTO::fromArray([
            'name' => $this->name,
            'email' => $this->email,
            'password' => $this->password,
            'address' => $this->address,
            'phone' => $this->phone,
            'contact_email' => $this->contact_email,
            'user_limit' => $this->user_limit,
            'is_active' => $this->boolean('is_active', true),
            'use_company_inventory' => $this->boolean('use_company_inventory', false),
        ]);
    }

    public function getCompany(): User
    {
        return $this->user()->isMaster() 
            ? User::findOrFail($this->company_id)
            : $this->user();
    }
}

class UpdateBranchRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        $branch = $this->route('branch');
        
        return $user && $branch->user && $user->canManageUser($branch->user);
    }

    public function rules(): array
    {
        $branch = $this->route('branch');
        $userId = $branch->user ? $branch->user->id : null;

        return [
            // Datos del Branch
            'name' => 'required|string|max:255',
            'address' => 'nullable|string|max:500',
            'phone' => 'nullable|string|max:30',
            'contact_email' => 'nullable|email|max:255',
            
            // Datos del User
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($userId)
            ],
            'password' => 'nullable|string|min:8|confirmed',
            'user_limit' => 'nullable|integer|min:0',
            'is_active' => 'sometimes|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'El nombre de la sucursal es obligatorio.',
            'email.required' => 'El email es obligatorio.',
            'email.email' => 'El email debe tener un formato válido.',
            'email.unique' => 'Este email ya está en uso.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'password.confirmed' => 'La confirmación de contraseña no coincide.',
        ];
    }

    public function toDTO(): UpdateBranchDTO
    {
        return UpdateBranchDTO::fromArray([
            'name' => $this->name,
            'email' => $this->email,
            'password' => $this->password,
            'address' => $this->address,
            'phone' => $this->phone,
            'contact_email' => $this->contact_email,
            'user_limit' => $this->user_limit,
            'is_active' => $this->boolean('is_active', true),
        ]);
    }
}
