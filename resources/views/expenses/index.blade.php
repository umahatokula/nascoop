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
    <div class="col-md-12">
        <div class="card m-b-30">
            <div class="card-body">

                @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                <div class="row mb-3">
                    <div class="col-md-12">
                        {!! Form::open(['route' => 'expensesIndex', 'method' => 'get']) !!}
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
                                <button type="submit"
                                    class="btn btn-primary waves-effect waves-light mb-3">Filter</button>
                            </div>
                        </div>
                        {!! Form::close() !!}
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 text-left">
                        <h4>Expenses</h4>
                    </div>
                    @can('post 3rd party payments')
                    <div class="col-md-6 text-right">
                        <a href="{{ route('newExpense') }}" class="btn btn-primary">Record New Expense</a>
                    </div>
                    @endcan
                </div>

                <div class="row mt-3">
                    <div class="col-lg-12">
                        <table class="table table-light table-bordered">
                            <thead class="thead-light">
                                <tr>
                                    <th class="text-left">Date</th>
                                    <th class="text-left">Supplier</th>
                                    <th class="text-left">Description</th>
                                    <th class="text-right">Amount</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($expenses as $expense)
                                <tr>
                                    <td>{{ $expense->date->toFormattedDateString() }}</td>
                                    <td>{{ $expense->supplier->fname }} {{ $expense->supplier->lname }}</td>
                                    <td>{{ $expense->description }}</td>
                                    <td class="text-right">{{ number_format($expense->amount, 2) }}</td>
                                    <td class="text-center">
                                        @if($expense->is_authorized == 1)
                                            <span class="badge badge-success">approved</span>
                                        @elseif($expense->is_authorized == 2)
                                            <span class="badge badge-danger">disapproved</span>
                                        @else
                                            <span class="badge badge-default">not processed</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <a class="btn btn-info btn-sm" href="{{ route('expensesPv', $expense->trxn_number) }}" target="_blank">View PV</a>
                                        <a href="{{ route('authorize', [$expense->trxn_number, 1]) }}" class="btn btn-success btn-sm {{$expense->is_authorized != 0 ? 'disabled' : ''}}" onclick = "return confirm('Are you sure?')">Approve</a>
                                        <a href="{{ route('authorize', [$expense->trxn_number, 2]) }}" class="btn btn-danger btn-sm {{$expense->is_authorized != 0 ? 'disabled' : ''}}" onclick = "return confirm('Are you sure?')">Cancel</a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                        {{ $expenses->appends(request()->except('page'))->links() }}
                    </div>
                </div>

            </div>
        </div>
    </div><!-- end col -->
</div>
@endsection
