<?php

namespace App\Http\Livewire\Inventory;

use Livewire\Component;
use Livewire\WithPagination;
use App\InventoryItem;

class Items extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $search = '';

    protected $queryString = ['search'];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function render()
    {
        return view('livewire.inventory.items', [
            'items' => InventoryItem::where('name', 'like', '%'.$this->search.'%')->orderBy('created_at', 'asc')->paginate(20),
        ]);
    }
}
