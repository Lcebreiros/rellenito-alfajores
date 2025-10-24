<?php
declare(strict_types=1);

namespace App\Livewire;

use App\Models\Order;
use App\Models\Service as ServiceModel;
use App\Services\OrderService;
use DomainException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Livewire\Component;

class ServiceCard extends Component
{
    public ?ServiceModel $service = null;
    public ?int $serviceId = null;

    public bool $isActive = false;
    public bool $isAdding = false;

    public string $displayMode = 'card';
    public string $buttonText = 'Agregar';
    public string $buttonClass = '';

    public function mount(
        mixed $service = null,
        ?int $serviceId = null,
        string $displayMode = 'card',
        string $buttonText = 'Agregar',
        string $buttonClass = ''
    ): void {
        $this->displayMode = $displayMode;
        $this->buttonText  = $buttonText;
        $this->buttonClass = $buttonClass;

        if ($service instanceof ServiceModel) {
            $this->service   = $service;
            $this->serviceId = $service->id;
        } elseif ($serviceId !== null) {
            $this->serviceId = $serviceId;
        } elseif (is_int($service)) { // retrocompat
            $this->serviceId = $service;
        }

        $this->refreshService();
    }

    private function refreshService(): void
    {
        if (!$this->serviceId) {
            $this->service = null;
            $this->isActive = false;
            $this->qty = 1;
            return;
        }

        if (!$this->service || $this->service->id !== $this->serviceId) {
            $this->service = ServiceModel::find($this->serviceId);
        } else {
            $this->service->refresh();
        }

        $this->isActive = (bool)($this->service?->is_active ?? false);
    }

    private function getCurrentDraftId(): int
    {
        $draftId = (int) session('draft_order_id', 0);
        if ($draftId) return $draftId;

        $draft = Order::create(); // STATUS_DRAFT por defecto
        session(['draft_order_id' => $draft->id]);
        return (int) $draft->id;
    }

    public function addOne(): void
    {
        if ($this->isAdding) return;
        $this->isAdding = true;

        try {
            if (!$this->serviceId) {
                $this->dispatch('notify', type:'error', message:'Servicio inválido.');
                return;
            }

            $this->refreshService();
            if (!$this->service) {
                $this->dispatch('notify', type:'error', message:'Servicio no encontrado.');
                return;
            }
            if (!$this->isActive) {
                $this->dispatch('notify', type:'error', message:'Servicio inactivo.');
                return;
            }

            $draftId = $this->getCurrentDraftId();
            $orders  = app(OrderService::class);
            $orders->addService($draftId, $this->serviceId, 1);

            $this->dispatch('item-added-to-order', orderId:$draftId);
            $this->dispatch('order-updated');

            $this->dispatch('notify', type:'success', message:'Servicio agregado');

        } catch (ModelNotFoundException) {
            $this->dispatch('notify', type:'error', message:'Servicio no encontrado.');
        } catch (DomainException $e) {
            $this->dispatch('notify', type:'error', message:$e->getMessage());
        } catch (\Throwable $e) {
            \Log::error('Error adding service to order', [
                'message'    => $e->getMessage(),
                'service_id' => $this->serviceId,
                'qty'        => $this->qty,
                'draft_id'   => session('draft_order_id'),
            ]);
            $this->dispatch('notify', type:'error', message:'Error al agregar servicio.');
        } finally {
            $this->isAdding = false;
        }
    }

    public function render()
    {
        return view('livewire.service-card');
    }
}
