@extends('app-layouts.admin-master')
@section('title', 'Add Market Trip Sheet')
@section('content')
<div class="container-fluid">
    <div class="row"><div class="col-12">
        @component('app-components.breadcrumb-3')
            @slot('title') Add Market Trip Sheet @endslot
            @slot('item1') Transport @endslot
            @slot('item2') Add Market Trip Sheet @endslot
        @endcomponent
    </div></div>
    <div class="row"><div class="col-12 col-lg-8 mx-auto"><div class="card">
        @if($errors->any())<div class="alert alert-danger mb-0"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
        <div class="card-body">
            <form method="POST" action="{{ route('transport.trip-sheets-market.store') }}">@csrf 
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
                            @foreach($vehicles as $id => $num)<option value="{{ $id }}" @selected(old('vehicle_id')==$id)>{{ $num }}</option>@endforeach
                        </select>@error('vehicle_id')<small class="text-danger">{{ $message }}</small>@enderror
                    </div></div>
                    <div class="col-md-3"><div class="form-group"><label>Route <small class="text-danger">*</small></label>
                        <select name="route_id" class="form-control @error('route_id') is-invalid @enderror">
                            <option value="">Select</option>
                            @foreach($routes as $id => $name)<option value="{{ $id }}" @selected(old('route_id')==$id)>{{ $name }}</option>@endforeach
                        </select>@error('route_id')<small class="text-danger">{{ $message }}</small>@enderror
                    </div></div>
                </div>
                <div class="row">
                    <div class="col-md-3"><div class="form-group"><label>Driver Name</label>
                        <input type="text" name="driver_name" value="{{ old('driver_name') }}" class="form-control" maxlength="100">
                    </div></div>
                    <div class="col-md-3"><div class="form-group"><label>Product Type</label>
                        <input type="text" name="product_type" value="{{ old('product_type') }}" class="form-control" maxlength="100">
                    </div></div>
                    <div class="col-md-3"><div class="form-group"><label>Loaded Qty <small class="text-danger">*</small></label>
                        <input type="number" name="loaded_qty" step="0.01" value="{{ old('loaded_qty', 0) }}" class="form-control @error('loaded_qty') is-invalid @enderror" min="0">
                        @error('loaded_qty')<small class="text-danger">{{ $message }}</small>@enderror
                    </div></div>
                    <div class="col-md-3"><div class="form-group"><label>Delivered Qty <small class="text-danger">*</small></label>
                        <input type="number" name="delivered_qty" step="0.01" value="{{ old('delivered_qty', 0) }}" class="form-control @error('delivered_qty') is-invalid @enderror" min="0">
                        @error('delivered_qty')<small class="text-danger">{{ $message }}</small>@enderror
                    </div></div>
                </div>
                <div class="row">
                    <div class="col-md-3"><div class="form-group"><label>Returned Qty</label>
                        <input type="number" name="returned_qty" step="0.01" value="{{ old('returned_qty', 0) }}" class="form-control" min="0">
                    </div></div>
                    <div class="col-md-3"><div class="form-group"><label>Trip Amount <small class="text-danger">*</small></label>
                        <input type="number" name="trip_amount" step="0.01" value="{{ old('trip_amount', 0) }}" class="form-control @error('trip_amount') is-invalid @enderror" min="0">
                        @error('trip_amount')<small class="text-danger">{{ $message }}</small>@enderror
                    </div></div>
                    <div class="col-md-3"><div class="form-group"><label>Diesel Cost</label>
                        <input type="number" name="diesel_cost" step="0.01" value="{{ old('diesel_cost', 0) }}" class="form-control" min="0">
                    </div></div>
                    <div class="col-md-3"><div class="form-group"><label>Status <small class="text-danger">*</small></label>
                        <select name="status" class="form-control @error('status') is-invalid @enderror">
                            <option value="completed" @selected(old('status','completed')==='completed')>Completed</option>
                            <option value="pending"   @selected(old('status')==='pending')>Pending</option>
                            <option value="cancelled" @selected(old('status')==='cancelled')>Cancelled</option>
                        </select>@error('status')<small class="text-danger">{{ $message }}</small>@enderror
                    </div></div>
                </div>
                <div class="form-group"><label>Remarks</label>
                    <textarea name="remarks" rows="2" class="form-control">{{ old('remarks') }}</textarea>
                </div>
                <hr><div class="d-flex justify-content-end">
                    <a href="{{ route('transport.trip-sheets-market.index') }}" class="btn btn-secondary px-3 mr-2">Cancel</a>
                    <button type="submit" class="btn btn-primary px-4">Save</button>
                </div>
            </form>
        </div>
    </div></div></div>
</div>
@stop