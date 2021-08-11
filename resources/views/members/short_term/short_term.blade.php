@extends('master')

@section('body')
<!-- Page-Title -->
@if($member)
<div class="row">
    <div class="col-sm-12">
        <div class="page-title-box">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h4 class="page-title m-0">Short Term Loans <span class="text-danger">[ {{ $member->full_name }} | {{ $member->ippis }} ]</span><span class="text-{{ $member->is_active? 'success' : 'danger' }}">[ {{ $member->is_active? 'active' : 'inactive' }} ]</span></h4>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card m-b-30">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-5">
                        <div class="mb-3">
                            <a href="{{route('members.dashboard', $member->ippis)}}" class="btn btn-primary waves-effect waves-light mb-1"><i class="mdi mdi-backspace-outline"></i> Dashboard</a>
                            @can('short term loan repayment')
                            <a href="{{route('members.shortLoanRepayment', $member->ippis)}}" class="btn btn-secondary waves-effect waves-light mb-1 {{ $member->is_active ? '' : 'disabled' }} {{ $shortTermLoans->count() > 0 ? '' : 'disabled' }}"><i class="mdi mdi-file-document-box"></i> Repayment</a>
                            @endcan

                            @can('create short term loan')
                            <a href="{{route('members.newShortLoan', $member->ippis)}}" class="btn btn-info waves-effect waves-light mb-1 {{ $member->is_active ? '' : 'disabled' }}"><i class="mdi mdi-file-document-box"></i> New Short Term Loan</a>
                            @endcan
                        </div>
                    </div>
                    <div class="col-md-7">
                        {!! Form::open(['route' => ['members.shortTermLoans', $member->ippis], 'method' => 'get']) !!}
                        <div class="row">
                            <div class="col-md-2 text-right mt-2">
                                Start Date
                            </div>
                            <div class="col-md-3">
                                {{Form::date('date_from', $date_from, ['class' => 'form-control mb-3'])}}
                            </div>
                            <div class="col-md-2 text-right mt-2">
                                End Date
                            </div>
                            <div class="col-md-3">
                                {{Form::date('date_to', $date_to, ['class' => 'form-control mb-3'])}}
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary waves-effect waves-light mb-3">Filter</button>
                            </div>
                        </div>
                        {!! Form::close() !!}
                    </div>
                </div>

                @if($shortTermLoans->count() > 0)
                <div class="row mt-3">
                    <div class="col-md-3">
                        <h6>Short Term Loans Taken</h6>
                        <table class="table table-bordered table-hover">
                            <tbody>
                                <thead>
                                    <tr>
                                        <td class="text-right">Date</td>
                                        <td class="text-right">Amount</td>
                                        <td class="text-center">Action(s)</td>
                                    </tr>
                                </thead>
                                @foreach($shortTermLoans as $shortTermLoan)
                                    <tr class="{{ $shortTermLoan->is_approved == 2 ? 'text-muted' : '' }}"
                                            style="{{ $shortTermLoan->is_approved == 2 ? 'text-decoration: line-through;' : '' }}">
                                        <td class="text-left">{{ $shortTermLoan->loan_date ? $shortTermLoan->loan_date->toFormattedDateString() : '' }}</td>
                                        <td class="text-right">{{ number_format($shortTermLoan->total_amount, 2) }}</td>
                                        <td class="text-center">
                                            <a data-toggle="modal" data-keyboard="false"
                                                data-target="#myModal"
                                                data-remote="{{route('members.shortLoanDetails', $shortTermLoan->id)}}" href="#"
                                                class="">Details</a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="col-md-9">
                        <div class="row">
                            <div class="col-6 text-left">
                                <h6>Short Term Loan Payment History</h6>
                            </div>
                            <div class="col-6 text-right">
                                <p>
                                    <span class="text-muted">P = Pending</span> |
                                    <span class="text-success">A = Authorized</span> |
                                    <span class="text-danger">C = Cancelled</span>
                                </p>
                            </div>
                            <div class="col-12">
                                <table class="table table-bordered table-hover table-responsive">
                                    <tbody>
                                        <thead>
                                            <tr>
                                                <td>Description</td>
                                                <td class="text-right">Debit</td>
                                                <td class="text-right">Credit</td>
                                                <td class="text-right">Balance</td>
                                                <td class="text-right">Date</td>
                                                <td class="text-right">Status</td>
                                            </tr>
                                        </thead>
                                        @foreach($shortTermLoans as $shortTermLoan)
                                            @foreach($shortTermLoan->payments as $payments)
                                            <tr class="{{ $payments->is_authorized == 2 ? 'text-muted' : '' }}" style="{{ $payments->is_authorized == 2 ? 'text-decoration: line-through;' : '' }}">
                                                <td>{{$payments->ref}}</td>
                                                <td class="text-right">{{ number_format($payments->dr, 2) }}</td>
                                                <td class="text-right">{{ number_format($payments->cr, 2) }}</td>
                                                <td class="text-right">{{ number_format($payments->bal, 2) }}</td>
                                                <td class="text-right">
                                                
                                                        @if($payments->deposit_date){{ 
                                                        $payments->deposit_date ? $payments->deposit_date->toFormattedDateString() : '' }}
                                                        @else{{ 
                                                        $payments->loan_date ? $payments->loan_date->toFormattedDateString() : '' }}
                                                        @endif
                                                </td>
                                                <td class="text-center">
                                                    @if($payments->is_authorized == 0)
                                                    <span class="text-default">P</span>
                                                    @elseif($payments->is_authorized == 1)
                                                    <span class="text-success">A</span>
                                                    @elseif($payments->is_authorized == 2)
                                                    <span class="text-danger">C</span>
                                                    @else
                                                    <span class="text-default">P</span>
                                                    @endif
                                                </td>
                                            </tr>
                                            @endforeach
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                @else
                <p class="mt-3">No records found.</p>
                @endif
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


