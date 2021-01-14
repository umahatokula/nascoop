<link href="{{ public_path('assets/css/bootstrap.min.css') }}" rel="stylesheet">

<!-- Page-Title -->
<div class="row">
    <div class="col-sm-12">
        <div class="page-title-box">
            <div class="row align-items-center">
                <div class="col-md-12">
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
            
                @if($logs->count() > 0)
                <div class="row-">
                  <div class="col-12">                  
                    <div class="mt-3">
                          @can('read centre')
                          <table class="table table-bordered">
                              <thead>
                                  <tr>
                                      <th>IPPIS</th>
                                      <th>Activity</th>
                                      <th>Amount (&#8358;)</th>
                                      <th>Authorized?</th>
                                      <th>Performed by</th>
                                  </tr>
                              </thead>
                              <tbody>
                                  @foreach($logs as $log)
                                  <tr>
                                      <td scope="row">{{ $log->member->full_name }} ({{ $log->ippis }})</td>
                                      <td>{{ $log->activity }}</td>
                                      <td class="text-right">{{ number_format($log->amount, 2) }}</td>
                                      <td>{{ $log->is_authorized ? 'Yes' : '-' }}</td>
                                      <td>{{ $log->performed_by->full_name }} ({{ $log->done_by }})</td>
                                  </tr>
                                  @endforeach
                              </tbody>
                          </table>
                          @endcan
                      </div>
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

