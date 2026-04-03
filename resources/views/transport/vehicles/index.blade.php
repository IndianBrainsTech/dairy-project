@extends('app-layouts.admin-master')

@section('title', 'Vehicles')

@section('headerStyle')
    <link href="{{ asset('plugins/datatables/dataTables.bootstrap4.min.css') }}" rel="stylesheet" type="text/css" />
@stop

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            @component('app-components.breadcrumb-4')
                @slot('title') Vehicles @endslot
                @slot('item1') Masters @endslot
                @slot('item2') Transport @endslot
                @slot('item3') Vehicles @endslot
            @endcomponent
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    @endif

    {{-- Filters --}}
    <div class="row mb-3">
        <div class="col-12">
            <div class="card">
                <div class="card-body py-2">
                    <form method="GET" action="{{ route('transport.vehicles.index') }}" class="form-inline flex-wrap gap-2">
                        <div class="form-group mr-2 mb-1">
                            <select name="status" class="form-control form-control-sm">
                                <option value="">All Status</option>
                                <option value="Active"   @selected(request('status') === 'Active')>Active</option>
                                <option value="Inactive" @selected(request('status') === 'Inactive')>Inactive</option>
                            </select>
                        </div>
                        <div class="form-group mr-2 mb-1">
                            <select name="vehicle_category_id" class="form-control form-control-sm">
                                <option value="">All Categories</option>
                                @foreach($categories as $id => $name)
                                    <option value="{{ $id }}" @selected(request('vehicle_category_id') == $id)>{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group mr-2 mb-1">
                            <select name="ownership_type" class="form-control form-control-sm">
                                <option value="">All Ownership</option>
                                <option value="own"    @selected(request('ownership_type') === 'own')>Own</option>
                                <option value="hired"  @selected(request('ownership_type') === 'hired')>Hired</option>
                                <option value="leased" @selected(request('ownership_type') === 'leased')>Leased</option>
                            </select>
                        </div>
                        <div class="form-group mr-2 mb-1">
                            <input type="text" name="search" value="{{ request('search') }}"
                                   class="form-control form-control-sm" placeholder="Search vehicle / driver...">
                        </div>
                        <button type="submit" class="btn btn-info btn-sm mb-1 mr-1">Filter</button>
                        <a href="{{ route('transport.vehicles.index') }}" class="btn btn-secondary btn-sm mb-1">Clear</a>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <button type="button" class="btn btn-pink btn-round font-weight-medium px-3">
                            {{ $vehicles->count() }} {{ Str::plural('Vehicle', $vehicles->count()) }}
                        </button>
                        <a href="{{ route('transport.vehicles.create') }}" class="btn btn-primary px-3">
                            <i class="mdi mdi-plus-circle-outline mr-1"></i>Add Vehicle
                        </a>
                    </div>

                    <div class="table-responsive">
                        <table id="datatable" class="table table-bordered table-sm table-hover dt-responsive nowrap w-100">
                            <thead class="thead-light">
                                <tr>
                                    <th class="text-center" style="width:45px">S.No</th>
                                    <th>Vehicle No.</th>
                                    <th>Name</th>
                                    <th>Type</th>
                                    <th>Category</th>
                                    <th>Ownership</th>
                                    <th>Driver</th>
                                    <th class="text-center">RC Expiry</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-center" style="width:80px">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($vehicles as $vehicle)
                                <tr>
                                    <td class="text-center">{{ $loop->iteration }}</td>
                                    <td class="font-weight-medium">{{ $vehicle->vehicle_number }}</td>
                                    <td>{{ $vehicle->vehicle_name ?? '—' }}</td>
                                    <td>{{ $vehicle->vehicle_type }}</td>
                                    <td>{{ $vehicle->category->name ?? '—' }}</td>
                                    <td>{{ ucfirst($vehicle->ownership_type) }}</td>
                                    <td>{{ $vehicle->driver_name ?? '—' }}</td>
                                    <td class="text-center">
                                        @if($vehicle->rc_expiry_date)
                                            @php $status = $vehicle->rc_expiry_status; @endphp
                                            <span class="badge badge-soft-{{ $status === 'valid' ? 'success' : ($status === 'expiring_soon' ? 'warning' : 'danger') }}">
                                                {{ $vehicle->rc_expiry_date->format('d-m-Y') }}
                                            </span>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($vehicle->status === 'Active')
                                            <span class="badge badge-soft-success">Active</span>
                                        @else
                                            <span class="badge badge-soft-danger">Inactive</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <a href="{{ route('transport.vehicles.show', $vehicle) }}" class="mr-1">
                                            <i class="dripicons-preview text-primary font-18"></i>
                                        </a>
                                        <a href="{{ route('transport.vehicles.edit', $vehicle) }}">
                                            <i class="dripicons-pencil text-warning font-18"></i>
                                        </a>
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
        "pageLength": 25,
        "order": [[0, "asc"]]
    });
});
</script>
@endpush

@section('footerScript')
    <script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables/dataTables.bootstrap4.min.js') }}"></script>
@stop
