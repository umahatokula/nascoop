@extends('master')

@section('body')
<!-- Page-Title -->
<div class="row d-print-none">
    <div class="col-sm-12">
        <div class="page-title-box">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h4 class="page-title m-0">IPPIS Ledger By Centers</h4>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-12">
        <div class="card m-b-30">
            <div class="card-body">
                <div class="row">
                    <div class="col-12 d-none d-print-block">
                        <h5 class="text-center">IPPIS ACCOUNTS</h5>
                        <h6 class="text-center">NASRDA STAFF MULTIPURPOSE COOPERATIVE SOCIETY LTD</h6>
                        <p class="text-center">{{$months[$month]}},&nbsp {{$year}}</p>
                    </div>
                </div>
                <div class="row d-print-none">
                    <div class="col-md-6 text-right">
                        {!! Form::open(['route' => ['ippis.trxns'], 'method' => 'get']) !!}
                        <div class="row">
                            <div class="col-md-2 text-right mt-2">
                                Month
                            </div>
                            <div class="col-md-3">
                                {{Form::select('month', $months, $month, ['class' => 'form-control mb-3'])}}
                            </div>
                            <div class="col-md-2 text-right mt-2">
                                Year
                            </div>
                            <div class="col-md-3">
                                {{Form::select('year', $years, $year, ['class' => 'form-control mb-3'])}}
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary waves-effect waves-light mb-3">Filter</button>
                            </div>
                        </div>
                        {!! Form::close() !!}
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3 float-right">
                            <a href="#" onclick="window.print()" class="btn btn-primary">Print</a>
                            @can('create member')
                                <a href="{{ route('ippis.debitBank') }}" class="btn btn-warning">Debit Bank</a>
                            @endcan
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <table class="table table-bordered table-responsive-md table-striped" style="border: solid #000 2px">
                            <thead>
                                <tr>
                                    <th class="text-center" style="border-bottom: solid #000 2px">Date</th>
                                    <th class="text-center" style="border-bottom: solid #000 2px">IPPIS Code</th>
                                    <th class="text-center" style="border-right: solid #000 2px; border-bottom: solid #000 2px">Balance</th>
                                    <th class="text-center" style=" border-bottom: solid #000 2px">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                $totalDr = 0;
                                $totalCr = 0;
                                @endphp

                                @foreach($trxns as $trxn)

                                @php
                                $totalDr = $trxn->ms_dr + $trxn->ltl_dr + $trxn->stl_dr + $trxn->coml_dr;
                                $totalCr = $trxn->ms_cr + $trxn->ltl_cr + $trxn->stl_cr + $trxn->coml_cr;

                                @endphp
                                <tr>
                                    <td class="text-center">{{ $trxn->deduction_for->format('d-m-Y') }}</td>
                                    <td class="text-left">{{ $trxn->center->name }}</td>
                                    <td class="text-right" style="border-right: solid #000 2px">{{ number_format($totalDr - $totalCr, 2) }}</td>
                                    <td class="text-center">
                                        <a href="{{route('ippis.trxnDetails', $trxn->id)}}" target="_blank" class="">Details</a>
                                    </td>
                                </tr>
                                @endforeach

                            </tbody>
                        </table>
                    </div>
                </div>
            
            </div>
        </div>
    </div>
</div>

@endsection


@section('js')

@endsection

