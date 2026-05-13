<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Product;
use App\Services\StockService;
use App\Services\UnitConverter;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class StockDiscountModal extends Component
{
    public $showModal = false;
    public $product = null;
    public $productId = null;
    public $productName = '';
    public float $currentStock = 0.0;
    public $quantity = '';
    public $adjustmentDate;
    public $notes = '';

    // Soporte para productos por peso (kg/g)
    public string $productUnit = 'u';
    public bool $isWeightProduct = false;
    public string $discountUnit = 'u'; // unidad de entrada del usuario

    protected $listeners = ['openDiscountModal'];

    protected function rules(): array
    {
        $maxQty = $this->maxInputQty();

        if ($this->isWeightProduct) {
            return [
                'quantity'       => ['required', 'numeric', 'min:0.001', "max:{$maxQty}"],
                'adjustmentDate' => 'required|date|before_or_equal:today',
                'notes'          => 'required|string|max:500|min:3',
            ];
        }

        return [
            'quantity'       => ['required', 'integer', 'min:1', "max:{$maxQty}"],
            'adjustmentDate' => 'required|date|before_or_equal:today',
            'notes'          => 'required|string|max:500|min:3',
        ];
    }

    protected $messages = [
        'quantity.required' => 'La cantidad es obligatoria',
        'quantity.numeric'  => 'La cantidad debe ser un número',
        'quantity.integer'  => 'La cantidad debe ser un número entero',
        'quantity.min'      => 'La cantidad debe ser mayor a cero',
        'quantity.max'      => 'No puede descontar más del stock disponible',
        'adjustmentDate.required'        => 'La fecha es obligatoria',
        'adjustmentDate.date'            => 'La fecha no es válida',
        'adjustmentDate.before_or_equal' => 'La fecha no puede ser futura',
        'notes.required' => 'Debe especificar un motivo',
        'notes.string'   => 'El motivo debe ser texto',
        'notes.max'      => 'El motivo no puede exceder 500 caracteres',
        'notes.min'      => 'El motivo debe tener al menos 3 caracteres',
    ];

    public function mount(): void
    {
        $this->adjustmentDate = now()->format('Y-m-d');
    }

    public function openDiscountModal(int|string $productId): void
    {
        $this->reset(['quantity', 'notes']);
        $this->adjustmentDate = now()->format('Y-m-d');

        $this->product = Product::find($productId);

        if (!$this->product) {
            session()->flash('error', 'Producto no encontrado');
            return;
        }

        $this->productId      = $this->product->id;
        $this->productName    = $this->product->name;
        $this->currentStock   = (float) $this->product->stock;
        $this->productUnit    = $this->product->unit ?? 'u';
        $this->isWeightProduct = in_array($this->productUnit, ['g', 'kg']);
        // Para kg mostramos el input en gramos por defecto (más natural para pesar)
        $this->discountUnit   = $this->isWeightProduct ? 'g' : $this->productUnit;

        if ($this->currentStock <= 0.0) {
            session()->flash('error', 'No hay stock disponible para descontar');
            return;
        }

        $this->showModal = true;
    }

    public function updatedDiscountUnit(): void
    {
        $this->quantity = '';
        $this->resetValidation('quantity');
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->reset(['product', 'productId', 'productName', 'currentStock', 'quantity', 'notes',
                       'productUnit', 'isWeightProduct', 'discountUnit']);
        $this->resetValidation();
    }

    public function discount(): void
    {
        $this->validate();

        try {
            DB::transaction(function () {
                $product = Product::findOrFail($this->productId);
                $stockService = app(StockService::class);

                // Convertir la cantidad a la unidad nativa del producto
                $qtyInProductUnit = $this->quantityInProductUnit();

                $stockService->adjust($product, -1 * $qtyInProductUnit, 'manual_discount', null);

                $latestAdjustment = \App\Models\StockAdjustment::query()
                    ->where('product_id', $this->productId)
                    ->latest('id')
                    ->first();

                if ($latestAdjustment) {
                    $latestAdjustment->update([
                        'notes'      => $this->notes,
                        'created_at' => Carbon::parse($this->adjustmentDate),
                    ]);
                }
            });

            $qty = $this->isWeightProduct
                ? number_format((float)$this->quantity, 0, ',', '.') . ' ' . $this->discountUnit
                : number_format((int)$this->quantity, 0, ',', '.') . ' u';

            session()->flash('ok', "Se descontaron {$qty} de {$this->productName}");

            $this->dispatch('stock-updated');
            $this->closeModal();
            $this->dispatch('refresh-page');

        } catch (\Exception $e) {
            session()->flash('error', 'Error al descontar stock: ' . $e->getMessage());
            \Log::error('Error en descuento de stock', [
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);
        }
    }

    // Cantidad en unidad nativa del producto (para pasar a StockService)
    private function quantityInProductUnit(): float
    {
        $qty = (float) $this->quantity;

        if ($this->isWeightProduct && $this->discountUnit !== $this->productUnit) {
            $factor = UnitConverter::factorToBase($this->discountUnit, $this->productUnit);
            return $qty * $factor;
        }

        return $qty;
    }

    // Máximo que puede ingresar el usuario en la unidad de entrada
    private function maxInputQty(): float
    {
        if (!$this->isWeightProduct || $this->discountUnit === $this->productUnit) {
            return $this->currentStock;
        }

        // Convertir currentStock (en productUnit) a discountUnit
        $factor = UnitConverter::factorToBase($this->productUnit, $this->discountUnit);
        return $this->currentStock * $factor;
    }

    public function render()
    {
        return view('livewire.stock-discount-modal');
    }
}
