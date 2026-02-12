<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\ParkingShift;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class EmployeeController extends Controller
{
    /**
     * Listar empleados
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $companyId = $this->getCompanyId($user);

        $query = Employee::where('company_id', $companyId)
            ->with(['branch', 'user']);

        // Filtros
        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'LIKE', "%{$search}%")
                    ->orWhere('last_name', 'LIKE', "%{$search}%")
                    ->orWhere('dni', 'LIKE', "%{$search}%")
                    ->orWhere('email', 'LIKE', "%{$search}%");
            });
        }

        if ($request->has('branch_id')) {
            $query->where('branch_id', $request->input('branch_id'));
        }

        // Paginación
        $perPage = min($request->input('per_page', 15), 100);
        $employees = $query->orderBy('first_name')->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $employees->items(),
            'pagination' => [
                'current_page' => $employees->currentPage(),
                'last_page' => $employees->lastPage(),
                'per_page' => $employees->perPage(),
                'total' => $employees->total(),
            ],
        ]);
    }

    /**
     * Crear empleado
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        $companyId = $this->getCompanyId($user);

        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'dni' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'branch_id' => 'nullable|exists:branches,id',
            'position' => 'nullable|string|max:100',
            'salary' => 'nullable|numeric|min:0',
            'hire_date' => 'nullable|date',
        ]);

        $validated['company_id'] = $companyId;

        // Verificar que la sucursal pertenezca a la empresa
        if (isset($validated['branch_id'])) {
            $branch = \App\Models\Branch::find($validated['branch_id']);
            if (!$branch || $branch->user_id != $companyId) {
                throw ValidationException::withMessages([
                    'branch_id' => 'La sucursal no pertenece a esta empresa.'
                ]);
            }
        }

        $employee = Employee::create($validated);
        $employee->load(['branch', 'user']);

        return response()->json([
            'success' => true,
            'message' => 'Empleado creado exitosamente',
            'data' => $employee,
        ], 201);
    }

    /**
     * Ver empleado
     */
    public function show(Employee $employee)
    {
        $user = Auth::user();
        $companyId = $this->getCompanyId($user);

        if ($employee->company_id != $companyId) {
            return response()->json([
                'success' => false,
                'message' => 'No autorizado',
            ], 403);
        }

        $employee->load(['branch', 'user']);

        return response()->json([
            'success' => true,
            'data' => $employee,
        ]);
    }

    /**
     * Actualizar empleado
     */
    public function update(Request $request, Employee $employee)
    {
        $user = Auth::user();
        $companyId = $this->getCompanyId($user);

        if ($employee->company_id != $companyId) {
            return response()->json([
                'success' => false,
                'message' => 'No autorizado',
            ], 403);
        }

        $validated = $request->validate([
            'first_name' => 'sometimes|required|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'dni' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'branch_id' => 'nullable|exists:branches,id',
            'position' => 'nullable|string|max:100',
            'salary' => 'nullable|numeric|min:0',
            'hire_date' => 'nullable|date',
        ]);

        // Verificar que la sucursal pertenezca a la empresa
        if (isset($validated['branch_id'])) {
            $branch = \App\Models\Branch::find($validated['branch_id']);
            if (!$branch || $branch->user_id != $companyId) {
                throw ValidationException::withMessages([
                    'branch_id' => 'La sucursal no pertenece a esta empresa.'
                ]);
            }
        }

        $employee->update($validated);
        $employee->load(['branch', 'user']);

        return response()->json([
            'success' => true,
            'message' => 'Empleado actualizado exitosamente',
            'data' => $employee,
        ]);
    }

    /**
     * Eliminar empleado
     */
    public function destroy(Employee $employee)
    {
        $user = Auth::user();
        $companyId = $this->getCompanyId($user);

        if ($employee->company_id != $companyId) {
            return response()->json([
                'success' => false,
                'message' => 'No autorizado',
            ], 403);
        }

        // Verificar si tiene turnos asociados
        $shiftsCount = ParkingShift::where('employee_id', $employee->id)->count();

        if ($shiftsCount > 0) {
            return response()->json([
                'success' => false,
                'message' => 'No se puede eliminar un empleado con turnos registrados',
            ], 422);
        }

        $employee->delete();

        return response()->json([
            'success' => true,
            'message' => 'Empleado eliminado exitosamente',
        ]);
    }

    /**
     * Obtener turnos del empleado
     */
    public function shifts(Request $request, Employee $employee)
    {
        $user = Auth::user();
        $companyId = $this->getCompanyId($user);

        if ($employee->company_id != $companyId) {
            return response()->json([
                'success' => false,
                'message' => 'No autorizado',
            ], 403);
        }

        $query = ParkingShift::where('employee_id', $employee->id)
            ->with(['company']);

        // Filtros
        if ($request->has('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->has('from_date')) {
            $query->whereDate('started_at', '>=', $request->input('from_date'));
        }

        if ($request->has('to_date')) {
            $query->whereDate('started_at', '<=', $request->input('to_date'));
        }

        // Ordenar por más reciente
        $query->orderBy('started_at', 'desc');

        // Paginación
        $perPage = min($request->input('per_page', 15), 100);
        $shifts = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $shifts->items(),
            'pagination' => [
                'current_page' => $shifts->currentPage(),
                'last_page' => $shifts->lastPage(),
                'per_page' => $shifts->perPage(),
                'total' => $shifts->total(),
            ],
        ]);
    }

    /**
     * Obtener ID de la compañía del usuario autenticado
     */
    private function getCompanyId($user): int
    {
        if ($user->isCompany()) {
            return $user->id;
        }

        if ($user->parent_id) {
            return $user->parent_id;
        }

        return $user->id;
    }
}
