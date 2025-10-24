<?php

namespace App\Http\Controllers;

use App\Models\Service;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    public function index(Request $request)
    {
        $auth = $request->user() ?? auth()->user();

        $query = (method_exists($auth, 'isMaster') && $auth->isMaster())
            ? Service::query()
            : Service::availableFor($auth);

        $query->when($request->filled('q'), function ($q) use ($request) {
            $term = trim((string) $request->input('q'));
            $lc = mb_strtolower($term, 'UTF-8');
            $q->whereRaw('LOWER(name) LIKE ?', ["%{$lc}%"]);
        });

        $services = $query->orderBy('name')->paginate(20)->withQueryString();

        return view('services.index', compact('services'));
    }

    public function create()
    {
        return view('services.create');
    }

    public function edit(Service $service)
    {
        return view('services.edit', compact('service'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:150',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'is_active' => 'nullable|boolean',
        ]);
        $data['is_active'] = $request->boolean('is_active');
        Service::create($data);

        return redirect()->route('services.index')->with('ok', 'Servicio creado');
    }

    public function update(Request $request, Service $service)
    {
        $data = $request->validate([
            'name' => 'required|string|max:150',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'is_active' => 'nullable|boolean',
        ]);
        $data['is_active'] = $request->boolean('is_active');
        $service->update($data);

        return back()->with('ok', 'Servicio actualizado');
    }

    public function destroy(Service $service)
    {
        try {
            $service->delete();
            return redirect()->route('services.index')->with('ok','Servicio eliminado');
        } catch (\Throwable $e) {
            return back()->with('error','No se pudo eliminar.');
        }
    }
}
