@extends('master')

@section('body')
<style>
    .ltl {
        background-color: #CCC
    }
    .stl {
        background-color: #DDD
    }
    .com {
        background-color: #EEE
    }
    .pageStyle {
        font-size: 0.8rem;
    }
    .veryblack {
        color: #000;
    }
</style>

<!-- Page-Title -->
@if($member)
<div class="row">
    <div class="col-sm-12">
        <div class="page-title-box">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h4 class="page-title m-0">Ledger <span class="text-danger">[ {{ $member->full_name }} |
                            {{ $member->ippis }} ]</span><span
                            class="text-{{ $member->is_active? 'success' : 'danger' }}">[
                            {{ $member->is_active? 'active' : 'inactive' }} ]</span></h4>
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
                    <div class="col-md-4">
                        <div class="mb-3">
                            <a href="{{route('members.dashboard', $member->ippis)}}" class="btn btn-primary waves-effect waves-light mb-1"><i class="mdi mdi-backspace-outline"></i> Dashboard</a>
                            <!-- <a href="{{ route('members.ledgerExcel', $member->ippis) }}" class="btn btn-success mb-1"><i class="mdi mdi-file-excel"></i> Excel</a> -->
                            <a href="{{ route('members.ledgerPdf', [$member->ippis, $date_from, $date_to]) }}" class="btn btn-danger mb-1"><i class="mdi mdi-file-pdf-box"></i> PDF</a>
                            <a href="{{ route('members.ledgerPrint', [$member->ippis, $date_from, $date_to]) }}" target="_blank" class="btn btn-primary mb-1"><i class="mdi mdi-file-pdf-box"></i> Print</a>
                        </div>
                    </div>
                    <div class="col-md-8">
                        {!! Form::open(['route' => ['members.ledger', $member->ippis], 'method' => 'get']) !!}
                        <div class="row">
                            <div class="col-md-2 text-right">
                                Start Date
                            </div>
                            <div class="col-md-3">
                                {{Form::date('date_from', $date_from, ['class' => 'form-control mb-3'])}}
                            </div>
                            <div class="col-md-2 text-right">
                                End Date
                            </div>
                            <div class="col-md-3">
                                {{Form::date('date_to', $date_to, ['class' => 'form-control mb-3'])}}
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary waves-effect waves-light mb-3">Filter</button>
                            </div>
                        </div>
                        {!! Form::close() !!}
                    </div>
                </div>
                
                <div class="row mt-3 ">
                    <div class="col-12 text-right">
                        <p>
                            <span class="text-muted">P = Pending</span> |
                            <span class="text-success">A = Authorized</span> |
                            <span class="text-danger">C = Cancelled</span>
                        </p>
                    </div>
                    <div class="col-12">
                        <div class="pageStyle">
                            @if($member->ledgers->count() > 0)

                            <table class="table table-bordered table-hover table-responsive mb-0">
                                <thead>
                                    <tr>
                                        <th class="text-center" colspan="2">&nbsp</th>
                                        <!-- <th class="text-center">PARTICULARS</th> -->
                                        <th class="text-center" colspan="3">SAVINGS</th>
                                        <th class="text-center" colspan="3" style="background-color: #EEE">LONG TERM</th>
                                        <th class="text-center" colspan="3" style="background-color: #DDD">SHORT TERM</th>
                                        <th class="text-center" colspan="3" style="background-color: #CCC">COMMODITY</th>
                                        <!-- <th class="text-center" colspan="1" style="background-color: #a8a6a6">SHARES</th> -->
                                        <th class="text-center" colspan="1"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <th class="text-left">DATE</th>
                                        <th class="text-left">DESCRIPTION</th>
                                        <th class="text-right">DR</th>
                                        <th class="text-right">CR</th>
                                        <th class="text-right">BALANCE</th>
                                        <th class="text-right ltl" style="background-color: #EEE">DR</th>
                                        <th class="text-right ltl" style="background-color: #EEE">CR</th>
                                        <th class="text-right ltl" style="background-color: #EEE">BALANCE</th>
                                        <th class="text-right stl" style="background-color: #DDD">DR</th>
                                        <th class="text-right stl" style="background-color: #DDD">CR</th>
                                        <th class="text-right stl" style="background-color: #DDD">BALANCE</th>
                                        <th class="text-right com" style="background-color: #CCC">DR</th>
                                        <th class="text-right com" style="background-color: #CCC">CR</th>
                                        <!-- <th class="text-right com" style="background-color: #CCC">BALANCE</th> -->
                                        <th class="text-center" style="background-color: #CCC">BALANCE</th>
                                        <th class="text-center">STATUS</th>
                                    </tr>
                                    @foreach($ledgers as $ledger)
                                    <tr class="{{ $ledger->is_authorized == 2 ? 'text-muted' : '' }}" style="{{ $ledger->is_authorized == 2 ? 'text-decoration: line-through;' : '' }}">
                                        @if($ledger->deposit_date)
                                        <th class="text-left" scope="row">{{ $ledger->deposit_date->toFormattedDateString() }}</th>
                                        @elseif($ledger->withdrawal_date)
                                        <th class="text-left" scope="row">{{ $ledger->withdrawal_date->toFormattedDateString() }}</th>
                                        @elseif($ledger->loan_date)
                                        <th class="text-left" scope="row">{{ $ledger->loan_date->toFormattedDateString() }}</th>
                                        @else
                                        <th class="text-left" scope="row">{{ $ledger->date->toFormattedDateString() }}</th>
                                        @endif
                                        <td>{{ $ledger->ref }}</td>
                                        <td class="text-right">
                                            {{ number_format($ledger->savings_dr, 2) }}</td>
                                        <td class="text-right">
                                            {{ number_format($ledger->savings_cr, 2) }}</td>
                                        <td class="text-right">
                                            {{ number_format($ledger->savings_bal, 2) }}
                                        </td>
                                        <td class="text-right" style="background-color: #EEE">
                                            {{ number_format($ledger->long_term_dr, 2) }}
                                        </td>
                                        <td class="text-right" style="background-color: #EEE">
                                            {{ number_format($ledger->long_term_cr, 2) }}
                                        </td>
                                        <td class="text-right" style="background-color: #EEE">
                                            {{ number_format($ledger->long_term_bal, 2) }}
                                        </td>
                                        <td class="text-right" style="background-color: #DDD">
                                            {{ number_format($ledger->short_term_dr, 2) }}
                                        </td>
                                        <td class="text-right" style="background-color: #DDD">
                                            {{ number_format($ledger->short_term_cr, 2) }}
                                        </td>
                                        <td class="text-right" style="background-color: #DDD">
                                            {{ number_format($ledger->short_term_bal, 2) }}
                                        </td>
                                        <td class="text-right" style="background-color: #CCC">
                                            {{ number_format($ledger->commodity_dr, 2) }}
                                        </td>
                                        <td class="text-right" style="background-color: #CCC">
                                            {{ number_format($ledger->commodity_cr, 2) }}
                                        </td>
                                        <td class="text-right" style="background-color: #CCC">
                                            {{ number_format($ledger->commodity_bal, 2) }}
                                        </td>
                                        <!-- <td class="text-right" style="background-color: #a8a6a6">
                                            {{ number_format($member->shares_amount, 2) }}
                                        </td> -->
                                        <td class="text-center">
                                            @if($ledger->is_authorized == 0)
                                            <span class="text-default">P</span>
                                            @elseif($ledger->is_authorized == 1)
                                            <span class="text-success">A</span>
                                            @elseif($ledger->is_authorized == 2)
                                            <span class="text-danger">C</span>
                                            @else
                                            <span class="text-default">P</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>

                            @else
                            <p>No records found</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@else

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

<div class="row">
    <div class="col-lg-12">
        <div class="card m-b-30">
            <div class="card-body">
            Member not found
            </div>
        </div>
    </div>
</div>
@endif

@endsection


@section('js')
<script>
    var data = [
    ['', 'Ford', 'Tesla', 'Toyota', 'Honda'],
    ['2017', 10, 11, 12, 13],
    ['2018', 20, 11, 14, 13],
    ['2019', 30, 15, 12, 13]
    ];

    var container = document.getElementById('memberledger');
    var hot = new Handsontable(container, {
    data: data,
    rowHeaders: true,
    colHeaders: true,
    filters: true,
    dropdownMenu: true,
    licenseKey: "non-commercial-and-evaluation"
    });
</script>
@endsection
