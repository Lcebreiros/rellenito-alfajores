<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class EmployeeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        // opcional: $this->authorizeResource(Employee::class, 'employee');
    }

    /**
     * Mostrar listado paginado y filtrable.
     */
    public function index(Request $request): View
    {
        $companyId = auth()->user()->company_id;

        $query = Employee::with('branch')
            ->where('company_id', $companyId)
            ->orderBy('id', 'desc');

        // filtros comunes
        if ($branch = $request->input('branch_id')) {
            $query->where('branch_id', $branch);
        }

        if ($role = $request->input('role')) {
            $query->where('role', $role);
        }

        if ($q = trim($request->input('q', ''))) {
            $query->where(function ($sub) use ($q) {
                $sub->where('first_name', 'like', "%{$q}%")
                    ->orWhere('last_name', 'like', "%{$q}%")
                    ->orWhere('dni', 'like', "%{$q}%")
                    ->orWhere('email', 'like', "%{$q}%");
            });
        }

        // cursorPaginate es eficiente para grandes volúmenes
        $employees = $query->cursorPaginate(50);

        return view('company.employees.index', compact('employees'));
    }

    /**
     * Mostrar formulario de creación.
     */
    public function create(): View
    {
        // si querés roles fijos los podés cargar desde config('hr.roles')
        $roles = config('hr.roles', ['Empleado', 'Supervisor', 'Gerente']);
        return view('company.employees.create', compact('roles'));
    }

    /**
     * Almacenar nuevo empleado.
     */
    public function store(Request $request): RedirectResponse
    {
        $companyId = auth()->user()->company_id;

        $data = $this->validateEmployee($request);

        $data['company_id'] = $companyId;

        // archivos
        if ($request->hasFile('photo')) {
            $data['photo_path'] = $request->file('photo')->store('employees/photos', 'public');
        }

        if ($request->hasFile('contract_file')) {
            $data['contract_file_path'] = $request->file('contract_file')->store('employees/contracts', 'public');
        }

        // asegurarse que campos JSON estén presentes como arrays si vienen
        foreach (['family_group','evaluations','objectives','tasks','schedules','benefits','salary'] as $k) {
            if ($request->has($k) && is_array($request->input($k))) {
                $data[$k] = $request->input($k);
            }
        }

        $employee = Employee::create($data);

        return redirect()->route('company.employees.show', $employee)
            ->with('success', 'Empleado creado correctamente.');
    }

    /**
     * Mostrar ficha del empleado.
     */
    public function show(Employee $employee): View
    {
        $this->authorize('view', $employee); // crea EmployeePolicy
        $employee->load(['branch','company']);
        return view('company.employees.show', compact('employee'));
    }

    /**
     * Formulario de edición.
     */
    public function edit(Employee $employee): View
    {
        $this->authorize('update', $employee);
        $roles = config('hr.roles', ['Empleado', 'Supervisor', 'Gerente']);
        return view('company.employees.edit', compact('employee','roles'));
    }

    /**
     * Actualizar ficha.
     */
    public function update(Request $request, Employee $employee): RedirectResponse
    {
        $this->authorize('update', $employee);

        $data = $this->validateEmployee($request, $employee->id);

        // archivos: reemplazo seguro
        if ($request->hasFile('photo')) {
            if ($employee->photo_path) {
                Storage::disk('public')->delete($employee->photo_path);
            }
            $data['photo_path'] = $request->file('photo')->store('employees/photos', 'public');
        }

        if ($request->hasFile('contract_file')) {
            if ($employee->contract_file_path) {
                Storage::disk('public')->delete($employee->contract_file_path);
            }
            $data['contract_file_path'] = $request->file('contract_file')->store('employees/contracts', 'public');
        }

        // normalizar arrays
        foreach (['family_group','evaluations','objectives','tasks','schedules','benefits','salary'] as $k) {
            if ($request->has($k)) {
                $data[$k] = $request->input($k);
            }
        }

        $employee->update($data);

        return redirect()->route('company.employees.show', $employee)
            ->with('success', 'Ficha actualizada.');
    }

    /**
     * Eliminar (soft delete).
     */
    public function destroy(Employee $employee): RedirectResponse
    {
        $this->authorize('delete', $employee);

        // borrar archivos asociados si querés (opcional)
        if ($employee->photo_path) {
            Storage::disk('public')->delete($employee->photo_path);
        }
        if ($employee->contract_file_path) {
            Storage::disk('public')->delete($employee->contract_file_path);
        }

        $employee->delete();

        return redirect()->route('company.employees.index')->with('success', 'Empleado eliminado.');
    }

    /**
     * Endpoint para importaciones masivas (delegar a job).
     * Sube un CSV y despacha un job que procese en background.
     */
    public function bulkImport(Request $request): RedirectResponse
    {
        $this->authorize('create', Employee::class);

        $request->validate([
            'file' => 'required|file|mimes:csv,txt|max:10240', // 10MB por ejemplo
        ]);

        // Guardar temporal y despachar job (debes crear el Job ImportEmployees)
        $path = $request->file('file')->store('imports', 'local');

        // dispatch(new \App\Jobs\ImportEmployeesJob($path, auth()->user()->company_id));
        // si no tenés colas, podés procesar con chunk en sync (no recomendado)

        // placeholder: notificá al usuario
        return back()->with('success', 'Archivo subido. La importación se procesará en segundo plano si tenés colas configuradas.');
    }

    /**
     * Validador central (podés extraer a FormRequest).
     */
    protected function validateEmployee(Request $request, int $employeeId = null): array
    {
        $rules = [
            'branch_id' => ['nullable','exists:branches,id'],
            'first_name' => ['required','string','max:120'],
            'last_name'  => ['required','string','max:120'],
            'email'      => ['nullable','email','max:255'],
            'dni'        => ['nullable','string','max:50', Rule::unique('employees','dni')->ignore($employeeId)],
            'address'    => ['nullable','string','max:1000'],
            'start_date' => ['nullable','date'],
            'role'       => ['nullable','string','max:100'],
            'contract_type' => ['nullable','string','max:100'],
            'photo'      => ['nullable','image','max:2048'],
            'contract_file' => ['nullable','file','mimes:pdf,doc,docx|max:5120'],
            'family_group' => ['nullable','array'],
            'evaluations'  => ['nullable','array'],
            'objectives'   => ['nullable','array'],
            'tasks'        => ['nullable','array'],
            'schedules'    => ['nullable','array'],
            'benefits'     => ['nullable','array'],
            'salary'       => ['nullable','array'],
            'medical_coverage' => ['nullable','string','max:255'],
            'has_computer' => ['nullable','boolean'],
        ];

        return $request->validate($rules);
    }
}
