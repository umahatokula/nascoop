@extends('master')

@section('body')
<!-- Page-Title -->


<div class="row">
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
    <div class="col-12">
        <div class="card m-b-30">
            <div class="card-body">

              <h4>Loans Durations Settings</h4>

              <div class="row mt-3">
                <div class="col-lg-6">
                  <ltl-loans-durations></ltl-loans-durations>
                </div>
                <div class="col-lg-6">
                  <stl-loans-durations></stl-loans-durations>
                </div>
              </div>

                <div class="row mt-3">
                <div class="col-lg-6">
                    <comm-loans-durations></comm-loans-durations>
                </div>
                <div class="col-lg-6">
                </div>
                </div>
              
            </div>
        </div>
    </div><!-- end col -->
</div>
@endsection
