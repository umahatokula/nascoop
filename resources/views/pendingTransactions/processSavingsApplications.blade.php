<div class="container">
    {!! Form::open(['route' => 'authorizeTransaction', 'method' => 'POST', 'onsubmit' => "return confirm('Are you sure?')", 'id' => 'savingsForm']) !!}

    {!! Form::hidden('trxn_number', $savings->trxn_number) !!}
    {!! Form::hidden('trxn_type', $savings->trxn_type) !!}
    {!! Form::hidden('ippis', $savings->ippis) !!}
    {!! Form::hidden('value_date', $savings->is_withdrawal == 0 ? $savings->deposit_date : $savings->withdrawal_date) !!}
    {!! Form::hidden('tab', 'savings') !!}
    {!! Form::hidden('amount', $savings->trxn_type == 'savings' ? $savings->cr : $savings->dr) !!}

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

