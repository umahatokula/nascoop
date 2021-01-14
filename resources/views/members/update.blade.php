@extends('master')

@section('body')
<!-- Page-Title -->

<link href="https://themesdesign.in/zinzer_1/plugins/dropzone/dist/dropzone.css" rel="stylesheet" type="text/css">

<div class="row">
    <div class="col-md-6">
        <div class="card m-b-30">
            <div class="card-body">
                <h4 class="mt-0 header-title">Update Members information</h4>

                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                
                {!! Form::open(['route' => 'members.postUpdateMemberInformation', 'class' => '','files'=>'true']) !!}
                    <div class="m-b-30">
                            <div class="fallback"><input name="file" type="file"></div>
                    </div>
                    <div class="text-center m-t-15">
                        <button type="submit" class="btn btn-primary waves-effect waves-light">Upload</button>
                    </div>
                {!! Form::close() !!}
            </div>
        </div>
    </div><!-- end col -->
</div>
@endsection
