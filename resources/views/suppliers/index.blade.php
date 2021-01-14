@extends('master')

@section('body')

<div class="row">
    <div class="col-sm-12">
        <div class="page-title-box">
            <div class="row d-flex justify-content-end">
                <div class="col-12">
                    &nbsp
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">

                @include('accounting.linksPartial')

            </div>
        </div>
    </div><!-- end col -->
</div>

<div class="row">
    <div class="col-12">
        <div class="card m-b-30">
            <div class="card-body">

                <div class="row">
                    <div class="col-md-12 text-right">
                        <a href="{{ route('suppliers.create') }}" class="btn btn-primary">Add Supplier</a>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-lg-12">
                        <table class="table table-light">
                            <thead class="thead-light">
                                <tr>
                                    <th>#</th>
                                    <th>Name</th>
                                    <th>Phone</th>
                                    <th>Email</th>
                                    <th class="text-center">Action(s)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($suppliers as $supplier)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $supplier->fname }} {{ $supplier->lname }}</td>
                                    <td>{{ $supplier->phone }}</td>
                                    <td>{{ $supplier->email }}</td>
                                    <td class="text-center">
                                        <a href="{{ route('suppliers.edit', $supplier->id) }}" class="btn btn-success">Edit</a>
                                        <a href="{{ route('suppliers.delete', $supplier->id) }}" class="btn btn-danger" onclick = "return confirm('Are you sure?')">Delete</a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </div><!-- end col -->
</div>
@endsection
