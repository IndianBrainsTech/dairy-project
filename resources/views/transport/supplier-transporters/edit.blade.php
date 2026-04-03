@extends('app-layouts.admin-master')
@section('title', 'Edit Supplier Transporter')
@section('content')
<div class="container-fluid">
    <div class="row"><div class="col-12">
        @component('app-components.breadcrumb-3')
            @slot('title') Edit Supplier Transporter @endslot
            @slot('item1') Transport @endslot @slot('item2') Supplier Transporters @endslot
        @endcomponent
    </div></div>
    <div class="row"><div class="col-12 col-lg-10 mx-auto"><div class="card">
        @if($errors->any())<div class="alert alert-danger mb-0"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
        <div class="card-body">
            <form method="POST" action="{{ route('transport.supplier-transporters.update', $supplierTransporter) }}">@csrf @method('PUT')
                <div class="row">
                    <div class="col-md-6"><div class="form-group"><label>Name <small class="text-danger">*</small></label>
                        <input type="text" name="name" value="{{ old('name', $supplierTransporter->name) }}" class="form-control @error('name') is-invalid @enderror" maxlength="150">
                        @error('name')<small class="text-danger">{{ $message }}</small>@enderror
                    </div></div>
                    <div class="col-md-6"><div class="form-group"><label>Contact Person</label>
                        <input type="text" name="contact_person" value="{{ old('contact_person', $supplierTransporter->contact_person) }}" class="form-control" maxlength="100">
                    </div></div>
                </div>
                <div class="row">
                    <div class="col-md-4"><div class="form-group"><label>Phone</label>
                        <input type="text" name="phone" value="{{ old('phone', $supplierTransporter->phone) }}" class="form-control" maxlength="15">
                    </div></div>
                    <div class="col-md-4"><div class="form-group"><label>Email</label>
                        <input type="email" name="email" value="{{ old('email', $supplierTransporter->email) }}" class="form-control" maxlength="100">
                    </div></div>
                    <div class="col-md-4"><div class="form-group"><label>GST Number</label>
                        <input type="text" name="gst_number" value="{{ old('gst_number', $supplierTransporter->gst_number) }}" class="form-control" maxlength="20" oninput="this.value=this.value.toUpperCase()">
                    </div></div>
                </div>
                <div class="row">
                    <div class="col-md-8"><div class="form-group"><label>Address</label>
                        <textarea name="address" rows="2" class="form-control">{{ old('address', $supplierTransporter->address) }}</textarea>
                    </div></div>
                    <div class="col-md-4"><div class="form-group"><label>Status <small class="text-danger">*</small></label>
                        <select name="status" class="form-control">
                            <option value="active"   @selected(old('status', $supplierTransporter->status)==='active')>Active</option>
                            <option value="inactive" @selected(old('status', $supplierTransporter->status)==='inactive')>Inactive</option>
                        </select>
                    </div></div>
                </div>
                <div class="form-group"><label>Remarks</label>
                    <textarea name="remarks" rows="2" class="form-control">{{ old('remarks', $supplierTransporter->remarks) }}</textarea>
                </div>
                <hr><div class="d-flex justify-content-end">
                    <a href="{{ route('transport.supplier-transporters.index') }}" class="btn btn-secondary px-3 mr-2">Cancel</a>
                    <button type="submit" class="btn btn-primary px-4">Update</button>
                </div>
            </form>
        </div>
    </div></div></div>
</div>
@stop