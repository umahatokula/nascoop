@extends('master')

@section('body')
<!-- Page-Title -->
<div class="row">
    <div class="col-sm-12">
        <div class="page-title-box">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h4 class="page-title m-0">Add Center</h4>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card m-b-30">
            <div class="card-body">
            @can('create centre')

            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {!! Form::open(['route' => 'ippis.oagfBalancesPost', 'method' => 'POST']) !!}
                @foreach($centers as $center)
                <div class="form-group row"><label for="code" class="col-sm-2 col-form-label">{{$center->name}}</label>
                    <div class="col-sm-10">
                        {!! Form::text('centers[]', $center->id, ['class' => 'form-control', 'id' => 'code']) !!}
                    </div>
                </div>
                <div class="form-group row"><label for="transacting_bank_ledger_no" class="col-sm-2 col-form-label">Amount</label>
                    <div class="col-sm-10">
                        {!! Form::text('amounts[]', 0, ['class' => 'form-control', 'id' => 'code']) !!}
                    </div>
                </div>
                @endforeach
                <div class="form-group row"><label for="transacting_bank_ledger_no" class="col-sm-2 col-form-label">Date</label>
                    <div class="col-sm-10">
                        {!! Form::date('deduction_for', null, ['class' => 'form-control', 'id' => 'code']) !!}
                    </div>
                </div>
                <div class="form-group row"><label for="coop_no" class="col-sm-2 col-form-label">&nbsp </label>
                    <div class="col-sm-10">
                        <button class="btn btn-primary">Submit</button>
                    </div>
                </div>
                {!! Form::close() !!}
            </div>
            @endcan
        </div>
    </div>
</div>

@endsection


@section('js')

@endsection

