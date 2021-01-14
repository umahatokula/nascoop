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

                <div class="row mt-3">
                    <div class="col-lg-12">
                        <h4 class="mb-5">Edit Inventory Item</h4>
                        <livewire:inventory.edit />
                    </div>
                </div>

            </div>
        </div>
    </div><!-- end col -->
</div>
@endsection
