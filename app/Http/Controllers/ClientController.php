<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ClientController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth']);
    }

    public function index(Request $request): View
    {
        $q = trim((string) $request->get('q', ''));

        $clients = Client::query()
            ->forUser(auth()->user())
            ->when($q !== '', function ($qb) use ($q) {
                $qb->where(function ($w) use ($q) {
                    $w->where('name', 'like', "%{$q}%")
                      ->orWhere('email', 'like', "%{$q}%")
                      ->orWhere('phone', 'like', "%{$q}%")
                      ->orWhere('document_number', 'like', "%{$q}%");
                });
            })
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('clients.index', compact('clients', 'q'));
    }

    public function create(): View
    {
        return view('clients.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateClient($request);

        // Asignar el user_id (company_id para el scope)
        $user = auth()->user();
        $data['user_id'] = $user->isCompany() ? $user->id : \App\Models\Order::findRootCompanyId($user);

        Client::create($data);
        return redirect()->route('clients.index')->with('ok', 'Cliente creado.');
    }

    public function show(Client $client): View
    {
        $this->authorizeClient($client);
        $client->load(['orders' => function ($q) { $q->latest()->limit(20); }]);
        return view('clients.show', compact('client'));
    }

    public function edit(Client $client): View
    {
        $this->authorizeClient($client);
        return view('clients.edit', compact('client'));
    }

    public function update(Request $request, Client $client): RedirectResponse
    {
        $this->authorizeClient($client);
        $data = $this->validateClient($request, $client->id);
        $client->update($data);
        return redirect()->route('clients.show', $client)->with('ok', 'Cliente actualizado.');
    }

    protected function validateClient(Request $request, ?int $id = null): array
    {
        return $request->validate([
            'name'            => ['required', 'string', 'max:120'],
            'email'           => ['nullable', 'email', 'max:150', Rule::unique('clients', 'email')->ignore($id)],
            'phone'           => ['nullable', 'string', 'max:50'],
            'document_number' => ['nullable', 'string', 'max:50'], // DNI opcional
            'company'         => ['nullable', 'string', 'max:150'],
            'address'         => ['nullable', 'string', 'max:255'],
            'city'            => ['nullable', 'string', 'max:100'],
            'province'        => ['nullable', 'string', 'max:100'],
            'country'         => ['nullable', 'string', 'max:100'],
            'tags'            => ['nullable', 'array'],
            'notes'           => ['nullable', 'string', 'max:5000'],
        ]);
    }
}

