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
}

</style>


<div class="row">
    <div class="col-12">
        <div class="card m-b-30">
            <div class="card-body">

                <div class="row mt-3">
                    <div class="col-lg-12">
                    
                        <div class="row">
                        <div class="col-12">
                        <table class="table table-bordered table-striped table-responsive-md">
                            <thead>
                                <tr>
                                    <th>Code</th>
                                    <th>Account Name</th>
                                    <th class="text-right">Balance</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($trialBalances as $trialBalance)
                                <tr style="line-height: 0;">
                                    @if($trialBalance[0]->usage == 'header')
                                        <td>{{ $trialBalance[0]->ledger_no }}</td>
                                        <td>{{ $trialBalance[0]->account_name }}</td>
                                    @endif

                                   @if($trialBalance[0]->usage == 'detail')
                                        <td><a href="{{ $trialBalance[0]->usage == 'detail' ? route('accountLedger', $trialBalance[0]->ledger_no) : '#' }}">{{ $trialBalance[0]->ledger_no }}</a></td>
                                        <td><a href="{{ $trialBalance[0]->usage == 'detail' ? route('accountLedger', $trialBalance[0]->ledger_no) : '#' }}">{{ $trialBalance[0]->account_name }}</a></td>
                                    @endif

                                    <td class="text-right"><b>{{ number_format($trialBalance[1], 2) }}</b></td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>

                        </div>
                        </div>
                        <!-- <trialbalance></trialbalance> -->
                    </div>
                </div>

            </div>
        </div>
    </div><!-- end col -->
</div>