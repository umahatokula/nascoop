<div class="row">
    <div class="col-12">

        @if($MonthlySavingPayment->bal > $totalLoan)
        <div class="alert alert-info" role="alert">
            Note that savings will be used to pay off loans and the balance automatically withdrawn.
        </div>
        @else
        <div class="alert alert-danger" role="alert">
            There isn't enough savings to pay off the loan(s)
        </div>
        @endif

        <table class="table table-bordered">
            <thead class="thead-light">
                <tr>
                    <th>Account</th>
                    <th class="text-right">Balance (&#8358;)</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Monthly Savings</td>
                    <td class="text-right">{{ number_format($MonthlySavingPayment->bal, 2) }}</td>
                </tr>
                <tr>
                    <td>Lont Term Loan</td>
                    <td class="text-right">{{ number_format($LongTermPayment->bal, 2) }}</td>
                </tr>
                <tr>
                    <td>Lont Term Loan</td>
                    <td class="text-right">{{ number_format($ShortTermPayment->bal, 2) }}</td>
                </tr>
                <tr>
                    <td>Lont Term Loan</td>
                    <td class="text-right">{{ number_format($CommodityPayment->bal, 2) }}</td>
                </tr>
            </tbody>
        </table>

        @if($MonthlySavingPayment->bal > $totalLoan)
        <a href="{{ route('members.status', $member->ippis) }}" class="btn btn-danger mb-1 float-right"> Deactivate</a>
        @endif

    </div>
</div>
