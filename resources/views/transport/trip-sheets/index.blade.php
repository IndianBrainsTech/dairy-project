@extends('app-layouts.admin-master')
@section('title', 'Trip Sheets')
@section('headerStyle')
    <link href="{{ asset('plugins/datatables/dataTables.bootstrap4.min.css') }}" rel="stylesheet">
@stop
@section('content')
<div class="container-fluid">
    <div class="row"><div class="col-12">
        @component('app-components.breadcrumb-4')
            @slot('title') Trip Sheets @endslot
            @slot('item1') Transport @endslot @slot('item2') Transactions @endslot @slot('item3') Trip Sheets @endslot
        @endcomponent
    </div></div>

    @if(session('success'))<div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="close" data-dismiss="alert"><span>&times;</span></button></div>@endif
    @if(session('error'))<div class="alert alert-danger alert-dismissible fade show">{{ session('error') }}<button type="button" class="close" data-dismiss="alert"><span>&times;</span></button></div>@endif

    {{-- Filters --}}
    <div class="card mb-3"><div class="card-body py-2">
        <form method="GET" action="{{ route('transport.trip-sheets.index') }}" class="form-inline flex-wrap">
            <select name="vehicle_id" class="form-control form-control-sm mr-2 mb-1">
                <option value="">All Vehicles</option>
                @foreach($vehicles as $id => $num)
                    <option value="{{ $id }}" @selected(request('vehicle_id') == $id)>{{ $num }}</option>
                @endforeach
            </select>
            <select name="status" class="form-control form-control-sm mr-2 mb-1">
                <option value="">All Status</option>
                <option value="pending"   @selected(request('status') === 'pending')>Pending</option>
                <option value="completed" @selected(request('status') === 'completed')>Completed</option>
                <option value="cancelled" @selected(request('status') === 'cancelled')>Cancelled</option>
            </select>
            <input type="date" name="from_date" value="{{ request('from_date') }}" class="form-control form-control-sm mr-2 mb-1" placeholder="From">
            <input type="date" name="to_date"   value="{{ request('to_date') }}"   class="form-control form-control-sm mr-2 mb-1" placeholder="To">
            <button type="submit" class="btn btn-info btn-sm mb-1 mr-1">Filter</button>
            <a href="{{ route('transport.trip-sheets.index') }}" class="btn btn-secondary btn-sm mb-1">Clear</a>
        </form>
    </div></div>

    {{-- Summary --}}
    <div class="row mb-3">
        <div class="col-6 col-md-3"><div class="card text-center py-2">
            <h5 class="mb-0 text-primary">{{ $totals->total_trips }}</h5><small class="text-muted">Total Trips</small>
        </div></div>
        <div class="col-6 col-md-3"><div class="card text-center py-2">
            <h5 class="mb-0 text-success">{{ number_format($totals->total_milk, 2) }}</h5><small class="text-muted">Net Milk (L)</small>
        </div></div>
        <div class="col-6 col-md-3"><div class="card text-center py-2">
            <h5 class="mb-0 text-info">₹ {{ number_format($totals->total_amount, 2) }}</h5><small class="text-muted">Trip Amount</small>
        </div></div>
        <div class="col-6 col-md-3"><div class="card text-center py-2">
            <h5 class="mb-0 text-warning">₹ {{ number_format($totals->total_diesel, 2) }}</h5><small class="text-muted">Diesel Cost</small>
        </div></div>
    </div>

    <div class="card"><div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <button class="btn btn-pink btn-round font-weight-medium px-3">{{ $tripSheets->count() }} Trips</button>
            <a href="{{ route('transport.trip-sheets.create') }}" class="btn btn-primary px-3">
                <i class="mdi mdi-plus-circle-outline mr-1"></i>Add Trip Sheet
            </a>
        </div>
        <div class="table-responsive">
            <table id="datatable" class="table table-bordered table-sm table-hover dt-responsive nowrap w-100">
                <thead class="thead-light"><tr>
                    <th class="text-center" style="width:45px">S.No</th>
                    <th>Trip No.</th><th>Date</th><th>Vehicle</th><th>Route</th>
                    <th class="text-right">Net Milk (L)</th><th class="text-right">Trip Amt</th>
                    <th class="text-center">Status</th><th class="text-center" style="width:80px">Action</th>
                </tr></thead>
                <tbody>
                    @foreach($tripSheets as $trip)
                    <tr>
                        <td class="text-center">{{ $loop->iteration }}</td>
                        <td class="font-weight-medium">{{ $trip->trip_number }}</td>
                        <td>{{ $trip->trip_date->format('d-m-Y') }}</td>
                        <td>{{ $trip->vehicle->vehicle_number ?? '—' }}</td>
                        <td>{{ $trip->route->name ?? '—' }}</td>
                        <td class="text-right">{{ number_format($trip->net_milk_litres, 2) }}</td>
                        <td class="text-right">₹ {{ number_format($trip->trip_amount, 2) }}</td>
                        <td class="text-center">
                            @php $sc = ['pending'=>'warning','completed'=>'success','cancelled'=>'danger'][$trip->status] ?? 'secondary'; @endphp
                            <span class="badge badge-soft-{{ $sc }}">{{ ucfirst($trip->status) }}</span>
                        </td>
                        <td class="text-center">
                            <a href="{{ route('transport.trip-sheets.show', $trip) }}" class="mr-1"><i class="dripicons-preview text-primary font-18"></i></a>
                            <a href="{{ route('transport.trip-sheets.edit', $trip) }}"><i class="dripicons-pencil text-warning font-18"></i></a>
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
<script>$(document).ready(function(){ $('#datatable').dataTable({"lengthMenu":[[25,50,100],[25,50,100]],"pageLength":25,"order":[[2,"desc"]]}); });</script>
@endpush
@section('footerScript')
<script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('plugins/datatables/dataTables.bootstrap4.min.js') }}"></script>
@stop
