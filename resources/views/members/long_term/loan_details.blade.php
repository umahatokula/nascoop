<table class="table table-bordered">
    <thead>
        <tr>
            <th class="text-center" colspan="2">
                <strong>{{ $loan->member->full_name }}</strong>
            </th>
        </tr>
        <tr>
            <th class="text-center" colspan="2">
                <a href="{{ route('members.longTermLoansPaymentVoucher', $loan->id) }}">Payment Voucher</a>
            </th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td scope="row">Ref</td>
            <td>{{ $loan->ref }}</td>
        </tr>
        <tr>
            <td scope="row">IPPIS</td>
            <td>{{ $loan->ippis }}</td>
        </tr>
        <tr>
            <td scope="row">Guarantor 1</td>
            <td>{{ $loan->loanGuarantor1 ? $loan->loanGuarantor1->full_name.' ['.$loan->loanGuarantor1->ippis.']' : '' }}</td>
        </tr>
        <tr>
            <td scope="row">Guarantor 2</td>
            <td>{{ $loan->loanGuarantor2 ? $loan->loanGuarantor2->full_name.' ['.$loan->loanGuarantor2->ippis.']' : '' }}</td>
        </tr>
        <tr>
            <td scope="row">Loan Start Date</td>
            <td>{{ $loan->loan_date ? $loan->loan_date->toFormattedDateString() : '' }}</td>
        </tr>
        <tr>
            <td scope="row">Loan End Date</td>
            <td>{{ $loan->loan_end_date ? $loan->loan_end_date->toFormattedDateString() : '' }}</td>
        </tr>
        <tr>
            <td scope="row">Duration</td>
            <td>{{ $loan->no_of_months }} months</td>
        </tr>
        <tr>
            <td scope="row">Amount</td>
            <td>&#8358; {{ number_format($loan->total_amount, 2) }}</td>
        </tr>
        <tr>
            <td scope="row">Adjustment</td>
            <td>&#8358; {{ number_format($loan->adjustment, 2) }}</td>
        </tr>
        <tr>
            <td scope="row">Processing Fee</td>
            <td>&#8358; {{ number_format($loan->processing_fee, 2) }}</td>
        </tr>
        <tr>
            <td scope="row">Bank Charges</td>
            <td>&#8358; {{ number_format($loan->bank_charges, 2) }}</td>
        </tr>
        <tr>
            <td scope="row">Interest</td>
            <td>&#8358; {{ number_format($loan->interest, 2) }}</td>
        </tr>
        <tr>
            <td scope="row">Net Payment</td>
            <td>&#8358; {{ number_format($loan->net_payment, 2) }}</td>
        </tr>
        <tr>
            <td scope="row">Monthly Amount</td>
            <td>&#8358; {{ number_format($loan->monthly_amount, 2)}}</td>
        </tr>
        <tr>
            <td scope="row">Balance</td>
            <td> &#8358; {{ $loan->payments->last() ? number_format($loan->payments->last()->bal, 2) : '0.00'  }}</td>
        </tr>
    </tbody>
</table>