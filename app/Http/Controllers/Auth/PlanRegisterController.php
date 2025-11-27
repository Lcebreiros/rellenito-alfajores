<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\TrialRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PlanRegisterController extends Controller
{
    /**
     * Mostrar el formulario de registro con el plan seleccionado
     */
    public function show($plan)
    {
        // Validar que el plan sea válido
        $validPlans = ['basic', 'premium', 'enterprise'];

        if (!in_array($plan, $validPlans)) {
            abort(404);
        }

        return view('auth.register-with-plan', [
            'plan' => $plan,
            'planName' => match($plan) {
                'basic' => 'Básico',
                'premium' => 'Premium',
                'enterprise' => 'Enterprise',
                default => $plan
            }
        ]);
    }

    /**
     * Procesar la solicitud de registro
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users', 'unique:trial_requests'],
            'plan' => ['required', 'string', 'in:basic,premium,enterprise'],
        ], [
            'email.unique' => 'Este correo electrónico ya está registrado.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Crear la solicitud de prueba
        TrialRequest::create([
            'name' => $request->name,
            'email' => $request->email,
            'plan' => $request->plan,
            'status' => 'pending',
        ]);

        // Redirigir con mensaje de éxito
        return redirect()->route('register.success');
    }

    /**
     * Mostrar el mensaje de éxito
     */
    public function success()
    {
        return view('auth.register-success');
    }
}
