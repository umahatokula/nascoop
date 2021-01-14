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
        <div class="card">
            <div class="card-body">
        
                @include('accounting.linksPartial')

            </div>
        </div>
    </div><!-- end col -->
</div>

<div class="row">
    <div class="col-12">
        <div class="card m-b-30">
            <div class="card-body">

                <div class="row mt-3">
                    <div class="col-lg-12">
                        
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="mt-4">
                    <h5>Record Expense</h5>
                    {!! Form::open(['route' => 'newExpensePost']) !!}

                        <div class="form-group row"><label for="debit_account" class="col-sm-3 col-form-label">Expense Account</label>
                            <div class="col-sm-9">
                                {!! Form::select('debit_account', $expense_accounts, null, ['class' => 'form-control select2', 'id' => 'debit_account', 'placeholder' => 'Select one']) !!}
                            </div>
                        </div>
                        <div class="form-group row"><label for="credit_account" class="col-sm-3 col-form-label">Account To Credit</label>
                            <div class="col-sm-9">
                                {!! Form::select('credit_account', $accounts, null, ['class' => 'form-control select2', 'id' => 'credit_account', 'placeholder' => 'Select one']) !!}
                            </div>
                        </div>
                        <div class="form-group row"><label for="supplier_id" class="col-sm-3 col-form-label">Supplier</label>
                            <div class="col-sm-9">
                                {!! Form::select('supplier_id', $suppliers, null, ['class' => 'form-control select2', 'id' => 'supplier_id', 'placeholder' => 'Select one']) !!}
                            </div>
                        </div>
                        <div class="form-group row"><label for="deposit_date" class="col-sm-3 col-form-label">Date</label>
                            <div class="col-sm-9">
                                {!! Form::date('date', null, ['class' => 'form-control', 'id' => 'deposit_date']) !!}
                            </div>
                        </div>

                        <div class="form-group row"><label for="ref" class="col-sm-3 col-form-label">Description</label>
                            <div class="col-sm-9">
                                {!! Form::text('description', null, ['class' => 'form-control', 'id' => 'ref']) !!}
                            </div>
                        </div>
                        <div class="form-group row"><label for="amount" class="col-sm-3 col-form-label">Amount</label>
                            <div class="col-sm-9">
                                {!! Form::number('amount', null, ['class' => 'form-control', 'id' => 'amount']) !!}
                            </div>
                        </div>
                        <div class="form-group row"><label for="coop_no" class="col-sm-3 col-form-label">&nbsp </label>
                            <div class="col-sm-9">
                                <button class="btn btn-primary">Submit</button>
                            </div>
                        </div>
                        {!! Form::close() !!}
                    </div>
                        <!-- <newexpense></newexpense> -->
                    </div>
                </div>

            </div>
        </div>
    </div><!-- end col -->
</div>
@endsection
