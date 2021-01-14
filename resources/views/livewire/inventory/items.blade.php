<div class="row">
    <div class="col-12">

        <div class="row mb-4">
            <div class="col-12">
                @if(Session::has('success'))
                    <div class="alert alert-success">
                        {{Session::get('success')}}
                    </div>
                @endif
                <input wire:model="search" class="form-control" type="text" placeholder="Search items by name...">
            </div>
        </div>

        <div class="row mb-5">
            <div class="col-12">
                <table class="table table-responsice-md table-bordered">
                    <thead class="thead-light">
                        <tr>
                            <th class="text-center">#</th>
                            <th class="text-center">Name</th>
                            <th class="text-center">Available Qty</th>
                            <th class="text-center">Unit Price</th>
                            <th class="text-center">Reorder Level</th>
                            <th class="text-center">Action(s)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($items as $item)
                        <tr>
                            <td class="text-center">{{ $loop->iteration }}</td>
                            <td>{{ $item->name }}</td>
                            <td class="text-center">{{ $item->inventory_onhand }}</td>
                            <td class="text-right">{{ number_format($item->unit_price, 2) }}</td>
                            <td class="text-center">{{ $item->reorder_level }}</td>
                            <td class="text-center">
                                <a href="{{ route('inventory.edit', $item->id) }}" class="btn btn-success">Edit</a>
                                <a href="{{ route('inventory.delete', $item->id) }}" class="btn btn-danger" onclick = "return confirm('Are you sure?')">Delete</a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="row">
            <div class="col-12 text-center">
                {{ $items->links() }}
            </div>
        </div>

    </div>
</div>
