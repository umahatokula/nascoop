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
                    <h5>Add New Supplier</h5>
                    {!! Form::model($supplier, ['route' => ['suppliers.update', $supplier->id], 'method' => 'PUT']) !!}

                        <div class="form-group row"><label for="fname" class="col-sm-3 col-form-label">First Name</label>
                            <div class="col-sm-9">
                                {!! Form::text('fname', null, ['class' => 'form-control', 'id' => 'fname']) !!}
                            </div>
                        </div>

                        <div class="form-group row"><label for="lname" class="col-sm-3 col-form-label">Surname</label>
                            <div class="col-sm-9">
                                {!! Form::text('lname', null, ['class' => 'form-control', 'id' => 'lname']) !!}
                            </div>
                        </div>

                        <div class="form-group row"><label for="phone" class="col-sm-3 col-form-label">Phone</label>
                            <div class="col-sm-9">
                                {!! Form::number('phone', null, ['class' => 'form-control', 'id' => 'phone']) !!}
                            </div>
                        </div>

                        <div class="form-group row"><label for="email" class="col-sm-3 col-form-label">Email</label>
                            <div class="col-sm-9">
                                {!! Form::email('email', null, ['class' => 'form-control', 'id' => 'email']) !!}
                            </div>
                        </div>

                        <div class="form-group row"><label for="ref" class="col-sm-3 col-form-label">Address</label>
                            <div class="col-sm-9">
                                {!! Form::text('address', null, ['class' => 'form-control', 'id' => 'ref']) !!}
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
