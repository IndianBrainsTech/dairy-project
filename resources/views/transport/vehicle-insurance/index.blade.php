@extends('app-layouts.admin-master')
@section('title', 'Vehicle Insurance')
@section('headerStyle')
    <link href="{{ asset('plugins/datatables/dataTables.bootstrap4.min.css') }}" rel="stylesheet">
@stop
@section('content')
<div class="container-fluid">
    <div class="row"><div class="col-12">
        @component('app-components.breadcrumb-4')
            @slot('title') Vehicle Insurance @endslot
            @slot('item1') Transport @endslot @slot('item2') Masters @endslot @slot('item3') Vehicle Insurance @endslot
        @endcomponent
    </div></div>
    @if(session('success'))<div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="close" data-dismiss="alert"><span>&times;</span></button></div>@endif
    @if(session('error'))<div class="alert alert-danger alert-dismissible fade show">{{ session('error') }}<button type="button" class="close" data-dismiss="alert"><span>&times;</span></button></div>@endif
    <div class="card"><div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <button class="btn btn-pink btn-round font-weight-medium px-3">Records</button>
            <a href="{{ route('transport.vehicle-insurance.create') }}" class="btn btn-primary px-3">
                <i class="mdi mdi-plus-circle-outline mr-1"></i>Add New
            </a>
        </div>
        <div class="table-responsive">
            <table id="datatable" class="table table-bordered table-sm table-hover dt-responsive nowrap w-100">
                <thead class="thead-light"><tr>
                    <th class="text-center" style="width:45px">S.No</th>
                    <th>Vehicle</th><th>Policy No.</th><th>Company</th><th>Type</th><th>Expiry</th><th class="text-center">Status</th>
                    <th class="text-center" style="width:80px">Action</th>
                </tr></thead>
                <tbody>
                    @foreach($insurances as $record)
                    <tr>
                        <td class="text-center">{{ $loop->iteration }}</td>
                        <td>{{ $record->vehicle->vehicle_number ?? '—' }}</td>
                        <td>{{ $record->policy_number }}</td>
                        <td>{{ $record->insurance_company }}</td>
                        <td>{{ ucfirst(str_replace('_',' ',$record->insurance_type)) }}</td>
                        <td>
                            @php $es = $record->expiry_status; @endphp
                            <span class="badge badge-soft-{{ $es === 'valid' ? 'success' : ($es === 'expiring_soon' ? 'warning' : 'danger') }}">
                                {{ $record->expiry_date->format('d-m-Y') }}
                            </span>
                        </td>
                        <td class="text-center">
                            @if($record->status === 'active')<span class="badge badge-soft-success">Active</span>
                            @else<span class="badge badge-soft-danger">{{ ucfirst($record->status) }}</span>@endif
                        </td>
                        <td class="text-center">
                            <a href="{{ route('transport.vehicle-insurance.show', $record) }}" class="mr-1"><i class="dripicons-preview text-primary font-18"></i></a>
                            <a href="{{ route('transport.vehicle-insurance.edit', $record) }}"><i class="dripicons-pencil text-warning font-18"></i></a>
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