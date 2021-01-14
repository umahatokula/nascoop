@extends('master')

@section('body')
<!-- Page-Title -->


<div class="row">
    <div class="col-sm-12">
        <div class="page-title-box">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h4 class="page-title m-0">Chart of Accounts (COA)</h4>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card m-b-30">
            <div class="card-body">

              <h6>Create new COA</h6>
              <div class="row mt-3">
                <div class="col-lg-6">

                  <new-coa></new-coa>

                </div>
                <div class="col-lg-6"></div>
              </div>

            </div>
        </div>
    </div><!-- end col -->
</div>
@endsection
