@extends('master')

@section('body')
<!-- Page-Title -->
<div class="row">
    <div class="col-sm-12">
        <div class="page-title-box">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h4 class="page-title m-0">Change Password</h4>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-8">
        <div class="card m-b-30">
            <div class="card-body">

            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            
            {!! Form::open(['route' => 'users.storeChangedPassword']) !!}

                <input name="ippis" type="hidden" value="{{Auth::user()->ippis}}" />

                <!-- Current Password -->
                <div class="form-group row">
                    <label for="now_password" class="col-sm-4 col-form-label">Current Password</label>
                    <div class="col-sm-8">
                        {!! Form::password('now_password', null, ['class' => 'form-control', 'id' =>
                        'now_password']) !!}
                    </div>
                </div>
                <!-- New Password -->
                <div class="form-group row">
                    <label for="password" class="col-sm-4 col-form-label">New Password</label>
                    <div class="col-sm-8">
                        {!! Form::password('password', null, ['class' => 'form-control', 'id' =>
                        'password']) !!}
                    </div>
                </div>
                <!-- Confirm New Password -->
                <div class="form-group row">
                    <label for="password_confirmation" class="col-sm-4 col-form-label">Confirm New
                        Password</label>
                    <div class="col-sm-8">
                        {!! Form::password('password_confirmation', null, ['class' => 'form-control', 'id'
                        => 'password_confirmation']) !!}
                    </div>
                </div>

                <!-- Submit Field -->
                <div class="form-group col-sm-12">
                    {!! Form::submit('Change Password', ['class' => 'btn btn-primary']) !!}
                </div>

            {!! Form::close() !!}
            </div>

        </div>
    </div>
</div>

@endsection


@section('js')

@endsection

