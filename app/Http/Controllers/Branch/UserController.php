<?php

namespace App\Http\Controllers\Branch;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Branch;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        // Si el admin es master, permitir listar usando branch_id query param
        if (method_exists($user, 'isMaster') && $user->isMaster()) {
            if (! $request->filled('branch_id')) {
                abort(400, 'branch_id required for master');
            }
            $branch = Branch::findOrFail($request->branch_id);
        } else {
            // Asumimos admin tiene branch relation: $user->branch o similar
            $branch = $user->branch ?? null;
            if (! $branch) {
                abort(403, 'No autorizado: no perteneces a ninguna sucursal.');
            }
        }

        $users = $branch->users()->paginate(20); // asegura relación users() en Branch
        return view('branch.users.index', compact('users', 'branch'));
    }

    public function create(Request $request)
    {
        $user = auth()->user();

        if (method_exists($user, 'isMaster') && $user->isMaster()) {
            $branches = Branch::with('company')->get();
            return view('branch.users.create', compact('branches'));
        }

        return view('branch.users.create');
    }

    public function store(Request $request)
    {
        $user = auth()->user();

        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
        ];

        if (method_exists($user, 'isMaster') && $user->isMaster()) {
            $rules['branch_id'] = ['required','exists:branches,id'];
        }

        $validated = $request->validate($rules);

        if (method_exists($user, 'isMaster') && $user->isMaster()) {
            $branch = Branch::findOrFail($validated['branch_id']);
        } else {
            $branch = $user->branch ?? null;
            if (! $branch) {
                abort(403, 'No autorizado: no perteneces a ninguna sucursal.');
            }
        }

        $newUser = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => bcrypt($validated['password']),
            // campos extras que uses
        ]);

        // Asignar al branch: por ejemplo via pivot o columna branch_id
        $newUser->branch_id = $branch->id;
        $newUser->save();

        return redirect()->route('branch.users.index')->with('success', 'Usuario creado correctamente');
    }
}
