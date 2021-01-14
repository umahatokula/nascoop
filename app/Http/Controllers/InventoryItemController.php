<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class InventoryItemController extends Controller
{
    public function __construct() {
        $this->middleware(['auth']);
    }
    
    public function index() {
        return view('inventory.items');
    }

    public function create() {
        return view('inventory.create');
    }

    public function edit() {
        return view('inventory.edit');
    }
}
