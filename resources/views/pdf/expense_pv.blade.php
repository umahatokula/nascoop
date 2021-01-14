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
                    <div class="col-6 text-right" style="font: 1.2rem bolder; ">
                      <p>{{$expense->pv_number}}</p>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                      <h5 class="text-center">PAYMENT VOUCHER</h5>
                      <h6 class="text-center">NASRDA STAFF MULTIPURPOSE COOPERATIVE SOCIETY LTD</h6>


                      <div class="row mt-2 ml-1">
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
                      <div class="row mt-2 ml-1">
                        <p><b>DATE OF CREDIT: </b></p>
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
