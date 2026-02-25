<?php

namespace App\Http\Controllers;

use App\Models\Rate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ParkingRateController extends Controller
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
        $rates = Rate::where('company_id', $companyId)
            ->orderBy('name')
            ->get();

        return view('parking.rates.index', compact('rates'));
    }

    public function store(Request $request)
    {
        $companyId = $this->currentCompanyId();
        $data = $request->validate([
            'name' => 'required|string|max:150',
            'vehicle_type' => 'nullable|string|max:50',
            'fraction_minutes' => 'nullable|integer|min:1',
            'price_per_fraction' => 'nullable|numeric|min:0',
            'initial_block_minutes' => 'nullable|integer|min:1',
            'initial_block_price' => 'nullable|numeric|min:0',
            'hour_price' => 'nullable|numeric|min:0',
            'half_day_price' => 'nullable|numeric|min:0',
            'day_price' => 'nullable|numeric|min:0',
            'week_price' => 'nullable|numeric|min:0',
            'month_price' => 'nullable|numeric|min:0',
            'is_active' => 'nullable|boolean',
            'price_hour' => 'nullable|numeric|min:0',
            'price_half_hour' => 'nullable|numeric|min:0',
        ]);
        $data['company_id'] = $companyId;
        $data['is_active'] = $request->boolean('is_active', true);

        // Atajo: si se especifica precio de 1h y/o 30 min, mapear a bloque inicial de 60 min + fracción de 30 min
        if ($request->filled('price_hour') || $request->filled('price_half_hour')) {
            $data['initial_block_minutes'] = 60;
            $data['initial_block_price'] = $request->input('price_hour', $data['initial_block_price'] ?? null);
            $data['fraction_minutes'] = 30;
            $data['price_per_fraction'] = $request->input('price_half_hour', $data['price_per_fraction'] ?? 0);
        }

        // Defaults para evitar nulos
        $data['fraction_minutes'] = $data['fraction_minutes'] ?? 30;
        $data['price_per_fraction'] = $data['price_per_fraction'] ?? 0;

        Rate::create($data);

        return back()->with('ok', 'Tarifa creada.');
    }

    public function update(Request $request, int $parkingRate)
    {
        $parkingRate = $this->findRateForUser($parkingRate);

        $data = $request->validate([
            'name' => 'required|string|max:150',
            'vehicle_type' => 'nullable|string|max:50',
            'fraction_minutes' => 'nullable|integer|min:1',
            'price_per_fraction' => 'nullable|numeric|min:0',
            'initial_block_minutes' => 'nullable|integer|min:1',
            'initial_block_price' => 'nullable|numeric|min:0',
            'hour_price' => 'nullable|numeric|min:0',
            'half_day_price' => 'nullable|numeric|min:0',
            'day_price' => 'nullable|numeric|min:0',
            'week_price' => 'nullable|numeric|min:0',
            'month_price' => 'nullable|numeric|min:0',
            'is_active' => 'nullable|boolean',
            'price_hour' => 'nullable|numeric|min:0',
            'price_half_hour' => 'nullable|numeric|min:0',
        ]);
        $data['is_active'] = $request->boolean('is_active', true);
        $data['company_id'] = $parkingRate->company_id ?: $this->currentCompanyId();

        if ($request->filled('price_hour') || $request->filled('price_half_hour')) {
            $data['initial_block_minutes'] = 60;
            $data['initial_block_price'] = $request->input('price_hour', $data['initial_block_price'] ?? null);
            $data['fraction_minutes'] = 30;
            $data['price_per_fraction'] = $request->input('price_half_hour', $data['price_per_fraction'] ?? 0);
        }

        $data['fraction_minutes'] = $data['fraction_minutes'] ?? 30;
        $data['price_per_fraction'] = $data['price_per_fraction'] ?? 0;

        $parkingRate->update($data);

        return back()->with('ok', 'Tarifa actualizada.');
    }

    public function destroy(int $parkingRate)
    {
        $parkingRate = $this->findRateForUser($parkingRate);
        $parkingRate->delete();

        return back()->with('ok', 'Tarifa eliminada.');
    }

    private function findRateForUser(int $id): Rate
    {
        $userCompanyId = (int) $this->currentCompanyId();
        $userId = (int) Auth::id();

        $rate = Rate::where('id', $id)
            ->where(function ($q) use ($userCompanyId, $userId) {
                $q->whereNull('company_id')
                  ->orWhere('company_id', $userCompanyId)
                  ->orWhere('company_id', $userId);
            })
            ->firstOrFail();

        return $rate;
    }

    private function currentCompanyId(): int
    {
        $user = Auth::user();

        // Si es empresa, usar su propio ID
        if ($user && $user->isCompany()) {
            return (int) $user->id;
        }

        // Si es admin/usuario con parent, usar el parent (sucursal ligada a empresa)
        if ($user && $user->parent_id) {
            return (int) $user->parent_id;
        }

        // Fallback: su propio ID
        return (int) Auth::id();
    }
}
