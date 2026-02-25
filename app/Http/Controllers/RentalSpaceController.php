<?php

namespace App\Http\Controllers;

use App\Models\RentalSpace;
use App\Models\RentalSpaceCategory;
use App\Models\RentalDurationOption;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RentalSpaceController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $user = Auth::user();
            if ($user && !$user->isMaster() && !$user->hasModule('alquileres')) {
                abort(404);
            }
            return $next($request);
        });
    }

    public function index()
    {
        $companyId = $this->currentCompanyId();

        $spacesQuery = RentalSpace::with(['category', 'activeDurationOptions'])->orderBy('name');
        if ($companyId) {
            $spacesQuery->where('company_id', $companyId);
        }
        $spaces = $spacesQuery->get();

        $categoriesQuery = RentalSpaceCategory::orderBy('name');
        if ($companyId) {
            $categoriesQuery->where('company_id', $companyId);
        }
        $categories = $categoriesQuery->get();

        return view('rentals.spaces.index', compact('spaces', 'categories'));
    }

    public function show(RentalSpace $rentalSpace)
    {
        $companyId = $this->currentCompanyId();
        if ($companyId !== null && (int) $rentalSpace->company_id !== $companyId) {
            abort(403);
        }

        $rentalSpace->load(['category', 'activeDurationOptions']);

        return view('rentals.spaces.show', ['space' => $rentalSpace]);
    }

    public function store(Request $request)
    {
        $companyId = $this->currentCompanyId();

        $data = $request->validate([
            'name'        => 'required|string|max:100',
            'description' => 'nullable|string|max:500',
            'color'       => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'category_id' => 'nullable|exists:rental_space_categories,id',
            'capacity'    => 'nullable|integer|min:1|max:999',
            'is_active'   => 'boolean',
            // Opciones de duración (array)
            'duration_options'           => 'nullable|array',
            'duration_options.*.label'   => 'required_with:duration_options|string|max:100',
            'duration_options.*.minutes' => 'required_with:duration_options|integer|min:15|max:1440',
            'duration_options.*.price'   => 'required_with:duration_options|numeric|min:0',
        ]);

        $space = RentalSpace::create([
            'company_id'  => $companyId,
            'name'        => $data['name'],
            'description' => $data['description'] ?? null,
            'color'       => $data['color'] ?? '#6366f1',
            'category_id' => $data['category_id'] ?? null,
            'capacity'    => $data['capacity'] ?? 1,
            'is_active'   => $data['is_active'] ?? true,
        ]);

        if (!empty($data['duration_options'])) {
            foreach ($data['duration_options'] as $option) {
                RentalDurationOption::create([
                    'rental_space_id' => $space->id,
                    'label'           => $option['label'],
                    'minutes'         => (int) $option['minutes'],
                    'price'           => (float) $option['price'],
                    'is_active'       => true,
                ]);
            }
        }

        return back()->with('ok', 'Espacio creado correctamente.');
    }

    public function update(Request $request, RentalSpace $rentalSpace)
    {
        $companyId = $this->currentCompanyId();
        if ($companyId !== null && (int) $rentalSpace->company_id !== $companyId) {
            abort(403);
        }

        $data = $request->validate([
            'name'        => 'required|string|max:100',
            'description' => 'nullable|string|max:500',
            'color'       => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'category_id' => 'nullable|exists:rental_space_categories,id',
            'capacity'    => 'nullable|integer|min:1|max:999',
            'is_active'   => 'boolean',
        ]);

        $rentalSpace->update($data);

        return back()->with('ok', 'Espacio actualizado.');
    }

    public function destroy(RentalSpace $rentalSpace)
    {
        $companyId = $this->currentCompanyId();
        if ($companyId !== null && (int) $rentalSpace->company_id !== $companyId) {
            abort(403);
        }
        $rentalSpace->delete();

        return back()->with('ok', 'Espacio eliminado.');
    }

    public function storeCategory(Request $request)
    {
        $companyId = $this->currentCompanyId();

        $data = $request->validate([
            'name'  => 'required|string|max:100',
            'color' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
        ]);

        RentalSpaceCategory::firstOrCreate(
            ['company_id' => $companyId, 'name' => $data['name']],
            ['color' => $data['color'] ?? '#6366f1']
        );

        return back()->with('ok', 'Categoría creada.');
    }

    public function storeDurationOption(Request $request, RentalSpace $rentalSpace)
    {
        $companyId = $this->currentCompanyId();
        if ($companyId !== null && (int) $rentalSpace->company_id !== $companyId) {
            abort(403);
        }

        $data = $request->validate([
            'label'   => 'required|string|max:100',
            'minutes' => 'required|integer|min:15|max:1440',
            'price'   => 'required|numeric|min:0',
        ]);

        RentalDurationOption::create([
            'rental_space_id' => $rentalSpace->id,
            'label'           => $data['label'],
            'minutes'         => (int) $data['minutes'],
            'price'           => (float) $data['price'],
            'is_active'       => true,
        ]);

        return back()->with('ok', 'Opción de duración agregada.');
    }

    public function destroyDurationOption(RentalDurationOption $durationOption)
    {
        $companyId = $this->currentCompanyId();
        $space = $durationOption->space;
        if ($companyId !== null && (int) $space->company_id !== $companyId) {
            abort(403);
        }
        $durationOption->delete();

        return back()->with('ok', 'Opción eliminada.');
    }

    private function currentCompanyId(): ?int
    {
        $user = Auth::user();

        if ($user && method_exists($user, 'isMaster') && $user->isMaster()) {
            return null;
        }

        if ($user && $user->isCompany()) {
            return (int) $user->id;
        }

        if ($user && $user->parent_id) {
            return (int) $user->parent_id;
        }

        return (int) Auth::id();
    }
}
