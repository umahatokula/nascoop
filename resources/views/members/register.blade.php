@extends('master')

@section('body')
<!-- Page-Title -->
<div class="row">
    <div class="col-sm-12">
        <div class="page-title-box">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h4 class="page-title m-0">Download member register</h4>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-6">
        <div class="card m-b-30">
            <div class="card-body">
            <div class="alert alert-info" role="alert">
                <h4 class="alert-heading">Note!</h4>
                All downloads are .csv files
            </div>

            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {!! Form::open(['route' => 'members.register.download']) !!}
                <div class="form-group row"><label for="center_id" class="col-sm-4 col-form-label">Center</label>
                    <div class="col-sm-8">
                        {!! Form::select('center_id', $centers, null, ['class' => 'form-control', 'id' => 'center_id', 'placeholder' => 'Select center']) !!}
                    </div>
                </div>
                <div class="form-group row"><label for="coop_no" class="col-sm-4 col-form-label">&nbsp </label>
                    <div class="col-sm-8">
                        <button class="btn btn-primary btn-block">Download</button>
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

