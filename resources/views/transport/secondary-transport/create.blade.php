@extends('app-layouts.admin-master')
@section('title', 'Add Secondary Transport')
@section('content')
<div class="container-fluid">
    <div class="row"><div class="col-12">
        @component('app-components.breadcrumb-3')
            @slot('title') Add Secondary Transport @endslot
            @slot('item1') Transport @endslot
            @slot('item2') Add Secondary Transport @endslot
        @endcomponent
    </div></div>
    <div class="row"><div class="col-12 col-lg-8 mx-auto"><div class="card">
        @if($errors->any())<div class="alert alert-danger mb-0"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
        <div class="card-body">
            <form method="POST" action="{{ route('transport.secondary-transport.store') }}">@csrf 
                <div class="row">
                    <div class="col-md-3"><div class="form-group"><label>Reference Number <small class="text-danger">*</small></label>
                        <input type="text" name="reference_number" value="{{ old('reference_number', $referenceNumber) }}" class="form-control @error('reference_number') is-invalid @enderror">
                        @error('reference_number')<small class="text-danger">{{ $message }}</small>@enderror
                    </div></div>
                    <div class="col-md-3"><div class="form-group"><label>Transport Date <small class="text-danger">*</small></label>
                        <input type="date" name="transport_date" value="{{ old('transport_date', now()->format('Y-m-d')) }}" class="form-control @error('transport_date') is-invalid @enderror">
                        @error('transport_date')<small class="text-danger">{{ $message }}</small>@enderror
                    </div></div>
                    <div class="col-md-6"><div class="form-group"><label>Supplier Transporter <small class="text-danger">*</small></label>
                        <select name="supplier_transporter_id" class="form-control @error('supplier_transporter_id') is-invalid @enderror">
                            <option value="">Select</option>
                            @foreach($transporters as $id => $name)<option value="{{ $id }}" @selected(old('supplier_transporter_id')==$id)>{{ $name }}</option>@endforeach
                        </select>@error('supplier_transporter_id')<small class="text-danger">{{ $message }}</small>@enderror
                    </div></div>
                </div>
                <div class="row">
                    <div class="col-md-3"><div class="form-group"><label>Vehicle Number <small class="text-danger">*</small></label>
                        <input type="text" name="vehicle_number" value="{{ old('vehicle_number') }}" class="form-control @error('vehicle_number') is-invalid @enderror" maxlength="20" oninput="this.value=this.value.toUpperCase()">
                        @error('vehicle_number')<small class="text-danger">{{ $message }}</small>@enderror
                    </div></div>
                    <div class="col-md-3"><div class="form-group"><label>Vehicle Type</label>
                        <input type="text" name="vehicle_type" value="{{ old('vehicle_type') }}" class="form-control" maxlength="100">
                    </div></div>
                    <div class="col-md-3"><div class="form-group"><label>From Location</label>
                        <input type="text" name="from_location" value="{{ old('from_location') }}" class="form-control" maxlength="150">
                    </div></div>
                    <div class="col-md-3"><div class="form-group"><label>To Location</label>
                        <input type="text" name="to_location" value="{{ old('to_location') }}" class="form-control" maxlength="150">
                    </div></div>
                </div>
                <div class="row">
                    <div class="col-md-3"><div class="form-group"><label>Loaded Qty <small class="text-danger">*</small></label>
                        <input type="number" name="loaded_qty" step="0.01" value="{{ old('loaded_qty', 0) }}" class="form-control @error('loaded_qty') is-invalid @enderror" min="0">
                        @error('loaded_qty')<small class="text-danger">{{ $message }}</small>@enderror
                    </div></div>
                    <div class="col-md-3"><div class="form-group"><label>Rate Type <small class="text-danger">*</small></label>
                        <select name="rate_type" class="form-control @error('rate_type') is-invalid @enderror">
                            <option value="per_trip"  @selected(old('rate_type','per_trip')==='per_trip')>Per Trip</option>
                            <option value="per_km"    @selected(old('rate_type')==='per_km')>Per KM</option>
                            <option value="per_litre" @selected(old('rate_type')==='per_litre')>Per Litre</option>
                        </select>@error('rate_type')<small class="text-danger">{{ $message }}</small>@enderror
                    </div></div>
                    <div class="col-md-3"><div class="form-group"><label>Rate <small class="text-danger">*</small></label>
                        <input type="number" name="rate" step="0.01" value="{{ old('rate', 0) }}" class="form-control @error('rate') is-invalid @enderror" min="0">
                        @error('rate')<small class="text-danger">{{ $message }}</small>@enderror
                    </div></div>
                    <div class="col-md-3"><div class="form-group"><label>Other Charges</label>
                        <input type="number" name="other_charges" step="0.01" value="{{ old('other_charges', 0) }}" class="form-control" min="0">
                    </div></div>
                </div>
                <div class="form-group"><label>Remarks</label>
                    <textarea name="remarks" rows="2" class="form-control">{{ old('remarks') }}</textarea>
                </div>
                <hr><div class="d-flex justify-content-end">
                    <a href="{{ route('transport.secondary-transport.index') }}" class="btn btn-secondary px-3 mr-2">Cancel</a>
                    <button type="submit" class="btn btn-primary px-4">Save</button>
                </div>
            </form>
        </div>
    </div></div></div>
</div>
@stop