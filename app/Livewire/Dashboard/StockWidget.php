<?php

namespace App\Livewire\Dashboard;

use App\Models\Product;
use Livewire\Component;
use Illuminate\Support\Facades\Schema;

class StockWidget extends Component
{
    /**
     * Tamaño del widget controlado por el dashboard:
     *  - sm  -> compacto (solo total)
     *  - md  -> con KPIs
     *  - lg  -> con KPIs + listado por producto
     *  - auto -> (default) tratar como md
     */
    public string $size = 'auto';

    /** Cantidad de productos a listar en lg */
    public int $limit = 8;

    protected function hasMinStockColumn(): bool
    {
        return Schema::hasColumn('products', 'min_stock');
    }

    public function mount(string $size = 'auto', int $limit = 8): void
    {
        $this->size  = $size;     // permite <livewire:dashboard.stock-widget size="sm" />
        $this->limit = max(3, min(20, $limit));
    }

    public function render()
    {
        $hasMin = $this->hasMinStockColumn();

        // KPIs seguros
        $totals = Product::selectRaw(
            $hasMin
                ? 'COUNT(*) as total_products, COALESCE(SUM(stock),0) as total_units, SUM(CASE WHEN stock <= min_stock THEN 1 ELSE 0 END) as low_count'
                : 'COUNT(*) as total_products, COALESCE(SUM(stock),0) as total_units, SUM(CASE WHEN stock <= 0 THEN 1 ELSE 0 END) as low_count'
        )->first();

        // Listado por producto (solo para lg)
        $items = [];
        if ($this->showsList()) {
            $select = ['id','name','sku','stock'];
            if ($hasMin) $select[] = 'min_stock';

            $items = Product::query()
                ->select($select)
                ->orderBy('stock', 'asc')   // primero los más bajos
                ->limit($this->limit)
                ->get();
        }

        return view('livewire.dashboard.stock-widget', [
            'hasMin'  => $hasMin,
            'totals'  => $totals,
            'items'   => $items,
        ]);
    }

    public function isSmall(): bool
    {
        return in_array($this->size, ['sm', 'xs'], true);
    }

    public function isMedium(): bool
    {
        return $this->size === 'md' || $this->size === 'auto';
    }

    public function isLarge(): bool
    {
        return $this->size === 'lg';
    }

    protected function showsList(): bool
    {
        return $this->isLarge();
    }
}
