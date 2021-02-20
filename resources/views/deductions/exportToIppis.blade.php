@extends('master')

@section('body')
<!-- Page-Title -->

<link href="https://themesdesign.in/zinzer_1/plugins/dropzone/dist/dropzone.css" rel="stylesheet" type="text/css">

<div class="row">
    <div class="col-sm-12">
        <div class="page-title-box">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h4 class="page-title m-0">Export and Import Monthly Deductions File</h4>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">

    @can('generate IPPIS deduction file')
    <div class="col-md-4">
        <div class="card m-b-30">
            <div class="card-body text-left">
                <h4 class="mt-0 header-title">Export Excel File</h4>
                <p class="text-muted m-b-30">
                    Select a center to generate monthly deductions
                </p>
                {!! Form::open(['route' => 'exportToIppisPost', 'method' => 'POST']) !!}

                    <div class="form-group row"><label for="deduction_for" class="col-sm-3 col-form-label">Deduction For</label>
                        <div class="col-sm-9">
                            {!! Form::date('deduction_for', null, ['class' => 'form-control', 'id' => 'deduction_for']) !!}
                        </div>
                    </div>

                    <div class="text-center m-t-15">
                        <button href="{{ route('exportToIppis') }}" class="btn btn-primary btn-block mt-3">Generate monthly deduction</button>
                    </div>

                {!! Form::close() !!}

            </div>
        </div>
    </div><!-- end col -->
    @endcan
    <div class="col-md-8">
        <div class="card m-b-30">
            <div class="card-body text-left">
            
                @can('generate IPPIS deduction file')
                <div class="row mb-5">
                    <div class="col-12">
                        <h5 class="page-title m-0">Download IPPIS Deductions Files</h5>
                        <p>&nbsp</p>
                    </div>
                    <div class="col-12">

                    {!! Form::open(['route' => 'exportToIppis', 'method' => 'get']) !!}
                        <div class="row">
                            <div class="col-md-4 mb-1">
                                {{Form::date('dateFrom', $dateFrom, ['class' => 'form-control'])}}
                            </div>
                            <div class="col-md-4 mb-1">
                                {{Form::date('dateTo', $dateTo, ['class' => 'form-control'])}}
                            </div>
                            <div class="col-md-4 mb-1">
                                <button type="submit" class="btn btn-primary waves-effect waves-light btn-block">Filter</button>
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
                            <th class="text-center">Date</th>
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
                            <td class="text-left">{{ $deduction->performed_by ? $deduction->performed_by->full_name : '' }}</td>
                            <td class="text-center"><span class="badge {{ $deduction->is_done ? 'badge-success' : 'badge-secondary' }}">{{ $deduction->is_done ? 'Yes' : 'No' }}</span></td>
                            <td class="text-center">
                                <a href="{{ route('downloadIppisDeductionFile', [$deduction->id, $deduction->deduction_for->format('F'), $deduction->deduction_for->format('Y')]) }}" class="btn btn-sm {{ $deduction->is_done ? 'btn-success' : 'btn-secondary disabled' }}">Download</a>
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
                @endcan
            </div>
        </div>
    </div><!-- end col -->
    
</div>
@endsection
