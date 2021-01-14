<form wire:submit.prevent="save">

    <div class="form-group row"><label for="fname" class="col-sm-3 col-form-label">Item Name</label>
        <div class="col-sm-9">
            <input class="form-control" type="text" wire:model.defer="name">
            @error('name') <span class="error">{{ $message }}</span> @enderror
        </div>
    </div>

    <div class="form-group row"><label for="fname" class="col-sm-3 col-form-label">SKU</label>
        <div class="col-sm-9">
            <input class="form-control" type="text" wire:model.defer="sku">
            @error('sku') <span class="error">{{ $message }}</span> @enderror
        </div>
    </div>

    <div class="form-group row"><label for="lname" class="col-sm-3 col-form-label">Quantity</label>
        <div class="col-sm-9">
            <input class="form-control" type="number" wire:model.defer="inventory_received">
            @error('inventory_received') <span class="error">{{ $message }}</span> @enderror
        </div>
    </div>

    <div class="form-group row"><label for="phone" class="col-sm-3 col-form-label">Reorder Level</label>
        <div class="col-sm-9">
            <input class="form-control" type="number" wire:model.defer="reorder_level">
            @error('reorder_level') <span class="error">{{ $message }}</span> @enderror
        </div>
    </div>

    <div class="form-group row"><label for="email" class="col-sm-3 col-form-label">Unit Price</label>
        <div class="col-sm-9">
            <input class="form-control" type="number" wire:model.defer="unit_price">
            @error('unit_price') <span class="error">{{ $message }}</span> @enderror
        </div>
    </div>

    <div class="form-group row"><label for="ref" class="col-sm-3 col-form-label">Description</label>
        <div class="col-sm-9">
            <input class="form-control" type="text" wire:model.defer="description">
            @error('description') <span class="error">{{ $message }}</span> @enderror
        </div>
    </div>
    
    <div class="form-group row"><label for="coop_no" class="col-sm-3 col-form-label">&nbsp </label>
        <div class="col-sm-9">
            <button class="btn btn-primary">Edit</button>
        </div>
    </div>

</form>
