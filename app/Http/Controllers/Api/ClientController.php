<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Client;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    /**
     * Lista de clientes con paginación
     */
    public function index(Request $request)
    {
        $auth = $request->user();

        $query = Client::forUser($auth)
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = trim($request->q);
                $lc = mb_strtolower($term, 'UTF-8');
                $q->where(function($w) use ($lc) {
                    $w->whereRaw('LOWER(name) LIKE ?', ["%{$lc}%"])
                      ->orWhereRaw('LOWER(email) LIKE ?', ["%{$lc}%"])
                      ->orWhereRaw('LOWER(phone) LIKE ?', ["%{$lc}%"])
                      ->orWhereRaw('LOWER(document_number) LIKE ?', ["%{$lc}%"]);
                });
            })
            ->when($request->filled('city'), function ($q) use ($request) {
                $q->where('city', $request->city);
            })
            ->when($request->filled('province'), function ($q) use ($request) {
                $q->where('province', $request->province);
            });

        $perPage = min((int) $request->input('per_page', 20), 100);
        $clients = $query->orderBy('name')->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $clients->items(),
            'meta' => [
                'current_page' => $clients->currentPage(),
                'last_page' => $clients->lastPage(),
                'per_page' => $clients->perPage(),
                'total' => $clients->total(),
            ],
        ], 200);
    }

    /**
     * Buscar clientes (para autocompletado)
     */
    public function search(Request $request)
    {
        $request->validate([
            'q' => 'required|string|min:1',
        ]);

        $auth = $request->user();
        $term = trim($request->q);
        $lc = mb_strtolower($term, 'UTF-8');

        $clients = Client::forUser($auth)
            ->where(function($w) use ($lc) {
                $w->whereRaw('LOWER(name) LIKE ?', ["%{$lc}%"])
                  ->orWhereRaw('LOWER(email) LIKE ?', ["%{$lc}%"])
                  ->orWhereRaw('LOWER(phone) LIKE ?', ["%{$lc}%"]);
            })
            ->select('id', 'name', 'email', 'phone', 'address', 'balance')
            ->limit(20)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $clients,
        ], 200);
    }

    /**
     * Mostrar un cliente específico
     */
    public function show(Request $request, Client $client)
    {
        $auth = $request->user();

        if (!$this->canAccessClient($auth, $client)) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes acceso a este cliente',
            ], 403);
        }

        $client->load(['orders' => function ($q) {
            $q->latest()->limit(10);
        }]);

        return response()->json([
            'success' => true,
            'data' => $client,
        ], 200);
    }

    /**
     * Crear un nuevo cliente
     */
    public function store(Request $request)
    {
        $auth = $request->user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'document_number' => 'nullable|string|max:50',
            'company' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'province' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
            'tags' => 'nullable|array',
            'balance' => 'nullable|numeric',
        ]);

        // Determinar el user_id apropiado
        if ($auth->isCompany()) {
            $validated['user_id'] = $auth->id;
        } else {
            $company = $auth->rootCompany();
            $validated['user_id'] = $company ? $company->id : $auth->id;
        }

        $client = Client::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Cliente creado exitosamente',
            'data' => $client,
        ], 201);
    }

    /**
     * Actualizar un cliente existente
     */
    public function update(Request $request, Client $client)
    {
        $auth = $request->user();

        if (!$this->canManageClient($auth, $client)) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para editar este cliente',
            ], 403);
        }

        $validated = $request->validate([
            'name' => 'string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'document_number' => 'nullable|string|max:50',
            'company' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'province' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
            'tags' => 'nullable|array',
            'balance' => 'nullable|numeric',
        ]);

        $client->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Cliente actualizado exitosamente',
            'data' => $client,
        ], 200);
    }

    /**
     * Eliminar un cliente
     */
    public function destroy(Request $request, Client $client)
    {
        $auth = $request->user();

        if (!$this->canManageClient($auth, $client)) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para eliminar este cliente',
            ], 403);
        }

        // Verificar si tiene pedidos asociados
        if ($client->orders()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'No se puede eliminar un cliente con pedidos asociados',
            ], 422);
        }

        $client->delete();

        return response()->json([
            'success' => true,
            'message' => 'Cliente eliminado exitosamente',
        ], 200);
    }

    /**
     * Verificar si el usuario puede acceder al cliente
     */
    private function canAccessClient($user, Client $client): bool
    {
        if ($user->isMaster()) {
            return true;
        }

        if ($user->isCompany()) {
            return $client->user_id === $user->id;
        }

        $company = $user->rootCompany();
        return $client->user_id === $company?->id;
    }

    /**
     * Verificar si el usuario puede gestionar el cliente
     */
    private function canManageClient($user, Client $client): bool
    {
        return $this->canAccessClient($user, $client);
    }
}
