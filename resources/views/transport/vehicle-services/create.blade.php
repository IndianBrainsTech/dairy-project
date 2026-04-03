@extends('app-layouts.admin-master')
@section('title', 'Add Service Record')
@section('content')
<div class="container-fluid">
    <div class="row"><div class="col-12">
        @component('app-components.breadcrumb-3')
            @slot('title') Add Service Record @endslot
            @slot('item1') Transport @endslot @slot('item2') Vehicle Services @endslot
        @endcomponent
    </div></div>
    <div class="row"><div class="col-12 col-lg-9 mx-auto"><div class="card">
        @if($errors->any())<div class="alert alert-danger mb-0"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
        <div class="card-body">
            <form method="POST" action="{{ route('transport.vehicle-services.store') }}" enctype="multipart/form-data">@csrf
                <div class="row">
                    <div class="col-md-3"><div class="form-group"><label>Service Number <small class="text-danger">*</small></label>
                        <input type="text" name="service_number" value="{{ old('service_number', $serviceNumber) }}" class="form-control @error('service_number') is-invalid @enderror">
                        @error('service_number')<small class="text-danger">{{ $message }}</small>@enderror
                    </div></div>
                    <div class="col-md-3"><div class="form-group"><label>Service Date <small class="text-danger">*</small></label>
                        <input type="date" name="service_date" value="{{ old('service_date', now()->format('Y-m-d')) }}" class="form-control @error('service_date') is-invalid @enderror">
                        @error('service_date')<small class="text-danger">{{ $message }}</small>@enderror
                    </div></div>
                    <div class="col-md-3"><div class="form-group"><label>Vehicle <small class="text-danger">*</small></label>
                        <select name="vehicle_id" class="form-control @error('vehicle_id') is-invalid @enderror">
                            <option value="">Select</option>
                            @foreach($vehicles as $id => $num)<option value="{{ $id }}" @selected(old('vehicle_id')==$id)>{{ $num }}</option>@endforeach
                        </select>@error('vehicle_id')<small class="text-danger">{{ $message }}</small>@enderror
                    </div></div>
                    <div class="col-md-3"><div class="form-group"><label>Service Type <small class="text-danger">*</small></label>
                        <select name="service_type" class="form-control @error('service_type') is-invalid @enderror">
                            @foreach(['routine','repair','breakdown','tyre','other'] as $st)
                                <option value="{{ $st }}" @selected(old('service_type','routine')===$st)>{{ ucfirst($st) }}</option>
                            @endforeach
                        </select>@error('service_type')<small class="text-danger">{{ $message }}</small>@enderror
                    </div></div>
                </div>
                <div class="row">
                    <div class="col-md-4"><div class="form-group"><label>Service Center</label>
                        <input type="text" name="service_center" value="{{ old('service_center') }}" class="form-control" maxlength="150">
                    </div></div>
                    <div class="col-md-4"><div class="form-group"><label>Odometer Reading</label>
                        <input type="number" name="odometer_reading" value="{{ old('odometer_reading') }}" class="form-control" min="0">
                    </div></div>
                    <div class="col-md-4"><div class="form-group"><label>Next Service Date</label>
                        <input type="date" name="next_service_date" value="{{ old('next_service_date') }}" class="form-control">
                    </div></div>
                </div>
                <hr><h6 class="text-muted mb-3">Cost Details</h6>
                <div class="row">
                    <div class="col-md-3"><div class="form-group"><label>Labour Cost <small class="text-danger">*</small></label>
                        <input type="number" name="labour_cost" step="0.01" value="{{ old('labour_cost', 0) }}" class="form-control @error('labour_cost') is-invalid @enderror" min="0">
                        @error('labour_cost')<small class="text-danger">{{ $message }}</small>@enderror
                    </div></div>
                    <div class="col-md-3"><div class="form-group"><label>Parts Cost <small class="text-danger">*</small></label>
                        <input type="number" name="parts_cost" step="0.01" value="{{ old('parts_cost', 0) }}" class="form-control @error('parts_cost') is-invalid @enderror" min="0">
                        @error('parts_cost')<small class="text-danger">{{ $message }}</small>@enderror
                    </div></div>
                    <div class="col-md-3"><div class="form-group"><label>Other Cost</label>
                        <input type="number" name="other_cost" step="0.01" value="{{ old('other_cost', 0) }}" class="form-control" min="0">
                    </div></div>
                    <div class="col-md-3"><div class="form-group"><label>Status <small class="text-danger">*</small></label>
                        <select name="status" class="form-control">
                            <option value="completed"  @selected(old('status','completed')==='completed')>Completed</option>
                            <option value="in_progress" @selected(old('status')==='in_progress')>In Progress</option>
                            <option value="scheduled"   @selected(old('status')==='scheduled')>Scheduled</option>
                        </select>
                    </div></div>
                </div>
                <div class="form-group"><label>Work Done</label>
                    <textarea name="work_done" rows="2" class="form-control">{{ old('work_done') }}</textarea>
                </div>
                <div class="form-group"><label>Document</label>
                    <input type="file" name="document" class="form-control-file" accept=".pdf,.jpg,.jpeg,.png">
                </div>
                <hr><div class="d-flex justify-content-end">
                    <a href="{{ route('transport.vehicle-services.index') }}" class="btn btn-secondary px-3 mr-2">Cancel</a>
                    <button type="submit" class="btn btn-primary px-4">Save</button>
                </div>
            </form>
        </div>
    </div></div></div>
</div>
@stop