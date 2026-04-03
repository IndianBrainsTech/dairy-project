@extends('app-layouts.admin-master')
@section('title', 'Vehicle Services')
@section('headerStyle')
    <link href="{{ asset('plugins/datatables/dataTables.bootstrap4.min.css') }}" rel="stylesheet">
@stop
@section('content')
<div class="container-fluid">
    <div class="row"><div class="col-12">
        @component('app-components.breadcrumb-4')
            @slot('title') Vehicle Services @endslot
            @slot('item1') Transport @endslot @slot('item2') Masters @endslot @slot('item3') Vehicle Services @endslot
        @endcomponent
    </div></div>
    @if(session('success'))<div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="close" data-dismiss="alert"><span>&times;</span></button></div>@endif
    @if(session('error'))<div class="alert alert-danger alert-dismissible fade show">{{ session('error') }}<button type="button" class="close" data-dismiss="alert"><span>&times;</span></button></div>@endif
    <div class="card"><div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <button class="btn btn-pink btn-round font-weight-medium px-3">Records</button>
            <a href="{{ route('transport.vehicle-services.create') }}" class="btn btn-primary px-3">
                <i class="mdi mdi-plus-circle-outline mr-1"></i>Add New
            </a>
        </div>
        <div class="table-responsive">
            <table id="datatable" class="table table-bordered table-sm table-hover dt-responsive nowrap w-100">
                <thead class="thead-light"><tr>
                    <th class="text-center" style="width:45px">S.No</th>
                    <th>Service No.</th><th>Date</th><th>Vehicle</th><th>Type</th><th class="text-right">Total Cost</th><th class="text-center">Status</th>
                    <th class="text-center" style="width:80px">Action</th>
                </tr></thead>
                <tbody>
                    @foreach($services as $record)
                    <tr>
                        <td class="text-center">{{ $loop->iteration }}</td>
                        <td>{{ $record->service_number }}</td>
                        <td>{{ $record->service_date->format('d-m-Y') }}</td>
                        <td>{{ $record->vehicle->vehicle_number ?? '—' }}</td>
                        <td>{{ ucfirst($record->service_type) }}</td>
                        <td class="text-right">₹ {{ number_format($record->total_cost,2) }}</td>
                        <td class="text-center">
                            @php $sc = ['completed'=>'success','in_progress'=>'warning','scheduled'=>'info'][$record->status] ?? 'secondary'; @endphp
                            <span class="badge badge-soft-{{ $sc }}">{{ ucfirst(str_replace('_',' ',$record->status)) }}</span>
                        </td>
                        <td class="text-center">
                            <a href="{{ route('transport.vehicle-services.show', $record) }}" class="mr-1"><i class="dripicons-preview text-primary font-18"></i></a>
                            <a href="{{ route('transport.vehicle-services.edit', $record) }}"><i class="dripicons-pencil text-warning font-18"></i></a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div></div>
</div>
@stop
@push('custom-scripts')
<script>$(document).ready(function(){ $('#datatable').dataTable({"lengthMenu":[[25,50],[25,50]],"pageLength":25,"order":[[0,"asc"]]}); });</script>
@endpush
@section('footerScript')
<script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('plugins/datatables/dataTables.bootstrap4.min.js') }}"></script>
@stop