<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Product;
use App\Services\StockService;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class StockDiscountModal extends Component
{
    public $showModal = false;
    public $product = null;
    public $productId = null;
    public $productName = '';
    public $currentStock = 0;
    public $quantity = 1;
    public $adjustmentDate;
    public $notes = '';

    protected $listeners = ['openDiscountModal'];

    protected function rules()
    {
        return [
            'quantity' => [
                'required',
                'integer',
                'min:1',
                'max:' . $this->currentStock
            ],
            'adjustmentDate' => 'required|date|before_or_equal:today',
            'notes' => 'required|string|max:500|min:3',
        ];
    }

    protected $messages = [
        'quantity.required' => 'La cantidad es obligatoria',
        'quantity.integer' => 'La cantidad debe ser un número entero',
        'quantity.min' => 'La cantidad mínima es 1',
        'quantity.max' => 'No puede descontar más de :max unidades (stock actual)',
        'adjustmentDate.required' => 'La fecha es obligatoria',
        'adjustmentDate.date' => 'La fecha no es válida',
        'adjustmentDate.before_or_equal' => 'La fecha no puede ser futura',
        'notes.required' => 'Debe especificar un motivo',
        'notes.string' => 'El motivo debe ser texto',
        'notes.max' => 'El motivo no puede exceder 500 caracteres',
        'notes.min' => 'El motivo debe tener al menos 3 caracteres',
    ];

    public function mount()
    {
        $this->adjustmentDate = now()->format('Y-m-d');
    }

    public function openDiscountModal($productId)
    {
        $this->reset(['quantity', 'notes']);
        $this->adjustmentDate = now()->format('Y-m-d');

        $this->product = Product::find($productId);

        if (!$this->product) {
            session()->flash('error', 'Producto no encontrado');
            return;
        }

        $this->productId = $this->product->id;
        $this->productName = $this->product->name;
        $this->currentStock = (int) $this->product->stock;

        if ($this->currentStock <= 0) {
            session()->flash('error', 'No hay stock disponible para descontar');
            return;
        }

        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->reset(['product', 'productId', 'productName', 'currentStock', 'quantity', 'notes']);
        $this->resetValidation();
    }

    public function discount()
    {
        $this->validate();

        try {
            DB::transaction(function () {
                // Recargar el producto desde la base de datos
                $product = Product::findOrFail($this->productId);

                $stockService = app(StockService::class);

                // Descontar stock (cantidad negativa)
                $stockService->adjust(
                    $product,
                    -1 * $this->quantity,
                    'manual_discount',
                    null
                );

                // Actualizar la nota del ajuste más reciente con la fecha y nota
                $latestAdjustment = \App\Models\StockAdjustment::query()
                    ->where('product_id', $this->productId)
                    ->latest('id')
                    ->first();

                if ($latestAdjustment) {
                    $latestAdjustment->update([
                        'notes' => $this->notes,
                        'created_at' => Carbon::parse($this->adjustmentDate),
                    ]);
                }
            });

            session()->flash('ok', "Se descontaron {$this->quantity} unidades de {$this->productName}");

            $this->dispatch('stock-updated');
            $this->closeModal();

            // Recargar la página para reflejar cambios
            $this->dispatch('refresh-page');

        } catch (\Exception $e) {
            session()->flash('error', 'Error al descontar stock: ' . $e->getMessage());
            \Log::error('Error en descuento de stock', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    public function render()
    {
        return view('livewire.stock-discount-modal');
    }
}
