@extends('app-layouts.admin-master')
@section('title', 'Add Vehicle Route Mapping')
@section('content')
<div class="container-fluid">
    <div class="row"><div class="col-12">
        @component('app-components.breadcrumb-3')
            @slot('title') Add Vehicle Route Mapping @endslot
            @slot('item1') Transport @endslot
            @slot('item2') Add Vehicle Route Mapping @endslot
        @endcomponent
    </div></div>
    <div class="row"><div class="col-12 col-lg-8 mx-auto"><div class="card">
        @if($errors->any())<div class="alert alert-danger mb-0"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
        <div class="card-body">
            <form method="POST" action="{{ route('transport.vehicle-route-mappings.store') }}">@csrf 
                <div class="row">
                    <div class="col-md-6"><div class="form-group"><label>Vehicle <small class="text-danger">*</small></label>
                        <select name="vehicle_id" class="form-control @error('vehicle_id') is-invalid @enderror">
                            <option value="">Select</option>
                            @foreach($vehicles as $id => $num)<option value="{{ $id }}" @selected(old('vehicle_id')==$id)>{{ $num }}</option>@endforeach
                        </select>@error('vehicle_id')<small class="text-danger">{{ $message }}</small>@enderror
                    </div></div>
                    <div class="col-md-6"><div class="form-group"><label>Route <small class="text-danger">*</small></label>
                        <select name="route_id" class="form-control @error('route_id') is-invalid @enderror">
                            <option value="">Select</option>
                            @foreach($routes as $id => $name)<option value="{{ $id }}" @selected(old('route_id')==$id)>{{ $name }}</option>@endforeach
                        </select>@error('route_id')<small class="text-danger">{{ $message }}</small>@enderror
                    </div></div>
                </div>
                <div class="row">
                    <div class="col-md-4"><div class="form-group"><label>Route Type <small class="text-danger">*</small></label>
                        <select name="route_type" class="form-control">
                            <option value="collection" @selected(old('route_type','collection')==='collection')>Collection</option>
                            <option value="marketing"  @selected(old('route_type')==='marketing')>Marketing</option>
                        </select>
                    </div></div>
                    <div class="col-md-4"><div class="form-group"><label>Shift</label>
                        <input type="text" name="shift" value="{{ old('shift') }}" class="form-control" maxlength="50">
                    </div></div>
                    <div class="col-md-4"><div class="form-group"><label>Status <small class="text-danger">*</small></label>
                        <select name="status" class="form-control">
                            <option value="active"   @selected(old('status','active')==='active')>Active</option>
                            <option value="inactive" @selected(old('status')==='inactive')>Inactive</option>
                        </select>
                    </div></div>
                </div>
                <div class="row">
                    <div class="col-md-3"><div class="form-group"><label>Distance (KM)</label>
                        <input type="number" name="distance_km" step="0.01" value="{{ old('distance_km') }}" class="form-control" min="0">
                    </div></div>
                    <div class="col-md-3"><div class="form-group"><label>Rate Per KM</label>
                        <input type="number" name="rate_per_km" step="0.01" value="{{ old('rate_per_km') }}" class="form-control" min="0">
                    </div></div>
                    <div class="col-md-3"><div class="form-group"><label>Effective From <small class="text-danger">*</small></label>
                        <input type="date" name="effective_from" value="{{ old('effective_from', now()->format('Y-m-d')) }}" class="form-control @error('effective_from') is-invalid @enderror">
                        @error('effective_from')<small class="text-danger">{{ $message }}</small>@enderror
                    </div></div>
                    <div class="col-md-3"><div class="form-group"><label>Effective To</label>
                        <input type="date" name="effective_to" value="{{ old('effective_to') }}" class="form-control">
                    </div></div>
                </div>
                <div class="form-group"><label>Remarks</label>
                    <textarea name="remarks" rows="2" class="form-control">{{ old('remarks') }}</textarea>
                </div>
                <hr><div class="d-flex justify-content-end">
                    <a href="{{ route('transport.vehicle-route-mappings.index') }}" class="btn btn-secondary px-3 mr-2">Cancel</a>
                    <button type="submit" class="btn btn-primary px-4">Save</button>
                </div>
            </form>
        </div>
    </div></div></div>
</div>
@stop