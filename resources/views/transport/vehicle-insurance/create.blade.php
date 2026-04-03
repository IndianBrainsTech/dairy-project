@extends('app-layouts.admin-master')
@section('title', 'Add Vehicle Insurance')
@section('content')
<div class="container-fluid">
    <div class="row"><div class="col-12">
        @component('app-components.breadcrumb-3')
            @slot('title') Add Insurance @endslot
            @slot('item1') Transport @endslot @slot('item2') Vehicle Insurance @endslot
        @endcomponent
    </div></div>
    <div class="row"><div class="col-12 col-lg-9 mx-auto"><div class="card">
        @if($errors->any())<div class="alert alert-danger mb-0"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
        <div class="card-body">
            <form method="POST" action="{{ route('transport.vehicle-insurance.store') }}" enctype="multipart/form-data">@csrf
                <div class="row">
                    <div class="col-md-4"><div class="form-group"><label>Vehicle <small class="text-danger">*</small></label>
                        <select name="vehicle_id" class="form-control @error('vehicle_id') is-invalid @enderror">
                            <option value="">Select</option>
                            @foreach($vehicles as $id => $num)<option value="{{ $id }}" @selected(old('vehicle_id')==$id)>{{ $num }}</option>@endforeach
                        </select>@error('vehicle_id')<small class="text-danger">{{ $message }}</small>@enderror
                    </div></div>
                    <div class="col-md-4"><div class="form-group"><label>Policy Number <small class="text-danger">*</small></label>
                        <input type="text" name="policy_number" value="{{ old('policy_number') }}" class="form-control @error('policy_number') is-invalid @enderror" maxlength="100">
                        @error('policy_number')<small class="text-danger">{{ $message }}</small>@enderror
                    </div></div>
                    <div class="col-md-4"><div class="form-group"><label>Insurance Company <small class="text-danger">*</small></label>
                        <input type="text" name="insurance_company" value="{{ old('insurance_company') }}" class="form-control @error('insurance_company') is-invalid @enderror" maxlength="150">
                        @error('insurance_company')<small class="text-danger">{{ $message }}</small>@enderror
                    </div></div>
                </div>
                <div class="row">
                    <div class="col-md-4"><div class="form-group"><label>Type <small class="text-danger">*</small></label>
                        <select name="insurance_type" class="form-control">
                            <option value="comprehensive" @selected(old('insurance_type','comprehensive')==='comprehensive')>Comprehensive</option>
                            <option value="third_party"  @selected(old('insurance_type')==='third_party')>Third Party</option>
                            <option value="fire_theft"   @selected(old('insurance_type')==='fire_theft')>Fire & Theft</option>
                        </select>
                    </div></div>
                    <div class="col-md-4"><div class="form-group"><label>Start Date <small class="text-danger">*</small></label>
                        <input type="date" name="start_date" value="{{ old('start_date') }}" class="form-control @error('start_date') is-invalid @enderror">
                        @error('start_date')<small class="text-danger">{{ $message }}</small>@enderror
                    </div></div>
                    <div class="col-md-4"><div class="form-group"><label>Expiry Date <small class="text-danger">*</small></label>
                        <input type="date" name="expiry_date" value="{{ old('expiry_date') }}" class="form-control @error('expiry_date') is-invalid @enderror">
                        @error('expiry_date')<small class="text-danger">{{ $message }}</small>@enderror
                    </div></div>
                </div>
                <div class="row">
                    <div class="col-md-4"><div class="form-group"><label>Premium Amount <small class="text-danger">*</small></label>
                        <input type="number" name="premium_amount" step="0.01" value="{{ old('premium_amount') }}" class="form-control @error('premium_amount') is-invalid @enderror" min="0">
                        @error('premium_amount')<small class="text-danger">{{ $message }}</small>@enderror
                    </div></div>
                    <div class="col-md-4"><div class="form-group"><label>Insured Value</label>
                        <input type="number" name="insured_value" step="0.01" value="{{ old('insured_value') }}" class="form-control" min="0">
                    </div></div>
                    <div class="col-md-4"><div class="form-group"><label>Status <small class="text-danger">*</small></label>
                        <select name="status" class="form-control">
                            <option value="active"    @selected(old('status','active')==='active')>Active</option>
                            <option value="expired"   @selected(old('status')==='expired')>Expired</option>
                            <option value="cancelled" @selected(old('status')==='cancelled')>Cancelled</option>
                        </select>
                    </div></div>
                </div>
                <div class="row">
                    <div class="col-md-4"><div class="form-group"><label>Agent Name</label>
                        <input type="text" name="agent_name" value="{{ old('agent_name') }}" class="form-control" maxlength="100">
                    </div></div>
                    <div class="col-md-4"><div class="form-group"><label>Agent Phone</label>
                        <input type="text" name="agent_phone" value="{{ old('agent_phone') }}" class="form-control" maxlength="15">
                    </div></div>
                    <div class="col-md-4"><div class="form-group"><label>Document (PDF/Image)</label>
                        <input type="file" name="document" class="form-control-file" accept=".pdf,.jpg,.jpeg,.png">
                    </div></div>
                </div>
                <div class="form-group"><label>Remarks</label>
                    <textarea name="remarks" rows="2" class="form-control">{{ old('remarks') }}</textarea>
                </div>
                <hr><div class="d-flex justify-content-end">
                    <a href="{{ route('transport.vehicle-insurance.index') }}" class="btn btn-secondary px-3 mr-2">Cancel</a>
                    <button type="submit" class="btn btn-primary px-4">Save</button>
                </div>
            </form>
        </div>
    </div></div></div>
</div>
@stop