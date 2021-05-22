@extends('master')

@section('body')

<div class="row">
    <div class="col-sm-12">
        <div class="page-title-box">
            <div class="row d-flex justify-content-end">
                <div class="col-12">
                &nbsp
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">

                @include('accounting.linksPartial')

            </div>
        </div>
    </div><!-- end col -->
</div>

<div class="row">
    <div class="col-12">
        <div class="card m-b-30">
            <div class="card-body">

                <div class="row mt-3">
                    <div class="col-lg-12">

                        @hasanyrole('super-admin|accountant')
                        
                        <h4>Journal Entries</h4>

                        <div class="row my-3 my-md-4">
                            <div class="col-md-12">
                                {!! Form::open(['route' => 'accountingJournal', 'method' => 'get']) !!}
                                <div class="row">
                                    <div class="col-md-2 text-left pt-2">
                                        Start Date
                                    </div>
                                    <div class="col-md-3">
                                        {{Form::date('dateFrom', $dateFrom, ['class' => 'form-control mb-3'])}}
                                    </div>
                                    <div class="col-md-2 text-left pt-2">
                                        End Date
                                    </div>
                                    <div class="col-md-3">
                                        {{Form::date('dateTo', $dateTo, ['class' => 'form-control mb-3'])}}
                                    </div>
                                    <div class="col-md-2">
                                        <button type="submit" class="btn btn-primary waves-effect waves-light mb-3">Filter</button>
                                        <!-- <a href="{{ route('trialBalancePdf', [$dateFrom, $dateTo]) }}" type="submit" class="btn btn-danger waves-effect waves-light mb-3">PDF</a> -->
                                    </div>
                                </div>
                                {!! Form::close() !!}
                            </div>
                        </div>
                            
                        <div class="row">
                            <div class="col-12">
                                <table class="table table-bordered table-condensed table-striped table-responsive-md">
                                    <thead>
                                        <tr>
                                        <th class="text-left">Posting Date</th>
                                        <th class="text-left">Value Date</th>
                                        <th class="text-left">Description</th>
                                        <th>Account</th>
                                        <th class="text-right">Debit</th>
                                        <th class="text-right">Credit</th>
                                        </tr>
                                    </thead>
                                    @foreach($entries as $entry)
                                    <tbody style="border-top: #000 2px solid;">
                                        <tr style="line-height: 0;">
                                        <td class="text-left">{{ $entry->date_time->toFormattedDateString() }}</td>
                                        <td class="text-left">{{ $entry->value_date->toFormattedDateString() }}</td>
                                        <td class="text-left"><br>{{ $entry->description }}</td>
                                        <td>{{ $entry->ledger_dr ? $entry->ledger_dr->account_name : '' }}</td>
                                        <td class="text-right">{{ $entry->ledger_no_dr ? number_format($entry->amount) : null }}</td>
                                        <td class="text-right"><br>&nbsp</td>
                                        </tr>
                                        <tr style="line-height: 0;">
                                        <td class="text-center">&nbsp</td>
                                        <td class="text-left"><br>&nbsp</td>
                                        <td class="text-left"><br>&nbsp</td>
                                        <td>{{ $entry->ledger_cr ? $entry->ledger_cr->account_name : '' }}</td>
                                        <td class="text-right">&nbsp</td>
                                        <td class="text-right"><br>{{ $entry->ledger_no ? number_format($entry->amount) : null }}</td>
                                        </tr>
                                    </tbody>
                                    @endforeach
                                </table>
                            </div>
                        </div>

                        <div class="row-">
                        <div class="col-12 d-flex justify-content-center">
                            {{ $entries->appends(request()->except('page'))->links() }}
                        </div>
                        </div>

                        @else
                        <p>You don't have the permission to view this content</p>
                        @endrole
                        
                    </div>
                </div>

            </div>
        </div>
    </div><!-- end col -->
</div>
@endsection
