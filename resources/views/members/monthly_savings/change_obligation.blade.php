@extends('master')

@section('body')
<!-- Page-Title -->
@if($member)
<div class="row">
    <div class="col-sm-12">
        <div class="page-title-box">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h4 class="page-title m-0">Change Monthly Contribution</h4>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-6">
        <div class="card m-b-30">
            <div class="card-body">

            <a href="{{route('members.dashboard', $member->ippis)}}" class="btn btn-primary waves-effect waves-light"><i class="mdi mdi-backspace-outline"></i> Dashboard</a>
            <a href="{{route('members.savings', $member->ippis)}}" class="btn btn-info waves-effect waves-light"><i class="mdi mdi-file-document-box"></i> Savings</a>

            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @can('change monthly contribution')
            <div class="mt-4">
            <h5>Change Monthly Contribution</h5>
            {!! Form::open(['route' => ['members.postSavingsChangeObligation', $member->ippis]]) !!}

                {!! Form::hidden('ippis', $member->ippis) !!}

                <div class="form-group row"><label for="current_obligation" class="col-sm-4 col-form-label">Current Savings</label>
                    <div class="col-sm-8">
                        {!! Form::number('old_amount', $member->monthly_savings->last() ? $member->monthly_savings->last()->amount : 0, ['class' => 'form-control', 'id' => 'old_amount', 'disabled']) !!}
                        {!! Form::hidden('old_amount', $member->monthly_savings->last() ? $member->monthly_savings->last()->amount : 0 ) !!}
                    </div>
                </div>
                <div class="form-group row"><label for="new_amount" class="col-sm-4 col-form-label">New Savings</label>
                    <div class="col-sm-8">
                        {!! Form::number('new_amount', null, ['class' => 'form-control', 'id' => 'new_amount', 'min' => 2000]) !!}
                    </div>
                </div>
                <div class="form-group row"><label for="is_indefinite" class="col-sm-4 col-form-label">Should this change be indefinite?</label>
                    <div class="col-sm-8 pt-3">
                        {!! Form::radio('is_indefinite', 1, ['class' => 'form-control', 'id' => 'is_indefinite']) !!} Yes
                        {!! Form::radio('is_indefinite', 0, ['class' => 'form-control', 'id' => 'is_indefinite']) !!} No
                    </div>
                </div>
                <div class="form-group row"><label for="revert_date" class="col-sm-4 col-form-label">If not indefite, when should it revert back to current monthly savings?</label>
                    <div class="col-sm-8">
                        {!! Form::date('revert_date', null, ['class' => 'form-control', 'id' => 'revert_date']) !!}
                    </div>
                </div>
                <div class="form-group row"><label for="coop_no" class="col-sm-3 col-form-label">&nbsp </label>
                    <div class="col-sm-9">
                        <button class="btn btn-primary">Submit</button>
                    </div>
                </div>
                {!! Form::close() !!}
            </div>
            </div>
            @endcan
        </div>
    </div>
</div>
@else
<div class="row">
    <div class="col-sm-12">
        <div class="page-title-box">
            <div class="row align-items-center">
                <div class="col-md-8">
                
                </div>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-12">
        <div class="card m-b-30">
            <div class="card-body">
            
                <p class="my-5">
                    This member does not exist
                </p>
            
            </div>
        </div>
    </div>
</div>
@endif

@endsection


@section('js')

@endsection

