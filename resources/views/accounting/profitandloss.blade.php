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

                <div class="row mb-3 mb-lg-5">
                    <div class="col-12 text-center">
                        <h2>Profit & Loss Statement</h2>
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

                @role('accountant')
                
                <div class="row">
                    <div class="col-md-12">
                        {!! Form::open(['route' => 'accountingProfitAndLoss', 'method' => 'get']) !!}
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

                        <h4>Income</h4>

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
                                        @foreach($incomeAccountBalances['account_type_data'] as $incomeBalance)
                                        <tr>
                                            @if($incomeBalance[0]->usage == 'header')
                                                    <td>{{ $incomeBalance[0]->account_name }}</td>
                                                    @if($incomeBalance[0]->show_total_amount_in_report)
                                                    <td class="text-right">{{ number_format($incomeBalance[1], 2) }}</td>
                                                    @else
                                                    <td class="text-right">&nbsp</td>
                                                    @endif
                                            @endif

                                            @if($incomeBalance[0]->usage == 'detail')
                                                    <td><a href="{{ $incomeBalance[0]->usage == 'detail' ? route('accountLedger', $incomeBalance[0]->ledger_no) : '#' }}">{{ $incomeBalance[0]->account_name }}</a></td>
                                                    @if($incomeBalance[0]->show_total_amount_in_report)
                                                    <td class="text-right">{{ number_format($incomeBalance[1], 2) }}</td>
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
                                                {{ number_format($incomeAccountBalances['account_type_total'], 2) }}
                                            </td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>

                        <h4>Expenditure</h4>

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
                                        @foreach($expenseAccountBalances['account_type_data'] as $expenseBalance)
                                        <tr>
                                            @if($expenseBalance[0]->usage == 'header')
                                                    <td>{{ $expenseBalance[0]->account_name }}</td>
                                                    @if($expenseBalance[0]->show_total_amount_in_report)
                                                    <td class="text-right">{{ number_format($expenseBalance[1], 2) }}</td>
                                                    @else
                                                    <td class="text-right">&nbsp</td>
                                                    @endif
                                            @endif

                                            @if($expenseBalance[0]->usage == 'detail')
                                                    <td><a href="{{ $expenseBalance[0]->usage == 'detail' ? route('accountLedger', $expenseBalance[0]->ledger_no) : '#' }}">{{ $expenseBalance[0]->account_name }}</a></td>
                                                    @if($expenseBalance[0]->show_total_amount_in_report)
                                                    <td class="text-right">{{ number_format($expenseBalance[1], 2) }}</td>
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
                                                {{ number_format($expenseAccountBalances['account_type_total'], 2) }}
                                            </td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                        <!-- <profitandloss></profitandloss> -->
                    </div>
                </div>

                @endrole

            </div>
        </div>
    </div><!-- end col -->
</div>
@endsection
