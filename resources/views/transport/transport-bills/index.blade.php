@extends('app-layouts.admin-master')
@section('title', 'Transport Bills')
@section('headerStyle')
    <link href="{{ asset('plugins/datatables/dataTables.bootstrap4.min.css') }}" rel="stylesheet">
@stop
@section('content')
<div class="container-fluid">
    <div class="row"><div class="col-12">
        @component('app-components.breadcrumb-4')
            @slot('title') Transport Bills @endslot
            @slot('item1') Transport @endslot @slot('item2') Billing @endslot @slot('item3') Transport Bills @endslot
        @endcomponent
    </div></div>
    @if(session('success'))<div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="close" data-dismiss="alert"><span>&times;</span></button></div>@endif
    @if(session('error'))<div class="alert alert-danger alert-dismissible fade show">{{ session('error') }}<button type="button" class="close" data-dismiss="alert"><span>&times;</span></button></div>@endif

    {{-- Filters --}}
    <div class="card mb-3"><div class="card-body py-2">
        <form method="GET" action="{{ route('transport.transport-bills.index') }}" class="form-inline flex-wrap">
            <select name="vehicle_id" class="form-control form-control-sm mr-2 mb-1">
                <option value="">All Vehicles</option>
                @foreach($vehicles as $id => $num)<option value="{{ $id }}" @selected(request('vehicle_id')==$id)>{{ $num }}</option>@endforeach
            </select>
            <select name="payment_status" class="form-control form-control-sm mr-2 mb-1">
                <option value="">All Payment</option>
                <option value="unpaid"  @selected(request('payment_status')==='unpaid')>Unpaid</option>
                <option value="partial" @selected(request('payment_status')==='partial')>Partial</option>
                <option value="paid"    @selected(request('payment_status')==='paid')>Paid</option>
            </select>
            <select name="status" class="form-control form-control-sm mr-2 mb-1">
                <option value="">All Status</option>
                <option value="draft"    @selected(request('status')==='draft')>Draft</option>
                <option value="approved" @selected(request('status')==='approved')>Approved</option>
            </select>
            <input type="date" name="from_date" value="{{ request('from_date') }}" class="form-control form-control-sm mr-2 mb-1">
            <input type="date" name="to_date"   value="{{ request('to_date') }}"   class="form-control form-control-sm mr-2 mb-1">
            <button type="submit" class="btn btn-info btn-sm mb-1 mr-1">Filter</button>
            <a href="{{ route('transport.transport-bills.index') }}" class="btn btn-secondary btn-sm mb-1">Clear</a>
        </form>
    </div></div>

    {{-- Summary --}}
    <div class="row mb-3">
        <div class="col-6 col-md-3"><div class="card text-center py-2">
            <h5 class="mb-0 text-primary">₹ {{ number_format($summary->total_gross,2) }}</h5><small class="text-muted">Gross Amount</small>
        </div></div>
        <div class="col-6 col-md-3"><div class="card text-center py-2">
            <h5 class="mb-0 text-success">₹ {{ number_format($summary->total_net,2) }}</h5><small class="text-muted">Net Amount</small>
        </div></div>
        <div class="col-6 col-md-3"><div class="card text-center py-2">
            <h5 class="mb-0 text-info">₹ {{ number_format($summary->total_paid,2) }}</h5><small class="text-muted">Paid</small>
        </div></div>
        <div class="col-6 col-md-3"><div class="card text-center py-2">
            <h5 class="mb-0 text-danger">₹ {{ number_format($summary->total_balance,2) }}</h5><small class="text-muted">Balance</small>
        </div></div>
    </div>

    <div class="card"><div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <button class="btn btn-pink btn-round font-weight-medium px-3">{{ $bills->count() }} Bills</button>
            <a href="{{ route('transport.transport-bills.create') }}" class="btn btn-primary px-3">
                <i class="mdi mdi-plus-circle-outline mr-1"></i>Create Bill
            </a>
        </div>
        <div class="table-responsive">
            <table id="datatable" class="table table-bordered table-sm table-hover dt-responsive nowrap w-100">
                <thead class="thead-light"><tr>
                    <th class="text-center" style="width:45px">S.No</th>
                    <th>Bill No.</th><th>Date</th><th>Vehicle</th><th>Period</th>
                    <th class="text-right">Gross</th><th class="text-right">Net</th><th class="text-right">Balance</th>
                    <th class="text-center">Payment</th><th class="text-center">Status</th>
                    <th class="text-center" style="width:80px">Action</th>
                </tr></thead>
                <tbody>
                    @foreach($bills as $bill)
                    <tr>
                        <td class="text-center">{{ $loop->iteration }}</td>
                        <td class="font-weight-medium">{{ $bill->bill_number }}</td>
                        <td>{{ $bill->bill_date->format('d-m-Y') }}</td>
                        <td>{{ $bill->vehicle->vehicle_number ?? '—' }}</td>
                        <td>{{ $bill->bill_period_from->format('d-m') }} to {{ $bill->bill_period_to->format('d-m-Y') }}</td>
                        <td class="text-right">₹ {{ number_format($bill->gross_amount,2) }}</td>
                        <td class="text-right">₹ {{ number_format($bill->net_amount,2) }}</td>
                        <td class="text-right text-danger">₹ {{ number_format($bill->balance_amount,2) }}</td>
                        <td class="text-center">
                            @php $pc = ['unpaid'=>'danger','partial'=>'warning','paid'=>'success'][$bill->payment_status] ?? 'secondary'; @endphp
                            <span class="badge badge-soft-{{ $pc }}">{{ ucfirst($bill->payment_status) }}</span>
                        </td>
                        <td class="text-center">
                            @if($bill->status === 'approved')<span class="badge badge-soft-success">Approved</span>
                            @else<span class="badge badge-soft-warning">Draft</span>@endif
                        </td>
                        <td class="text-center">
                            <a href="{{ route('transport.transport-bills.show', $bill) }}" class="mr-1"><i class="dripicons-preview text-primary font-18"></i></a>
                            @if($bill->status !== 'approved')
                            <a href="{{ route('transport.transport-bills.edit', $bill) }}"><i class="dripicons-pencil text-warning font-18"></i></a>
                            @endif
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
<script>$(document).ready(function(){ $('#datatable').dataTable({"lengthMenu":[[25,50],[25,50]],"pageLength":25,"order":[[2,"desc"]]}); });</script>
@endpush
@section('footerScript')
<script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('plugins/datatables/dataTables.bootstrap4.min.js') }}"></script>
@stop
