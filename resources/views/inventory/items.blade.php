@extends('master')

@section('body')

<div class="row">
    <div class="col-sm-12">
        <div class="page-title-box">
            <div class="row d-flex justify-content-end">
                <div class="col-12">
                    &nbsp
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card m-b-30">
            <div class="card-body">

                <div class="row">
                    <div class="col-md-12 text-right">
                        <a href="{{ route('inventory.create') }}" class="btn btn-primary">Add Item</a>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-lg-12">
                        @livewire('inventory.items')
                    </div>
                </div>

            </div>
        </div>
    </div><!-- end col -->
</div>
@endsection
