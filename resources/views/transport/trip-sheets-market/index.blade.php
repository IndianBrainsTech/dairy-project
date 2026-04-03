@extends('app-layouts.admin-master')
@section('title', 'Market Trip Sheets')
@section('headerStyle')
    <link href="{{ asset('plugins/datatables/dataTables.bootstrap4.min.css') }}" rel="stylesheet">
@stop
@section('content')
<div class="container-fluid">
    <div class="row"><div class="col-12">
        @component('app-components.breadcrumb-4')
            @slot('title') Market Trip Sheets @endslot
            @slot('item1') Transport @endslot @slot('item2') Transactions @endslot @slot('item3') Market Trip Sheets @endslot
        @endcomponent
    </div></div>
    @if(session('success'))<div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="close" data-dismiss="alert"><span>&times;</span></button></div>@endif
    @if(session('error'))<div class="alert alert-danger alert-dismissible fade show">{{ session('error') }}<button type="button" class="close" data-dismiss="alert"><span>&times;</span></button></div>@endif
    <div class="card"><div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <button class="btn btn-pink btn-round font-weight-medium px-3">Records</button>
            <a href="{{ route('transport.trip-sheets-market.create') }}" class="btn btn-primary px-3">
                <i class="mdi mdi-plus-circle-outline mr-1"></i>Add New
            </a>
        </div>
        <div class="table-responsive">
            <table id="datatable" class="table table-bordered table-sm table-hover dt-responsive nowrap w-100">
                <thead class="thead-light"><tr>
                    <th class="text-center" style="width:45px">S.No</th>
                    <th>Trip No.</th><th>Date</th><th>Vehicle</th><th class="text-right">Delivered Qty</th><th class="text-right">Trip Amt</th><th class="text-center">Status</th>
                    <th class="text-center" style="width:80px">Action</th>
                </tr></thead>
                <tbody>
                    @foreach($tripSheets as $record)
                    <tr>
                        <td class="text-center">{{ $loop->iteration }}</td>
                        <td>{{ $record->trip_number }}</td>
                        <td>{{ $record->trip_date->format('d-m-Y') }}</td>
                        <td>{{ $record->vehicle->vehicle_number ?? '—' }}</td>
                        <td class="text-right">{{ number_format($record->delivered_qty,2) }}</td>
                        <td class="text-right">₹ {{ number_format($record->trip_amount,2) }}</td>
                        <td class="text-center">
                            @php $sc = ['pending'=>'warning','completed'=>'success','cancelled'=>'danger'][$record->status] ?? 'secondary'; @endphp
                            <span class="badge badge-soft-{{ $sc }}">{{ ucfirst($record->status) }}</span>
                        </td>
                        <td class="text-center">
                            <a href="{{ route('transport.trip-sheets-market.show', $record) }}" class="mr-1"><i class="dripicons-preview text-primary font-18"></i></a>
                            <a href="{{ route('transport.trip-sheets-market.edit', $record) }}"><i class="dripicons-pencil text-warning font-18"></i></a>
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