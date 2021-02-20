@extends('master')

@section('body')
<!-- Page-Title -->

<link href="https://themesdesign.in/zinzer_1/plugins/dropzone/dist/dropzone.css" rel="stylesheet" type="text/css">

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

<div class="card m-b-30">
    <div class="card-body">
        <div class="row">
            <div class="col-12">
                <h4 class="page-title m-0">Import Monthly Deductions File From IPPIS</h4>
            </div>
        </div>
        <div class="row">
            @can('import and reconcile IPPIS deduction file')
            <div class="col-md-4">
                <div class="row">
                    <div class="col-12">
                        <h5 class="my-5">Import Excel File</h5>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    {!! Form::open(['route' => 'importFromIppisPost', 'class' => '','files'=>'true']) !!}

                        <div class="form-group row"><label for="ref" class="col-sm-3 col-form-label">Select file</label>
                            <div class="col-sm-9">
                                <div class="fallback"><input name="file" type="file"></div>
                            </div>
                        </div>

                        <div class="form-group row"><label for="deduction_for" class="col-sm-3 col-form-label">Deduction For</label>
                            <div class="col-sm-9">
                                {!! Form::date('deduction_for', null, ['class' => 'form-control', 'id' => 'deduction_for']) !!}
                            </div>
                        </div>

                        <div class="form-group row"><label for="ref" class="col-sm-3 col-form-label">Description</label>
                            <div class="col-sm-9">
                                {!! Form::text('ref', null, ['class' => 'form-control', 'id' => 'ref']) !!}
                            </div>
                        </div>

                        <div class="text-center m-t-15">
                            <button type="submit" class="btn btn-block btn-primary waves-effect waves-light">Upload monthly repayment</button>
                        </div>
                    {!! Form::close() !!}
                    </div>
                </div>
            </div>
            <div class="col-md-8">
                <div class="row">
                    <div class="col-12">
                        <h5 class="my-5">Download Reconciled IPPIS Deductions</h5>
                    </div>
                    <div class="col-12">

                    {!! Form::open(['route' => 'importFromIppis', 'method' => 'get']) !!}
                        <div class="row">
                            <div class="col-md-3 mb-1">
                                {{Form::date('dateFrom', $dateFrom, ['class' => 'form-control'])}}
                            </div>
                            <div class="col-md-3 mb-1">
                                {{Form::date('dateTo', $dateTo, ['class' => 'form-control'])}}
                            </div>
                            <div class="col-md-3 mb-1">
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
                            <td class="text-center">{{ Carbon\Carbon::parse($deduction->deduction_for)->format('F, Y') }}</td>
                            <td class="text-left">{{ $deduction->performed_by ? $deduction->performed_by->full_name : '' }}</td>
                            <td class="text-center"><span class="badge {{ $deduction->is_done == 1 ? 'badge-success' : 'badge-secondary' }}">{{ $deduction->is_done ? 'Yes' : 'No' }}</span></td>
                            <td class="text-center">
                                <a href="{{ route('downloadDeductionsFile', [$deduction->id, Carbon\Carbon::parse($deduction->deduction_for)->format('F'), Carbon\Carbon::parse($deduction->deduction_for)->format('Y')]) }}" class="btn btn-sm {{ $deduction->is_done == 1 ? 'btn-success' : 'btn-secondary disabled' }}">Download</a>
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
            @endcan
        </div>
    </div>
    
</div>
@endsection
