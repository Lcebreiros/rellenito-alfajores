<?php

namespace App\Http\Livewire\Company;

use Livewire\Component;
use App\Services\BranchService;
use App\DTOs\CreateBranchDTO;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Exception;

class BranchCreate extends Component
{
    // Campos de Branch
    public $name = '';
    public $address = '';
    public $phone = '';
    public $contact_email = '';

    // Campos de User (representante de la Branch)
    public $email = '';
    public $password = '';
    public $password_confirmation = '';
    public $user_limit = null;
    public $is_active = true;

    // Para master users
    public $company_id = '';
    public $companies = [];

    // Estados
    public $loading = false;
    public $showSuccess = false;

    protected $rules = [
        'name' => 'required|string|max:255',
        'address' => 'nullable|string|max:500',
        'phone' => 'nullable|string|max:50',
        'contact_email' => 'nullable|email|max:255',
        'email' => 'required|email|unique:users,email',
        'password' => 'required|string|min:8',
        'password_confirmation' => 'required|string|same:password',
        'user_limit' => 'nullable|integer|min:0',
        'is_active' => 'boolean',
    ];

    protected $messages = [
        'name.required' => 'El nombre de la sucursal es obligatorio.',
        'email.required' => 'El email es obligatorio.',
        'email.email' => 'El email debe tener un formato válido.',
        'email.unique' => 'Este email ya está en uso.',
        'password.required' => 'La contraseña es obligatoria.',
        'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
        'password_confirmation.required' => 'Debe confirmar la contraseña.',
        'password_confirmation.same' => 'Las contraseñas no coinciden.',
    ];

    public function mount()
    {
        $user = auth()->user();
        
        if ($user->isMaster()) {
            $this->companies = User::where('hierarchy_level', User::HIERARCHY_COMPANY)
                                   ->orderBy('name')
                                   ->get()
                                   ->toArray();
            
            // Agregar validación para company_id si es master
            $this->rules['company_id'] = 'required|exists:users,id';
            $this->messages['company_id.required'] = 'Debe seleccionar una empresa.';
        }
    }

    public function updated($propertyName)
    {
        $this->validateOnly($propertyName);
        $this->showSuccess = false;
    }

    public function createBranch()
    {
        $this->loading = true;
        $this->showSuccess = false;

        try {
            // Validar
            $this->validate();

            $user = auth()->user();
            $company = $user->isMaster() 
                ? User::findOrFail($this->company_id)
                : $user;

            // Crear DTO
            $dto = CreateBranchDTO::fromArray([
                'name' => $this->name,
                'email' => $this->email,
                'password' => $this->password,
                'address' => $this->address,
                'phone' => $this->phone,
                'contact_email' => $this->contact_email,
                'user_limit' => $this->user_limit,
                'is_active' => $this->is_active,
            ]);

            // Usar el service
            $result = app(BranchService::class)->createBranch($company, $dto);

            // Limpiar formulario
            $this->resetForm();

            // Mostrar éxito
            $this->showSuccess = true;
            session()->flash('success', 
                "Sucursal '{$result['branch']->name}' creada correctamente. Email de acceso: {$result['user']->email}"
            );

            // Emitir evento para componentes padre
            $this->emit('branchCreated', $result['branch']->id);

        } catch (Exception $e) {
            $this->addError('general', $e->getMessage());
        } finally {
            $this->loading = false;
        }
    }

    protected function resetForm()
    {
        $this->reset([
            'name', 'address', 'phone', 'contact_email',
            'email', 'password', 'password_confirmation', 
            'user_limit', 'company_id'
        ]);
        $this->is_active = true;
    }

    public function render()
    {
        return view('livewire.company.branch-create')
            ->layout('layouts.app');
    }
}