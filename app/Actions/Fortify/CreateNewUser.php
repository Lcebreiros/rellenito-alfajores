<?php

namespace App\Actions\Fortify;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\CreatesNewUsers;
use Laravel\Jetstream\Jetstream;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => $this->passwordRules(),
            'business_type' => ['nullable', 'string', 'in:comercio,alquiler'],
            'terms' => Jetstream::hasTermsAndPrivacyPolicyFeature() ? ['accepted', 'required'] : '',
        ])->validate();

        $businessType = $input['business_type'] ?? 'comercio';

        // Determinar preset de módulos según tipo de negocio
        $presetKey = match($businessType) {
            'alquiler' => 'estacionamiento',
            'comercio' => 'tienda',
            default => 'generic'
        };

        return User::create([
            'name' => $input['name'],
            'email' => $input['email'],
            'password' => Hash::make($input['password']),
            'business_type' => $businessType,
            'modulos_activos' => User::presetModules($presetKey),
        ]);
    }
}
