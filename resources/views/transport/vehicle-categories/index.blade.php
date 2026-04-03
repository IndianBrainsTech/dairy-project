@extends('app-layouts.admin-master')

@section('title', 'Vehicle Categories')

@section('headerStyle')
    <link href="{{ asset('plugins/datatables/dataTables.bootstrap4.min.css') }}" rel="stylesheet" type="text/css" />
@stop

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            @component('app-components.breadcrumb-4')
                @slot('title') Vehicle Categories @endslot
                @slot('item1') Masters @endslot
                @slot('item2') Transport @endslot
                @slot('item3') Vehicle Categories @endslot
            @endcomponent
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    @endif

    <div class="row">
        <div class="col-12 col-lg-8">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <button type="button" class="btn btn-pink btn-round font-weight-medium px-3">
                            {{ $categories->count() }} {{ Str::plural('Category', $categories->count()) }}
                        </button>
                        <a href="{{ route('transport.vehicle-categories.create') }}" class="btn btn-primary px-3">
                            <i class="mdi mdi-plus-circle-outline mr-1"></i>Add Category
                        </a>
                    </div>

                    <div class="table-responsive">
                        <table id="datatable" class="table table-bordered table-sm table-hover dt-responsive nowrap w-100">
                            <thead class="thead-light">
                                <tr>
                                    <th class="text-center" style="width:50px">S.No</th>
                                    <th>Category Name</th>
                                    <th>Description</th>
                                    <th class="text-center">Vehicles</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-center" style="width:80px">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($categories as $category)
                                <tr>
                                    <td class="text-center">{{ $loop->iteration }}</td>
                                    <td>{{ $category->name }}</td>
                                    <td>{{ $category->description ?? '—' }}</td>
                                    <td class="text-center">
                                        <span class="badge badge-info">{{ $category->vehicles_count }}</span>
                                    </td>
                                    <td class="text-center">
                                        @if($category->status === 'active')
                                            <span class="badge badge-soft-success">Active</span>
                                        @else
                                            <span class="badge badge-soft-danger">Inactive</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <a href="{{ route('transport.vehicle-categories.edit', $category) }}" class="mr-2">
                                            <i class="dripicons-pencil text-warning font-18"></i>
                                        </a>
                                        <form action="{{ route('transport.vehicle-categories.destroy', $category) }}"
                                              method="POST" class="d-inline"
                                              onsubmit="return confirm('Delete this category?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-link p-0">
                                                <i class="dripicons-trash text-danger font-18"></i>
                                            </button>
                                        </form>
                                    </td>
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
@stop

@push('custom-scripts')
<script>
$(document).ready(function () {
    $('#datatable').dataTable({
        "lengthMenu": [[10, 25, 50], [10, 25, 50]],
        "pageLength": 25
    });
});
</script>
@endpush

@section('footerScript')
    <script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables/dataTables.bootstrap4.min.js') }}"></script>
@stop
