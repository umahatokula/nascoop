<?php

namespace App\Http\Livewire\Inventory;

use Livewire\Component;
use App\InventoryItem;

class Create extends Component
{
    public $item;
    public $name;
    public $sku;
    public $inventory_received;
    public $reorder_level = 0;
    public $unit_price;
    public $description;
    public $inventory_onhand;

    protected $rules = [
        'name'               => 'required|string|min:2',
        'inventory_received' => 'required',
        'unit_price'         => 'required',
    ];

    public function save()
    {
        $this->validate();

        InventoryItem::create([
            'name'               => $this->name,
            'sku'                => $this->sku,
            'inventory_received' => $this->inventory_received,
            'inventory_onhand'   => $this->inventory_received,
            'reorder_level'      => $this->reorder_level,
            'unit_price'         => $this->unit_price,
            'description'        => $this->description,
        ]);

        return redirect()->route('inventory.index');
    }

    public function render()
    {
        return view('livewire.inventory.create');
    }
}
