<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\Product;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class ProductCatalog extends Component
{
    use WithPagination;

    #[Url(as: 'q', except: '')]
    public string $search = '';

    #[Url(as: 'filter', except: 'all')]
    public string $filter = 'all'; // all | favorites | top

    #[Url(as: 'sort', except: 'name_asc')]
    public string $sort = 'name_asc';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedFilter(): void
    {
        $this->resetPage();
    }

    public function updatedSort(): void
    {
        $this->resetPage();
    }

    public function toggleFavorite(int $productId): void
    {
        $userId = auth()->id();

        $exists = \DB::table('product_favorites')
            ->where('user_id', $userId)
            ->where('product_id', $productId)
            ->exists();

        if ($exists) {
            \DB::table('product_favorites')
                ->where('user_id', $userId)
                ->where('product_id', $productId)
                ->delete();
        } else {
            \DB::table('product_favorites')->insert([
                'user_id'    => $userId,
                'product_id' => $productId,
                'created_at' => now(),
            ]);
        }

        // Si estamos en vista favoritos y se quitó, puede que desaparezca de la lista
        unset($this->products);
    }

    #[On('order-finalized')]
    public function onOrderFinalized(): void
    {
        // refrescar top-sellers si se acaba de finalizar una venta
        unset($this->products);
    }

    #[Computed]
    public function products(): LengthAwarePaginator
    {
        $user = auth()->user();

        $query = $user->isMaster()
            ? Product::withoutGlobalScope('byUser')
            : Product::availableFor($user);

        $query->active();

        // Búsqueda
        if ($this->search !== '') {
            $term = '%' . $this->search . '%';
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', $term)
                  ->orWhere('sku', 'like', $term)
                  ->orWhere('category', 'like', $term);
            });
        }

        // Filtros
        $userId = auth()->id();
        match ($this->filter) {
            'favorites' => $query->whereExists(function ($q) use ($userId) {
                $q->from('product_favorites')
                  ->whereColumn('product_favorites.product_id', 'products.id')
                  ->where('product_favorites.user_id', $userId);
            }),
            'top' => $query->withCount(['orderItems as times_sold' => function ($q) {
                $q->whereHas('order', fn($o) => $o->whereNotNull('sold_at'));
            }])->having('times_sold', '>', 0),
            default => null,
        };

        // Ordenamiento
        match ($this->sort) {
            'name_asc'    => $query->orderBy('name'),
            'name_desc'   => $query->orderByDesc('name'),
            'price_asc'   => $query->orderBy('price'),
            'price_desc'  => $query->orderByDesc('price'),
            'newest'      => $query->orderByDesc('created_at'),
            'oldest'      => $query->orderBy('created_at'),
            'top'         => $query->orderByDesc(
                \DB::raw('(SELECT COUNT(*) FROM order_items oi
                           JOIN orders o ON o.id = oi.order_id
                           WHERE oi.product_id = products.id
                           AND o.sold_at IS NOT NULL)')
            ),
            default       => $query->orderBy('name'),
        };

        // Si el filtro es top y el sort también, la subquery ya está
        if ($this->filter === 'top' && $this->sort !== 'top') {
            // reordenar por times_sold cuando el filtro es top pero no el sort
            $query->reorder()->orderByDesc('times_sold');
        }

        return $query->paginate(24);
    }

    #[Computed]
    public function favoriteIds(): array
    {
        return \DB::table('product_favorites')
            ->where('user_id', auth()->id())
            ->pluck('product_id')
            ->toArray();
    }

    public function render()
    {
        return view('livewire.product-catalog', [
            'products'    => $this->products,
            'favoriteIds' => $this->favoriteIds,
        ]);
    }
}
