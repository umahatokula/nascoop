@extends('master')

@section('body')
<!-- Page-Title -->
<div class="row">
    <div class="col-sm-12">
        <div class="page-title-box">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h4 class="page-title m-0">Edit member</h4>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card m-b-30">
            <div class="card-body">
            <p>
                Fields marked <span class="text-danger">*</span> are required
            </p>

            @if ($errors->any())
                <div class="alert alert-danger">
                    <ol>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ol>
                </div>
            @endif

            {!! Form::model($member, ['route' => ['updateMember', $member->ippis], 'method' => 'put']) !!}
                <div class="form-group row"><label for="pf" class="col-sm-4 col-form-label">PF</label>
                    <div class="col-sm-8">
                        {!! Form::text('pf', null, ['class' => 'form-control', 'id' => 'pf']) !!}
                    </div>
                </div>
                <div class="form-group row"><label for="ippis" class="col-sm-4 col-form-label">IPPIS <span class="text-danger">*</span></label>
                    <div class="col-sm-8">
                        {!! Form::number('ippis', null, ['class' => 'form-control', 'id' => 'ippis', 'readonly']) !!}
                        {!! Form::hidden('old_ippis', $member->ippis) !!}
                    </div>
                </div>
                <div class="form-group row"><label for="fname" class="col-sm-4 col-form-label">FIRST NAME <span class="text-danger">*</span></label>
                    <div class="col-sm-8">
                        {!! Form::text('fname', null, ['class' => 'form-control', 'id' => 'fname']) !!}
                    </div>
                </div>
                <div class="form-group row"><label for="lname" class="col-sm-4 col-form-label">SURNAME NAME <span class="text-danger">*</span></label>
                    <div class="col-sm-8">
                        {!! Form::text('lname', null, ['class' => 'form-control', 'id' => 'lname']) !!}
                    </div>
                </div>
                <div class="form-group row"><label for="phone" class="col-sm-4 col-form-label">PHONE <span class="text-danger">*</span></label>
                    <div class="col-sm-8">
                        {!! Form::text('phone', null, ['class' => 'form-control', 'id' => 'phone']) !!}
                    </div>
                </div>
                <div class="form-group row"><label for="email" class="col-sm-4 col-form-label">EMAIL</label>
                    <div class="col-sm-8">
                        {!! Form::email('email', null, ['class' => 'form-control', 'id' => 'email']) !!}
                    </div>
                </div>
                <div class="form-group row"><label for="pay_point" class="col-sm-4 col-form-label">PAY POINT <span class="text-danger">*</span></label>
                    <div class="col-sm-8">
                        {!! Form::select('pay_point', $centers, null, ['class' => 'form-control', 'id' => 'pay_point', 'placeholder' => 'Select pay point']) !!}
                    </div>
                </div>
                <div class="form-group row"><label for="center_id" class="col-sm-4 col-form-label">CENTER</label>
                    <div class="col-sm-8">
                        {!! Form::select('center_id', $centers, null, ['class' => 'form-control', 'id' => 'center_id', 'placeholder' => 'Select center']) !!}
                    </div>
                </div>
                <div class="form-group row"><label for="coop_no" class="col-sm-4 col-form-label">COOP NO</label>
                    <div class="col-sm-8">
                        {!! Form::text('coop_no', null, ['class' => 'form-control', 'id' => 'coop_no']) !!}
                    </div>
                </div>
                <!-- <div class="form-group row"><label for="sbu" class="col-sm-4 col-form-label">SBU</label>
                    <div class="col-sm-8">
                        {!! Form::text('sbu', null, ['class' => 'form-control', 'id' => 'sbu']) !!}
                    </div>
                </div> -->
                <div class="form-group row"><label for="nok_name" class="col-sm-4 col-form-label">NEXT OF KIN NAME</label>
                    <div class="col-sm-8">
                        {!! Form::text('nok_name', null, ['class' => 'form-control', 'id' => 'nok_name']) !!}
                    </div>
                </div>
                <div class="form-group row"><label for="nok_phone" class="col-sm-4 col-form-label">NEXT OF KIN PHONE</label>
                    <div class="col-sm-8">
                        {!! Form::text('nok_phone', null, ['class' => 'form-control', 'id' => 'nok_phone']) !!}
                    </div>
                </div>
                <div class="form-group row"><label for="nok_rship" class="col-sm-4 col-form-label">RELATIONSHIP WITH NEXT OF KIN</label>
                    <div class="col-sm-8">
                        {!! Form::text('nok_rship', null, ['class' => 'form-control', 'id' => 'nok_rship']) !!}
                    </div>
                </div>
                <div class="form-group row"><label for="nok_address" class="col-sm-4 col-form-label">NEXT OF KIN ADDRESS</label>
                    <div class="col-sm-8">
                        {!! Form::textarea('nok_address', null, ['class' => 'form-control', 'id' => 'nok_address']) !!}
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

