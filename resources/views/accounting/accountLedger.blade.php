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

                
                @hasanyrole('super-admin|accountant')
                
                <div class="row">
                    <div class="col-md-12">
                        {!! Form::open(['route' => ['accountLedger', $account->ledger_no], 'method' => 'get']) !!}
                        <div class="row">
                            <div class="col-md-2 mb-1">
                                <p>Start Date</p>
                                {{Form::date('dateFrom', $dateFrom, ['class' => 'form-control'])}}
                            </div>
                            <div class="col-md-2">
                                <p>End Date</p>
                                {{Form::date('dateTo', $dateTo, ['class' => 'form-control mb-3'])}}
                            </div>
                            <div class="col-md-2 mb-1">
                                <p>Order By</p>
                                {{Form::select('orderBy', $orderBys, $orderBy, ['class' => 'form-control'])}}
                            </div>
                            <div class="col-md-2 mb-1">
                                <p>Order Direction</p>
                                {{Form::select('orderDirection', $orderDirections, $orderDirection, ['class' => 'form-control'])}}
                            </div>
                            <div class="col-md-2">
                                <p>&nbsp</p>
                                <button type="submit" class="btn btn-primary waves-effect waves-light mb-3">Filter</button>
                                <a href="{{ route('accountLedgerPdf', [$account->ledger_no, $dateFrom, $dateTo]) }}" type="submit" class="btn btn-danger waves-effect waves-light mb-3">PDF</a>
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
                                        <th colspan="5" class="text-center"><h4>General Ledger</h4></th>
                                    </tr>
                                    <tr>
                                        <th colspan="2">Account Name: {{ $account->account_name }}</th>
                                        <th colspan="3">Account Code: {{ $account->ledger_no }}</th>
                                    </tr>
                                    <tr>
                                        <th colspan="3">&nbsp</th>
                                        <th class="text-right" colspan="1">Total DR: {{ number_format($total_dr, 2) }}</th>
                                        <th class="text-right" colspan="1">Total CR: {{ number_format($total_cr, 2) }}</th>
                                    </tr>
                                    <tr>
                                        <th>Posting Date</th>
                                        <th>Value Date</th>
                                        <th>Description</th>
                                        <th class="text-right">DR</th>
                                        <th class="text-right">CR</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($trxns as $trxn)
                                    <tr style="line-height: 0;">
                                        <td>{{ $trxn->created_at->toFormattedDateString() }}</td>
                                        <td>{{ $trxn->value_date ? $trxn->value_date->toFormattedDateString() : '' }}</td>
                                        <td>{{ $trxn->description }}</td>
                                        <td class="text-right">{{ $trxn->ledger_no_dr == $account->ledger_no ? number_format($trxn->amount, 2) : '' }}</td>
                                        <td class="text-right">{{ $trxn->ledger_no == $account->ledger_no ? number_format($trxn->amount, 2) : '' }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{$trxns->appends(request()->except('page'))->links()}}

                    <!-- <accountledger></accountledger> -->
                    </div>
                </div>

                @endrole

            </div>
        </div>
    </div><!-- end col -->
</div>
@endsection
