<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\TrialRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
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

        // Si el usuario está autenticado, crear solicitud automáticamente
        if (auth()->check()) {
            $user = auth()->user();

            // Verificar si ya tiene una solicitud pendiente
            $existingRequest = TrialRequest::where('email', $user->email)
                ->where('status', 'pending')
                ->first();

            if ($existingRequest) {
                return redirect()->route('register.success')
                    ->with('message', 'Ya tienes una solicitud pendiente de aprobación.');
            }

            // Crear la solicitud automáticamente con los datos del usuario
            TrialRequest::create([
                'name' => $user->name,
                'email' => $user->email,
                'plan' => $plan,
                'business_type' => $user->business_type ?? 'comercio',
                'status' => 'pending',
            ]);

            return redirect()->route('register.success');
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
            'business_type' => ['required', 'string', 'in:comercio,alquiler'],
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
            'business_type' => $request->business_type,
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

    /**
     * Mostrar el formulario de registro multi-step
     */
    public function showWizard()
    {
        return view('auth.register-wizard');
    }

    /**
     * Procesar la solicitud de registro desde el wizard
     */
    public function storeWizard(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users', 'unique:trial_requests'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'plan' => ['required', 'string', 'in:basic,premium,enterprise'],
            'business_type' => ['required', 'string', 'in:comercio,alquiler'],
        ], [
            'email.unique' => 'Este correo electrónico ya está registrado.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Crear el usuario inactivo (se activa cuando el admin aprueba la solicitud)
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'business_type' => $request->business_type,
            'is_active' => false,
            'hierarchy_level' => User::HIERARCHY_COMPANY,
        ]);

        // Crear la solicitud de prueba vinculada al usuario
        TrialRequest::create([
            'name' => $request->name,
            'email' => $request->email,
            'plan' => $request->plan,
            'business_type' => $request->business_type,
            'status' => 'pending',
            'user_id' => $user->id,
        ]);

        // Redirigir con mensaje de éxito
        return redirect()->route('register.success');
    }
}
