@extends('master')

@section('body')

@if($trxn)
@if($trxn->payments->count() > 0)
<div class="row d-print-none">
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
    <div class="col-lg-12">
        <div class="card m-b-30">
            <div class="card-body">

                <div class="row">
                    <div class="col-12 d-none d-print-block">
                        <h5 class="text-center">PAYMENTS</h5>
                        <h6 class="text-center">NASRDA STAFF MULTIPURPOSE COOPERATIVE SOCIETY LTD</h6>
                    </div>
                </div>
                <div class="row d-print-none">
                    <div class="col-md-6">
                        <h6>Payments for {{ $trxn->center ? $trxn->center->name : '' }}</h6>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3 float-right">
                            <a href="#" onclick="window.print()" class="btn btn-primary">Print</a>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <table class="table table-responsive-md table-striped table-bordered" style="border: solid #000 2px">
                            <thead>
                                <tr>
                                    <!-- <th style="border-bottom: solid #000 2px">Center</th> -->
                                    <th style="border-bottom: solid #000 2px">&nbsp</th>
                                    <th class="text-center" colspan="3" style="border-left: solid #000 2px; border-bottom: solid #000 2px">SAVINGS</th>
                                    <th class="text-center" colspan="3" style="border-left: solid #000 2px; border-bottom: solid #000 2px">LONG TERM</th>
                                    <th class="text-center" colspan="3" style="border-left: solid #000 2px; border-bottom: solid #000 2px">SHORT TERM</th>
                                    <th class="text-center" colspan="3" style="border-left: solid #000 2px; border-bottom: solid #000 2px">COMMODITY</th>
                                </tr>
                                <tr>
                                    <!-- <th style="border-bottom: solid #000 2px">Center</th> -->
                                    <th style="border-bottom: solid #000 2px">IPPIS Code</th>
                                    <th style="border-left: solid #000 2px; border-bottom: solid #000 2px">DR</th>
                                    <th style="border-bottom: solid #000 2px">CR</th>
                                    <th style="border-bottom: solid #000 2px">Balance</th>
                                    <th style="border-left: solid #000 2px; border-bottom: solid #000 2px">DR</th>
                                    <th style="border-bottom: solid #000 2px">CR</th>
                                    <th style="border-bottom: solid #000 2px">Balance</th>
                                    <th style="border-left: solid #000 2px; border-bottom: solid #000 2px">DR</th>
                                    <th style="border-bottom: solid #000 2px">CR</th>
                                    <th style="border-bottom: solid #000 2px">Balance</th>
                                    <th style="border-left: solid #000 2px; border-bottom: solid #000 2px">DR</th>
                                    <th style="border-bottom: solid #000 2px">CR</th>
                                    <th style="border-right: solid #000 2px; border-bottom: solid #000 2px">Balance</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($trxn->payments as $payment)
                                <tr>
                                    <td class="text-center" scope="row">{{ $payment->deduction_for->format('d-m-Y') }}</td>
                                    <td class="text-right" style="border-left: solid #000 2px">{{ number_format( $payment->ms_dr, 2) }}</td>
                                    <td class="text-right">{{ number_format( $payment->ms_cr, 2) }}</td>
                                    <td class="text-right">{{ number_format( $payment->ms_bal, 2) }}</td>
                                    <td class="text-right" style="border-left: solid #000 2px">{{ number_format( $payment->ltl_dr, 2) }}</td>
                                    <td class="text-right">{{ number_format( $payment->ltl_cr, 2) }}</td>
                                    <td class="text-right">{{ number_format( $payment->ltl_bal, 2) }}</td>
                                    <td class="text-right" style="border-left: solid #000 2px">{{ number_format( $payment->stl_dr, 2) }}</td>
                                    <td class="text-right">{{ number_format( $payment->stl_cr, 2) }}</td>
                                    <td class="text-right">{{ number_format( $payment->stl_bal, 2) }}</td>
                                    <td class="text-right" style="border-left: solid #000 2px">{{ number_format( $payment->coml_dr, 2) }}</td>
                                    <td class="text-right">{{ number_format( $payment->coml_cr, 2) }}</td>
                                    <td class="text-right">{{ number_format( $payment->coml_bal, 2) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                @else
                <div class="card">
                    <div class="card-body">
                        <p class="card-text">No Payments</p>
                    </div>
                </div>
                @endif
                
            </div>
        </div>
    </div>
</div>
@else
<<div class="card">
    <div class="card-body">
        <p class="card-text">No record found</p>
    </div>
</div>
@endif

@endsection

@section('js')

@endsection