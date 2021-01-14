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
                <div class="col-md-12">

                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card m-b-30">
            <div class="card-body">
                <div class="row">
                    <div class="col-6">
                      <!-- <a href="{{ route('expensesPvPdf', $expense->trxn_number) }}" class="btn btn-primary waves-effect waves-light mb-1 d-print-none"><i class="mdi  mdi-file-pdf-box"></i> PDF</a> -->
                    </div>
                    <div class="col-6 text-right" style="font: 1.2rem bolder; ">
                      <p>{{$expense->pv_number}}</p>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                      <h5 class="text-center">PAYMENT VOUCHER</h5>
                      <h6 class="text-center">NASRDA STAFF MULTIPURPOSE COOPERATIVE SOCIETY LTD</h6>

                      <table class="table table-bordered mb-4">
                          <tbody>
                              <tr>
                                  <th style="width: 20%">MEMBER'S CARD NO:</th>
                                  <td>&nbsp</td>
                              </tr>
                              <tr>
                                  <th style="width: 20%">PAYEE:</th>
                                  <td>{{$expense->supplier ? $expense->supplier->fname.' '.$expense->supplier->lname : null  }}</td>
                              </tr>
                              <tr>
                                  <th style="width: 20%">ADDRESS:</th>
                                  <td>{{$expense->supplier ? $expense->supplier->address : null  }}</td>
                              </tr>
                          </tbody>
                      </table>

                      <table class="table table-bordered my-5">
                          <tbody>
                              <tr>
                                  <th>DATE</th>
                                  <th>PARTICULARS</th>
                                  <th class="text-right">AMOUNT(&#8358;)</th>
                              </tr>
                              <tr>
                                  <td>{{ $expense->created_at->toFormattedDateString() }}</td>
                                  <td style="width: 50%">Payment of <b>&#8358;{{ number_format($expense->amount, 2) }}</b> to the above person being purchase of <b>{{$expense->description}}</b>. Vide Minute of approval as attached.</td>
                                  <td class="text-right">{{ number_format($expense->amount, 2) }}</td>
                              </tr>
                              <tr>
                                  <td>&nbsp</td>
                                  <td><b>TOTAL</b></td>
                                  <td class="text-right">{{ number_format($expense->amount, 2) }}</td>
                              </tr>
                          </tbody>
                      </table>

                      <div class="row mt-4 ml-1">
                        <p><b>AMOUNT: </b></p>
                      </div>
                      <div class="row mt-2 ml-1">
                        <p><b>PREPARED BY: </b> &nbsp {{$expense->doneBy ? $expense->doneBy->full_name : ''}}</p>
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
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection


@section('js')

@endsection
