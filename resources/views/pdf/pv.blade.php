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
    <div class="col-12">
        <div class="card m-b-30">
            <div class="card-body">
                @can('create long term loan')
                <div class="row">
                    <div class="col-12 text-right" style="font: 1.2rem bolder; ">
                      <p>{{$loan->pv_number}}</p>
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
                            <th>{{$loan->member->ippis}}</th>
                          </tr>
                          <tr>
                            <th>PAYEE: </th>
                            <th>{{$loan->member->full_name}}</th>
                          </tr>
                          <tr>
                            <th>ADDRESS/CENTER: </th>
                            <th>{{$loan->member->member_pay_point? $loan->member->member_pay_point->name : ''}}</th>
                          </tr>
                        </tbody>
                      </table>
                      <table class="table table-bordered mt-1">
                        <thead>
                          <tr>
                            <th class="text-center" style="width: 20%">DATE</th>
                            <th class="text-center" style="width: 60%">PARTICULARS</th>
                            <th class="text-center" style="width: 20%">AMOUNT (N)</th>
                          </tr>
                        </thead>
                        <tbody>
                          <tr>
                            <td>{{ $loan->loan_date->toFormattedDateString() }}</td>
                            <td>Payments of <b>N {{ number_format($loan->total_amount, 2) }}</b> to the above named being Loan granted to enable him/her meet up with personal needs which is to be paid in {{ $loan->no_of_months }} months</td>
                            <td class="text-right">{{ number_format($loan->total_amount, 2) }}</td>
                          </tr>
                          <tr>
                            <td></td>
                            <td class="text-right">LESS {{$loan->interest_percentage}}% INTREST</td>
                            <td class="text-right">{{ number_format($loan->interest, 2) }}</td>
                          </tr>
                          <tr>
                            <td></td>
                            <td class="text-right">LESS PROCESSING FEE</td>
                            <td class="text-right">{{ number_format($loan->processing_fee, 2) }}</td>
                          </tr>
                          <tr>
                            <td></td>
                            <td class="text-right">LESS ADJUSTMENT</td>
                            <td class="text-right">{{ number_format($loan->adjustment, 2) }}</td>
                          </tr>
                          <tr>
                            <td></td>
                            <td class="text-right">LESS TRANSFER FEE</td>
                            <td class="text-right">{{ number_format($loan->bank_charges, 2) }}</td>
                          </tr>
                          <tr>
                            <td></td>
                            <td class="text-center"><b>TOTAL NET</b></td>
                            <td class="text-right">{{ number_format($loan->net_payment, 2) }}</td>
                          </tr>
                        </tbody>
                      </table>

                      <div class="row mt-2 ml-1">
                        <p><b>AMOUNT: </b></p>
                      </div>
                      <div class="row mt-2 ml-1">
                        <p><b>PREPARED BY: </b> {{$loan->doneBy ? $loan->doneBy->full_name : ''}}</p>
                      </div>
                      <div class="row mt-2 ml-1">
                        <p><b>CHECKED BY: </b></p>
                      </div>
                      <div class="row mt-2 ml-1">
                        <p><b>APPROVED BY: </b></p>
                      </div>
                      <div class="row mt-2 ml-1">
                        <p><b>CHEQUE/MANDATE No: </b></p>
                      </div>
                      <div class="row mt-2 ml-1">
                        <p><b>DATE OF CREDIT: </b></p>
                      </div>
                    </div>
                </div>
                @endcan
            </div>
        </div>
    </div>
</div>
