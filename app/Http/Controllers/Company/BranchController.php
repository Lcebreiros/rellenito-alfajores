<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateBranchRequest;
use App\Http\Requests\UpdateBranchRequest;
use App\Services\BranchService;
use App\Models\User;
use App\Models\Branch;
use Illuminate\Http\Request;

class BranchController extends Controller
{
    public function __construct(
        protected BranchService $branchService
    ) {}

    public function index(Request $request)
    {
        $user = auth()->user();
        
        if ($user->isMaster()) {
            $branches = $this->branchService->getBranchesForMaster($request->get('company_id'));
            $companies = User::where('hierarchy_level', User::HIERARCHY_COMPANY)
                             ->orderBy('name')
                             ->get();
            $company = $request->filled('company_id') 
                ? $companies->find($request->get('company_id'))
                : null;
        } else {
            $branches = $this->branchService->getBranchesForCompany($user);
            $companies = null;
            $company = $user;
        }

        $remaining = null;
        if ($company && !is_null($company->branch_limit)) {
            $currentBranches = Branch::where('company_id', $company->id)->count();
            $remaining = max(0, $company->branch_limit - $currentBranches);
        }

        return view('company.branches.index', compact(
            'branches', 
            'company', 
            'remaining', 
            'companies'
        ));
    }

    public function create()
    {
        $user = auth()->user();
        $companies = null;

        if ($user->isMaster()) {
            $companies = User::where('hierarchy_level', User::HIERARCHY_COMPANY)
                             ->orderBy('name')
                             ->get();
        }

        return view('company.branches.create', compact('companies'));
    }

    public function store(CreateBranchRequest $request)
    {
        try {
            $company = $request->getCompany();
            $result = $this->branchService->createBranch($company, $request->toDTO());
            
            return redirect()
                ->route('company.branches.index')
                ->with('success', "Sucursal '{$result['branch']->name}' creada correctamente. Email de acceso: {$result['user']->email}");

        } catch (\Exception $e) {
            return back()
                ->withErrors(['general' => $e->getMessage()])
                ->withInput();
        }
    }

    public function show(Branch $branch)
    {
        $user = auth()->user();

        // Cargar relaciones antes de cualquier autorización
        $branch->load(['user', 'company']);

        // Validar existencia del usuario representante
        if (! $branch->user) {
            abort(404, 'Sucursal sin usuario representante asociado');
        }

        // Autorizar usando el usuario cargado
        if (! $user->canManageUser($branch->user)) {
            abort(403, 'No tienes permisos para ver esta sucursal');
        }
        
        $stats = [
            'total_users' => $branch->users_count,
            'active_users' => $branch->users()->where('is_active', true)->count(),
            'user_limit' => $branch->user_limit,
        ];

        return view('company.branches.show', compact('branch', 'stats'));
    }

    public function edit(Branch $branch)
    {
        $user = auth()->user();

        // Cargar relación
        $branch->load('user');

        if (! $branch->user) {
            abort(404, 'Sucursal sin usuario representante asociado');
        }

        if (! $user->canManageUser($branch->user)) {
            abort(403, 'No tienes permisos para editar esta sucursal');
        }

        return view('company.branches.edit', compact('branch'));
    }

    public function update(UpdateBranchRequest $request, Branch $branch)
    {
        try {
            $this->branchService->updateBranch($branch, $request->toDTO());

            return redirect()
                ->route('company.branches.index')
                ->with('success', 'Sucursal actualizada correctamente');

        } catch (\Exception $e) {
            return back()
                ->withErrors(['general' => $e->getMessage()])
                ->withInput();
        }
    }

    public function destroy(Branch $branch)
    {
        $user = auth()->user();

        // Cargar relación
        $branch->load('user');

        if (! $branch->user) {
            abort(404, 'Sucursal sin usuario representante asociado');
        }

        if (! $user->canManageUser($branch->user)) {
            abort(403, 'No tienes permisos para eliminar esta sucursal');
        }

        try {
            $branchName = $branch->name;
            $this->branchService->deleteBranch($branch);

            return redirect()
                ->route('company.branches.index')
                ->with('success', "Sucursal '{$branchName}' eliminada correctamente");

        } catch (\Exception $e) {
            return back()->withErrors(['general' => $e->getMessage()]);
        }
    }

    public function users(Branch $branch)
    {
        $user = auth()->user();

        // Cargar relaciones
        $branch->load('user');

        if (! $branch->user) {
            abort(404, 'Sucursal sin usuario representante asociado');
        }

        if (! $user->canManageUser($branch->user)) {
            abort(403, 'No tienes permisos para ver los usuarios de esta sucursal');
        }

        // Obtener query de usuarios a través del user representante y paginar
        $usersQuery = $branch->user->children()->where('hierarchy_level', User::HIERARCHY_USER);

        $users = $usersQuery->latest()->paginate(20);

        return view('company.branches.users', compact('branch', 'users'));
    }

    // ===================== RECIBIR PRODUCTOS =====================
    public function receiveProductsForm(Branch $branch)
    {
        $user = auth()->user();
        $branch->load('user', 'company');
        abort_unless($branch->user && $user->canManageUser($branch->user), 403);

        // Catálogo de la empresa (sin duplicar por sucursal)
        $products = \App\Models\Product::query()
            ->withoutGlobalScope('byUser')
            ->where('company_id', $branch->company_id)
            ->active()
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('company.branches.receive-products', compact('branch', 'products'));
    }

    public function receiveProductsStore(\Illuminate\Http\Request $request, Branch $branch)
    {
        $user = auth()->user();
        $branch->load('user', 'company');
        abort_unless($branch->user && $user->canManageUser($branch->user), 403);

        $data = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|integer|exists:products,id',
            'items.*.qty' => 'required|numeric|min:0',
        ]);

        // Validar que todos los productos pertenezcan a la empresa de la sucursal
        $productIds = collect($data['items'])->pluck('product_id')->unique()->values();
        $validIds = \App\Models\Product::query()
            ->withoutGlobalScope('byUser')
            ->where('company_id', $branch->company_id)
            ->whereIn('id', $productIds)
            ->pluck('id')
            ->all();

        if (count($validIds) !== $productIds->count()) {
            return back()->withErrors(['items' => 'Hay productos que no pertenecen a la empresa.'])->withInput();
        }

        // Actualizar stock por sucursal (ProductLocation)
        foreach ($data['items'] as $row) {
            $pid = (int) $row['product_id'];
            $qty = (float) $row['qty'];
            // Importante: ProductLocation.branch_id referencia al User que representa la sucursal (admin), no al modelo Branch
            $branchUserId = (int) ($branch->user?->id ?? 0);
            if ($branchUserId <= 0) {
                return back()->withErrors(['branch' => 'Sucursal sin usuario representante válido.'])->withInput();
            }
            \App\Models\ProductLocation::updateStock($pid, $branchUserId, $qty);
        }

        return redirect()->route('company.branches.index')->with('success', 'Stock recibido para la sucursal.');
    }
}
