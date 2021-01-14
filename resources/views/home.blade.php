@extends('master')

@section('body')
<!-- Page-Title -->
<div class="row">
    <div class="col-sm-12">
        <div class="page-title-box">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h4 class="page-title m-0">Dashboard</h4>
                </div>
                <div class="col-md-4">
                </div>
            </div>
        </div>
    </div>
</div><!-- end page title end breadcrumb -->
<div class="row">
    <div class="col-xl-3 col-md-6">
        <div class="card bg-primary mini-stat text-white">
            <div class="p-3 mini-stat-desc">
                <div class="clearfix">
                    <h6 class="text-uppercase mt-0 float-left text-white-50">Savings</h6>
                    <h4 class="mb-3 mt-0 float-right">&#8358; {{ number_format($totalSavings) }}</h4>
                </div>
            </div>
            <!-- <div class="p-3">
                <div class="float-right"><a href="#" class="text-white-50"><i class="mdi mdi-cube-outline h5"></i></a>
                </div>
                <p class="font-14 m-0">Last : 1447</p>
            </div> -->
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card bg-info mini-stat text-white">
            <div class="p-3 mini-stat-desc">
                <div class="clearfix">
                    <h6 class="text-uppercase mt-0 float-left text-white-50">Long Term Balance</h6>
                    <h4 class="mb-3 mt-0 float-right">&#8358; {{ number_format($totalLTL) }}</h4>
                </div>
            </div>
            <!-- <div class="p-3">
                <div class="float-right"><a href="#" class="text-white-50"><i class="mdi mdi-buffer h5"></i></a></div>
                <p class="font-14 m-0">Last : $47,596</p>
            </div> -->
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card bg-pink mini-stat text-white">
            <div class="p-3 mini-stat-desc">
                <div class="clearfix">
                    <h6 class="text-uppercase mt-0 float-left text-white-50">Short Term Balance</h6>
                    <h4 class="mb-3 mt-0 float-right">&#8358; {{ number_format($totalSTL) }}</h4>
                </div>
            </div>
            <!-- <div class="p-3">
                <div class="float-right"><a href="#" class="text-white-50"><i
                            class="mdi mdi-tag-text-outline h5"></i></a></div>
                <p class="font-14 m-0">Last : 15.8</p>
            </div> -->
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card bg-success mini-stat text-white">
            <div class="p-3 mini-stat-desc">
                <div class="clearfix">
                    <h6 class="text-uppercase mt-0 float-left text-white-50">Commodities Balance</h6>
                    <h4 class="mb-3 mt-0 float-right">&#8358; {{ number_format($totalCommodity) }}</h4>
                </div>
            </div>
            <!-- <div class="p-3">
                <div class="float-right"><a href="#" class="text-white-50"><i
                            class="mdi mdi-briefcase-check h5"></i></a></div>
                <p class="font-14 m-0">Last : 1776</p>
            </div> -->
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card bg-success mini-stat text-white">
            <div class="p-3 mini-stat-desc">
                <div class="clearfix">
                    <h6 class="text-uppercase mt-0 float-left text-white-50">Total units of shares</h6>
                    <h4 class="mb-3 mt-0 float-right">{{ number_format($shares) }}</h4>
                </div>
            </div>
            <!-- <div class="p-3">
                <div class="float-right"><a href="#" class="text-white-50"><i
                            class="mdi mdi-briefcase-check h5"></i></a></div>
                <p class="font-14 m-0">Last : 1776</p>
            </div> -->
        </div>
    </div>
</div><!-- end row -->

<div class="row">
    <div class="col-xl-6">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Membership by Center</h4>
                <table class="table table-bordered table-condensed">
                    <thead>
                        <tr>
                            <th class="text-left">CENTRE</th>
                            <th class="text-center">COUNT</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($membersByCenterData as $center => $data)
                        <tr>
                            <td class="text-left"><b>{{ $center  }}</b></td>
                            <td class="text-center">{{ $data }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div><!-- end row -->

<div class="row">
    <div class="col-xl-12">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Savings</h4>
                <div id="savings_chart" style="height: 300px;"></div>
            </div>
        </div>
    </div>
    <div class="col-xl-12">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Long Term Loans Balance</h4>
                <div id="ltl_chart" style="height: 300px;"></div>
            </div>
        </div>
    </div>
    <div class="col-xl-12">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Short Term Loans Balance</h4>
                <div id="stl_chart" style="height: 300px;"></div>
            </div>
        </div>
    </div>
    <div class="col-xl-12">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Commodities Loans Balance</h4>
                <div id="coml_chart" style="height: 300px;"></div>
            </div>
        </div>
    </div>
</div><!-- end row -->

@endsection

@section('js')
<!-- dashboard js -->
<script src="{{ asset('assets/pages/dashboard.int.js') }}"></script>
<script src="{{ asset('js/Chart.min.js') }}"></script>
@endsection
