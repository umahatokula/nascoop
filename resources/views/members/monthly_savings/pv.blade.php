@extends('master')

@section('body')
<style>
  .hline { width:100%; height:1px; background: #fff }
</style>

<!-- Page-Title -->
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
    <div class="col-md-4">
        <div class="card m-b-30">
            <div class="card-body">
              <a href="{{route('members.dashboard', $withdrawal->member->ippis)}}"
                    class="btn btn-primary waves-effect waves-light mb-1"><i class="mdi mdi-backspace-outline"></i>
                    Dashboard</a>
            </div>
        </div>
    </div>
    <div class="col-md-8">
        <div class="card m-b-30">
            <div class="card-body">
                @can('withdraw from savings')
                <div class="row">
                    <div class="col-6">
                      <a href="{{ route('members.withdrawalPaymentVoucherPDF', $withdrawal->id) }}" class="btn btn-primary waves-effect waves-light mb-1"><i class="mdi  mdi-file-pdf-box"></i> PDF</a>
                    </div>
                    <div class="col-6 text-right" style="font: 1.2rem bolder; ">
                      <p>{{$withdrawal->pv_number}}</p>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                      <h5 class="text-center">PAYMENT VOUCHER</h5>
                      <h6 class="text-center">NASRDA STAFF MULTIPURPOSE COOPERATIVE SOCIETY LTD</h6>
                      <table class="table table-bordered mt-4">
                        <tbody>
                          <tr>
                            <th>MEMBER'S CARD NO: </th>
                            <th></th>
                          </tr>
                          <tr>
                            <th>IPPIS NO: </th>
                            <th>{{$withdrawal->member->ippis}}</th>
                          </tr>
                          <tr>
                            <th>PAYEE: </th>
                            <th>{{$withdrawal->member->full_name}}</th>
                          </tr>
                          <tr>
                            <th>ADDRESS/CENTER: </th>
                            <th>{{$withdrawal->member->member_pay_point? $withdrawal->member->member_pay_point->name : ''}}</th>
                          </tr>
                        </tbody>
                      </table>
                      <table class="table table-bordered mt-5">
                        <thead>
                          <tr>
                            <th class="text-center" style="width: 20%">DATE</th>
                            <th class="text-center" style="width: 60%">PARTICULARS</th>
                            <th class="text-center" style="width: 20%">AMOUNT (&#8358;)</th>
                          </tr>
                        </thead>
                        <tbody>
                          <tr>
                            <td>{{ $withdrawal->withdrawal_date->toFormattedDateString() }}</td>
                            @if($withdrawal->is_withdrawal == 1)
                            <td>
                            Payments of <b>&#8358; {{ number_format($withdrawal->dr, 2) }}</b> to the above named being Withdrawal from Savings to enable him/her meet up with personal need. Vide Minute of approval as attached.
                            </td>
                            @endif
                            @if($withdrawal->is_withdrawal == 2)
                            <td>
                            Payments of <b>&#8358; {{ number_format($withdrawal->dr, 2) }}</b> to the above named being amount for Refund for over deduction for the month of {{ $withdrawal->withdrawal_date->format('m Y') }}. Vide Minute of approval as attached.
                            </td>
                            @endif
                            <td class="text-right">{{ number_format($withdrawal->dr, 2) }}</td>
                          </tr>
                          <tr>
                            <td>&nbsp</td>
                            <td class="text-right">LESS {{$withdrawal->interest_percentage}}% INTEREST</td>
                            <td class="text-right">{{ number_format($withdrawal->interest, 2) }}</td>
                          </tr>
                          @if($withdrawal->is_withdrawal == 1)
                          <tr>
                            <td>&nbsp</td>
                            <td class="text-right">LESS PROCESSING FEE</td>
                            <td class="text-right">{{ number_format($withdrawal->processing_fee, 2) }}</td>
                          </tr>
                          <tr>
                            <td>&nbsp</td>
                            <td class="text-right">LESS TRANSFER FEE</td>
                            <td class="text-right">{{ number_format($withdrawal->bank_charges, 2) }}</td>
                          </tr>
                          @endif
                          <tr>
                            <td>&nbsp</td>
                            <td class="text-center"><b>TOTAL NET</b></td>
                            <td class="text-right">{{ number_format($withdrawal->net_payment, 2) }}</td>
                          </tr>
                        </tbody>
                      </table>

                      <div class="row mt-2 ml-1">
                        <p><b>AMOUNT: </b></p>
                      </div>
                      <div class="row mt-2 ml-1">
                        <p><b>PREPARED BY: </b> {{$withdrawal->doneBy ? $withdrawal->doneBy->full_name : ''}}</p>
                      </div>
                      <div class="row mt-2 ml-1">
                        <p><b>CHECKED BY: </b></p>
                      </div>
                      <div class="row mt-2 ml-1">
                        <p><b>APPROVED BY: </b></p>
                      </div>
                      <div class="row mt-2 ml-1">
                        <p><b>CHEQUE No: </b></p>
                      </div>
                      @if($withdrawal->is_withdrawal == 1)
                      <div class="row mt-2 ml-1">
                        <p><b>BENEFICIARY's SIGN/DATE: </b></p>
                      </div>
                      @endif
                    </div>
                </div>
                @endcan
            </div>
        </div>
    </div>
</div>

@endsection


@section('js')

@endsection
