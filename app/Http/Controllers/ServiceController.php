<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Models\ServiceCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

        if ($request->filled('service_category_id')) {
            $query->where('service_category_id', $request->integer('service_category_id'));
        }

        $services = $query->with('category')->orderBy('name')->paginate(20)->withQueryString();

        $categories = ServiceCategory::where('company_id', $auth->rootCompany()?->id ?? $auth->id)
            ->orderBy('name')
            ->get();

        return view('services.index', compact('services', 'categories'));
    }

    public function create()
    {
        $user = auth()->user();
        $categories = ServiceCategory::where('company_id', $user->rootCompany()?->id ?? $user->id)
            ->orderBy('name')
            ->get();

        return view('services.create', compact('categories'));
    }

    public function edit(Service $service)
    {
        $user = auth()->user();
        $service->load('variants', 'category');
        $categories = ServiceCategory::where('company_id', $user->rootCompany()?->id ?? $user->id)
            ->orderBy('name')
            ->get();

        return view('services.edit', compact('service', 'categories'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:150',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'is_active' => 'nullable|boolean',
            'tags' => 'nullable|string',
            'variants' => 'nullable|array',
            'variants.*.name' => 'required_with:variants|string|max:150',
            'variants.*.duration_minutes' => 'nullable|integer|min:0',
            'variants.*.price' => 'required_with:variants|numeric|min:0',
            'variants.*.description' => 'nullable|string',
            'variants.*.is_active' => 'nullable|boolean',
            'service_category_id' => 'nullable|exists:service_categories,id',
            'new_category' => 'nullable|string|max:100',
        ]);
        $data['is_active'] = $request->boolean('is_active');
        $data['tags'] = $this->parseTags($request->input('tags'));

        DB::transaction(function () use ($data, $request) {
            $data['service_category_id'] = $this->resolveCategoryId($request, $request->user());
            $service = Service::create($data);
            $variants = $this->prepareVariantsPayload($request);
            if (!empty($variants)) {
                $service->variants()->createMany($variants);
            }
        });

        return redirect()->route('services.index')->with('ok', 'Servicio creado');
    }

    public function update(Request $request, Service $service)
    {
        $data = $request->validate([
            'name' => 'required|string|max:150',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'is_active' => 'nullable|boolean',
            'tags' => 'nullable|string',
            'variants' => 'nullable|array',
            'variants.*.name' => 'required_with:variants|string|max:150',
            'variants.*.duration_minutes' => 'nullable|integer|min:0',
            'variants.*.price' => 'required_with:variants|numeric|min:0',
            'variants.*.description' => 'nullable|string',
            'variants.*.is_active' => 'nullable|boolean',
            'service_category_id' => 'nullable|exists:service_categories,id',
            'new_category' => 'nullable|string|max:100',
        ]);
        $data['is_active'] = $request->boolean('is_active');
        $data['tags'] = $this->parseTags($request->input('tags'));

        DB::transaction(function () use ($service, $data, $request) {
            $data['service_category_id'] = $this->resolveCategoryId($request, $request->user());
            $service->update($data);
            // Reemplazar variantes (simple y seguro)
            $service->variants()->delete();
            $variants = $this->prepareVariantsPayload($request);
            if (!empty($variants)) {
                $service->variants()->createMany($variants);
            }
        });

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

    private function parseTags(?string $tags): ?array
    {
        if (!$tags) return null;
        $parts = array_filter(array_map('trim', explode(',', $tags)));
        return $parts ? array_values(array_unique($parts)) : null;
    }

    private function prepareVariantsPayload(Request $request): array
    {
        $variants = $request->input('variants', []);
        $result = [];
        foreach ($variants as $variant) {
            if (empty($variant['name'])) continue;
            $result[] = [
                'name' => $variant['name'],
                'duration_minutes' => $variant['duration_minutes'] ?? null,
                'price' => $variant['price'] ?? 0,
                'description' => $variant['description'] ?? null,
                'is_active' => !empty($variant['is_active']),
            ];
        }
        return $result;
    }

    private function resolveCategoryId(Request $request, $user): ?int
    {
        $newName = trim((string) $request->input('new_category', ''));
        if ($newName !== '') {
            $companyId = $user->rootCompany()?->id ?? $user->id;
            $category = ServiceCategory::firstOrCreate(
                ['company_id' => $companyId, 'name' => $newName],
                []
            );
            return $category->id;
        }

        return $request->input('service_category_id');
    }
}
