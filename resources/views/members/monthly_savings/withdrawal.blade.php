@extends('master')

@section('body')
<!-- Page-Title -->
@if($member)
<div class="row">
    <div class="col-sm-12">
        <div class="page-title-box">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h4 class="page-title m-0">Withdrawal</h4>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
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

            <div class="mt-4">
            <h5>Withdrawal</h5>

            @if($lastMonthlySaving->bal <= 0)
                <p>No savings in this account. Click <a href="{{route('members.newSavings', $member->ippis)}}">here</a> to add some funds</p>
            @else
                <monthly-savings-withdrawal :member="{{ $member }}"></monthly-savings-withdrawal>
            @endif
            

            <!-- {!! Form::open(['route' => ['members.postSavingsWithrawal', $member->ippis]]) !!}

                {!! Form::hidden('ippis', $member->ippis) !!}

                <div class="form-group row"><label for="ref" class="col-sm-3 col-form-label">Description</label>
                    <div class="col-sm-9">
                        {!! Form::text('ref', null, ['class' => 'form-control', 'id' => 'ref']) !!}
                    </div>
                </div>
                <div class="form-group row"><label for="total_amount" class="col-sm-3 col-form-label">Amount</label>
                    <div class="col-sm-9">
                        {!! Form::number('total_amount', null, ['class' => 'form-control', 'id' => 'total_amount']) !!}
                    </div>
                </div>
                <div class="form-group row"><label for="coop_no" class="col-sm-3 col-form-label">&nbsp </label>
                    <div class="col-sm-9">
                        <button class="btn btn-primary">Submit</button>
                    </div>
                </div>
                {!! Form::close() !!} -->
            </div>
            </div>
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

