<?php

namespace App\Http\Controllers;

use App\Models\Rate;
use App\Models\ParkingSpace;
use App\Models\ParkingSpaceCategory;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ParkingSpaceController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (!Auth::user()?->hasModule('parking')) {
                abort(404);
            }
            return $next($request);
        });
    }

    public function index()
    {
        $companyId = $this->currentCompanyId();

        $spaces = ParkingSpace::with(['category', 'rate', 'service'])
            ->where('company_id', $companyId)
            ->orderBy('name')
            ->get();

        $categories = ParkingSpaceCategory::where('company_id', $companyId)
            ->orderBy('name')
            ->get();

        $rates = Rate::where('company_id', $companyId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $services = Service::availableFor(Auth::user())
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('parking.spaces.index', compact('spaces', 'categories', 'rates', 'services'));
    }

    public function store(Request $request)
    {
        $companyId = $this->currentCompanyId();
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'code' => 'nullable|string|max:50',
            'category_id' => 'nullable|exists:parking_space_categories,id',
            'rate_id' => 'nullable|exists:parking_rates,id',
            'service_id' => 'nullable|exists:services,id',
            'status' => 'nullable|in:disponible,ocupada,alquilada,mantenimiento',
            'usage' => 'nullable|in:horaria,mensual',
            'notes' => 'nullable|string|max:500',
        ]);

        $data['company_id'] = $companyId;
        $data['status'] = $data['status'] ?? ParkingSpace::STATUS_AVAILABLE;
        $data['usage'] = $data['usage'] ?? ParkingSpace::USAGE_HOURLY;

        // Si la cochera ya está ocupada, no permitir asignarla como disponible sin stay
        ParkingSpace::create($data);

        return back()->with('ok', 'Cochera creada.');
    }

    public function update(Request $request, int $parkingSpace)
    {
        $parkingSpace = $this->findSpaceForUser($parkingSpace);

        $data = $request->validate([
            'name' => 'required|string|max:100',
            'code' => 'nullable|string|max:50',
            'category_id' => 'nullable|exists:parking_space_categories,id',
            'rate_id' => 'nullable|exists:parking_rates,id',
            'service_id' => 'nullable|exists:services,id',
            'status' => 'required|in:disponible,ocupada,alquilada,mantenimiento',
            'usage' => 'nullable|in:horaria,mensual',
            'notes' => 'nullable|string|max:500',
        ]);

        $parkingSpace->update($data);

        return back()->with('ok', 'Cochera actualizada.');
    }

    public function destroy(int $parkingSpace)
    {
        $parkingSpace = $this->findSpaceForUser($parkingSpace);
        $parkingSpace->delete();

        return back()->with('ok', 'Cochera eliminada.');
    }

    public function storeCategory(Request $request)
    {
        $companyId = $this->currentCompanyId();
        $data = $request->validate([
            'name' => 'required|string|max:100',
        ]);

        ParkingSpaceCategory::firstOrCreate(
            ['company_id' => $companyId, 'name' => $data['name']],
            []
        );

        return back()->with('ok', 'Categoría creada.');
    }

    private function findSpaceForUser(int $id): ParkingSpace
    {
        $companyId = (int) $this->currentCompanyId();
        $userId = (int) Auth::id();

        return ParkingSpace::where('id', $id)
            ->where(function ($q) use ($companyId, $userId) {
                $q->where('company_id', $companyId)
                  ->orWhere('company_id', $userId); // legacy
            })
            ->firstOrFail();
    }

    private function currentCompanyId(): int
    {
        $user = Auth::user();

        if ($user && $user->isCompany()) {
            return (int) $user->id;
        }

        if ($user && $user->parent_id) {
            return (int) $user->parent_id;
        }

        return (int) Auth::id();
    }
}
