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
    <div class="col-lg-8">
        <div class="card m-b-30">
            <div class="card-body">
              <h4 class="mb-5">Shares Settings</h4>

              @if ($errors->any())
                  <div class="alert alert-danger">
                      <ul>
                          @foreach ($errors->all() as $error)
                              <li>{{ $error }}</li>
                          @endforeach
                      </ul>
                  </div>
              @endif

              {!! Form::open(['route' => 'sharesSettingsPost', 'method' => 'POST']) !!}
              <div class="form-group row">
                <label for="rate" class="col-sm-2 col-form-label">Conversion rate <br> <small>1 unit of share equals how much Naira?</small></label>
                
                  <div class="col-sm-10">
                      {!! Form::text('rate', $settings ? $settings->rate : null, ['class' => 'form-control', 'id' => 'rate']) !!}
                  </div>
              </div>
              <div class="form-group row"><label for="open_date" class="col-sm-2 col-form-label">Open Date</label>
                  <div class="col-sm-10">
                      {!! Form::date('open_date', $settings ? $settings->open_date : null, ['class' => 'form-control', 'id' => 'open_date']) !!}
                  </div>
              </div>
              <div class="form-group row"><label for="close_date" class="col-sm-2 col-form-label">Close Date</label>
                  <div class="col-sm-10">
                      {!! Form::date('close_date', $settings ? $settings->close_date : null, ['class' => 'form-control', 'id' => 'close_date']) !!}
                  </div>
              </div>
              <div class="form-group row"><label for="coop_no" class="col-sm-2 col-form-label">&nbsp </label>
                  <div class="col-sm-10">
                      <button class="btn btn-primary">Submit</button>
                  </div>
              </div>
              {!! Form::close() !!}

            </div>
        </div>
    </div><!-- end col -->
</div>
@endsection
