<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class WelcomeModal extends Component
{
    public $showModal = false;
    public $currentStep = 1;

    /** @var array<int, array<string, mixed>> */
    public $steps = [
        1 => ['image'=>null,                'title'=>'Bienvenido a Gestior', 'description'=>'¿Qué tal un tour por lo que podés hacer?', 'icon'=>'M3 7h18M3 12h12M3 17h18'],
        2 => ['image'=>'dashboard.png',     'title'=>'Dashboard',            'description'=>'Tu centro de visualización con un resumen de estadísticas.', 'icon'=>'M3 7h18M3 12h12M3 17h18'],
        3 => ['image'=>'crear-pedido.png',  'title'=>'Crear pedido',         'description'=>'Agregá productos y armá pedidos rápidamente.', 'icon'=>'M8 7h8M6 11h12M4 15h16'],
        4 => ['image'=>'pedidos.png',       'title'=>'Pedidos',              'description'=>'Listado de pedidos, detalle y acceso a comprobantes.', 'icon'=>'M7 7h10v10H7z'],
        5 => ['image'=>'productos.png',     'title'=>'Productos',            'description'=>'Gestioná tus productos y existencias en un mismo lugar.', 'icon'=>'M4 7h16M4 12h16M4 17h16'],
        6 => ['image'=>'stock.png',         'title'=>'Stock',                'description'=>'Reporte completo, alertas de baja y descarga del informe.', 'icon'=>'M5 19l7-7 7 7'],
        7 => ['image'=>'calcular-costos.png','title'=>'Calcular costos',     'description'=>'Registrá insumos, recetas y analizá costos por rendimiento.', 'icon'=>'M12 6v12M6 12h12'],
        8 => ['image'=>null,                'title'=>'¡Listo!',              'description'=>'Terminamos el recorrido. ¿Comenzamos?', 'icon'=>'M5 13l4 4L19 7'],
    ];

    public $totalSteps = 8;

    // ⚙️ Diagnóstico
    public $debugInfo = [
        'auth' => false,
        'user_id' => null,
        'has_seen_welcome' => null,
        'forced_by_query' => false,
    ];

    public function mount(): void
    {
        $this->totalSteps = count($this->steps);

        $user = Auth::user();
        $this->debugInfo['auth'] = (bool) $user;
        $this->debugInfo['user_id'] = $user?->id;
        $this->debugInfo['has_seen_welcome'] = $user?->has_seen_welcome;

        // 1) Regla normal: mostrar si hay usuario y NO lo vio aún
        $this->showModal = $user && !$user->has_seen_welcome;

        // 2) Forzar con query: ?welcome=1 (para aislar issues de Alpine/estilos/z-index)
        if (request()->boolean('welcome')) {
            $this->showModal = true;
            $this->debugInfo['forced_by_query'] = true;
        }

        // Log opcional
        // logger()->info('WelcomeModal mount', ['debug' => $this->debugInfo, 'showModal'=>$this->showModal]);
    }

    public function nextStep(): void
    {
        if ($this->currentStep < $this->totalSteps) {
            $this->currentStep++;
        } else {
            $this->completeWelcome();
        }
    }

    public function previousStep(): void
    {
        if ($this->currentStep > 1) $this->currentStep--;
    }

    public function completeWelcome(): void
    {
        if ($user = Auth::user()) {
            $user->forceFill(['has_seen_welcome' => true])->save();
        }
        $this->showModal = false;
        $this->dispatch('welcome-completed');
    }

    public function skipWelcome(): void
    {
        // Si no querés marcar como visto al saltar, reemplazá por: $this->showModal = false; return;
        $this->completeWelcome();
    }

    public function render()
    {
        return view('livewire.welcome-modal');
    }
}
