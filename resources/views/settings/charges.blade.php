@extends('master')

@section('body')
<!-- Page-Title -->
<div class="row">
    <div class="col-sm-12">
        <div class="page-title-box">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h4 class="page-title m-0">Charges</h4>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card m-b-30">
            <div class="card-body">
                <processingfee></processingfee>
            </div>
        </div>
        <div class="card m-b-30">
            <div class="card-body">
                <withdrawalsettings></withdrawalsettings>
            </div>
        </div>
        <div class="card m-b-30">
            <div class="card-body">
                <banks></banks>
            </div>
        </div>
    </div>
</div>

@endsection


@section('js')

@endsection
