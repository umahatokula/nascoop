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
                <div class="col-12">
                  <h5 class="page-title m-0">Download Reconciled IPPIS Deductions</h5>
                  <p>&nbsp</p>
                </div>
                <div class="col-12">

                {!! Form::open(['route' => 'downloadDeductions', 'method' => 'get']) !!}
                  <div class="row">
                      <div class="col-md-3 mb-1">
                          {{Form::date('dateFrom', $dateFrom, ['class' => 'form-control'])}}
                      </div>
                      <div class="col-md-3 mb-1">
                          {{Form::date('dateTo', $dateTo, ['class' => 'form-control'])}}
                      </div>
                      <!-- <div class="col-md-3 mb-1">
                          {{Form::select('center_id', $centers, $center_id, ['class' => 'form-control'])}}
                      </div> -->
                      <div class="col-md-3 mb-1">
                          <button type="submit" class="btn btn-primary waves-effect waves-light">Filter</button>
                      </div>
                  </div>
                {!! Form::close() !!}
                </div>
              </div>

              <div class="row">
                <div class="col-12">
                  @if($deductions->count() > 0)
                  <table class="table table-striped table-bordered table-responsive">
                    <thead>
                      <th class="text-center">S/N</th>
                      <th class="text-left">Description</th>
                      <th class="text-center">Deduction Month</th>
                      <!-- <th class="text-center">Center</th> -->
                      <th class="text-left">Performed By</th>
                      <th class="text-center">Processing Complete</th>
                      <th class="text-center">Action(s)</th>
                    </thead>
                    <tbody>
                      @foreach($deductions as $deduction)
                      <tr>
                        <td class="text-center">{{ $loop->iteration }}</td>
                        <td class="text-left">{{ $deduction->ref }}</td>
                        <td class="text-center">{{ $deduction->deduction_for->format('F, Y') }}</td>
                        <!-- <td class="text-center">{{ $deduction->center ? $deduction->center->name : '' }}</td> -->
                        <td class="text-left">{{ $deduction->performed_by ? $deduction->performed_by->full_name : '' }}</td>
                        <td class="text-center"><span class="badge {{ $deduction->is_done ? 'badge-success' : 'badge-secondary' }}">{{ $deduction->is_done ? 'Yes' : 'No' }}</span></td>
                        <td class="text-center">
                          <a href="{{ route('downloadDeductionsFile', [$deduction->id, $deduction->deduction_for->format('F'), $deduction->deduction_for->format('Y')]) }}" class="btn btn-sm {{ $deduction->is_done ? 'btn-success' : 'btn-secondary disabled' }}">Download</a>
                        </td>
                      </tr>
                      @endforeach
                    </tbody>
                  </table>
                  @else
                  <p>No records founds</p>
                  @endif
                </div>
              </div>

            </div>
        </div>
    </div><!-- end col -->
</div>
@endsection
