<link href="{{ public_path('assets/css/bootstrap.min.css') }}" rel="stylesheet">
<style>
.pageStyle {
    font-size: 0.8rem;
}
.table-bordered {
    border: 1px solid #000000;
}
.table-bordered th {
    border: 1px solid #000000;
}
.table thead th {
    vertical-align: bottom;
    border-bottom: 2px solid #000000;
}
.table-bordered td, .table-bordered th {
    border: 1px solid #000000;
}
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

<div class="row pageStyle">
    <div class="col-md-12">
        <div class="card m-b-30">
            <div class="card-body">
                @can('withdraw from savings')
                <div class="row">
                    <div class="col-12 text-right" style="font: 1.2rem bolder; ">
                      <p>{{$withdrawal->pv_number}}</p>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                      <h5 class="text-center">PAYMENT VOUCHER</h5>
                      <h6 class="text-center">NASRDA MEMBER MULTIPURPOSE COOPERATIVE SOCIETY LTD</h6>
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
                            <th class="text-center" style="width: 20%">AMOUNT (N)</th>
                          </tr>
                        </thead>
                        <tbody>
                          <tr>
                            <td>{{ $withdrawal->withdrawal_date->toFormattedDateString() }}</td>
                            @if($withdrawal->is_withdrawal == 1)
                            <td>
                            Payments of <b>N {{ number_format($withdrawal->dr, 2) }}</b> to the above named being Withdrawal from Savings to enable him/her meet up with personal need. Vide Minute of approval as attached.
                            </td>
                            @endif
                            @if($withdrawal->is_withdrawal == 2)
                            <td>
                            Payments of <b>N {{ number_format($withdrawal->dr, 2) }}</b> to the above named being amount for Refund for over deduction for the month of {{ strtoupper($withdrawal->withdrawal_date->format('M Y')) }}. Vide Minute of approval as attached.
                            </td>
                            @endif
                            <td class="text-right">{{ number_format($withdrawal->dr, 2) }}</td>
                          </tr>
                          <tr>
                            <td></td>
                            <td class="text-right">LESS {{$withdrawal->interest_percentage}}% INTREST</td>
                            <td class="text-right">{{ number_format($withdrawal->interest, 2) }}</td>
                          </tr>
                          @if($withdrawal->is_withdrawal == 1)
                          <tr>
                            <td></td>
                            <td class="text-right">LESS PROCESSING FEE</td>
                            <td class="text-right">{{ number_format($withdrawal->processing_fee, 2) }}</td>
                          </tr>
                          <tr>
                            <td></td>
                            <td class="text-right">LESS TRANSFER FEE</td>
                            <td class="text-right">{{ number_format($withdrawal->bank_charges, 2) }}</td>
                          </tr>
                          @endif
                          <tr>
                            <td></td>
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
