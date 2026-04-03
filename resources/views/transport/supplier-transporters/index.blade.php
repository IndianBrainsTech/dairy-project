@extends('app-layouts.admin-master')
@section('title', 'Supplier Transporters')
@section('headerStyle')
    <link href="{{ asset('plugins/datatables/dataTables.bootstrap4.min.css') }}" rel="stylesheet">
@stop
@section('content')
<div class="container-fluid">
    <div class="row"><div class="col-12">
        @component('app-components.breadcrumb-4')
            @slot('title') Supplier Transporters @endslot
            @slot('item1') Masters @endslot @slot('item2') Transport @endslot @slot('item3') Supplier Transporters @endslot
        @endcomponent
    </div></div>
    @if(session('success'))<div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="close" data-dismiss="alert"><span>&times;</span></button></div>@endif
    @if(session('error'))<div class="alert alert-danger alert-dismissible fade show">{{ session('error') }}<button type="button" class="close" data-dismiss="alert"><span>&times;</span></button></div>@endif
    <div class="row"><div class="col-12"><div class="card"><div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <button class="btn btn-pink btn-round font-weight-medium px-3">{{ $transporters->count() }} {{ Str::plural('Transporter', $transporters->count()) }}</button>
            <a href="{{ route('transport.supplier-transporters.create') }}" class="btn btn-primary px-3"><i class="mdi mdi-plus-circle-outline mr-1"></i>Add Transporter</a>
        </div>
        <div class="table-responsive">
            <table id="datatable" class="table table-bordered table-sm table-hover dt-responsive nowrap w-100">
                <thead class="thead-light"><tr>
                    <th class="text-center" style="width:45px">S.No</th>
                    <th>Name</th><th>Contact Person</th><th>Phone</th><th>GST Number</th>
                    <th class="text-center">Status</th><th class="text-center" style="width:80px">Action</th>
                </tr></thead>
                <tbody>
                    @foreach($transporters as $t)
                    <tr>
                        <td class="text-center">{{ $loop->iteration }}</td>
                        <td class="font-weight-medium">{{ $t->name }}</td>
                        <td>{{ $t->contact_person ?? '—' }}</td>
                        <td>{{ $t->phone ?? '—' }}</td>
                        <td>{{ $t->gst_number ?? '—' }}</td>
                        <td class="text-center">
                            @if($t->status === 'active')<span class="badge badge-soft-success">Active</span>
                            @else<span class="badge badge-soft-danger">Inactive</span>@endif
                        </td>
                        <td class="text-center">
                            <a href="{{ route('transport.supplier-transporters.show', $t) }}" class="mr-1"><i class="dripicons-preview text-primary font-18"></i></a>
                            <a href="{{ route('transport.supplier-transporters.edit', $t) }}"><i class="dripicons-pencil text-warning font-18"></i></a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div></div></div></div>
</div>
@stop
@push('custom-scripts')
<script>$(document).ready(function(){ $('#datatable').dataTable({"lengthMenu":[[10,25,50],[10,25,50]],"pageLength":25}); });</script>
@endpush
@section('footerScript')
<script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('plugins/datatables/dataTables.bootstrap4.min.js') }}"></script>
@stop