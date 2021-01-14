<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width,initial-scale=1,user-scalable=0,minimal-ui">
    <title>NASRDA COOPERATIVE SOCIETY</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta content="Admin Dashboard" name="description">
    <meta content="ThemeDesign" name="author">
    <meta http-equiv="X-UA-Compatible" content="IE=edge"><!-- App Icons -->
    <link rel="shortcut icon" href="{{ asset('assets/images/favicon.ico') }}"><!-- morris css -->
    <link href="{{ asset('assets/css/bootstrap.min.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('assets/css/icons.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('assets/css/style.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('handsontable/handsontable.full.min.css') }}" rel="stylesheet" type="text/css">
    <link rel="stylesheet" href="http://cdn.bootcss.com/toastr.js/latest/css/toastr.min.css">
    <link href="{{ asset('css/awesomplete.css') }}" rel="stylesheet" />
    <link href="{{asset('DataTables/datatables.min.css')}}" rel="stylesheet" type="text/css">
    
    <script src="https://unpkg.com/vue/dist/vue.js"></script>
    <script src="https://unpkg.com/vue-toastr/dist/vue-toastr.umd.min.js"></script>

    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/js/select2.min.js"></script>

    @livewireStyles
</head>

<body>

    <div class="header-bg">
        @include('includes/header') 
    </div>

    <div id="app" class="wrapper">
        <div class="container-fluid">

            <div class="mt-5">
                @include('flash::message')
            </div>

            @yield('body')
        </div>
    </div>

    <footer class="footer">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">© {{date('Y')}}</div>
            </div>
        </div>
    </footer>

    <script src="{{ asset('js/app.js') }}"></script>
    <script src="{{ asset('js/jquery-3.3.0.js') }}"></script>
    <script src="https://code.jquery.com/jquery-3.3.1.min.js" integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8=" crossorigin="anonymous"></script>
    <script src="{{ asset('assets/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('assets/js/modernizr.min.js') }}"></script>
    <script src="{{ asset('handsontable/handsontable.full.min.js') }}"></script>
    <script src="http://cdn.bootcss.com/toastr.js/latest/js/toastr.min.js"></script>
    <script src="{{ asset('DataTables/datatables.min.js') }}"></script>

    <script src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/js/select2.min.js"></script>

    <script src="{{ asset('js/awesomplete.js') }}"></script>

    {!! Toastr::message() !!} 
    <script>
        $(document).ready(function () {

            $('.select2').select2();
        });
        
        $('#flash-overlay-modal').modal();
    </script>
    <script>
        $(document).ready(function () {

            $('body').on('click', '[data-toggle="modal"]', function () {
                url = $(this).data("remote")
                // console.log(url)
                $($(this).data("target") + ' .modal-body').load(url);
            });

            $('#confirmationModal').on('show.bs.modal', function (e) {
                $(this).find('.confirm').attr('href', $(e.relatedTarget).data('href'));
            });

            $(".selectBank").change(function() {

                var bank_code = $(this).val();

                $.ajax({
                    url: '{{ url('ledger/check-account-balance') }}/'+bank_code+'',
                    type: 'GET',
                    dataType: "JSON",

                    success: function(result) {
                        $(".bankbalance span").text(result);
                    }
                });
            });

            $('.select2').select2();

        });

        // AWESOMEPLETE
        var searchForm = document.getElementById("search");
        var awesomplete_searchForm = new Awesomplete(searchForm, {
            minChars: 1,
            autoFirst: true
        });

        $("input[name=search]").on("keyup", function(){

            $.ajax({
                url: "{{ url('members/awesomplete') }}",
                headers: {'X-CSRF-TOKEN': $('input[name=_token]').val()},
                type: 'POST',
                data: {q:this.value},
                dataType: 'json',
                success: function(data) {
                    var list = [];
                    $.each(data, function(key, value) {
                        // list.push(`IPPIS: ${value.ippis} Name: ${value.full_name}`);
                        list.push({ label: `IPPIS: ${value.ippis} Name: ${value.full_name}`, value: value.ippis })
                    });
                    awesomplete_searchForm.list = list;
                }
            })
        });

        $('.datatable').DataTable( {
            fixedHeader: true,
            paging: false,
            // dom: 'Bfrtip',
            // buttons: [
            //     'copy', 'csv', 'excel', 'pdf', 'print'
            // ]
        } );

    </script>

    @yield('js')

    @livewireScripts

</body>

</html>





<div class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content p-4">
            <div class="modal-header">
                <h5 class="modal-title mt-0" id="myModalLabel">New Ledger Entry</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                loading...
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-secondary waves-effect"
                    data-dismiss="modal">Close</button> <button type="button"
                    class="btn btn-primary waves-effect waves-light">Save
                    changes</button></div>
        </div>
    </div>
</div>


<div id="myModal" class="modal fade bs-example-modal-lg" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content p-4">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">loading...
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-secondary waves-effect"
                    data-dismiss="modal">Close</button></div>
        </div>
    </div>
</div>

<div id="largeModal" class="modal fade bs-example-modal-lg" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel"
    aria-hidden="true" style="min-width: 960px;">
    <div class="modal-dialog modal-lg">
        <div class="modal-content p-4">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">loading...
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-secondary waves-effect"
                    data-dismiss="modal">Close</button></div>
        </div>
    </div>
</div>
