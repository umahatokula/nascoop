<link href="{{ public_path('assets/css/bootstrap.min.css') }}" rel="stylesheet">
<style>
.ledgerPreview {
    font-size: 0.7rem;
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
    padding: 1px 2px;
}

</style>

<div class="">

@if($member->ledgers->count() > 0)

<div class="mb-3" style="background-color: #FFF; height: 100px;">
    <div class="col-3 text-center" style="width:30%; float: left">
        <img class="img-fluid" src="{{ public_path('assets/images/nasrdalogo.png') }}" width="100" />
    </div>
    <div class="col-9 mt-2" style="width:70%; float: right; color: #000">
        <h5 class="text-left" style="font-size: 0.8rem">NASMCSL</h5>
        <h6 class="text-left" style="font-size: 0.8rem">NASRDA STAFF MULTIPURPOSE COOPERATIVE SOCIETY LTD</h6>
        <h6 class="text-left" style="font-size: 0.8rem">P.M.B 437, GARKI, ABUJA | info@nasrdacoop.com | nasrdacoop.com</h6>
    </div>
</div>

<div style="clear: both;"></div>

<div class="row mt-1 mb-0 ledgerPreview">
    <div class="col-12">
    <table class="table table-bordered table-condensed" style="font-size: 9px;">
        <tbody>
            <tr>
                <td><strong>Name:</strong></td>
                <td>{{$member->full_name}}</td>
                <td><strong>IPPIS:</strong></td>
                <td>{{$member->ippis}}</td>
            </tr>
            <tr>
                <td><strong>Paypoint:</strong></td>
                <td>{{$member->member_pay_point ? $member->member_pay_point->name : ''}}</td>
                <td><strong>Monthly Contribution:</strong></td>
                <td>N {{ count($member->monthly_savings) > 0 ? number_format($member->monthly_savings->last()->amount, 2) : 0.00 }}</td>
            </tr>
        </tbody>
    </table>
    </div>
</div>

<table class="table table-bordered table-hover mb-0 ledgerPreview" style="font-size: 9px;">
    <thead>
        <tr>
            <th class="text-center"></th>
            <th class="text-center">PARTICULARS</th>
            <th class="text-center" colspan="3">SAVINGS</th>
            <th class="text-center" colspan="3">LONG TERM</th>
            <th class="text-center" colspan="3">SHORT TERM</th>
            <th class="text-center" colspan="3">COMMODITY</th>
            <!-- <th class="text-center">&nbsp</th> -->
        </tr>
    </thead>
    <tbody>
        <tr>
            <th class="text-left">DATE</th>
            <th class="text-left">DESCRIPTION</th>
            <th class="text-right">DR</th>
            <th class="text-right">CR</th>
            <th class="text-right">BALANCE</th>
            <th class="text-right">DR</th>
            <th class="text-right">CR</th>
            <th class="text-right">BALANCE</th>
            <th class="text-right">DR</th>
            <th class="text-right">CR</th>
            <th class="text-right">BALANCE</th>
            <th class="text-right">DR</th>
            <th class="text-right">CR</th>
            <th class="text-right">BALANCE</th>
            <!-- <th class="text-center">EDIT</th> -->
        </tr>
        @foreach($ledgers as $ledger)
        @if($ledger->is_authorized == 1)
            <tr>
                <th class="text-left" scope="row" style="width: 6%">{{ $ledger->date->format('d-m-Y') }}</th>
                <td>{{ $ledger->ref }}</td>
                <td class="text-right" style="width: 10%">
                    {{ number_format($ledger->savings_dr, 2) }}</td>
                <td class="text-right">
                    {{ number_format($ledger->savings_cr, 2) }}</td>
                <td class="text-right">
                    {{ number_format($ledger->savings_bal, 2) }}
                </td>
                <td class="text-right">
                    {{ number_format($ledger->long_term_dr, 2) }}
                </td>
                <td class="text-right">
                    {{ number_format($ledger->long_term_cr, 2) }}
                </td>
                <td class="text-right">
                    {{ number_format($ledger->long_term_bal, 2) }}
                </td>
                <td class="text-right">
                    {{ number_format($ledger->short_term_dr, 2) }}
                </td>
                <td class="text-right">
                    {{ number_format($ledger->short_term_cr, 2) }}
                </td>
                <td class="text-right">
                    {{ number_format($ledger->short_term_bal, 2) }}
                </td>
                <td class="text-right">
                    {{ number_format($ledger->commodity_dr, 2) }}
                </td>
                <td class="text-right">
                    {{ number_format($ledger->commodity_cr, 2) }}
                </td>
                <td class="text-right">
                    {{ number_format($ledger->commodity_bal, 2) }}
                </td>
            </tr>
        @endif
        @endforeach
    </tbody>
</table>

@else
<p>No records found</p>
@endif
</div>