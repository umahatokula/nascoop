@extends('master')

@section('body')
<!-- Page-Title -->


<div class="row">
    <div class="col-sm-12">
        <div class="page-title-box">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h4 class="page-title m-0">Shares <span class="text-info">[ {{ $member->full_name }} |
                            {{ $member->ippis }} ]</span><span
                            class="text-{{ $member->is_active? 'success' : 'danger' }}">[
                            {{ $member->is_active? 'active' : 'inactive' }} ]</span></h4>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card m-b-30">
            <div class="card-body">

              <div class="row mb-3">
                <div class="col-12 text-right">
                  @can('create long term loan')
                  <a href="{{route('sharesBuy', $member->ippis)}}"
                      class="btn btn-success waves-effect waves-light mb-1 {{ $member->is_active ? '' : 'disabled' }}"><i
                          class="mdi mdi-file-document-box"></i> Buy Shares</a>
                  @endcan
                </div>
              </div>

              <div class="row">
                <div class="col-12">
                  @if($shares->count() > 0)
                  <table class="table table-stripped table-bordered">
                    <thead>
                      <th class="text-center">Purchase Date</th>
                      <th class="text-center">Units</th>
                      <th class="text-center">Amount</th>
                      <th class="text-center">Method of Payment</th>
                      <th class="text-center">Authorized</th>
                    </thead>
                    <tbody>
                      @foreach($shares as $share)
                      <tr class="{{ $share->is_authorized == 2 ? 'text-muted' : '' }}" style="{{ $share->is_authorized == 2 ? 'text-decoration: line-through;' : '' }}">
                        <td class="text-center">{{ $share->date_bought->toFormattedDateString() }}</td>
                        <td class="text-center">{{ $share->units }}</td>
                        <td class="text-right">{{ number_format($share->amount, 2) }}</td>
                        <td class="text-center">{{ $share->payment_method == 'savings' ? 'From Savings' : 'Bank Deposit' }}</td>
                        <td class="text-center">
                          @if($share->is_authorized == 0)
                          <span class="text-default">P</span>
                          @elseif($share->is_authorized == 1)
                          <span class="text-success">A</span>
                          @else
                          <span class="text-danger">C</span>
                          @endif
                        </td>
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
