@extends('master')

@section('body')
<!-- Page-Title -->
<div class="row">
    <div class="col-sm-12">
        <div class="page-title-box">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h4 class="page-title m-0">Manual Ledger Posting</h4>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-6">
        <div class="card m-b-30">
            <div class="card-body">
            <p>
                Fields marked <span class="text-danger">*</span> are required
            </p>

            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {!! Form::open(['route' => 'manual-ledger-postings.store']) !!}
                <div class="form-group row"><label for="ippis" class="col-sm-4 col-form-label">Member <span class="text-danger">*</span></label>
                    <div class="col-sm-8">
                        {!! Form::select('ippis', $members, null, ['class' => 'form-control select2', 'id' => 'ippis', 'placeholder' => 'Select a member']) !!}
                    </div>
                </div>
                <div class="form-group row"><label for="debit_account" class="col-sm-4 col-form-label">Account to debit <span class="text-danger">*</span></label>
                    <div class="col-sm-8">
                        {!! Form::select('debit_account', $debit_accounts, null, ['class' => 'form-control', 'id' => 'debit_account', 'placeholder' => 'Select pay debit account']) !!}
                    </div>
                </div>
                <div class="form-group row"><label for="credit_account" class="col-sm-4 col-form-label">Account to credit <span class="text-danger">*</span></label>
                    <div class="col-sm-8">
                        {!! Form::select('credit_account', $credit_accounts, 'savings', ['class' => 'form-control', 'id' => 'credit_account', 'placeholder' => 'Select pay credit account']) !!}
                    </div>
                </div>
                <div class="form-group row"><label for="amount" class="col-sm-4 col-form-label">Amount <span class="text-danger">*</span></label>
                    <div class="col-sm-8">
                        {!! Form::number('amount', null, ['class' => 'form-control', 'id' => 'amount', 'placeholder' => '']) !!}
                    </div>
                </div>
                <div class="form-group row"><label for="description" class="col-sm-4 col-form-label">Description <span class="text-danger">*</span></label>
                    <div class="col-sm-8">
                        {!! Form::text('description', null, ['class' => 'form-control', 'id' => 'description', 'placeholder' => '']) !!}
                    </div>
                </div>
                <div class="form-group row"><label for="value_date" class="col-sm-4 col-form-label">Value Date <span class="text-danger">*</span></label>
                    <div class="col-sm-8">
                        {!! Form::date('value_date', null, ['class' => 'form-control', 'id' => 'value_date', 'placeholder' => '']) !!}
                    </div>
                </div>
                <div class="form-group row"><label for="coop_no" class="col-sm-4 col-form-label">&nbsp </label>
                    <div class="col-sm-8">
                        <button class="btn btn-primary">Submit</button>
                    </div>
                </div>
                {!! Form::close() !!}
            </div>
        </div>
    </div>
</div>

@endsection


@section('js')

@endsection

