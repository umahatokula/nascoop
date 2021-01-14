@extends('master')

@section('body')
<!-- Page-Title -->
@if($member)
<div class="row">
    <div class="col-sm-12">
        <div class="page-title-box">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h4 class="page-title m-0">Monthly Savings <span class="text-primary">[ {{ $member->full_name }} | {{ $member->ippis }} ]</span></h4>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card m-b-30">
            <div class="card-body">

                <a href="{{route('members.dashboard', $member->ippis)}}" class="btn btn-primary waves-effect waves-light"><i class="mdi mdi-backspace-outline"></i> Dashboard</a>
                @can('add to savings')
                <a href="{{route('members.newSavings', $member->ippis)}}" class="btn btn-info waves-effect waves-light"><i class="mdi mdi-file-document-box"></i> Add Saving</a>
                @endcan
                @can('withdraw from savings')
                <a href="{{route('members.savingsWithrawal', $member->ippis)}}" class="btn btn-danger waves-effect waves-light"><i class="mdi mdi-file-document-box"></i> Withdrawal</a>
                @endcan
                @can('change monthly contribution')
                <a href="{{route('members.savingsChangeObligation', $member->ippis)}}" class="btn btn-secondary waves-effect waves-light"><i class="mdi mdi-file-document-box"></i> Change Monthly Contribution</a>
                @endcan

                @if($monthlySavings->count() > 0)
                <div class="row mt-3">
                    <div class="col-md-3">
                    <h6>Monthly Contributions</h6>
                        <table class="table table-bordered table-hover">
                            <tbody>
                                <thead>
                                    <tr>
                                        <td class="text-left">Date</td>
                                        <td class="text-right">Monthly Contribution</td>
                                    </tr>
                                </thead>
                                @foreach($monthlySavings as $monthlySaving)
                                    <tr>
                                        <td class="text-left">{{ $monthlySaving->created_at->toFormattedDateString() }}</td>
                                        <td class="text-right">{{ number_format($monthlySaving->amount, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="col-md-9">
                        <div class="row">
                            <div class="col-6 text-left">
                                <h6>Monthly Contributions Payment History</h6>
                            </div>
                            <div class="col-6 text-right pt-2">
                                <p>
                                    <span class="text-muted">P = Pending</span> |
                                    <span class="text-success">A = Authorized</span> |
                                    <span class="text-danger">C = Cancelled</span> |
                                    <span class="text-info">PV = Payment Voucher</span>
                                </p>
                            </div>
                            <div class="col-12">
                                    <table class="table table-bordered table-hover">
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
                                            @foreach($monthlySavings as $monthlySaving)
                                                @foreach($monthlySaving->payments as $payments)
                                                <tr class="{{ $payments->is_authorized == 2 ? 'text-muted' : '' }}" style="{{ $payments->is_authorized == 2 ? 'text-decoration: line-through;' : '' }}">
                                                    <td>{{$payments->ref}}</td>
                                                    <td class="text-right">{{ number_format($payments->dr, 2) }}</td>
                                                    <td class="text-right">{{ number_format($payments->cr, 2) }}</td>
                                                    <td class="text-right">{{ number_format($payments->bal, 2) }}</td>
                                                    <td class="text-right">
                                                        @if($payments->deposit_date){{ 
                                                        $payments->deposit_date ? $payments->deposit_date->toFormattedDateString() : '' }}
                                                        @else{{ 
                                                        $payments->withdrawal_date ? $payments->withdrawal_date->toFormattedDateString() : '' }}
                                                        @endif
                                                    </td>
                                                    <td class="text-center">
                                                        @if($payments->is_authorized == 0)
                                                        <span class="text-default">P</span>
                                                            @if($payments->is_withdrawal == 1)
                                                            | 
                                                            <a href="{{ route('members.withdrawalPaymentVoucher', $payments->id) }}">PV</a>
                                                            @endif
                                                        @endif

                                                        @if($payments->is_authorized == 1)
                                                        <span class="text-success">A</span>
                                                            @if($payments->is_withdrawal == 1)
                                                            | 
                                                            <a href="{{ route('members.withdrawalPaymentVoucher', $payments->id) }}">PV</a>
                                                            @endif
                                                        @endif

                                                        @if($payments->is_authorized == 2)
                                                        <span class="text-danger">C</span>
                                                            @if($payments->is_withdrawal == 1)
                                                            | 
                                                            <a href="{{ route('members.withdrawalPaymentVoucher', $payments->id) }}">PV</a>
                                                            @endif
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


@section('js')

@endsection

