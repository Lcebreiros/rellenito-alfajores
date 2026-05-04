<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\Service;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

class ServiceCatalog extends Component
{
    use WithPagination;

    public string $search = '';
    public string $sort   = 'name_asc';

    public function updatedSearch(): void { $this->resetPage(); }
    public function updatedSort(): void   { $this->resetPage(); }

    #[Computed]
    public function services(): LengthAwarePaginator
    {
        $user  = auth()->user();
        $query = Service::availableFor($user)->active();

        if ($this->search !== '') {
            $term = '%' . $this->search . '%';
            $query->where(fn ($q) => $q->where('name', 'like', $term)
                                       ->orWhere('description', 'like', $term));
        }

        match ($this->sort) {
            'name_desc'   => $query->orderByDesc('name'),
            'price_asc'   => $query->orderBy('price'),
            'price_desc'  => $query->orderByDesc('price'),
            default       => $query->orderBy('name'),
        };

        return $query->paginate(24);
    }

    public function render()
    {
        return view('livewire.service-catalog', [
            'services' => $this->services,
        ]);
    }
}
