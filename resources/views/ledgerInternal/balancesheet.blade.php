@extends('master')

@section('body')
<!-- Page-Title -->

<style>
  tr {
    line-height: 0;
  }
</style>


<div class="row">
    <div class="col-sm-12">
        <div class="page-title-box">
            <div class="row d-flex justify-content-end">
                <div class="col-12">
                {!! Form::open(['route' => 'accountsBalancesheet', 'method' => 'get']) !!}
                  <div class="row">
                      <div class="col-md-3 mb-1">
                          {{Form::date('dateFrom', $dateFrom, ['class' => 'form-control'])}}
                      </div>
                      <div class="col-md-3 mb-1">
                          {{Form::date('dateTo', $dateTo, ['class' => 'form-control'])}}
                      </div>
                      <div class="col-md-3 mb-1">
                          <button type="submit" class="btn btn-primary waves-effect waves-light">Generate</button>
                      </div>
                  </div>
                {!! Form::close() !!}
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
                <div class="col-12">
                  
                  <h4 class="text-center">NASRDA MEMBER MULTIPURPOSE COOPERATIVE SOCIETY LTD</h4>
                  <h5 class="page-title m-0 text-center">Balance Sheet </h5>
                  <p>&nbsp</p>
                </div>
              </div>

              <div class="row mt-3">
                <div class="col-lg-6">
                  <table class="table table-bordered">
                    <thead>
                      <th colspan="2">Assets</th>
                    </thead>
                    <tbody>
                    @php
                    $total = 0;
                    @endphp
                      @foreach($currentAssets as $result)
                      <tr style="line-height: 0;">
                        <td>
                        @for($i=2 ; $i<$result->level ; $i++ )
                          &nbsp &nbsp &nbsp &nbsp
                        @endfor

                        @if($loop->first) 
                        <b>{{ ucwords(strtolower($result->account_name)) }}</b>
                        @else
                        {{ ucwords(strtolower($result->account_name)) }}
                        @endif
                        </td>
                        <td class="text-right">
                        @if(!$loop->first)

                        @php
                            $amount = $result->getAccountBalance($result->ledger_no, $dateFrom, $dateTo);
                            $total += $amount;
                        @endphp

                        {{ number_format($amount, 2) }}
                        @endif
                        </td>
                      </tr>
                      @endforeach
                    </tbody>
                    <tfoot>
                      <tr>
                        <td class="text-center">Total {{ ucwords(strtolower($currentAssets[0]->account_name)) }}</td>
                        <td class="text-right">&#8358; {{ number_format($total, 2) }}</td>
                      </tr>
                    </tfoot>
                  </table>
                </div>
                <div class="col-lg-6">
                  <table class="table table-bordered">
                    <thead>
                      <th colspan="2">Liabilities & Share Holder's Equity</th>
                    </thead>
                    <tbody>
                    @php
                    $total = 0;
                    @endphp
                      @foreach($currentLiabilities as $result)
                      <tr style="line-height: 0;">
                        <td>
                        @for($i=2 ; $i<$result->level ; $i++ )
                          &nbsp &nbsp &nbsp &nbsp
                        @endfor

                        @if($loop->first) 
                        <b>{{ ucwords(strtolower($result->account_name)) }}</b>
                        @else
                        {{ ucwords(strtolower($result->account_name)) }}
                        @endif
                        </td>
                        <td class="text-right">
                        @if(!$loop->first)

                        @php
                            $amount = $result->getAccountBalance($result->ledger_no, $dateFrom, $dateTo);
                            $total += $amount;
                        @endphp

                        {{ number_format($amount, 2) }}
                        @endif
                        </td>
                      </tr>
                      @endforeach
                    </tbody>
                    <tfoot>
                      <tr>
                        <td class="text-center">Total {{ ucwords(strtolower($currentLiabilities[0]->account_name)) }}</td>
                        <td class="text-right">&#8358; {{ number_format($total, 2) }}</td>
                      </tr>
                    </tfoot>
                  </table>
                </div>
              </div>

            </div>
        </div>
    </div><!-- end col -->
</div>
@endsection
