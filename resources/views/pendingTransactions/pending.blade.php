@extends('master')

@section('body')
<!-- Page-Title -->

@php
$tab = session()->get( 'tab' );
if(is_null($tab)):
$tab = 'ltl';
endif;
@endphp

<div class="row">
    <div class="col-sm-12">
        <div class="page-title-box">
            <div class="row align-items-center">
                <div class="col-md-8">
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

                <div class="row mb-5">
                    <div class="col-md-4">
                        <h4 class="page-title m-0">Process Pending Transactions</h4>
                        <p>&nbsp</p>
                    </div>
                    <div class="col-md-8">

                        {!! Form::open(['route' => ['pendingTransactions', $tab], 'method' => 'get']) !!}
                        <div class="row">
                            <div class="col-md-3 mb-1">
                                {{Form::date('dateFrom', $dateFrom, ['class' => 'form-control'])}}
                            </div>
                            <div class="col-md-3 mb-1">
                                {{Form::date('dateTo', $dateTo, ['class' => 'form-control'])}}
                            </div>
                            <div class="col-md-3 mb-1">
                                {{Form::select('pay_point', $centers, $pay_point, ['class' => 'form-control', 'placeholder' => 'All centers'])}}
                            </div>
                            <div class="col-md-3 mb-1">
                                <button type="submit" class="btn btn-primary waves-effect waves-light">Generate</button>
                            </div>
                        </div>
                        {!! Form::close() !!}
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-2">
                        <div class="nav flex-column nav-pills" id="v-pills-tab" role="tablist"
                            aria-orientation="vertical">

                            <a class="nav-link mb-2 {{ $tab == 'ltl' ? 'active' : '' }}" id="v-pills-profile-tab"
                                data-toggle="pill" href="#v-pills-profile" role="tab" aria-controls="v-pills-profile"
                                aria-selected="false">Long Term Loans <span
                                    class="text-{{ $pendingLtlTrxnCount > 0 ? 'danger' : 'success' }}">({{ $pendingLtlTrxnCount }})</span></a>

                            <a class="nav-link mb-2 {{ $tab == 'stl' ? 'active' : '' }}" id="v-pills-messages-tab"
                                data-toggle="pill" href="#v-pills-messages" role="tab" aria-controls="v-pills-messages"
                                aria-selected="false">Short Term Loans <span
                                    class="text-{{ $pendingSltTrxnCount > 0 ? 'danger' : 'success' }}">({{ $pendingSltTrxnCount }})</span></a>

                            <a class="nav-link mb-2 {{ $tab == 'coml' ? 'active' : '' }}" id="v-pills-settings-tab"
                                data-toggle="pill" href="#v-pills-settings" role="tab" aria-controls="v-pills-settings"
                                aria-selected="false">Commodity Loans <span
                                    class="text-{{ $pendingComlTrxnCount > 0 ? 'danger' : 'success' }}">({{ $pendingComlTrxnCount }})</span></a>

                            <a class="nav-link mb-2 {{ $tab == 'savings' ? 'active' : '' }}" id="v-pills-home-tab"
                                data-toggle="pill" href="#v-pills-home" role="tab" aria-controls="v-pills-home"
                                aria-selected="true">Savings <span
                                    class="text-{{ $pendingMonthlySavingsTrxnCount > 0 ? 'danger' : 'success' }}">({{ $pendingMonthlySavingsTrxnCount }})</span></a>

                            <a class="nav-link mb-2 {{ $tab == 'shares' ? 'active' : '' }}" id="shares-tab"
                                data-toggle="pill" href="#shares" role="tab" aria-controls="shares"
                                aria-selected="true">Shares <span
                                    class="text-{{ $pendingShareCount > 0 ? 'danger' : 'success' }}">({{ $pendingShareCount }})</span></a>

                        </div>
                    </div>
                    <div class="col-md-10">
                        
                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul>
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <div class="tab-content text-muted mt-4 mt-md-0" id="v-pills-tabContent">
                            <div class="tab-pane fade {{ $tab == 'ltl' ? 'show active' : '' }}" id="v-pills-profile"
                                role="tabpanel" aria-labelledby="v-pills-profile-tab">
                                <div class="row">
                                    <div class="col-8">
                                        <h5>Long Term Loans</h5>
                                    </div>
                                    <div class="col-4 pt-3 bankbalance">
                                        Bank Balance: <strong><span></span></strong>                                       
                                    </div>
                                </div>
                                @if($pendingLongTermTnxs->count() > 0)
                                <table class="table table-stripped table-bordered table-responsive-md">
                                    <thead>
                                        <th>Loan Date</th>
                                        <th>IPPIS</th>
                                        <th>Name</th>
                                        <th>Amount</th>
                                        <th>Type</th>
                                        <th class="text-center">Action(s)</th>
                                    </thead>
                                    <tbody>
                                        @foreach($pendingLongTermTnxs as $pendingLongTermTnx)
                                        <tr>
                                            <td>{{ $pendingLongTermTnx->trxn_type == 'ltl' ? $pendingLongTermTnx->loan_date : $pendingLongTermTnx->deposit_date }}
                                            </td>
                                            <td><a
                                                    href="{{ route('members.dashboard', $pendingLongTermTnx->ippis) }}">{{ $pendingLongTermTnx->ippis }}</a>
                                            </td>
                                            <td>{{ $pendingLongTermTnx->longTermLoan->member ? $pendingLongTermTnx->longTermLoan->member->full_name : '' }}
                                            </td>
                                            <td class="text-right">
                                                {{$pendingLongTermTnx->trxn_type == 'ltl' ? number_format($pendingLongTermTnx->dr, 2) : number_format($pendingLongTermTnx->cr, 2) }}
                                            </td>
                                            <td>{{ $pendingLongTermTnx->transaction_type ? $pendingLongTermTnx->transaction_type->description : '' }}
                                            </td>
                                            <td class="text-center">

                                            @if(!$pendingLongTermTnx->longTermLoan->start_processing && !$pendingLongTermTnx->longTermLoan->is_approved)
                                                <a href="{{route('pendingTransactions.startProcessing', [$pendingLongTermTnx->longTermLoan->id, 'ltl'])}}" class="" onclick = "return confirm('Are you sure?')">Start Processing</a> | 
                                                <a class="text-success" href="{{ route('members.longTermLoansPaymentVoucher', $pendingLongTermTnx->longTermLoan->id) }}" target="_blank">PV</a> |

                                                <a data-toggle="modal" data-keyboard="false" data-target="#myModal"
                                                data-remote="{{route('members.longLoanDetails', $pendingLongTermTnx->longTermLoan->id)}}"
                                                href="#" class="">Details</a>
                                            @endif

                                            @if($pendingLongTermTnx->longTermLoan->start_processing && !$pendingLongTermTnx->longTermLoan->is_approved)
                                                <a data-toggle="modal" data-keyboard="false" data-target="#myModal"
                                                    data-remote="{{route('pendingTransactions.processApplications', [$pendingLongTermTnx->id, 'ltl'])}}"
                                                    href="#" class="text-danger">Process</a> | 
                                                <a class="text-success" href="{{ route('members.longTermLoansPaymentVoucher', $pendingLongTermTnx->longTermLoan->id) }}" target="_blank">PV</a>
                                            @endif
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                                @else
                                <p>There are no pending long term loans transactions</p>
                                @endif
                            </div>
                            <div class="tab-pane fade {{ $tab == 'stl' ? 'show active' : '' }}" id="v-pills-messages"
                                role="tabpanel" aria-labelledby="v-pills-messages-tab">
                                <div class="row">
                                    <div class="col-8">
                                        <h5>Short Term Loans</h5>
                                    </div>
                                    <div class="col-4 pt-3 bankbalance">
                                        Bank Balance: <strong><span></span></strong>                                       
                                    </div>
                                </div>
                                @if($pendingShortTermTnxs->count() > 0)
                                <table class="table table-stripped table-bordered table-responsive-md">
                                    <thead>
                                        <th>Loan Date</th>
                                        <th>IPPIS</th>
                                        <th>Name</th>
                                        <th>Amount</th>
                                        <th>Type</th>
                                        <th>Action(s)</th>
                                    </thead>
                                    <tbody>
                                        @foreach($pendingShortTermTnxs as $pendingShortTermTnx)
                                        <tr>
                                            <td>{{ $pendingShortTermTnx->trxn_type == 'stl' ? $pendingShortTermTnx->loan_date : $pendingShortTermTnx->deposit_date }}
                                            </td>
                                            <td><a
                                                    href="{{ route('members.dashboard', $pendingShortTermTnx->ippis) }}">{{ $pendingShortTermTnx->ippis }}</a>
                                            </td>
                                            <td>{{ $pendingShortTermTnx->shortTermLoan->member ? $pendingShortTermTnx->shortTermLoan->member->full_name : '' }}
                                            </td>
                                            <td class="text-right">
                                                {{ $pendingShortTermTnx->trxn_type == 'stl' ?  number_format($pendingShortTermTnx->dr, 2) :  number_format($pendingShortTermTnx->cr, 2) }}
                                            </td>
                                            <td>{{ $pendingShortTermTnx->transaction_type ? $pendingShortTermTnx->transaction_type->description : '' }}
                                            </td>
                                            <td class="text-center">

                                            @if(!$pendingShortTermTnx->shortTermLoan->start_processing && !$pendingShortTermTnx->shortTermLoan->is_approved)
                                                <a href="{{route('pendingTransactions.startProcessing', [$pendingShortTermTnx->shortTermLoan->id, 'stl'])}}" class="" onclick = "return confirm('Are you sure?')">Start Processing</a> | 
                                                <a class="text-success" href="{{ route('members.shortTermLoansPaymentVoucher', $pendingShortTermTnx->shortTermLoan->id) }}" target="_blank">PV</a>
                                            @else
                                                <a data-toggle="modal" data-keyboard="false" data-target="#myModal"
                                                    data-remote="{{route('pendingTransactions.processApplications', [$pendingShortTermTnx->id, 'stl'])}}"
                                                    href="#" class="text-danger">Process</a>
                                            @endif
                                          
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                                @else
                                <p>There are no pending short term loans transactions</p>
                                @endif
                            </div>
                            <div class="tab-pane fade {{ $tab == 'coml' ? 'show active' : '' }}" id="v-pills-settings"
                                role="tabpanel" aria-labelledby="v-pills-settings-tab">
                                <div class="row">
                                    <div class="col-8">
                                        <h5>Commodity Loans</h5>
                                    </div>
                                    <div class="col-4 pt-3 bankbalance">
                                        Bank Balance: <strong><span></span></strong>                                       
                                    </div>
                                </div>
                                @if($pendingCommodityTnxs->count() > 0)
                                <table class="table table-stripped table-bordered table-responsive-md">
                                    <thead>
                                        <th>Loan Date</th>
                                        <th>IPPIS</th>
                                        <th>Name</th>
                                        <th>Amount</th>
                                        <th>Type</th>
                                        <th>Action(s)</th>
                                    </thead>
                                    <tbody>
                                        @foreach($pendingCommodityTnxs as $pendingCommodityTnx)
                                        <tr>
                                            <td>{{ $pendingCommodityTnx->trxn_type == 'coml' ? $pendingCommodityTnx->loan_date : $pendingCommodityTnx->deposit_date }}
                                            </td>
                                            <td><a
                                                    href="{{ route('members.dashboard', $pendingCommodityTnx->ippis) }}">{{ $pendingCommodityTnx->ippis }}</a>
                                            </td>
                                            <td>
                                                @if($pendingCommodityTnx->commodity)
                                                {{ $pendingCommodityTnx->commodity->member ? $pendingCommodityTnx->commodity->member->full_name : '' }}
                                                @endif</td>
                                            <td class="text-right">
                                                {{ $pendingCommodityTnx->trxn_type == 'coml' ? number_format($pendingCommodityTnx->dr, 2) : number_format($pendingCommodityTnx->cr, 2) }}
                                            </td>
                                            <td>{{ $pendingCommodityTnx->transaction_type ? $pendingCommodityTnx->transaction_type->description : '' }}
                                            </td>
                                            <td class="text-center">

                                            @if(!$pendingCommodityTnx->commodity->start_processing && !$pendingCommodityTnx->commodity->is_approved)
                                                <a href="{{route('pendingTransactions.startProcessing', [$pendingCommodityTnx->commodity->id, 'coml'])}}" class="" onclick = "return confirm('Are you sure?')">Start Processing</a>
                                            @else
                                                <a data-toggle="modal" data-keyboard="false" data-target="#myModal"
                                                    data-remote="{{route('pendingTransactions.processApplications', [$pendingCommodityTnx->id, 'coml'])}}"
                                                    href="#" class="text-danger">Process</a>
                                            @endif
                                          
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                                @else
                                <p>There are no pending commodity loans transactions</p>
                                @endif
                            </div>
                            <div class="tab-pane fade {{ $tab == 'savings' ? 'show active' : '' }}" id="v-pills-home"
                                role="tabpanel" aria-labelledby="v-pills-home-tab">
                                <div class="row">
                                    <div class="col-8">
                                        <h5>Monthly Savings & Withdrawals</h5>
                                    </div>
                                    <div class="col-4 pt-3 bankbalance">
                                        Bank Balance: <strong><span></span></strong>                                       
                                    </div>
                                </div>
                                @if($pendingMonthlyTnxs->count() > 0)
                                <table class="table table-stripped table-bordered table-responsive-md">
                                    <thead>
                                        <th class="text-center">Deposit Date</th>
                                        <th class="text-center">IPPIS</th>
                                        <th class="text-center">Name</th>
                                        <th class="text-center">Amount</th>
                                        <th class="text-center">Type</th>
                                        <th class="text-center">Action(s)</th>
                                    </thead>
                                    <tbody>
                                        @foreach($pendingMonthlyTnxs as $pendingMonthlyTnx)
                                        <tr>
                                            <td>{{ $pendingMonthlyTnx->deposit_date->format('d-m-Y') }}</td>
                                            <td><a
                                                    href="{{ route('members.dashboard', $pendingMonthlyTnx->ippis) }}">{{ $pendingMonthlyTnx->ippis }}</a>
                                            </td>
                                            <td>{{ $pendingMonthlyTnx->member ? $pendingMonthlyTnx->member->full_name : '' }}
                                            </td>
                                            <td class="text-right amount">
                                                {{ $pendingMonthlyTnx->is_withdrawal == 1 || $pendingMonthlyTnx->is_withdrawal == 2 ? number_format($pendingMonthlyTnx->dr, 2) : number_format($pendingMonthlyTnx->cr, 2) }}
                                            </td>
                                            <td>{{ $pendingMonthlyTnx->transaction_type ? $pendingMonthlyTnx->transaction_type->description : '' }}
                                            </td>
                                            <td class="text-center">

                                                @if(!$pendingMonthlyTnx->start_processing && !$pendingMonthlyTnx->is_approved)
                                                    <a href="{{route('pendingTransactions.startProcessing', [$pendingMonthlyTnx->id, 'savings'])}}" class="" onclick = "return confirm('Are you sure?')">Start Processing</a>
                                                    @if($pendingMonthlyTnx->monthlySaving->is_withdrawal)
                                                     | 
                                                    <a class="text-success" href="{{ route('members.withdrawalPaymentVoucher', $pendingMonthlyTnx->id) }}" target="_blank">PV</a>
                                                    @endif
                                                @else
                                                    <a data-toggle="modal" data-keyboard="false" data-target="#myModal"
                                                        data-remote="{{route('pendingTransactions.processApplications', [$pendingMonthlyTnx->id, 'savings'])}}"
                                                        href="#" class="text-danger">Process</a>
                                                @endif

                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                                @else
                                <p>There are no pending monthly savings transactions</p>
                                @endif
                            </div>
                            <div class="tab-pane fade {{ $tab == 'shares' ? 'show active' : '' }}" id="shares"
                                role="tabpanel" aria-labelledby="shares-tab">
                                <div class="row">
                                    <div class="col-8">
                                        <h5>Shares</h5>
                                    </div>
                                    <div class="col-4 pt-3 bankbalance">
                                        Bank Balance: <strong><span></span></strong>                                       
                                    </div>
                                </div>
                                @if($pendingSharesTnxs->count() > 0)
                                <table class="table table-stripped table-bordered table-responsive-md">
                                    <thead>
                                        <th>Purchase Date</th>
                                        <th>IPPIS</th>
                                        <th>Name</th>
                                        <th>Amount</th>
                                        <th>Units</th>
                                        <th>Action(s)</th>
                                    </thead>
                                    <tbody>
                                        @foreach($pendingSharesTnxs as $pendingSharesTnx)
                                        <tr>
                                            <td>{{ $pendingSharesTnx->date_bought->toFormattedDateString() }}</td>
                                            <td><a
                                                    href="{{ route('members.dashboard', $pendingSharesTnx->ippis) }}">{{ $pendingSharesTnx->ippis }}</a>
                                            </td>
                                            <td>{{ $pendingSharesTnx->member ? $pendingSharesTnx->member->full_name : '' }}
                                            </td>
                                            <td class="text-right">{{ number_format($pendingSharesTnx->amount, 2) }}
                                            </td>
                                            <td class="text-center">{{ $pendingSharesTnx->units }}</td>
                                            <td class="text-center">
                                                @if($pendingSharesTnx->is_authorized == 0)
                                                    
                                                {!! Form::open(['route' => 'authorizeSharesTransaction', 'method' => 'POST', 'onsubmit' => "return confirm('Are you sure?')", 'id' => 'monthlySavingsForm']) !!}
                                                
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        {!! Form::select('bank', $banks, null, ['class' => 'form-control selectBank', 'id' => 'name', 'placeholder' => 'Select Bank']) !!}

                                                        {!! Form::hidden('amount', $pendingSharesTnx->amount) !!}
                                                    </div>
                                                    <div class="col-md-6">
                                                        {!! Form::hidden('trxn_number', $pendingSharesTnx->trxn_number) !!}
                                                        {!! Form::hidden('trxn_type', $pendingSharesTnx->trxn_type) !!}
                                                        {!! Form::hidden('ippis', $pendingSharesTnx->ippis) !!}
                                                        {!! Form::hidden('tab', 'shares') !!}

                                                        @if($pendingSharesTnx->is_authorized == 0)
                                                        <button name="action" type="submit" value="authorize" class="btn btn-xs btn-success">authorize</button>
                                                        <button name="action" type="submit" value="cancel" class="btn btn-xs btn-danger">cancel</button>
                                                        @elseif($pendingSharesTnx->is_authorized == 1)
                                                        <span class="text-success">A</span>
                                                        @else
                                                        <span class="text-danger">C</span>
                                                        @endif
                                                    </div>
                                                </div>

                                                {!! Form::close() !!}

                                                @endif
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                                @else
                                <p>There are no pending shares transactions</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div><!-- end col -->
</div>
@endsection
