@extends('master')

@section('body')
<!-- Page-Title -->


<div class="row">
    <div class="col-sm-12">
        <div class="page-title-box">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h4 class="page-title m-0">Chart of Accounts </h4>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card m-b-30">
            <div class="card-body">

              <h6>Create New Account</h6>
              <div class="row mt-3">
                <div class="col-lg-6">

                  <new-ledger-account></new-ledger-account>

                </div>
                <div class="col-lg-6"></div>
              </div>

            </div>
        </div>
    </div><!-- end col -->
</div>
@endsection
