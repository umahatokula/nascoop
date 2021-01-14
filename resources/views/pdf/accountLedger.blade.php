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

                <div class="row mt-3 ledgerPreview">
                    <div class="col-lg-12">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th colspan="4" class="text-center"><h4>General Ledger</h4></th>
                                </tr>
                                <tr>
                                    <th colspan="2">Account Name: {{ $account->account_name }}</th>
                                    <th colspan="2">Account No: {{ $account->ledger_no }}</th>
                                </tr>
                                <tr>
                                    <th colspan="4">&nbsp</th>
                                </tr>
                                <tr>
                                    <th>Date</th>
                                    <th>Account Name</th>
                                    <th class="text-right">DR</th>
                                    <th class="text-right">CR</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($trxns as $trxn)
                                <tr style="line-height: 1;">
                                    <td>{{ $trxn->created_at->toFormattedDateString() }}</td>
                                    <td>{{ $trxn->description }}</td>
                                    <td class="text-right">{{ $trxn->ledger_no_dr == $account->ledger_no ? number_format($trxn->amount, 2) : '' }}</td>
                                    <td class="text-right">{{ $trxn->ledger_no == $account->ledger_no ? number_format($trxn->amount, 2) : '' }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </div><!-- end col -->
</div>
