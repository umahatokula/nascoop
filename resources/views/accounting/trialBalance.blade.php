@extends('master')

@section('body')

<div class="row">
    <div class="col-sm-12">
        <div class="page-title-box">
            <div class="row d-flex justify-content-end">
                <div class="col-12">
                &nbsp
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">

                @include('accounting.linksPartial')

            </div>
        </div>
    </div><!-- end col -->
</div>

<div class="row">
    <div class="col-12">
        <div class="card m-b-30">
            <div class="card-body">

                <div class="row">
                    <div class="col-md-12">
                        {!! Form::open(['route' => 'trialBalance', 'method' => 'get']) !!}
                        <div class="row">
                            <div class="col-md-2 text-left pt-2">
                                Start Date
                            </div>
                            <div class="col-md-3">
                                {{Form::date('dateFrom', $dateFrom, ['class' => 'form-control mb-3'])}}
                            </div>
                            <div class="col-md-2 text-left pt-2">
                                End Date
                            </div>
                            <div class="col-md-3">
                                {{Form::date('dateTo', $dateTo, ['class' => 'form-control mb-3'])}}
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary waves-effect waves-light mb-3">Filter</button>
                                <!-- <a href="{{ route('trialBalancePdf', [$dateFrom, $dateTo]) }}" type="submit" class="btn btn-danger waves-effect waves-light mb-3">PDF</a> -->
                            </div>
                        </div>
                        {!! Form::close() !!}
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-lg-12">
                    
                        <div class="row">
                        <div class="col-12">
                        <table class="table table-bordered table-striped table-responsive-md">
                            <thead>
                                <tr>
                                    <th>Code</th>
                                    <th>Account Name</th>
                                    <th class="text-right">Dr</th>
                                    <th class="text-right">Cr</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($trialBalances as $trialBalance)
                                <tr style="line-height: 0;">
                                    @if($trialBalance[0]->usage == 'header')
                                        <td>{{ $trialBalance[0]->ledger_no }}</td>
                                        <td>{{ $trialBalance[0]->account_name }}</td>
                                    @else
                                        <td><a href="{{ $trialBalance[0]->usage == 'detail' ? route('accountLedger', $trialBalance[0]->ledger_no) : '#' }}">{{ $trialBalance[0]->ledger_no }}</a></td>
                                        <td><a href="{{ $trialBalance[0]->usage == 'detail' ? route('accountLedger', $trialBalance[0]->ledger_no) : '#' }}">{{ $trialBalance[0]->account_name }}</a></td>
                                    @endif

                                    <td class="text-right"><b>{{ $trialBalance[0]->show_total_amount_in_report ? number_format($trialBalance[2], 2) : '' }}</b></td>
                                    <td class="text-right"><b>{{ $trialBalance[0]->show_total_amount_in_report ? number_format($trialBalance[3], 2) : '' }}</b></td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>

                        </div>
                        </div>
                        <!-- <trialbalance></trialbalance> -->
                    </div>
                </div>

            </div>
        </div>
    </div><!-- end col -->
</div>
@endsection
