@extends('app-layouts.admin-master')

@section('title', 'Customer')

@section('headerStyle')
    <link href="{{ asset('plugins/dropify/css/dropify.min.css') }}" rel="stylesheet">
    <link href="{{ asset('plugins/sweet-alert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('plugins/animate/animate.css') }}" rel="stylesheet" type="text/css">
@stop

@php
    if(!isset($customer) && !isset($conversion)) {
        $isEdit = false;
        $title = "Add Customer";
        $action = route('customers.store');
        $id = $name = $code = $group = "";
        $route_id = $area_id = "";
        $address = $district = $state = "";
        $landmark = $pincode = "";
        $contact_num = $contact_name = "";
        $alternate_num = $alternate_name = "";
        $email = $staff_id = $remarks = "";
        $billing_name = $credit_limit = "";
        $gst_type = $gst_number = $pan_number = "";
        $outstanding = $incentive_mode = $payment_mode = "";
        $tcs_status = $tds_status = "";
        $link_customer = $link_cust_id = $customer_since = "";
        $owner_name = $gender = $dob = $aadhaar = "";
        $bank_name = $branch = $ifsc = "";
        $acc_holder = $acc_number = "";
        $enq_id = "";        
    }
    else if(isset($conversion)) {
        $isEdit = false;
        $title = "Enquiry to Customer";
        $action = route('customers.store');
        $id = "";        
        $name = $conversion['customer_name'];
        $code = $conversion['customer_code'];
        $group = $conversion['group'];
        $route_id = $conversion['route_id'];
        $area_id = $conversion['area_id'];
        $address = $conversion['address_lines'];
        $district = $state = "";
        $landmark = $conversion['landmark'];
        $pincode = "";
        $contact_num = $conversion['contact_num'];
        $contact_name = $conversion['contact_name'];
        $alternate_num = $conversion['alternate_num'];
        $alternate_name = $conversion['alternate_name'];
        $email = "";
        $staff_id = $conversion['emp_id'];
        $remarks = "";
        $billing_name = $conversion['customer_name'];
        $credit_limit = "";
        $gst_type = $gst_number = $pan_number = "";
        $outstanding = $incentive_mode = $payment_mode = "";
        $tcs_status = $tds_status = "";
        $link_customer = $link_cust_id = "";
        $customer_since = $conversion['customer_since'];
        $owner_name = $gender = $dob = $aadhaar = "";
        $bank_name = $branch = $ifsc = "";
        $acc_holder = $acc_number = "";
        $enq_id = $conversion['enquiry_id'];        
    }
    else {
        $isEdit = true;
        $title = "Edit Customer";
        $action = route('customers.update', ['id' => $customer->id]);
        $id = $customer->id;
        $name = $customer->customer_name;
        $code = $customer->customer_code;
        $group = $customer->group;
        $route_id = $customer->route_id;
        $area_id = $customer->area_id;
        $address = $customer->address_lines;
        $district = $customer->district;
        $state = $customer->state;
        $landmark = $customer->landmark;
        $pincode = $customer->pincode;
        $contact_num = $customer->contact_num;
        $contact_name = $customer->contact_name;
        $alternate_num = $customer->alternate_num;
        $alternate_name = $customer->alternate_name;
        $email = $customer->email_id;
        $staff_id = $customer->staff_id;
        $remarks = $customer->remarks;
        $billing_name = $customer->billing_name;
        $credit_limit = $customer->credit_limit;
        $gst_type = $customer->gst_type;
        $gst_number = $customer->gst_number;
        $pan_number = $customer->pan_number;
        $outstanding = $customer->outstanding;
        $incentive_mode = $customer->incentive_mode;
        $payment_mode = $customer->payment_mode;
        $tcs_status = $customer->tcs_status;
        $tds_status = $customer->tds_status;
        $link_customer = $customer->link_customer;
        $link_cust_id = $customer->link_cust_id;
        $customer_since = $customer->customer_since;
        $owner_name = $customer->owner_name;
        $gender = $customer->gender;
        $dob = $customer->dob;
        $aadhaar = $customer->aadhaar;
        $bank_name = $customer->bank_name;
        $branch = $customer->branch;
        $ifsc = $customer->ifsc;
        $acc_holder = $customer->acc_holder;
        $acc_number = $customer->acc_number;
        $enq_id = "";
    }
    $isSuccess = false;
    $msg = "";
@endphp

@section('content')
    <div class="container-fluid">
        <!-- Page-Title -->
        <div class="row">
            <div class="col-sm-12">                
                @component('app-components.breadcrumb-4')
                    @slot('title') {{$title}} @endslot
                    @slot('item1') Masters @endslot
                    @slot('item2') Profiles @endslot
                    @slot('item3') Customers @endslot
                @endcomponent
            </div><!--end col-->
        </div>
        <!-- end page title end breadcrumb -->
        
        <div class="row">
            <div class="col-lg-8 mx-auto">
                <div class="card">                

                    @if(Session::has('success'))
                        <div class="alert alert-success">
                            @php
                                $isSuccess = true;
                                $msg = Session::get('success');                                
                                echo $msg;
                            @endphp
                        </div>
                    @elseif(Session::has('error'))
                        <div class="alert alert-danger">
                            @php
                                $isSuccess = false;
                                $msg = Session::get('error');
                                if(str_contains($msg,'customer_name_unique')) {
                                    $msg = "Customer name already exists. Please try with different name...";
                                }
                                else if(str_contains($msg,'customer_code_unique')) {
                                    $msg = "Customer ID already exists. Please try with different id...";
                                }
                                echo $msg;
                            @endphp
                        </div>
                    @endif

                    <div class="card-body">                    
                        <form class="mb-0" method="post" action="{{ $action }}" enctype="multipart/form-data">
                            @csrf
                            <div class="row">
                                <div class="col-md-12 col-lg-12">
                                      
                                    <div class="row">
                                        @if ($errors->any())
                                            <div class="alert alert-danger" style="margin-left: 16px;">
                                                <ul>
                                                    @foreach ($errors->all() as $error)
                                                        <li>{{ $error }}</li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                            <hr/>
                                        @endif                                        
                                    </div>
                                                    
                                    <div class="card">
                                        <div class="card-body">                                            
            
                                            <!-- Nav tabs --> 
                                            <ul class="nav nav-tabs" role="tablist">
                                                <li class="nav-item">
                                                    <a class="nav-link active" id="tab_home" data-toggle="tab" href="#home" role="tab">Home</a>
                                                </li>
                                                <li class="nav-item">
                                                    <a class="nav-link" id="tab_settings" data-toggle="tab" href="#settings" role="tab">Settings</a>
                                                </li>
                                                <li class="nav-item">
                                                    <a class="nav-link" id="tab_profile" data-toggle="tab" href="#profile" role="tab">Profile</a>
                                                </li>
                                                <li class="nav-item">
                                                    <a class="nav-link" id="tab_account" data-toggle="tab" href="#account" role="tab">Account</a>
                                                </li>
                                            </ul>
          
                                            <!-- Tab panes -->
                                            <div class="tab-content">
                                                <div class="tab-pane active p-3" id="home" role="tabpanel">
                                                    <div class="row" style="margin-top:16px">
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label>Customer Name <small class="text-danger font-13">*</small></label>
                                                                <input type="text" name="customer_name" id="customer_name" value="{{ old('customer_name',$name) }}" class="form-control @error('customer_name') is-invalid @enderror">
                                                                @error('customer_name')
                                                                    <div class="alert alert-danger">{{ $message }}</div>
                                                                @enderror
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label>Customer ID <small class="text-danger font-13">*</small></label>
                                                                <input type="text" name="customer_code" id="customer_code" value="{{ old('customer_code',$code) }}" class="form-control @error('customer_code') is-invalid @enderror">
                                                                @error('customer_code')
                                                                    <div class="alert alert-danger">{{ $message }}</div>
                                                                @enderror
                                                            </div>
                                                        </div>                                                        
                                                    </div><!--end row-->
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label>Group <small class="text-danger font-13">*</small></label>
                                                                <select name="customer_group" class="form-control @error('customer_group') is-invalid @enderror">
                                                                    <option value="">Select</option>
                                                                    <option value="Retailer" @selected(old('customer_group',$group) == 'Retailer')>Retailer</option>
                                                                    <option value="Distributor" @selected(old('customer_group',$group) == 'Distributor')>Distributor</option>
                                                                    <option value="Outlet" @selected(old('customer_group',$group) == 'Outlet')>Outlet</option>
                                                                    <option value="Company" @selected(old('customer_group',$group) == 'Company')>Company</option>
                                                                    <option value="Function" @selected(old('customer_group',$group) == 'Function')>Function</option>
                                                                </select>
                                                                @error('customer_group')
                                                                    <div class="alert alert-danger">{{ $message }}</div>
                                                                @enderror
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label>Route <small class="text-danger font-13">*</small></label>
                                                                <select name="route" id="route" class="form-control @error('route') is-invalid @enderror">
                                                                    <option value="">Select</option>
                                                                    @foreach($routes as $route)
                                                                        <option value="{{ $route->id }}" @selected(old('route',$route_id) == $route->id)>{{ $route->name }}</option>
                                                                    @endforeach
                                                                </select>
                                                                @error('route')
                                                                    <div class="alert alert-danger">{{ $message }}</div>
                                                                @enderror
                                                            </div>
                                                        </div>                                                        
                                                    </div><!--end row-->
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label>Area <small class="text-danger font-13">*</small></label>
                                                                <select name="area" id="area" class="form-control @error('area') is-invalid @enderror">
                                                                    <option value="">Select</option>                                                                
                                                                </select>
                                                                @error('area')
                                                                    <div class="alert alert-danger">{{ $message }}</div>
                                                                @enderror
                                                            </div>
                                                            <div class="form-group">
                                                                <label>District - State</label>
                                                                <input type="text" id="district-state" class="form-control" readonly>
                                                                <input type="hidden" name="district" id="district" class="form-control" readonly>
                                                                <input type="hidden" name="state" id="state" class="form-control" readonly>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label>Address <small class="text-danger font-13">*</small></label>
                                                                <textarea name="address" rows="4" placeholder="Address Lines.." class="form-control @error('address') is-invalid @enderror" style="min-height:125px">{{ old('address',$address) }}</textarea>
                                                                @error('address')
                                                                    <div class="alert alert-danger">{{ $message }}</div>
                                                                @enderror
                                                            </div>
                                                        </div>
                                                    </div><!--end row-->
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label>Landmark</label>
                                                                <input type="text" name="landmark" value="{{ old('landmark',$landmark) }}" class="form-control">                                                                
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label>Pin Code</label>
                                                                <input type="number" name="pincode" value="{{ old('pincode',$pincode) }}" class="form-control @error('pincode') is-invalid @enderror">
                                                                @error('pincode')
                                                                    <div class="alert alert-danger">{{ $message }}</div>
                                                                @enderror
                                                            </div>
                                                        </div>
                                                    </div><!--end row-->
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label>Contact Number <small class="text-danger font-13">*</small></label>
                                                                <div class="input-group">
                                                                    <div class="input-group-prepend">
                                                                        <span class="input-group-text"><i class="dripicons-phone"></i></span>
                                                                    </div>
                                                                    <input type="number" name="contact_number" value="{{ old('contact_number',$contact_num) }}" class="form-control @error('contact_number') is-invalid @enderror">
                                                                    @error('contact_number')
                                                                        <div class="alert alert-danger">{{ $message }}</div>
                                                                    @enderror
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label>Name</label>
                                                                <input type="text" name="contact_name" value="{{ old('contact_name',$contact_name) }}" class="form-control">                                                                
                                                            </div>
                                                        </div>
                                                    </div><!--end row-->
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label>Alternate Number</label>
                                                                <div class="input-group">
                                                                    <div class="input-group-prepend">
                                                                        <span class="input-group-text"><i class="dripicons-phone"></i></span>
                                                                    </div>
                                                                    <input type="number" name="alternate_number" value="{{ old('alternate_number',$alternate_num) }}" class="form-control @error('alternate_number') is-invalid @enderror">
                                                                    @error('alternate_number')
                                                                        <div class="alert alert-danger">{{ $message }}</div>
                                                                    @enderror
                                                                </div>                                                            
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label>Name</label>
                                                                <input type="text" name="alternate_name" value="{{ old('alternate_name',$alternate_name) }}" class="form-control">                                                                
                                                            </div>
                                                        </div>
                                                    </div><!--end row-->
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label>Email ID</label>
                                                                <div class="input-group">
                                                                    <div class="input-group-prepend">
                                                                        <span class="input-group-text"><i class="mdi mdi-email"></i></span>
                                                                    </div>
                                                                    <input type="text" name="email" value="{{ old('email',$email) }}" class="form-control @error('email') is-invalid @enderror">
                                                                    @error('email')
                                                                        <div class="alert alert-danger">{{ $message }}</div>
                                                                    @enderror
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label>Staff Incharge <small class="text-danger font-13">*</small></label>
                                                                <select name="staff_id" class="form-control @error('staff') is-invalid @enderror">
                                                                    <option value="">Select</option>
                                                                    <option value="0" @selected(old('staff_id',$staff_id) == '0')>Admin</option>
                                                                    @foreach($staffs as $staff)
                                                                        <option value="{{ $staff->emp_id }}" @selected(old('staff_id',$staff_id) == $staff->emp_id)>                                                                        
                                                                            <!-- {{ $staff->emp_name }} [{{ $staff->emp_id }}] -->
                                                                            {{ $staff->emp_name }}
                                                                            @if($staff->short_name)
                                                                                ({{ $staff->short_name }})
                                                                            @else
                                                                                ({{ $staff->full_name }})
                                                                            @endif
                                                                        </option>
                                                                    @endforeach  
                                                                </select>
                                                                @error('staff')
                                                                    <div class="alert alert-danger">{{ $message }}</div>
                                                                @enderror
                                                            </div>
                                                        </div>
                                                    </div><!--end row-->
                                                    <div class="row">
                                                        <div class="col-md-8">
                                                            <div class="form-group">
                                                                <label>Remarks</label>
                                                                <textarea name="remarks" rows="3" placeholder="Comments about customer" class="form-control">{{ old('remarks',$remarks) }}</textarea>
                                                            </div>
                                                        </div>
                                                    </div><!--end row-->
                                                    <hr/>
                                                    <input type="button" value="Next" class="btn btn-gradient-primary btn-sm text-light px-4 mt-3 float-right mb-0" onclick="document.getElementById('tab_settings').click()" />
                                                </div>

                                                <div class="tab-pane p-3" id="settings" role="tabpanel">
                                                    <div class="row" style="margin-top:16px">
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label>Billing Name <small class="text-danger font-13">*</small></label>
                                                                <input type="text" name="billing_name" id="billing_name" value="{{ old('billing_name',$billing_name) }}" class="form-control @error('billing_name') is-invalid @enderror">
                                                                @error('billing_name')
                                                                    <div class="alert alert-danger">{{ $message }}</div>
                                                                @enderror
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label>Credit Limit</label>
                                                                <div class="input-group">
                                                                    <div class="input-group-prepend">
                                                                        <span class="input-group-text"><i class="fas fa-rupee-sign"></i></span>
                                                                    </div>
                                                                    <input type="number" name="credit_limit" value="{{ old('credit_limit',$credit_limit) }}" class="form-control @error('credit_limit') is-invalid @enderror">                                                                
                                                                    @error('credit_limit')
                                                                        <div class="alert alert-danger">{{ $message }}</div>
                                                                    @enderror
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div><!--end row-->
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label>GST Type <small class="text-danger font-13">*</small></label>
                                                                <select name="gst_type" class="form-control @error('gst_type') is-invalid @enderror">
                                                                    <option value="">Select</option>
                                                                    <option value="Intrastate Registered" @selected(old('gst_type',$gst_type) == 'Intrastate Registered')>Intrastate Registered (Tamilnadu)</option>
                                                                    <option value="Intrastate Unregistered" @selected(old('gst_type',$gst_type) == 'Intrastate Unregistered')>Intrastate Unregistered (Tamilnadu)</option>
                                                                    <option value="Interstate Registered" @selected(old('gst_type',$gst_type) == 'Interstate Registered')>Interstate Registered (Other State)</option>
                                                                    <option value="Interstate Unregistered" @selected(old('gst_type',$gst_type) == 'Interstate Unregistered')>Interstate Unregistered (Other State)</option>
                                                                </select>
                                                                @error('gst_type')
                                                                    <div class="alert alert-danger">{{ $message }}</div>
                                                                @enderror
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label>GST Number</label>
                                                                <input type="text" name="gst_number" maxlength="15" value="{{ old('gst_number',$gst_number) }}" class="form-control @error('gst_number') is-invalid @enderror" oninput="this.value = this.value.toUpperCase()">
                                                                @error('gst_number')
                                                                    <div class="alert alert-danger">{{ $message }}</div>
                                                                @enderror
                                                            </div>
                                                        </div>
                                                    </div><!--end row-->
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label>PAN Number</label>
                                                                <input type="text" name="pan_number" maxlength="10" value="{{ old('pan_number',$pan_number) }}" class="form-control @error('pan_number') is-invalid @enderror" oninput="this.value = this.value.toUpperCase()">
                                                                @error('pan_number')
                                                                    <div class="alert alert-danger">{{ $message }}</div>
                                                                @enderror
                                                            </div>
                                                        </div>  
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label>Outstanding Amount</label>
                                                                <div class="input-group">
                                                                    <div class="input-group-prepend">
                                                                        <span class="input-group-text"><i class="fas fa-rupee-sign"></i></span>
                                                                    </div>
                                                                    <input type="number" name="outstanding" value="{{ old('outstanding',$outstanding) }}" class="form-control @error('outstanding') is-invalid @enderror">
                                                                    @error('outstanding')
                                                                        <div class="alert alert-danger">{{ $message }}</div>
                                                                    @enderror
                                                                </div>                                                            
                                                            </div>
                                                        </div>                                                  
                                                    </div><!--end row-->
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label>TCS Status <small class="text-danger font-13">*</small></label>                                                                
                                                                <select name="tcs_status" id="tcs_status" class="form-control" @error('tcs_status') is-invalid @enderror>
                                                                    <option value="">Select</option>                                                                    
                                                                    <option value="TCS Not Applicable" @selected(old('tcs_status',$tcs_status) == 'TCS Not Applicable')>TCS Not Applicable</option>
                                                                    <option value="TCS Applicable" @selected(old('tcs_status',$tcs_status) == 'TCS Applicable')>TCS Applicable</option>                                                                    
                                                                    <option value="TCS Applied" @selected(old('tcs_status',$tcs_status) == 'TCS Applied')>Already in TCS</option>
                                                                </select>
                                                                @error('tcs_status')
                                                                    <div class="alert alert-danger">{{ $message }}</div>
                                                                @enderror
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label>TDS Status <small class="text-danger font-13">*</small></label>
                                                                <select name="tds_status" class="form-control" @error('tds_status') is-invalid @enderror">
                                                                    <option value="">Select</option>                                                                    
                                                                    <option value="TDS Not Applicable" @selected(old('tds_status',$tds_status) == 'TDS Not Applicable')>TDS Not Applicable</option>
                                                                    <option value="TDS Applicable" @selected(old('tds_status',$tds_status) == 'TDS Applicable')>TDS Applicable</option>                                                                    
                                                                    <option value="TDS Applied" @selected(old('tds_status',$tds_status) == 'TDS Applied')>Already in TDS</option>
                                                                </select>
                                                                @error('tds_status')
                                                                    <div class="alert alert-danger">{{ $message }}</div>
                                                                @enderror
                                                            </div>
                                                        </div>
                                                    </div><!--end row-->
                                                    <div class="row">                                                        
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label>Payment Mode <small class="text-danger font-13">*</small></label>
                                                                <select name="payment_mode" class="form-control">
                                                                    <option value="">Select</option>
                                                                    <option value="Cash & Carry" @selected(old('payment_mode',$payment_mode) == 'Cash & Carry')>Cash & Carry (Daily)</option>
                                                                    <option value="Bill to Bill" @selected(old('payment_mode',$payment_mode) == 'Bill to Bill')>Bill to Bill (Alternate Days)</option>
                                                                    <option value="Weekly" @selected(old('payment_mode',$payment_mode) == 'Weekly')>Weekly</option>                                                                    
                                                                    <option value="Twice Monthly" @selected(old('payment_mode',$payment_mode) == 'Monthly')>Twice Monthly</option>
                                                                    <option value="Monthly" @selected(old('payment_mode',$payment_mode) == 'Monthly')>Monthly</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label>Incentive Mode</label>
                                                                <select name="incentive_mode" class="form-control">
                                                                    <option value="">Select</option>
                                                                    <option value="Daily" @selected(old('incentive_mode',$incentive_mode) == 'Daily')>Daily</option>
                                                                    <option value="Weekly" @selected(old('incentive_mode',$incentive_mode) == 'Weekly')>Weekly</option>
                                                                    <option value="Twice Monthly" @selected(old('incentive_mode',$incentive_mode) == 'Twice Monthly')>Twice Monthly</option>
                                                                    <option value="Monthly" @selected(old('incentive_mode',$incentive_mode) == 'Monthly')>Monthly</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </div><!--end row-->
                                                    <div class="row" style="margin-top:8px">
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label>Customer Since</label>
                                                                <input type="date" name="customer_since" value="{{ old('customer_since',$customer_since) }}" class="form-control @error('customer_since') is-invalid @enderror">
                                                                @error('customer_since')
                                                                    <div class="alert alert-danger">{{ $message }}</div>
                                                                @enderror                                                                
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <div class="checkbox checkbox-primary">
                                                                    <input type="checkbox" name="link_cust_chk" id="link_cust_chk" value="1"  @checked(old('link_cust_chk', $link_customer))>
                                                                    <label for="link_cust_chk">Link Customer</label>
                                                                </div>
                                                                <div id="link_cust_div">
                                                                    <select name="link_customer" id="link_customer" class="form-control @error('link_customer') is-invalid @enderror">
                                                                        <option value="">Select</option>
                                                                        @foreach($link_customers as $custmr)
                                                                            @if($custmr->id <> $id)
                                                                                <option value="{{ $custmr->id }}" @selected(old('link_customer',$link_cust_id) == $custmr->id)>{{ $custmr->customer_name }}</option>
                                                                            @endif
                                                                        @endforeach
                                                                    </select>
                                                                    @error('link_customer')
                                                                        <div class="alert alert-danger">{{ $message }}</div>
                                                                    @enderror
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div><!--end row-->
                                                    <input type="button" value="Previous" class="btn btn-gradient-primary btn-sm text-light px-4 mt-3 float-left mb-0" onclick="document.getElementById('tab_home').click()" />
                                                    <input type="button" value="Next" class="btn btn-gradient-primary btn-sm text-light px-4 mt-3 float-right mb-0" onclick="document.getElementById('tab_profile').click()" />
                                                </div>

                                                <div class="tab-pane p-3" id="profile" role="tabpanel">
                                                    <div class="row" style="margin-top:16px">
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label>Owner Name</label>
                                                                <div class="input-group">
                                                                    <div class="input-group-prepend">
                                                                        <span class="input-group-text"><i class="far fa-user"></i></span>
                                                                    </div>
                                                                    <input type="text" name="owner_name" value="{{ old('owner_name',$owner_name) }}" class="form-control">
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label>Gender</label>
                                                                <div class="form-control" style="border:none; padding:1px">
                                                                    <div class="form-check-inline my-1">
                                                                        <div class="custom-control custom-radio">
                                                                            <input type="radio" name="gender" id="rdo_male" value="Male" class="custom-control-input" checked>
                                                                            <label class="custom-control-label" for="rdo_male">Male</label>
                                                                        </div>
                                                                    </div>
                                                                    <div class="form-check-inline my-1">
                                                                        <div class="custom-control custom-radio">
                                                                            <input type="radio" name="gender" id="rdo_female" value="Female" class="custom-control-input" {{ (old("gender",$gender) == "Female" ? "checked":"") }}>
                                                                            <label class="custom-control-label" for="rdo_female">Female</label>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div><!--end row-->                                                    
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label>Date of Birth</label>
                                                                <input type="date" name="dob" value="{{ old('dob',$dob) }}" class="form-control @error('dob') is-invalid @enderror">
                                                                @error('dob')
                                                                    <div class="alert alert-danger">{{ $message }}</div>
                                                                @enderror
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label>Aadhaar Number</label>
                                                                <input type="number" name="aadhaar" maxlength="16" value="{{ old('aadhaar',$aadhaar) }}" class="form-control @error('aadhaar') is-invalid @enderror">
                                                                @error('aadhaar')
                                                                    <div class="alert alert-danger">{{ $message }}</div>
                                                                @enderror
                                                            </div>
                                                        </div>
                                                    </div><!--end row-->
                                                    @if(!$isEdit)
                                                        <div class="row">
                                                            <div class="col-xl-6">
                                                                <div class="card">
                                                                    <div class="card-body">
                                                                        <h4 class="mt-0 header-title">Profile Image</h4>                                    
                                                                        <input type="file" name="profile_image" accept="image/*" value="{{ old('profile_image') }}" class="dropify @error('profile_image') is-invalid @enderror" />
                                                                        @error('profile_image')
                                                                            <div class="alert alert-danger">{{ $message }}</div>
                                                                        @enderror
                                                                    </div><!--end card-body-->
                                                                </div><!--end card-->
                                                            </div><!--end col-->
                                                            <div class="col-xl-6">
                                                                <div class="card">
                                                                    <div class="card-body">
                                                                        <h4 class="mt-0 header-title">Shop Photo</h4>                                    
                                                                        <input type="file" name="shop_photo" accept="image/*" value="{{ old('shop_photo') }}" class="dropify @error('shop_photo') is-invalid @enderror" />
                                                                        @error('shop_photo')
                                                                            <div class="alert alert-danger">{{ $message }}</div>
                                                                        @enderror
                                                                    </div><!--end card-body-->
                                                                </div><!--end card-->
                                                            </div><!--end col-->
                                                        </div><!--end row-->   
                                                    @endif                                             
                                                    <input type="button" value="Previous" class="btn btn-gradient-primary btn-sm text-light px-4 mt-3 float-left mb-0" onclick="document.getElementById('tab_settings').click()" />
                                                    <input type="button" value="Next" class="btn btn-gradient-primary btn-sm text-light px-4 mt-3 float-right mb-0" onclick="document.getElementById('tab_account').click()" />
                                                </div>         

                                                <div class="tab-pane p-3" id="account" role="tabpanel">
                                                    <div class="row" style="margin-top:16px">
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label>Bank Name</label>
                                                                <div class="input-group">
                                                                    <div class="input-group-prepend">
                                                                        <span class="input-group-text"><i class="mdi mdi-bank"></i></span>
                                                                    </div>
                                                                    <input type="text" name="bank_name" value="{{ old('bank_name',$bank_name) }}" class="form-control">
                                                                </div>                                                            
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label>Branch</label>
                                                                <input type="text" name="branch" value="{{ old('branch',$branch) }}" class="form-control">
                                                            </div>
                                                        </div>
                                                    </div><!--end row-->
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label>IFSC</label>
                                                                <input type="text" name="ifsc" maxlength="11" value="{{ old('ifsc',$ifsc) }}" class="form-control @error('ifsc') is-invalid @enderror" oninput="this.value = this.value.toUpperCase()">
                                                                @error('ifsc')
                                                                    <div class="alert alert-danger">{{ $message }}</div>
                                                                @enderror
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label>Account Holder</label>
                                                                <input type="text" name="acc_holder" value="{{ old('acc_holder',$acc_holder) }}" class="form-control">
                                                            </div>
                                                        </div>
                                                    </div><!--end row-->
                                                    <div class="row">
                                                        <div class="col-md-8">
                                                            <div class="form-group">
                                                                <label>Account Number</label>
                                                                <input type="text" name="acc_number" maxlength="20" value="{{ old('acc_number',$acc_number) }}" class="form-control @error('acc_number') is-invalid @enderror" oninput="this.value = this.value.toUpperCase()">
                                                                @error('acc_number')
                                                                    <div class="alert alert-danger">{{ $message }}</div>
                                                                @enderror
                                                            </div>
                                                        </div>
                                                    </div><!--end row-->
                                                    
                                                    <input type="hidden" value="{{$enq_id}}" name="enq_id"/>
                                                    <input type="button" value="Previous" class="btn btn-gradient-primary btn-sm text-light px-4 mt-3 float-left mb-0" onclick="document.getElementById('tab_profile').click()" />
                                                    <input type="submit" value="Submit" class="btn btn-gradient-primary btn-sm text-light px-4 mt-3 float-right mb-0" />
                                                </div>    
                                                
                                            </div>        
                                        </div><!--end card-body-->
                                    </div><!--end card-->
                                </div><!--end col-->
                            </div><!--end row-->

                        </form><!--end form-->
                    </div><!--end card-body-->
                    
                </div><!--end card-->
            </div><!--end col-->
        </div><!--end row-->

    </div><!-- container -->
@stop

@push('custom-scripts')
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.js"></script>     
    <script>
        $(document).ready(function () {
            $.ajaxSetup({
                headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
            });

            if($("#link_cust_chk").is(':checked'))
                $("#link_cust_div").show();
            else
                $("#link_cust_div").hide();

            $('#customer_name').focusout(function () {
                var name = $(this).val();
                $("#billing_name").val(name);                
            });
            
            $('#route').change(function () {
                var id = $(this).val();
                $('#area').children(`option:not(:first)`).remove();
                $('#district').val('');
                $('#state').val('');
                $('#district-state').val('');
                if(id) {
                    let url = "{{ route('areas.list', ':id') }}".replace(':id', id);
                    $.get(url, function (data) {
                        var areas = data.areas;
                        for (var i=0; i<areas.length; i++) {
                            $('#area').append(new Option(areas[i].name, areas[i].id));
                        }   

                        var value = {{old("area",$area_id)}} + "";
                        if ($("#area").find("option[value='"+value+"']").length == 1) {
                            $('#area').val(value).change();
                        }
                    });
                }
            });

            $('#area').change(function () {
                var id = $('#area').val();
                $('#district').val('');
                $('#state').val('');
                if(id) {
                    let url = "{{ route('areas.info', ':id') }}".replace(':id', id);
                    $.get(url, function (data) {                    
                        $('#district').val(data.district);
                        $('#state').val(data.state);
                        $('#district-state').val(data.district + ' - ' + data.state);
                    });
                }
            });

            $('#link_cust_chk').change(function () {
                if(this.checked) {
                    $("#link_cust_div").show();
                }
                else {
                    $("#link_cust_div").hide();
                    $('#link_customer').val('');
                }
            });

            $("#route").trigger('change');

            var success = '<?php echo $isSuccess; ?>';
            var message = '<?php echo $msg; ?>';
            if(success) {
                Swal.fire({
                                title:'Success!',
                                text:message,
                                type:'success'
                            }
                        )
                        .then(
                            function() {
                                if(message.includes('Convert'))
                                    window.location.replace("{{ route('conversions.index') }}");
                                else
                                    window.location.replace("{{ route('customers.index') }}");
                            }
                        ); 
            }
        });  
    </script> 
@endpush 

@section('footerScript')
    <!-- Sweet-Alert  -->
    <script src="{{ asset('plugins/sweet-alert2/sweetalert2.min.js') }}"></script>
    <script src="{{ asset('assets/pages/jquery.sweet-alert.init.js') }}"></script>
    <!-- Dropify  -->
    <script src="{{ asset('plugins/dropify/js/dropify.min.js') }}"></script>
    <script>
        $('.dropify').dropify();
    </script> 
@stop