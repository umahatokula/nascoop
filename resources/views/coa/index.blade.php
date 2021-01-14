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

              <div class="row">
                  <div class="col-sm-12">
                      <div class="page-title-box">
                          <div class="row align-items-center">
                              <div class="col-md-8">
                                  <h4 class="page-title m-0">Chart of Accounts (COA)</h4>
                              </div>
                          </div>
                      </div>
                  </div>
              </div>

              <div class="row">
                <div class="col-md-3">
                    <div class="nav flex-column nav-pills" id="v-pills-tab" role="tablist" aria-orientation="vertical">
                    <a class="nav-link mb-2 active" id="v-pills-profile-tab" data-toggle="pill" href="#v-pills-profile" role="tab" aria-controls="v-pills-profile" aria-selected="false">Assets</a>
                    <a class="nav-link mb-2" id="v-pills-messages-tab" data-toggle="pill" href="#v-pills-messages" role="tab" aria-controls="v-pills-messages" aria-selected="false">Liability</a>
                    <a class="nav-link" id="v-pills-settings-tab" data-toggle="pill" href="#v-pills-settings" role="tab" aria-controls="v-pills-settings" aria-selected="false">Income</a>
                    <a class="nav-link mb-2" id="v-pills-home-tab" data-toggle="pill" href="#v-pills-home" role="tab" aria-controls="v-pills-home" aria-selected="true">Direct Cost</a>
                    <a class="nav-link mb-2" id="shares-tab" data-toggle="pill" href="#shares" role="tab" aria-controls="shares" aria-selected="true">Expenses</a>
                    </div>
                </div>
                <div class="col-md-9">
                    <div class="tab-content text-muted mt-4 mt-md-0" id="v-pills-tabContent">

                        <div class="tab-pane fade show active" id="v-pills-profile" role="tabpanel" aria-labelledby="v-pills-profile-tab">
                          
                            <table class="table table-bordered">
                              <thead>
                                <th>Account</th>
                                <th class="text-right">Amount</th>
                              </thead>
                              <tbody>
                                @foreach($assets as $res)
                                <tr>
                                  <td>
                                    @for($i=0 ; $i<$res->level ; $i++ )
                                      &nbsp &nbsp &nbsp &nbsp
                                    @endfor
                                    {{ strtoupper($res->title) }} &nbsp (#ID: {{ strtoupper($res->id) }})
                                  </td>
                                  <td class="text-right">NGN</td>
                                </tr>
                                @endforeach
                              </tbody>
                            </table>

                        </div>

                        <div class="tab-pane fade" id="v-pills-messages" role="tabpanel" aria-labelledby="v-pills-messages-tab">
                                                   
                          <table class="table table-bordered">
                              <thead>
                                <th>Account</th>
                                <th class="text-right">Amount</th>
                              </thead>
                              <tbody>
                                @foreach($liability as $res)
                                <tr>
                                  <td>
                                    @for($i=0 ; $i<$res->level ; $i++ )
                                      &nbsp &nbsp &nbsp &nbsp
                                    @endfor
                                    {{ strtoupper($res->title) }} &nbsp (#ID: {{ strtoupper($res->id) }})
                                  </td>
                                  <td class="text-right">NGN</td>
                                </tr>
                                @endforeach
                              </tbody>
                            </table>

                        </div>

                        <div class="tab-pane fade" id="v-pills-settings" role="tabpanel" aria-labelledby="v-pills-settings-tab">
                          
                          <table class="table table-bordered">
                              <thead>
                                <th>Account</th>
                                <th class="text-right">Amount</th>
                              </thead>
                              <tbody>
                                @foreach($income as $res)
                                <tr>
                                  <td>
                                    @for($i=0 ; $i<$res->level ; $i++ )
                                      &nbsp &nbsp &nbsp &nbsp
                                    @endfor
                                    {{ strtoupper($res->title) }} &nbsp (#ID: {{ strtoupper($res->id) }})
                                  </td>
                                  <td class="text-right">NGN </td>
                                </tr>
                                @endforeach
                              </tbody>
                            </table>

                        </div>
                        <div class="tab-pane fade" id="v-pills-home" role="tabpanel" aria-labelledby="v-pills-home-tab">
                          
                          <table class="table table-bordered">
                              <thead>
                                <th>Account</th>
                                <th class="text-right">Amount</th>
                              </thead>
                              <tbody>
                                @foreach($directCost as $res)
                                <tr>
                                  <td>
                                    @for($i=0 ; $i<$res->level ; $i++ )
                                      &nbsp &nbsp &nbsp &nbsp
                                    @endfor
                                    {{ strtoupper($res->title) }} &nbsp (#ID: {{ strtoupper($res->id) }})
                                  </td>
                                  <td class="text-right">NGN </td>
                                </tr>
                                @endforeach
                              </tbody>
                            </table>

                        </div>
                        <div class="tab-pane fade" id="shares" role="tabpanel" aria-labelledby="shares-tab">
                          
                          <table class="table table-bordered">
                              <thead>
                                <th>Account</th>
                                <th class="text-right">Amount</th>
                              </thead>
                              <tbody>
                                @foreach($expenses as $res)
                                <tr>
                                  <td>
                                    @for($i=0 ; $i<$res->level ; $i++ )
                                      &nbsp &nbsp &nbsp &nbsp
                                    @endfor
                                    {{ strtoupper($res->title) }} &nbsp (#ID: {{ strtoupper($res->id) }})
                                  </td>
                                  <td class="text-right">NGN </td>
                                </tr>
                                @endforeach
                              </tbody>
                            </table>
                            
                        </div>
                    </div>
                </div>
              </div>

            </div>
        </div>
    </div><!-- end col -->
</div>
@endsection
