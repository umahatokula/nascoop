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
                  <h4 class="page-title m-0"> Ledger Snapshots</h4>
                  <p>&nbsp</p>
                </div>
                <div class="col-lg-8 float-right">

                {!! Form::open(['route' => 'ledgerSnapShotPost', 'method' => 'post']) !!}
                  <div class="row">
                      <div class="col-md-3 mb-1">
                          Select Center:
                      </div>
                      <div class="col-md-3 mb-1">
                          {{Form::select('pay_point_id', $paypoints, null, ['class' => 'form-control'])}}
                      </div>
                      <div class="col-md-3 mb-1">
                          <button type="submit" class="btn btn-primary waves-effect waves-light">Take Snapshot</button>
                      </div>
                  </div>
                {!! Form::close() !!}
                </div>
              </div>

              <div class="row">
                  <div class="col-md-12">
                    @if($snapShots->count() > 0)
                      <table class="table table-stripped table-bordered table-responsive">
                        <thead>
                          <th class="text-center">S/N</th>
                          <th class="text-center">Center</th>
                          <th class="text-left">Performed By</th>
                          <th class="text-center">Processing Complete</th>
                          <th class="text-center">Date Taken</th>
                          <th class="text-center">Action(s)</th>
                        </thead>
                        <tbody>
                          @foreach($snapShots as $snapShot)
                          <tr>
                        <td class="text-center">{{ $loop->iteration }}</td>
                        <td class="text-center">{{ $snapShot->center ? $snapShot->center->name : '' }}</td>
                        <td class="text-left">{{ $snapShot->performed_by ? $snapShot->performed_by->full_name : '' }}</td>
                        <td class="text-center"><span class="badge {{ $snapShot->is_done ? 'badge-success' : 'badge-secondary' }}">{{ $snapShot->is_done ? 'Yes' : 'No' }}</span></td>
                        <td class="text-center">{{ $snapShot->created_at->format('Y-m-d H:m:i') }}</td>
                        <td class="text-center">
                          <a href="{{ route('downloadSnapshotFile', [$snapShot->id, $snapShot->center ? $snapShot->center->name : '']) }}" class="btn btn-sm {{ $snapShot->is_done ? 'btn-success' : 'btn-secondary disabled' }}">Download</a>
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
                  </div>
              </div>

            </div>
        </div>
    </div><!-- end col -->
</div>
@endsection
