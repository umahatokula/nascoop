@extends('master')

@section('body')
<!-- Page-Title -->

<div class="row">
    <div class="col-sm-12">
        <div class="page-title-box">
            <div class="row align-items-center">
                <div class="col-md-8">
                    &nbsp
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card m-b-30">
            <div class="card-body">

              <div class="row mb-5">
                <div class="col-lg-4">
                  <h4 class="page-title m-0"> Shares Bought</h4>
                  <p>&nbsp</p>
                </div>
                <div class="col-lg-8">

                {!! Form::open(['route' => 'sharesBought', 'method' => 'get']) !!}
                  <div class="row">
                      <div class="col-md-3 mb-1">
                          {{Form::date('dateFrom', $dateFrom, ['class' => 'form-control'])}}
                      </div>
                      <div class="col-md-3 mb-1">
                          {{Form::date('dateTo', $dateTo, ['class' => 'form-control'])}}
                      </div>
                      <div class="col-md-3 mb-1">
                          {{Form::select('pay_point_id', $paypoints, $pay_point_id, ['class' => 'form-control'])}}
                      </div>
                      <div class="col-md-3 mb-1">
                          <button type="submit" class="btn btn-primary waves-effect waves-light">Filter</button>
                      </div>
                  </div>
                {!! Form::close() !!}
                </div>
              </div>

              <div class="row">
                  <div class="col-md-12">
                    @if($sharesBought->count() > 0)
                      <table class="table table-stripped table-bordered table-responsive-sm datatable">
                        <thead>
                          <th>Date Bought</th>
                          <th>IPPIS</th>
                          <th>Name</th>
                          <th>Amount</th>
                          <th>Units</th>
                          <th>Pay Point</th>
                          <th class="text-center">Payment Mode</th>
                          <th>Status</th>
                        </thead>
                        <tbody>
                          @foreach($sharesBought as $shares)
                          <tr>
                            <td>{{ $shares->date_bought ? $shares->date_bought->toFormattedDateString() : '' }}</td>
                            <td><a href="{{ route('members.dashboard', $shares->ippis) }}">{{ $shares->ippis }}</a></td>
                            <td>{{ $shares->member ? $shares->member->full_name : '' }}</td>
                            <td class="text-right">{{ number_format($shares->amount, 2) }}</td>
                            <td class="text-right">{{ $shares->units }}</td>
                            <td class="text-right">{{ $shares->member->member_pay_point ? $shares->member->member_pay_point->name : '' }}</td>
                            <td class="text-center">
                              @if($shares->payment_method == 'savings')
                              <span class="text-default">Savings</span>
                              @endif

                              @if($shares->payment_method == 'bank_deposit')
                              <span class="text-default">Bank Deposit</span>
                              @endif

                              @if($shares->payment_method == 'salary')
                              <span class="text-default">Salary</span>
                              @endif
                            </td>
                            <td class="text-right">
                              @if($shares->is_authorized == 0)
                              <span class="text-default">P</span>
                              @endif

                              @if($shares->is_authorized == 1)
                              <span class="text-success">A</span>
                              @endif

                              @if($shares->is_authorized == 2)
                              <span class="text-danger">C</span>
                              @endif
                            </td>
                          </tr>
                          @endforeach
                        </tbody>
                      </table>
                    @else
                      <p>No records</p>
                    @endif
                  </div>
                  <div class="col-md-12 d-flex justify-center">
                    {{ $sharesBought->appends(request()->except('page'))->links() }}
                  </div>
              </div>

            </div>
        </div>
    </div><!-- end col -->
</div>
@endsection
