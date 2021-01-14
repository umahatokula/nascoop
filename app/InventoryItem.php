<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class InventoryItem extends Model
{
    protected $fillable = ['name', 'inventory_received', 'minimum_required', 'unit_price', 'description', 'sku', 'inventory_onhand',
'reorder_level'];
}
