<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');

        // Solo masters pueden usar este controlador
        $this->middleware(function ($request, $next) {
            $user = auth()->user();
            if (! $user || ! method_exists($user, 'isMaster') || ! $user->isMaster()) {
                abort(403, 'Acceso denegado');
            }
            return $next($request);
        });
    }

    // Lista paginada
    // Master/UserController.php (index)
public function index()
{
    $total = \App\Models\User::count();
    $active = \App\Models\User::where('is_active', 1)->count();
    $suspended = \App\Models\User::where('is_active', 0)->count();
    $deleted = \App\Models\User::onlyTrashed()->count(); // si usas soft deletes

    $stats = [
        'total' => $total,
        'active' => $active,
        'suspended' => $suspended,
        'deleted' => $deleted,
    ];

    $users = \App\Models\User::paginate(20);

    return view('master.users.index', compact('users', 'stats'));
}


    // Ver detalle del usuario
    public function show(User $user): View
    {
        return view('master.users.show', compact('user'));
    }

    // Form editar
    public function edit(User $user): View
    {
        return view('master.users.edit', compact('user'));
    }

    // Actualizar datos (sin password aquí)
    public function update(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required','string','max:255'],
            'email' => ['required','email','max:255', Rule::unique('users')->ignore($user->id)],
            'hierarchy_level' => ['nullable','integer'],
            'is_active' => ['nullable','boolean'],
            // otros campos que quieras permitir (profile_photo_path, app_logo_path, etc)
        ]);

        // Normalizar booleano
        if (array_key_exists('is_active', $data)) {
            $user->is_active = (bool) $data['is_active'];
        }

        $user->fill($data);
        $user->save();

        return redirect()->route('master.users.show', $user)->with('success', 'Usuario actualizado correctamente.');
    }

    // Alterna suspendido/activo
    public function toggleActive(User $user): RedirectResponse
    {
        // no permitir que master se auto-suspenda (opcional)
        if (auth()->id() === $user->id) {
            return back()->withErrors(['error' => 'No te puedes suspender a vos mismo.']);
        }

        $user->is_active = ! (bool) $user->is_active;
        $user->save();

        $msg = $user->is_active ? 'Usuario reactivado' : 'Usuario suspendido';
        return back()->with('success', $msg.' correctamente.');
    }

    // Resetear contraseña a un password provisto (devuelve éxito)
    public function resetPassword(Request $request, User $user): RedirectResponse
    {
        $request->validate([
            'password' => ['required','string','min:8','confirmed'],
        ]);

        // No mostrar la contraseña en ningún sitio; solo setearla
        $user->password = Hash::make($request->input('password'));
        $user->save();

        return back()->with('success', 'Contraseña restablecida correctamente.');
    }

    // Eliminar usuario
    public function destroy(User $user): RedirectResponse
    {
        // proteger contra borrar master principal u otros checks
        if ($user->isMaster()) {
            return back()->withErrors(['error' => 'No se puede eliminar un usuario master.']);
        }

        // evitar que master se borre a sí mismo
        if (auth()->id() === $user->id) {
            return back()->withErrors(['error' => 'No puedes eliminar tu propia cuenta desde aquí.']);
        }

        $user->delete();

        return redirect()->route('master.users.index')->with('success', 'Usuario eliminado correctamente.');
    }
}
