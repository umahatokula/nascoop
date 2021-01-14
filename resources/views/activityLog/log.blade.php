@extends('master')

@section('body')
<!-- Page-Title -->
<div class="row">
    <div class="col-sm-12">
        <div class="page-title-box">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h4 class="page-title m-0">Activity Log</h4>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card m-b-30">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-12 text-right">
                        {!! Form::open(['route' => ['showActivityLog'], 'method' => 'get']) !!}
                        <div class="row">
                            <div class="col-md-4">
                                {{Form::date('date_from', $date_from, ['class' => 'form-control mb-3'])}}
                            </div>
                            <div class="col-md-4">
                                {{Form::date('date_to', $date_to, ['class' => 'form-control mb-3'])}}
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary waves-effect waves-light mb-3">Filter</button>
                            </div>
                            <div class="col-md-2">
                                <!-- <a href="{{ route('showActivityLogPDF', [$date_from, $date_to]) }}" class="btn btn-danger waves-effect waves-light mb-3">PDF</a> -->
                            </div>
                        </div>
                        {!! Form::close() !!}
                    </div>
                </div>
            
                @if($logs->count() > 0)
                <div class="row-">
                  <div class="col-12">                  
                    <div class="mt-3">
                          @can('read centre')
                          <table class="table table-bordered">
                              <thead>
                                  <tr>
                                      <th>Date</th>
                                      <th>Member</th>
                                      <th>Activity</th>
                                      <th>Amount (&#8358;)</th>
                                      <th>Authorized?</th>
                                      <th>Performed by</th>
                                  </tr>
                              </thead>
                              <tbody>
                                  @foreach($logs as $log)
                                  <tr>
                                      <td scope="row">{{ $log->created_at->toDateTimeString() }}</td>
                                      <td scope="row">{{ $log->member->full_name }} ({{ $log->ippis }})</td>
                                      <td>{{ $log->activity }}</td>
                                      <td class="text-right">{{ number_format($log->amount, 2) }}</td>
                                      <td class="text-center">{{ $log->is_authorized ? 'Yes' : '-' }}</td>
                                      <td>{{ $log->performed_by->full_name }} ({{ $log->done_by }})</td>
                                  </tr>
                                  @endforeach
                              </tbody>
                          </table>
                          @endcan
                      </div>
                    </div>
                </div>
                <div class="row-">
                  <div class="col-12 d-flex justify-content-center">
                    {{ $logs->appends(request()->except('page'))->links() }}
                  </div>
                </div>
                @else
                <p class="mt-3">
                    No records found
                </p>
                @endif
            </div>
        </div>
    </div>
</div>

@endsection


@section('js')

@endsection

