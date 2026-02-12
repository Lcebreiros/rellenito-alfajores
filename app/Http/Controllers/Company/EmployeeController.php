<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Str;
use App\Models\ParkingShift;

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
        $companyId = auth()->user()->rootCompany()?->id ?? auth()->id();

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

        if ($request->filled('has_computer')) {
            $query->where('has_computer', (bool) $request->input('has_computer'));
        }

        // Usamos paginate para disponer de total() en la vista
        $employees = $query->paginate(50);

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
        $this->authorize('create', Employee::class);

        $companyId = auth()->user()->rootCompany()?->id ?? auth()->id();
        $company   = auth()->user()->rootCompany() ?? auth()->user();

        // Normalizar JSON opcional (textareas *_json)
        foreach ([
            'family_group_json' => 'family_group',
            'objectives_json'   => 'objectives',
            'tasks_json'        => 'tasks',
            'schedules_json'    => 'schedules',
            'benefits_json'     => 'benefits',
        ] as $jsonField => $target) {
            if ($request->filled($jsonField)) {
                $decoded = json_decode($request->input($jsonField), true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    $request->merge([$target => $decoded]);
                }
            }
        }

        $data = $this->validateEmployee($request);

        $data['company_id'] = $companyId;
        $data['branch_id']  = $this->resolveBranchId($companyId, $data['branch_id'] ?? null, $company);

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
        $shifts = [];
        if (auth()->user()->hasModule('parking')) {
            $shifts = ParkingShift::where('company_id', $employee->company_id)
                ->where('employee_id', $employee->id)
                ->orderByDesc('started_at')
                ->limit(20)
                ->get();
        }
        return view('company.employees.show', compact('employee','shifts'));
    }

    public function addEvaluation(Request $request, Employee $employee): RedirectResponse
    {
        $this->authorize('update', $employee);
        $data = $request->validate([
            'evaluation' => ['required','string','max:2000'],
        ]);

        $evaluations = $employee->evaluations ?: [];
        $evaluations[] = [
            'text' => $data['evaluation'],
            'by'   => auth()->id(),
            'at'   => now()->toIso8601String(),
        ];
        $employee->update(['evaluations' => $evaluations]);

        return back()->with('success', 'Evaluación agregada.');
    }

    public function addNote(Request $request, Employee $employee): RedirectResponse
    {
        $this->authorize('update', $employee);
        $data = $request->validate([
            'note' => ['required','string','max:2000'],
        ]);

        $notes = $employee->notes ?: [];
        $notes[] = [
            'text' => $data['note'],
            'by'   => auth()->id(),
            'at'   => now()->toIso8601String(),
        ];
        $employee->update(['notes' => $notes]);

        return back()->with('success', 'Nota agregada.');
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

        // Normalizar JSON opcional (textareas *_json)
        foreach ([
            'family_group_json' => 'family_group',
            'objectives_json'   => 'objectives',
            'tasks_json'        => 'tasks',
            'schedules_json'    => 'schedules',
            'benefits_json'     => 'benefits',
        ] as $jsonField => $target) {
            if ($request->filled($jsonField)) {
                $decoded = json_decode($request->input($jsonField), true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    $request->merge([$target => $decoded]);
                }
            } else if ($request->has($jsonField) && $request->input($jsonField) === '') {
                // si se envía vacío explícitamente, borra el campo
                $request->merge([$target => null]);
            }
        }

        $data = $this->validateEmployee($request, $employee->id);
        $companyId = auth()->user()->rootCompany()?->id ?? auth()->id();
        $company   = auth()->user()->rootCompany() ?? auth()->user();

        $data['branch_id'] = $this->resolveBranchId($companyId, $data['branch_id'] ?? $employee->branch_id, $company);

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
        $companyId = auth()->user()->rootCompany()?->id ?? auth()->id();

        $rules = [
            'branch_id' => ['nullable', Rule::exists('branches','id')->where('company_id', $companyId)],
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
            'salary'       => ['nullable','numeric','min:0'],
            'medical_coverage' => ['nullable','string','max:255'],
            'has_computer' => ['nullable','boolean'],
        ];

        return $request->validate($rules);
    }

    /**
     * Asegura que branch_id no quede nulo; si no hay sucursal se crea una por defecto.
     */
    protected function resolveBranchId(int $companyId, ?int $branchId, $companyUser): int
    {
        if ($branchId) {
            return $branchId;
        }

        // Buscar la primera sucursal existente de la empresa.
        $existing = Branch::where('company_id', $companyId)->value('id');
        if ($existing) {
            return (int) $existing;
        }

        // Crear sucursal por defecto (empresa sin sucursales).
        $name = $companyUser->business_name ?? $companyUser->name ?? 'Casa Central';

        $branch = Branch::create([
            'company_id' => $companyId,
            'name'       => $name,
            'slug'       => Str::slug($name) . '-' . Str::random(6),
            'is_active'  => true,
        ]);

        return (int) $branch->id;
    }
}
