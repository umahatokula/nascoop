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
                    <!-- <h5>Add New Supplier</h5> -->
                    {!! Form::open(['route' => 'singleLegEntryPost']) !!}

                        <div class="form-group row"><label for="type" class="col-sm-3 col-form-label">Type</label>
                            <div class="col-sm-9">
                                {!! Form::select('type', $types, null, ['class' => 'form-control', 'id' => 'type']) !!}
                            </div>
                        </div>

                        <div class="form-group row"><label for="account_no" class="col-sm-3 col-form-label">Account Name</label>
                            <div class="col-sm-9">
                                {!! Form::select('account_no', $accounts, null, ['class' => 'form-control', 'id' => 'account_no']) !!}
                            </div>
                        </div>

                        <div class="form-group row"><label for="amount" class="col-sm-3 col-form-label">Amount</label>
                            <div class="col-sm-9">
                                {!! Form::text('amount', null, ['class' => 'form-control', 'id' => 'amount']) !!}
                            </div>
                        </div>

                        <div class="form-group row"><label for="description" class="col-sm-3 col-form-label">Description</label>
                            <div class="col-sm-9">
                                {!! Form::text('description', null, ['class' => 'form-control', 'id' => 'description']) !!}
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
