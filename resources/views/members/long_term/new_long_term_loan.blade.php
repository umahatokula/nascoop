@extends('master')

@section('body')
<!-- Page-Title -->
@if($member)
<div class="row">
    <div class="col-sm-12">
        <div class="page-title-box">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h4 class="page-title m-0">New Long Term Loans <span class="text-danger">[ {{ $member->full_name }}
                            | {{ $member->ippis }} ]</span></h4>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card m-b-30">
            <div class="card-body">

                @can('create long term loan')
                <a href="{{route('members.dashboard', $member->ippis)}}"
                    class="btn btn-primary waves-effect waves-light"><i class="mdi mdi-backspace-outline"></i>
                    Dashboard</a>
                <a href="{{route('members.longTermLoans', $member->ippis)}}"
                    class="btn btn-primary waves-effect waves-light"><i class="mdi mdi-backspace-outline"></i> Long Term
                    Loans</a>
                <div class="mt-3">

                    @if($member->long_term_payments->isEmpty())
                    <new-long-term-loan :member="{{ $member }}"></new-long-term-loan>
                    @else
                    
                    <new-long-term-loan :member="{{ $member }}"></new-long-term-loan>
                    
                    @endif
                    
                </div>
                @endcan
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
