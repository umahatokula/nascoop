@extends('master')

@section('body')
<!-- Page-Title -->


<div class="row">
    <div class="col-sm-12">
        <div class="page-title-box">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h4 class="page-title m-0">Buy Shares <span class="text-info">[ {{ $member->full_name }} |
                            {{ $member->ippis }} ]</span><span
                            class="text-{{ $member->is_active? 'success' : 'danger' }}">[
                            {{ $member->is_active? 'active' : 'inactive' }} ]</span></h4>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card m-b-30">
            <div class="card-body">

              @can('create long term loan')
              <a href="{{route('members.dashboard', $member->ippis)}}"
                  class="btn btn-primary waves-effect waves-light"><i class="mdi mdi-backspace-outline"></i>
                  Dashboard</a>
              <a href="{{route('sharesShow', $member->ippis)}}"
                  class="btn btn-primary waves-effect waves-light"><i class="mdi mdi-backspace-outline"></i> Shares</a>

              <div class="row mt-3">
                <div class="col-lg-6">
                  <shares-buy :ippis="{{ $member->ippis }}"></shares-buy>
                </div>
                <div class="col-lg-6"></div>
              </div>
              @endcan
            </div>
        </div>
    </div><!-- end col -->
</div>
@endsection
