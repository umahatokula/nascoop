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
                <div class="row mb-5">
                    <div class="col-12 text-center">
                        <h2>Balance Sheet</h2>
                        <h6 class="text-center">NASRDA MEMBER MULTIPURPOSE COOPERATIVE SOCIETY LTD</h6>
                    </div>
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

                <div class="row">
                    <div class="col-md-12">
                        {!! Form::open(['route' => 'accountingBalanceSheet', 'method' => 'get']) !!}
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
                                <button type="submit"
                                    class="btn btn-primary waves-effect waves-light mb-3">Filter</button>
                            </div>
                        </div>
                        {!! Form::close() !!}
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-lg-12">

                        <h4>Assets</h4>

                        <div class="row">
                            <div class="col-12">
                                <table class="table table-bordered table-condensed">
                                    <thead>
                                        <tr>
                                            <th>Account</th>
                                            <th>Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($assetAccountBalances['account_type_data'] as $assetBalance)
                                        <tr>
                                            @if($assetBalance[0]->usage == 'header')
                                                    <td>{{ $assetBalance[0]->account_name }}</td>
                                                    @if($assetBalance[0]->show_total_amount_in_report)
                                                    <td class="text-right">{{ number_format($assetBalance[1], 2) }}</td>
                                                    @else
                                                    <td class="text-right">&nbsp</td>
                                                    @endif
                                            @endif

                                            @if($assetBalance[0]->usage == 'detail')
                                                    <td><a href="{{ $assetBalance[0]->usage == 'detail' ? route('accountLedger', $assetBalance[0]->ledger_no) : '#' }}">{{ $assetBalance[0]->account_name }}</a></td>
                                                    @if($assetBalance[0]->show_total_amount_in_report)
                                                    <td class="text-right">{{ number_format($assetBalance[1], 2) }}</td>
                                                    @else
                                                    <td class="text-right">&nbsp</td>
                                                    @endif
                                            @endif
                                        </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td colspan="2" class="text-right">
                                                {{ number_format($assetAccountBalances['account_type_total'], 2) }}</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>


                        <h4>Liabilities</h4>

                        <div class="row">
                            <div class="col-12">
                                <table class="table table-bordered table-condensed">
                                    <thead>
                                        <tr>
                                            <th>Account</th>
                                            <th>Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($liabilityAccountBalances['account_type_data'] as $liabilityBalance)
                                        <tr>
                                            @if($liabilityBalance[0]->usage == 'header')
                                                    <td>{{ $liabilityBalance[0]->account_name }}</td>
                                                    @if($liabilityBalance[0]->show_total_amount_in_report)
                                                    <td class="text-right">{{ number_format($liabilityBalance[1], 2) }}</td>
                                                    @else
                                                    <td class="text-right">&nbsp</td>
                                                    @endif
                                            @endif

                                            @if($liabilityBalance[0]->usage == 'detail')
                                                    <td><a href="{{ $liabilityBalance[0]->usage == 'detail' ? route('accountLedger', $liabilityBalance[0]->ledger_no) : '#' }}">{{ $liabilityBalance[0]->account_name }}</a></td>
                                                    @if($liabilityBalance[0]->show_total_amount_in_report)
                                                    <td class="text-right">{{ number_format($liabilityBalance[1], 2) }}</td>
                                                    @else
                                                    <td class="text-right">&nbsp</td>
                                                    @endif
                                            @endif
                                        </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td colspan="2" class="text-right">
                                                {{ number_format($liabilityAccountBalances['account_type_total'], 2) }}
                                            </td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>

                        <h4>Equities</h4>

                        <div class="row">
                            <div class="col-12">
                                <table class="table table-bordered table-condensed">
                                    <thead>
                                        <tr>
                                            <th>Account</th>
                                            <th>Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($equityAccountBalances['account_type_data'] as $equityBalance)
                                        <tr>
                                            @if($equityBalance[0]->usage == 'header')
                                                    <td>{{ $equityBalance[0]->account_name }}</td>
                                                    @if($equityBalance[0]->show_total_amount_in_report)
                                                    <td class="text-right">{{ number_format($equityBalance[1], 2) }}</td>
                                                    @else
                                                    <td class="text-right">&nbsp</td>
                                                    @endif
                                            @endif

                                            @if($equityBalance[0]->usage == 'detail')
                                                    <td><a href="{{ $equityBalance[0]->usage == 'detail' ? route('accountLedger', $equityBalance[0]->ledger_no) : '#' }}">{{ $equityBalance[0]->account_name }}</a></td>
                                                    @if($equityBalance[0]->show_total_amount_in_report)
                                                    <td class="text-right">{{ number_format($equityBalance[1], 2) }}</td>
                                                    @else
                                                    <td class="text-right">&nbsp</td>
                                                    @endif
                                            @endif
                                        </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td colspan="2" class="text-right">
                                                {{ number_format($equityAccountBalances['account_type_total'], 2) }}
                                            </td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>

                        <!-- <BalanceSheet></BalanceSheet> -->
                    </div>
                </div>

                @else
                <p>You do not have the permission to view this content.</p>
                @endrole

            </div>
        </div>
    </div><!-- end col -->
</div>
@endsection
