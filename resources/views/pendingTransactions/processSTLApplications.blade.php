<div class="container">
    {!! Form::open(['route' => 'authorizeTransaction', 'method' => 'POST', 'onsubmit' => "return confirm('Are you sure?')", 'id' => 'stlForm']) !!}

    {!! Form::hidden('trxn_number', $loan->trxn_number) !!}
    {!! Form::hidden('trxn_type', $loan->trxn_type) !!}
    {!! Form::hidden('ippis', $loan->ippis) !!}
    {!! Form::hidden('tab', 'stl') !!}
    {!! Form::hidden('amount', $loan->trxn_type == 'stl' ? $loan->dr : $loan->cr) !!}

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="row m-5">
        <div class="col-md-4 text-center">
            {!! Form::select('bank', $banks, null, ['class' => 'form-control selectBank mb-1', 'id' => 'name', 'placeholder' => 'Select Bank']) !!}
            <span class="float-right"><small>Balance: <span class="bankbalance"></span></small></span>
        </div>
        <div class="col-md-4 text-center">
            <button name="action" type="submit" value="authorize" class="btn btn-xs btn-success btn-block mb-1">Authorize</button>
        </div>
        <div class="col-md-4 text-center">
            <button name="action" type="submit" value="cancel" class="btn btn-xs btn-danger btn-block mb-1">Disapprove</button>
        </div>
    </div>
    {!! Form::close() !!}
    <div class="row">
        <div class="col-12">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th class="">
                            <strong>Name</strong>
                        </th>
                        <th class="">
                            <strong>{{ $loan->member->full_name }} (<a href="{{ route('members.shortTermLoansPaymentVoucher', $loan->id) }}" target="_blank">View PV</a>)</strong>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td scope="row">Ref</td>
                        <td>{{ $loan->shortTermLoan->ref }}</td>
                    </tr>
                    <tr>
                        <td scope="row">IPPIS</td>
                        <td>{{ $loan->shortTermLoan->ippis }}</td>
                    </tr>
        <!-- <tr>
            <td scope="row">Guarantor 1</td>
            <td>{{ $loan->shortTermLoan->loanGuarantor1 ? $loan->shortTermLoan->loanGuarantor1->full_name.' ['.$loan->shortTermLoan->loanGuarantor1->ippis.']' : '' }}</td>
        </tr>
        <tr>
            <td scope="row">Guarantor 2</td>
            <td>{{ $loan->shortTermLoan->loanGuarantor2 ? $loan->shortTermLoan->loanGuarantor2->full_name.' ['.$loan->shortTermLoan->loanGuarantor2->ippis.']' : '' }}</td>
        </tr> -->
                    <tr>
                        <td scope="row">Loan Start Date</td>
                        <td>{{ $loan->shortTermLoan->loan_date ? $loan->shortTermLoan->loan_date->toFormattedDateString() : '' }}</td>
                    </tr>
                    <tr>
                        <td scope="row">Loan End Date</td>
                        <td>{{ $loan->shortTermLoan->loan_end_date ? $loan->shortTermLoan->loan_end_date->toFormattedDateString() : '' }}</td>
                    </tr>
                    <tr>
                        <td scope="row">Duration</td>
                        <td>{{ $loan->shortTermLoan->no_of_months }} months</td>
                    </tr>
                    <tr>
                        <td scope="row">Amount</td>
                        <td>&#8358; {{ number_format($loan->shortTermLoan->total_amount, 2) }}</td>
                    </tr>
                    <tr>
                        <td scope="row">Adjustment</td>
                        <td>&#8358; {{ number_format($loan->shortTermLoan->adjustment, 2) }}</td>
                    </tr>
                    <tr>
                        <td scope="row">Processing Fee</td>
                        <td>&#8358; {{ number_format($loan->shortTermLoan->processing_fee, 2) }}</td>
                    </tr>
                    <tr>
                        <td scope="row">Bank Charges</td>
                        <td>&#8358; {{ number_format($loan->shortTermLoan->bank_charges, 2) }}</td>
                    </tr>
                    <tr>
                        <td scope="row">Interest</td>
                        <td>&#8358; {{ number_format($loan->shortTermLoan->interest, 2) }}</td>
                    </tr>
                    <tr>
                        <td scope="row">Net Payment</td>
                        <td>&#8358; {{ number_format($loan->shortTermLoan->net_payment, 2) }}</td>
                    </tr>
                    <tr>
                        <td scope="row">Monthly Amount</td>
                        <td>&#8358; {{ number_format($loan->shortTermLoan->monthly_amount, 2)}}</td>
                    </tr>
                    <tr>
                        <td scope="row">Balance</td>
                        @if($loan->shortTermLoan->payments)
                        <td> &#8358; {{ $loan->shortTermLoan->payments->last() ? number_format($loan->shortTermLoan->payments->last()->bal, 2) : '0.00'  }}</td>
                        @else
                        <td>&nbsp</td>
                        @endif
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>


<script>
    $(document).ready(function () {

        $(".selectBank").change(function() {

            var bank_code = $(this).val();

            $.ajax({
                url: '{{ url('ledger/check-account-balance') }}/'+bank_code+'',
                type: 'GET',
                dataType: "JSON",

                success: function(result) {
                    $(".bankbalance").text(result);
                }
            });
        });

    });
</script>

