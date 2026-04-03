@extends('app-layouts.admin-master')
@section('title', 'Create Transport Bill')
@section('content')
<div class="container-fluid">
    <div class="row"><div class="col-12">
        @component('app-components.breadcrumb-4')
            @slot('title') Create Transport Bill @endslot
            @slot('item1') Transport @endslot @slot('item2') Billing @endslot @slot('item3') Transport Bills @endslot
        @endcomponent
    </div></div>
    <div class="row"><div class="col-12 col-lg-10 mx-auto"><div class="card">
        @if($errors->any())<div class="alert alert-danger mb-0"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
        <div class="card-body">
            <form method="POST" action="{{ route('transport.transport-bills.store') }}" id="bill-form">@csrf
                <div class="row">
                    <div class="col-md-3"><div class="form-group"><label>Bill Number <small class="text-danger">*</small></label>
                        <input type="text" name="bill_number" value="{{ old('bill_number', $billNumber) }}" class="form-control @error('bill_number') is-invalid @enderror">
                        @error('bill_number')<small class="text-danger">{{ $message }}</small>@enderror
                    </div></div>
                    <div class="col-md-3"><div class="form-group"><label>Bill Date <small class="text-danger">*</small></label>
                        <input type="date" name="bill_date" value="{{ old('bill_date', now()->format('Y-m-d')) }}" class="form-control @error('bill_date') is-invalid @enderror">
                        @error('bill_date')<small class="text-danger">{{ $message }}</small>@enderror
                    </div></div>
                    <div class="col-md-3"><div class="form-group"><label>Vehicle <small class="text-danger">*</small></label>
                        <select name="vehicle_id" id="ddl-vehicle" class="form-control @error('vehicle_id') is-invalid @enderror">
                            <option value="">Select</option>
                            @foreach($vehicles as $id => $num)<option value="{{ $id }}" @selected(old('vehicle_id')==$id)>{{ $num }}</option>@endforeach
                        </select>@error('vehicle_id')<small class="text-danger">{{ $message }}</small>@enderror
                    </div></div>
                    <div class="col-md-3"><div class="form-group"><label>Bill Type <small class="text-danger">*</small></label>
                        <select name="bill_type" class="form-control">
                            <option value="own"   @selected(old('bill_type','own')==='own')>Own Vehicle</option>
                            <option value="hired" @selected(old('bill_type')==='hired')>Hired Vehicle</option>
                        </select>
                    </div></div>
                </div>
                <div class="row">
                    <div class="col-md-3"><div class="form-group"><label>Period From <small class="text-danger">*</small></label>
                        <input type="date" name="bill_period_from" id="txt-period-from" value="{{ old('bill_period_from') }}" class="form-control @error('bill_period_from') is-invalid @enderror">
                        @error('bill_period_from')<small class="text-danger">{{ $message }}</small>@enderror
                    </div></div>
                    <div class="col-md-3"><div class="form-group"><label>Period To <small class="text-danger">*</small></label>
                        <input type="date" name="bill_period_to" id="txt-period-to" value="{{ old('bill_period_to') }}" class="form-control @error('bill_period_to') is-invalid @enderror">
                        @error('bill_period_to')<small class="text-danger">{{ $message }}</small>@enderror
                    </div></div>
                    <div class="col-md-3"><div class="form-group"><label>Supplier Transporter</label>
                        <select name="supplier_transporter_id" class="form-control">
                            <option value="">None</option>
                            @foreach($transporters as $id => $name)<option value="{{ $id }}" @selected(old('supplier_transporter_id')==$id)>{{ $name }}</option>@endforeach
                        </select>
                    </div></div>
                    <div class="col-md-3"><div class="form-group"><label>&nbsp;</label><br>
                        <button type="button" id="btn-load-trips" class="btn btn-info btn-block">
                            <i class="mdi mdi-magnify mr-1"></i>Load Unbilled Trips
                        </button>
                    </div></div>
                </div>

                {{-- Trip selection table --}}
                <div id="trips-section" class="d-none">
                    <hr><h6 class="text-muted">Select Trips to Include <small class="text-danger">*</small></h6>
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm" id="trips-table">
                            <thead class="thead-light"><tr>
                                <th style="width:40px"><input type="checkbox" id="chk-all"></th>
                                <th>Trip No.</th><th>Date</th><th>Route</th>
                                <th class="text-right">Net Milk</th><th class="text-right">Trip Amt</th><th class="text-right">Diesel</th>
                            </tr></thead>
                            <tbody id="trips-tbody"></tbody>
                        </table>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-md-3"><div class="form-group"><label>TDS %</label>
                        <input type="number" name="tds_percentage" step="0.01" value="{{ old('tds_percentage', 0) }}" class="form-control" min="0" max="100">
                    </div></div>
                    <div class="col-md-3"><div class="form-group"><label>Other Charges</label>
                        <input type="number" name="other_charges" step="0.01" value="{{ old('other_charges', 0) }}" class="form-control" min="0">
                    </div></div>
                    <div class="col-md-3"><div class="form-group"><label>Due Date</label>
                        <input type="date" name="due_date" value="{{ old('due_date') }}" class="form-control">
                    </div></div>
                </div>
                <div class="form-group"><label>Remarks</label>
                    <textarea name="remarks" rows="2" class="form-control">{{ old('remarks') }}</textarea>
                </div>
                <hr><div class="d-flex justify-content-end">
                    <a href="{{ route('transport.transport-bills.index') }}" class="btn btn-secondary px-3 mr-2">Cancel</a>
                    <button type="submit" class="btn btn-primary px-4">Create Bill</button>
                </div>
            </form>
        </div>
    </div></div></div>
</div>
@stop
@push('custom-scripts')
<script>
$(document).ready(function(){
    $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

    $('#btn-load-trips').on('click', function(){
        var vehicleId  = $('#ddl-vehicle').val();
        var periodFrom = $('#txt-period-from').val();
        var periodTo   = $('#txt-period-to').val();
        if(!vehicleId || !periodFrom || !periodTo){
            alert('Please select vehicle and period first.');
            return;
        }
        $.get('{{ route("transport.transport-bills.unbilled-trips") }}', {
            vehicle_id: vehicleId, from: periodFrom, to: periodTo
        }, function(trips){
            var tbody = $('#trips-tbody').empty();
            if(trips.length === 0){
                tbody.append('<tr><td colspan="7" class="text-center text-muted">No unbilled trips found for this period.</td></tr>');
            } else {
                $.each(trips, function(i, t){
                    tbody.append(
                        '<tr><td><input type="checkbox" name="trip_sheet_ids[]" value="'+t.id+'" checked></td>'+
                        '<td>'+t.trip_number+'</td>'+
                        '<td>'+t.trip_date+'</td>'+
                        '<td>'+(t.route ? t.route.name : '—')+'</td>'+
                        '<td class="text-right">'+parseFloat(t.net_milk_litres).toFixed(2)+'</td>'+
                        '<td class="text-right">₹ '+parseFloat(t.trip_amount).toFixed(2)+'</td>'+
                        '<td class="text-right">₹ '+parseFloat(t.diesel_cost).toFixed(2)+'</td>'+
                        '</tr>'
                    );
                });
            }
            $('#trips-section').removeClass('d-none');
        });
    });

    $('#chk-all').on('change', function(){
        $('input[name="trip_sheet_ids[]"]').prop('checked', $(this).is(':checked'));
    });
});
</script>
@endpush