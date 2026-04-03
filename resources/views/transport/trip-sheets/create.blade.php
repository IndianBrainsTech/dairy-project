@extends('app-layouts.admin-master')
@section('title', 'Add Trip Sheet')
@section('content')
<div class="container-fluid">
    <div class="row"><div class="col-12">
        @component('app-components.breadcrumb-4')
            @slot('title') Add Trip Sheet @endslot
            @slot('item1') Transport @endslot @slot('item2') Transactions @endslot @slot('item3') Trip Sheets @endslot
        @endcomponent
    </div></div>
    <div class="row"><div class="col-12 col-lg-10 mx-auto"><div class="card">
        @if($errors->any())<div class="alert alert-danger mb-0"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
        <div class="card-body">
            <form method="POST" action="{{ route('transport.trip-sheets.store') }}">@csrf
                <div class="row">
                    <div class="col-md-3"><div class="form-group"><label>Trip Number <small class="text-danger">*</small></label>
                        <input type="text" name="trip_number" value="{{ old('trip_number', $tripNumber) }}" class="form-control @error('trip_number') is-invalid @enderror">
                        @error('trip_number')<small class="text-danger">{{ $message }}</small>@enderror
                    </div></div>
                    <div class="col-md-3"><div class="form-group"><label>Trip Date <small class="text-danger">*</small></label>
                        <input type="date" name="trip_date" value="{{ old('trip_date', now()->format('Y-m-d')) }}" class="form-control @error('trip_date') is-invalid @enderror">
                        @error('trip_date')<small class="text-danger">{{ $message }}</small>@enderror
                    </div></div>
                    <div class="col-md-3"><div class="form-group"><label>Vehicle <small class="text-danger">*</small></label>
                        <select name="vehicle_id" class="form-control @error('vehicle_id') is-invalid @enderror">
                            <option value="">Select</option>
                            @foreach($vehicles as $id => $num)<option value="{{ $id }}" @selected(old('vehicle_id') == $id)>{{ $num }}</option>@endforeach
                        </select>
                        @error('vehicle_id')<small class="text-danger">{{ $message }}</small>@enderror
                    </div></div>
                    <div class="col-md-3"><div class="form-group"><label>Route <small class="text-danger">*</small></label>
                        <select name="route_id" class="form-control @error('route_id') is-invalid @enderror">
                            <option value="">Select</option>
                            @foreach($routes as $id => $name)<option value="{{ $id }}" @selected(old('route_id') == $id)>{{ $name }}</option>@endforeach
                        </select>
                        @error('route_id')<small class="text-danger">{{ $message }}</small>@enderror
                    </div></div>
                </div>
                <div class="row">
                    <div class="col-md-3"><div class="form-group"><label>Shift</label>
                        <input type="text" name="shift" value="{{ old('shift') }}" class="form-control" maxlength="50">
                    </div></div>
                    <div class="col-md-3"><div class="form-group"><label>Driver Name</label>
                        <input type="text" name="driver_name" value="{{ old('driver_name') }}" class="form-control" maxlength="100">
                    </div></div>
                    <div class="col-md-3"><div class="form-group"><label>Driver Phone</label>
                        <input type="text" name="driver_phone" value="{{ old('driver_phone') }}" class="form-control" maxlength="15">
                    </div></div>
                </div>
                <hr><h6 class="text-muted mb-3">Odometer</h6>
                <div class="row">
                    <div class="col-md-3"><div class="form-group"><label>Start KM</label>
                        <input type="number" name="odometer_start" value="{{ old('odometer_start') }}" class="form-control" min="0">
                    </div></div>
                    <div class="col-md-3"><div class="form-group"><label>End KM</label>
                        <input type="number" name="odometer_end" value="{{ old('odometer_end') }}" class="form-control" min="0">
                    </div></div>
                    <div class="col-md-3"><div class="form-group"><label>Departure Time</label>
                        <input type="time" name="departure_time" value="{{ old('departure_time') }}" class="form-control">
                    </div></div>
                    <div class="col-md-3"><div class="form-group"><label>Arrival Time</label>
                        <input type="time" name="arrival_time" value="{{ old('arrival_time') }}" class="form-control">
                    </div></div>
                </div>
                <hr><h6 class="text-muted mb-3">Milk Collection</h6>
                <div class="row">
                    <div class="col-md-3"><div class="form-group"><label>Collected Litres <small class="text-danger">*</small></label>
                        <input type="number" name="milk_collected_litres" step="0.01" value="{{ old('milk_collected_litres', 0) }}" class="form-control @error('milk_collected_litres') is-invalid @enderror" min="0">
                        @error('milk_collected_litres')<small class="text-danger">{{ $message }}</small>@enderror
                    </div></div>
                    <div class="col-md-3"><div class="form-group"><label>Rejected Litres</label>
                        <input type="number" name="milk_rejected_litres" step="0.01" value="{{ old('milk_rejected_litres', 0) }}" class="form-control" min="0">
                    </div></div>
                    <div class="col-md-3"><div class="form-group"><label>Payment Mode <small class="text-danger">*</small></label>
                        <select name="payment_mode" class="form-control @error('payment_mode') is-invalid @enderror" id="ddl-payment-mode">
                            <option value="flat_rate" @selected(old('payment_mode','flat_rate')==='flat_rate')>Flat Rate</option>
                            <option value="per_litre" @selected(old('payment_mode')==='per_litre')>Per Litre</option>
                        </select>
                        @error('payment_mode')<small class="text-danger">{{ $message }}</small>@enderror
                    </div></div>
                    <div class="col-md-3"><div class="form-group"><label>Rate</label>
                        <input type="number" name="flat_rate_amount" id="txt-flat-rate" step="0.01" value="{{ old('flat_rate_amount', 0) }}" class="form-control" min="0" placeholder="Flat rate amount">
                        <input type="number" name="rate_per_litre" id="txt-per-litre" step="0.01" value="{{ old('rate_per_litre', 0) }}" class="form-control d-none" min="0" placeholder="Rate per litre">
                    </div></div>
                </div>
                <hr><h6 class="text-muted mb-3">Diesel</h6>
                <div class="row">
                    <div class="col-md-3"><div class="form-group"><label>Diesel Consumed (L)</label>
                        <input type="number" name="diesel_consumed" step="0.01" value="{{ old('diesel_consumed') }}" class="form-control" min="0">
                    </div></div>
                    <div class="col-md-3"><div class="form-group"><label>Diesel Cost (₹)</label>
                        <input type="number" name="diesel_cost" step="0.01" value="{{ old('diesel_cost', 0) }}" class="form-control" min="0">
                    </div></div>
                    <div class="col-md-3"><div class="form-group"><label>Status <small class="text-danger">*</small></label>
                        <select name="status" class="form-control @error('status') is-invalid @enderror">
                            <option value="completed" @selected(old('status','completed')==='completed')>Completed</option>
                            <option value="pending"   @selected(old('status')==='pending')>Pending</option>
                            <option value="cancelled" @selected(old('status')==='cancelled')>Cancelled</option>
                        </select>
                        @error('status')<small class="text-danger">{{ $message }}</small>@enderror
                    </div></div>
                </div>
                <div class="form-group"><label>Remarks</label>
                    <textarea name="remarks" rows="2" class="form-control">{{ old('remarks') }}</textarea>
                </div>
                <hr><div class="d-flex justify-content-end">
                    <a href="{{ route('transport.trip-sheets.index') }}" class="btn btn-secondary px-3 mr-2">Cancel</a>
                    <button type="submit" class="btn btn-primary px-4">Save</button>
                </div>
            </form>
        </div>
    </div></div></div>
</div>
@stop
@push('custom-scripts')
<script>
$(document).ready(function(){
    $('#ddl-payment-mode').on('change', function(){
        if($(this).val() === 'per_litre'){
            $('#txt-flat-rate').addClass('d-none');
            $('#txt-per-litre').removeClass('d-none');
        } else {
            $('#txt-per-litre').addClass('d-none');
            $('#txt-flat-rate').removeClass('d-none');
        }
    }).trigger('change');
});
</script>
@endpush
