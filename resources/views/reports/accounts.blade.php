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
                <div class="col-2">
                  <h4 class="page-title m-0">Balances</h4>
                  <p>&nbsp</p>
                </div>
                <div class="col-10 text-right">

                {!! Form::open(['route' => 'reports.accounts', 'method' => 'get']) !!}
                  <div class="row">
                      <div class="col-md-2 mb-1">
                          {{Form::select('account_type', $accountTypes, $account_type, ['class' => 'form-control'])}}
                      </div>
                      <div class="col-md-2 mb-1">
                          {{Form::select('pay_point', $centers, $pay_point, ['class' => 'form-control'])}}
                      </div>
                      <div class="col-md-2 mb-1">
                          {{Form::date('dateFrom', $dateFrom, ['class' => 'form-control'])}}
                      </div>
                      <div class="col-md-2 mb-1">
                          {{Form::date('dateTo', $dateTo, ['class' => 'form-control'])}}
                      </div>
                      <div class="col-md-3 mb-1">
                          <button type="submit" class="btn btn-primary waves-effect waves-light">Generate</button>
                      </div>
                  </div>
                {!! Form::close() !!}
                </div>
              </div>

              <div class="row">
                <div class="col-12">
                  @if($results->count() > 0)
                  <table class="table table-stripped table-bordered">
                    <thead>
                      <th class="text-center">IPPIS</th>
                      <th class="text-left">Name</th>
                      <th class="text-center">Balance (&#8358;)</th>
                    </thead>
                    <tbody>
                      @foreach($results as $result)
                      <tr class="">
                        <td class="text-center">{{ $result->ippis }}</td>
                        <td class="text-left">{{ $result->member->full_name }}</td>
                        <td class="text-right">{{ $result->bal }}</td>
                      </tr>
                      @endforeach
                    </tbody>
                  </table>
                  @else
                  <p>No shares data available</p>
                  @endif
                </div>
              </div>             

            </div>
        </div>
    </div><!-- end col -->
</div>
@endsection
