@extends('app-layouts.admin-master')
@section('title', 'Add Supplier Transporter')
@section('content')
<div class="container-fluid">
    <div class="row"><div class="col-12">
        @component('app-components.breadcrumb-4')
            @slot('title') Add Supplier Transporter @endslot
            @slot('item1') Masters @endslot @slot('item2') Transport @endslot @slot('item3') Supplier Transporters @endslot
        @endcomponent
    </div></div>
    <div class="row"><div class="col-12 col-lg-10 mx-auto"><div class="card">
        @if($errors->any())<div class="alert alert-danger mb-0"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
        <div class="card-body">
            <form method="POST" action="{{ route('transport.supplier-transporters.store') }}">@csrf
                <ul class="nav nav-tabs mb-0">
                    <li class="nav-item"><a class="nav-link active" data-toggle="tab" href="#tab-profile">Profile</a></li>
                    <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#tab-bank">Banking</a></li>
                </ul>
                <div class="tab-content border border-top-0 p-3">
                    <div class="tab-pane active" id="tab-profile">
                        <div class="row mt-2">
                            <div class="col-md-6">
                                <div class="form-group"><label>Name <small class="text-danger">*</small></label>
                                    <input type="text" name="name" value="{{ old('name') }}" class="form-control @error('name') is-invalid @enderror" maxlength="150">
                                    @error('name')<small class="text-danger">{{ $message }}</small>@enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group"><label>Contact Person</label>
                                    <input type="text" name="contact_person" value="{{ old('contact_person') }}" class="form-control" maxlength="100">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4"><div class="form-group"><label>Phone</label>
                                <input type="text" name="phone" value="{{ old('phone') }}" class="form-control" maxlength="15">
                            </div></div>
                            <div class="col-md-4"><div class="form-group"><label>Alt Phone</label>
                                <input type="text" name="alt_phone" value="{{ old('alt_phone') }}" class="form-control" maxlength="15">
                            </div></div>
                            <div class="col-md-4"><div class="form-group"><label>Email</label>
                                <input type="email" name="email" value="{{ old('email') }}" class="form-control" maxlength="100">
                            </div></div>
                        </div>
                        <div class="row">
                            <div class="col-md-8"><div class="form-group"><label>Address</label>
                                <textarea name="address" rows="2" class="form-control">{{ old('address') }}</textarea>
                            </div></div>
                            <div class="col-md-4"><div class="form-group"><label>City</label>
                                <input type="text" name="city" value="{{ old('city') }}" class="form-control" maxlength="100">
                            </div></div>
                        </div>
                        <div class="row">
                            <div class="col-md-4"><div class="form-group"><label>State</label>
                                <input type="text" name="state" value="{{ old('state') }}" class="form-control" maxlength="100">
                            </div></div>
                            <div class="col-md-2"><div class="form-group"><label>Pincode</label>
                                <input type="text" name="pincode" value="{{ old('pincode') }}" class="form-control" maxlength="10">
                            </div></div>
                            <div class="col-md-3"><div class="form-group"><label>GST Number</label>
                                <input type="text" name="gst_number" value="{{ old('gst_number') }}" class="form-control" maxlength="20" oninput="this.value=this.value.toUpperCase()">
                            </div></div>
                            <div class="col-md-3"><div class="form-group"><label>PAN Number</label>
                                <input type="text" name="pan_number" value="{{ old('pan_number') }}" class="form-control" maxlength="15" oninput="this.value=this.value.toUpperCase()">
                            </div></div>
                        </div>
                        <div class="row">
                            <div class="col-md-3"><div class="form-group"><label>Status <small class="text-danger">*</small></label>
                                <select name="status" class="form-control">
                                    <option value="active" @selected(old('status','active')==='active')>Active</option>
                                    <option value="inactive" @selected(old('status')==='inactive')>Inactive</option>
                                </select>
                            </div></div>
                        </div>
                        <div class="form-group"><label>Remarks</label>
                            <textarea name="remarks" rows="2" class="form-control">{{ old('remarks') }}</textarea>
                        </div>
                        <hr><div class="d-flex justify-content-end">
                            <button type="button" class="btn btn-info btn-sm px-3" onclick="$('#tab-bank').tab('show')">Next &gt;</button>
                        </div>
                    </div>
                    <div class="tab-pane" id="tab-bank">
                        <div class="row mt-2">
                            <div class="col-md-6"><div class="form-group"><label>Bank Name</label>
                                <input type="text" name="bank_name" value="{{ old('bank_name') }}" class="form-control" maxlength="100">
                            </div></div>
                            <div class="col-md-6"><div class="form-group"><label>Account Number</label>
                                <input type="text" name="bank_account_number" value="{{ old('bank_account_number') }}" class="form-control" maxlength="30">
                            </div></div>
                        </div>
                        <div class="row">
                            <div class="col-md-4"><div class="form-group"><label>IFSC Code</label>
                                <input type="text" name="bank_ifsc" value="{{ old('bank_ifsc') }}" class="form-control" maxlength="15" oninput="this.value=this.value.toUpperCase()">
                            </div></div>
                            <div class="col-md-8"><div class="form-group"><label>Branch</label>
                                <input type="text" name="bank_branch" value="{{ old('bank_branch') }}" class="form-control" maxlength="100">
                            </div></div>
                        </div>
                        <hr><div class="d-flex justify-content-between">
                            <button type="button" class="btn btn-info btn-sm px-3" onclick="$('#tab-profile').tab('show')">&lt; Previous</button>
                            <div>
                                <a href="{{ route('transport.supplier-transporters.index') }}" class="btn btn-secondary px-3 mr-2">Cancel</a>
                                <button type="submit" class="btn btn-primary px-4">Save</button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div></div></div>
</div>
@stop