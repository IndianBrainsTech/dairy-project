@extends('app-layouts.admin-master')

@section('title', isset($vehicle) ? 'Edit Vehicle' : 'Add Vehicle')

@section('headerStyle')
    <link href="{{ asset('plugins/sweet-alert2/sweetalert2.min.css') }}" rel="stylesheet">
@stop

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            @component('app-components.breadcrumb-4')
                @slot('title') {{ isset($vehicle) ? 'Edit Vehicle' : 'Add Vehicle' }} @endslot
                @slot('item1') Masters @endslot
                @slot('item2') Transport @endslot
                @slot('item3') Vehicles @endslot
            @endcomponent
        </div>
    </div>

    <div class="row">
        <div class="col-12 col-lg-10 mx-auto">
            <div class="card">
                @if($errors->any())
                    <div class="alert alert-danger mb-0">
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                        </ul>
                    </div>
                @endif
                <div class="card-body">
                    <form method="POST"
                          action="{{ isset($vehicle)
                              ? route('transport.vehicles.update', $vehicle)
                              : route('transport.vehicles.store') }}">
                        @csrf
                        @if(isset($vehicle)) @method('PUT') @endif

                        {{-- Tab navigation --}}
                        <ul class="nav nav-tabs mb-0" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" data-toggle="tab" href="#tab-basic">Basic Info</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-toggle="tab" href="#tab-driver">Driver & RC</a>
                            </li>
                        </ul>

                        <div class="tab-content border border-top-0 p-3">

                            {{-- Tab 1: Basic Info --}}
                            <div class="tab-pane active" id="tab-basic">
                                <div class="row mt-2">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Vehicle Number <small class="text-danger">*</small></label>
                                            <input type="text" name="vehicle_number"
                                                   value="{{ old('vehicle_number', $vehicle->vehicle_number ?? '') }}"
                                                   class="form-control @error('vehicle_number') is-invalid @enderror"
                                                   maxlength="20" oninput="this.value=this.value.toUpperCase()">
                                            @error('vehicle_number')<small class="text-danger">{{ $message }}</small>@enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Vehicle Name</label>
                                            <input type="text" name="vehicle_name"
                                                   value="{{ old('vehicle_name', $vehicle->vehicle_name ?? '') }}"
                                                   class="form-control @error('vehicle_name') is-invalid @enderror"
                                                   maxlength="100">
                                            @error('vehicle_name')<small class="text-danger">{{ $message }}</small>@enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Category <small class="text-danger">*</small></label>
                                            <select name="vehicle_category_id" class="form-control @error('vehicle_category_id') is-invalid @enderror">
                                                <option value="">Select</option>
                                                @foreach($categories as $id => $name)
                                                    <option value="{{ $id }}" @selected(old('vehicle_category_id', $vehicle->vehicle_category_id ?? '') == $id)>{{ $name }}</option>
                                                @endforeach
                                            </select>
                                            @error('vehicle_category_id')<small class="text-danger">{{ $message }}</small>@enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Vehicle Type <small class="text-danger">*</small></label>
                                            <select name="vehicle_type" class="form-control @error('vehicle_type') is-invalid @enderror">
                                                <option value="">Select</option>
                                                @foreach(['Lorry','Truck','Van','Two Wheeler'] as $type)
                                                    <option value="{{ $type }}" @selected(old('vehicle_type', $vehicle->vehicle_type ?? '') === $type)>{{ $type }}</option>
                                                @endforeach
                                            </select>
                                            @error('vehicle_type')<small class="text-danger">{{ $message }}</small>@enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Make</label>
                                            <input type="text" name="make"
                                                   value="{{ old('make', $vehicle->make ?? '') }}"
                                                   class="form-control" maxlength="30">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Model</label>
                                            <input type="text" name="model"
                                                   value="{{ old('model', $vehicle->model ?? '') }}"
                                                   class="form-control" maxlength="30">
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Year of Manufacture</label>
                                            <input type="number" name="year_of_manufacture"
                                                   value="{{ old('year_of_manufacture', $vehicle->year_of_manufacture ?? '') }}"
                                                   class="form-control @error('year_of_manufacture') is-invalid @enderror"
                                                   min="1990" max="{{ now()->year }}">
                                            @error('year_of_manufacture')<small class="text-danger">{{ $message }}</small>@enderror
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Capacity (Litres)</label>
                                            <input type="number" name="capacity_litres" step="0.01"
                                                   value="{{ old('capacity_litres', $vehicle->capacity_litres ?? '') }}"
                                                   class="form-control" min="0">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Fuel Type <small class="text-danger">*</small></label>
                                            <select name="fuel_type" class="form-control @error('fuel_type') is-invalid @enderror">
                                                @foreach(['diesel','petrol','electric','cng'] as $fuel)
                                                    <option value="{{ $fuel }}" @selected(old('fuel_type', $vehicle->fuel_type ?? 'diesel') === $fuel)>{{ ucfirst($fuel) }}</option>
                                                @endforeach
                                            </select>
                                            @error('fuel_type')<small class="text-danger">{{ $message }}</small>@enderror
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Ownership <small class="text-danger">*</small></label>
                                            <select name="ownership_type" class="form-control @error('ownership_type') is-invalid @enderror">
                                                <option value="own"    @selected(old('ownership_type', $vehicle->ownership_type ?? 'own') === 'own')>Own</option>
                                                <option value="hired"  @selected(old('ownership_type', $vehicle->ownership_type ?? '') === 'hired')>Hired</option>
                                                <option value="leased" @selected(old('ownership_type', $vehicle->ownership_type ?? '') === 'leased')>Leased</option>
                                            </select>
                                            @error('ownership_type')<small class="text-danger">{{ $message }}</small>@enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Supplier / Transporter</label>
                                            <select name="supplier_transporter_id" class="form-control">
                                                <option value="">None</option>
                                                @foreach($transporters as $id => $name)
                                                    <option value="{{ $id }}" @selected(old('supplier_transporter_id', $vehicle->supplier_transporter_id ?? '') == $id)>{{ $name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Status <small class="text-danger">*</small></label>
                                            <select name="status" class="form-control @error('status') is-invalid @enderror">
                                                <option value="Active"   @selected(old('status', $vehicle->status ?? 'Active') === 'Active')>Active</option>
                                                <option value="Inactive" @selected(old('status', $vehicle->status ?? '') === 'Inactive')>Inactive</option>
                                            </select>
                                            @error('status')<small class="text-danger">{{ $message }}</small>@enderror
                                        </div>
                                    </div>
                                </div>

                                <hr>
                                <div class="d-flex justify-content-end">
                                    <button type="button" class="btn btn-info btn-sm px-3"
                                            onclick="$('#tab-driver').tab('show')">
                                        Next &gt;
                                    </button>
                                </div>
                            </div>

                            {{-- Tab 2: Driver & RC --}}
                            <div class="tab-pane" id="tab-driver">
                                <div class="row mt-2">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Driver Name</label>
                                            <input type="text" name="driver_name"
                                                   value="{{ old('driver_name', $vehicle->driver_name ?? '') }}"
                                                   class="form-control" maxlength="100">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Driver Phone</label>
                                            <input type="text" name="driver_phone"
                                                   value="{{ old('driver_phone', $vehicle->driver_phone ?? '') }}"
                                                   class="form-control" maxlength="15">
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>RC Number</label>
                                            <input type="text" name="rc_number"
                                                   value="{{ old('rc_number', $vehicle->rc_number ?? '') }}"
                                                   class="form-control" maxlength="50"
                                                   oninput="this.value=this.value.toUpperCase()">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>RC Expiry Date</label>
                                            <input type="date" name="rc_expiry_date"
                                                   value="{{ old('rc_expiry_date', isset($vehicle->rc_expiry_date) ? $vehicle->rc_expiry_date->format('Y-m-d') : '') }}"
                                                   class="form-control @error('rc_expiry_date') is-invalid @enderror">
                                            @error('rc_expiry_date')<small class="text-danger">{{ $message }}</small>@enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label>Remarks</label>
                                    <textarea name="remarks" rows="2" class="form-control">{{ old('remarks', $vehicle->remarks ?? '') }}</textarea>
                                </div>

                                <hr>
                                <div class="d-flex justify-content-between">
                                    <button type="button" class="btn btn-info btn-sm px-3"
                                            onclick="$('#tab-basic').tab('show')">&lt; Previous</button>
                                    <div>
                                        <a href="{{ route('transport.vehicles.index') }}" class="btn btn-secondary px-3 mr-2">Cancel</a>
                                        <button type="submit" class="btn btn-primary px-4">
                                            {{ isset($vehicle) ? 'Update' : 'Save' }}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@stop
