<?php

namespace App\Http\Livewire\Inventory;

use Livewire\Component;
use App\InventoryItem;

class Edit extends Component
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

    public function mount($itemId) {
        $this->item = InventoryItem::find($itemId);
    }

    public function save()
    {
        $this->validate();

        InventoryItem::create([
            'name'               => $this->item->name,
            'sku'                => $this->item->sku,
            'inventory_received' => $this->item->inventory_received,
            'inventory_onhand'   => $this->item->inventory_received,
            'reorder_level'      => $this->item->reorder_level,
            'unit_price'         => $this->item->unit_price,
            'description'        => $this->item->description,
        ]);

        return redirect()->route('inventory.index');
    }

    public function render()
    {
        return view('livewire.inventory.edit');
    }
}
