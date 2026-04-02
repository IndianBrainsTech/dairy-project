@extends('app-layouts.admin-master')

@section('title', 'Employee')

@section('headerStyle')
    <link href="{{ asset('plugins/dropify/css/dropify.min.css') }}" rel="stylesheet">
    <link href="{{ asset('plugins/sweet-alert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('plugins/animate/animate.css') }}" rel="stylesheet" type="text/css">
@stop

@php
    if(!isset($employee)) {
        $isEdit = false;
        $title = "Add Employee";
        $action = route('employees.store');
        $id = $name = $code = "";
        $role = $reporting_head = "";
        $dob = $gender = $remarks = "";
        $user_name = $password = "";
        $address = $district = $state = "";
        $landmark = $pincode = "";
        $mobile_num = $alternate_num = $email = "";
        $father_name = $aadhaar = "";
        $license_number = $license_validity = "";
        $blood_group = $doj = "";
        $bank_name = $branch = $ifsc = "";
        $acc_holder = $acc_number = "";
    }
    else {
        $isEdit = true;
        $title = "Edit Employee";
        $action = route('employees.update', ['id' => $employee->id]);
        $id = $employee->id;
        $name = $employee->name;
        $code = $employee->code;
        $role = $employee->role_id;
        $reporting_head = $employee->manager_id;
        $dob = $employee->dob;
        $gender = $employee->gender;
        $user_name = $employee->user_name;
        $password = $employee->password;
        $remarks = $employee->remarks;
        $address = $employee->address;
        $district = $employee->district;
        $state = $employee->state;
        $landmark = $employee->landmark;
        $pincode = $employee->pincode;
        $mobile_num = $employee->mobile_num;
        $alternate_num = $employee->alternate_num;
        $email = $employee->email_id;
        $father_name = $employee->father_name;
        $aadhaar = $employee->aadhaar_num;
        $license_number = $employee->license_num;
        $license_validity = $employee->license_validity;
        $blood_group = $employee->blood_group;
        $doj = $employee->doj;
        $bank_name = $employee->bank_name;
        $branch = $employee->branch;
        $ifsc = $employee->ifsc;
        $acc_holder = $employee->acc_holder;
        $acc_number = $employee->acc_number;
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
                    @slot('item3') Employees @endslot
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
                                if(str_contains($msg,'code_unique'))
                                    $msg = "Employee Code already exists. Please try with different code...";
                                else if(str_contains($msg,'user_name_unique'))
                                    $msg = "User name already exists. Please try with different user name...";
                                else if(str_contains($msg,'mobile_num_unique'))
                                    $msg = "Mobile Number already allotted. Please try with different number...";
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
                                                    <a class="nav-link active" id="tab_profile" data-toggle="tab" href="#profile" role="tab">Profile</a>
                                                </li>
                                                <li class="nav-item">
                                                    <a class="nav-link" id="tab_contact" data-toggle="tab" href="#contact" role="tab">Contact Info</a>
                                                </li>
                                                <li class="nav-item">
                                                    <a class="nav-link" id="tab_additional" data-toggle="tab" href="#additional" role="tab">Additional Info</a>
                                                </li>
                                            </ul>
          
                                            <!-- Tab panes -->
                                            <div class="tab-content">
                                                <div class="tab-pane active p-3" id="profile" role="tabpanel">
                                                    <div class="row" style="margin-top:16px">
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label>Employee Name <small class="text-danger font-13">*</small></label>
                                                                <div class="input-group">
                                                                    <div class="input-group-prepend">
                                                                        <span class="input-group-text"><i class="far fa-user"></i></span>
                                                                    </div>
                                                                    <input type="text" name="name" value="{{ old('name',$name) }}" class="form-control @error('name') is-invalid @enderror">
                                                                    @error('name')
                                                                        <div class="alert alert-danger">{{ $message }}</div>
                                                                    @enderror
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-group">                                                            
                                                                <label>Employee Code <small class="text-danger font-13">*</small></label>
                                                                <input type="text" name="code" value="{{ old('code',$code) }}" maxlength="10" class="form-control @error('code') is-invalid @enderror" oninput="this.value = this.value.toUpperCase()">
                                                                @error('code')
                                                                    <div class="alert alert-danger">{{ $message }}</div>
                                                                @enderror
                                                            </div>
                                                        </div>
                                                    </div><!--end row-->
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label>Role [Designation]<small class="text-danger font-13">*</small></label>
                                                                <select name="role" id="role" class="form-control @error('role') is-invalid @enderror">
                                                                    <option value="">Select</option>                                                                    
                                                                    @foreach($designations as $designation)
                                                                        <option value="{{ $designation->id }}" @selected(old('role',$role) == $designation->id)>                                                                            
                                                                            {{ $designation->role_name }}
                                                                            @if($designation->short_name)
                                                                                ({{ $designation->short_name }})
                                                                            @endif
                                                                        </option>
                                                                    @endforeach
                                                                </select>
                                                                @error('role')
                                                                    <div class="alert alert-danger">{{ $message }}</div>
                                                                @enderror
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label>Reporting Head <small class="text-danger font-13">*</small></label>
                                                                <select name="reporting_head" id="reporting_head" class="form-control @error('reporting_head') is-invalid @enderror">
                                                                    <option value="">Select</option>                                                                    
                                                                </select>
                                                                @error('reporting_head')
                                                                    <div class="alert alert-danger">{{ $message }}</div>
                                                                @enderror
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
                                                                <label>User Name <small class="text-danger font-13">*</small></label>
                                                                <input type="text" name="user_name" value="{{ old('user_name',$user_name) }}" class="form-control @error('user_name') is-invalid @enderror">
                                                                @error('user_name')
                                                                    <div class="alert alert-danger">{{ $message }}</div>
                                                                @enderror
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-group">                                                            
                                                                <label>Password <small class="text-danger font-13">*</small></label>
                                                                <input type="password" name="password" value="{{ old('password',$password) }}" class="form-control @error('password') is-invalid @enderror">
                                                                @error('password')
                                                                    <div class="alert alert-danger">{{ $message }}</div>
                                                                @enderror
                                                            </div>
                                                        </div>
                                                    </div>
                                                    
                                                    @if(!$isEdit)
                                                        <div class="row">
                                                            <div class="col-xl-6">
                                                                <div class="card">
                                                                    <label>Photo</label>
                                                                    <input type="file" name="photo" accept="image/*" class="dropify @error('photo') is-invalid @enderror" />
                                                                    @error('photo')
                                                                        <div class="alert alert-danger">{{ $message }}</div>
                                                                    @enderror
                                                                </div><!--end card-->
                                                            </div><!--end col-->
                                                            <div class="col-xl-6">
                                                                <div class="form-group">
                                                                    <label>Remarks</label>
                                                                    <textarea name="remarks" rows="5" placeholder="Comments about employee" class="form-control">{{ old('remarks',$remarks) }}</textarea>
                                                                </div>
                                                                <input type="button" value="Next" class="btn btn-gradient-primary btn-sm text-light px-4 mt-3 float-right mb-0" onclick="document.getElementById('tab_contact').click()" />
                                                            </div><!--end col-->
                                                        </div><!--end row-->
                                                    @else
                                                        <div class="row">
                                                            <div class="col-xl-8">
                                                                <div class="form-group">
                                                                    <label>Remarks</label>
                                                                    <textarea name="remarks" rows="4" placeholder="Comments about employee" class="form-control">{{ old('remarks',$remarks) }}</textarea>
                                                                </div>
                                                            </div>
                                                            <div class="col-xl-4">
                                                                <div class="form-group">
                                                                    <br/><br/><br/><br/>
                                                                    <input type="button" value="Next" class="btn btn-gradient-primary btn-sm text-light px-4 mt-3 float-right mb-0" onclick="document.getElementById('tab_contact').click()" />
                                                                </div>
                                                            </div> 
                                                        </div><!--end row-->
                                                    @endif                                                        
                                                    
                                                </div>

                                                <div class="tab-pane p-3" id="contact" role="tabpanel">
                                                    <div class="row" style="margin-top:16px">
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label>Address <small class="text-danger font-13">*</small></label>
                                                                <textarea name="address" rows="4" placeholder="Address Lines.." class="form-control @error('address') is-invalid @enderror">{{ old('address',$address) }}</textarea>
                                                                @error('address')
                                                                    <div class="alert alert-danger">{{ $message }}</div>
                                                                @enderror
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label>District<small class="text-danger font-13">*</small></label>
                                                                <select name="district" id="district" class="form-control @error('district') is-invalid @enderror">
                                                                    <option value="">Select</option>
                                                                    @foreach($districts as $dist)
                                                                        <option value="{{ $dist->name }}" @selected(old('district',$district) == $dist->name)>{{ $dist->name }}</option>
                                                                    @endforeach
                                                                </select>
                                                                @error('district')
                                                                    <div class="alert alert-danger">{{ $message }}</div>
                                                                @enderror
                                                            </div>
                                                            <div class="form-group">
                                                                <label>State</label> <br/>
                                                                <label id="lblstate" value="{{ old('state',$state) }}" style="margin-left: 20px">State</label>
                                                                <input type="hidden" id="state" name="state" value="{{ old('state',$state) }}">
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
                                                                <label>Mobile Number <small class="text-danger font-13">*</small></label>
                                                                <div class="input-group">
                                                                    <div class="input-group-prepend">
                                                                        <span class="input-group-text"><i class="dripicons-phone"></i></span>
                                                                    </div>
                                                                    <input type="number" name="mobile_number" value="{{ old('mobile_number',$mobile_num) }}" class="form-control @error('mobile_number') is-invalid @enderror">
                                                                    @error('mobile_number')
                                                                        <div class="alert alert-danger">{{ $message }}</div>
                                                                    @enderror
                                                                </div>
                                                            </div>
                                                        </div>
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
                                                    </div><!--end row-->
                                                    <div class="row">
                                                        <div class="col-md-8">
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
                                                    </div><!--end row-->                                             
                                                    <input type="button" value="Previous" class="btn btn-gradient-primary btn-sm text-light px-4 mt-3 float-left mb-0" onclick="document.getElementById('tab_profile').click()" />
                                                    <input type="button" value="Next" class="btn btn-gradient-primary btn-sm text-light px-4 mt-3 float-right mb-0" onclick="document.getElementById('tab_additional').click()" />
                                                </div>

                                                <div class="tab-pane p-3" id="additional" role="tabpanel">
                                                    <div class="row" style="margin-top:16px">
                                                        <div class="col-md-6">
                                                            <div class="form-group">                                                            
                                                                <label>Father Name</label>
                                                                <input type="text" name="father_name" value="{{ old('father_name',$father_name) }}" class="form-control">
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
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label>Driving License Number</label>
                                                                <input type="text" name="license_number" maxlength="16" value="{{ old('license_number',$license_number) }}" class="form-control @error('license_number') is-invalid @enderror" oninput="this.value = this.value.toUpperCase()">
                                                                @error('license_number')
                                                                    <div class="alert alert-danger">{{ $message }}</div>
                                                                @enderror
                                                            </div>
                                                        </div>  
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label>Driving License Validity</label>
                                                                <input type="date" name="license_validity" value="{{ old('license_validity',$license_validity) }}" class="form-control @error('license_validity') is-invalid @enderror">
                                                                @error('license_validity')
                                                                    <div class="alert alert-danger">{{ $message }}</div>
                                                                @enderror
                                                            </div>
                                                        </div>                                                  
                                                    </div><!--end row-->
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label>Blood Group</label>
                                                                <select name="blood_group" class="form-control">
                                                                    <option value="">Select</option>
                                                                    <option value="O+" @selected(old('blood_group',$blood_group) == 'O+')>O+</option>
                                                                    <option value="O-" @selected(old('blood_group',$blood_group) == 'O-')>O-</option>
                                                                    <option value="A+" @selected(old('blood_group',$blood_group) == 'A+')>A+</option>
                                                                    <option value="A-" @selected(old('blood_group',$blood_group) == 'A-')>A-</option>
                                                                    <option value="B+" @selected(old('blood_group',$blood_group) == 'B+')>B+</option>
                                                                    <option value="B-" @selected(old('blood_group',$blood_group) == 'B-')>B-</option>
                                                                    <option value="AB+" @selected(old('blood_group',$blood_group) == 'AB+')>AB+</option>
                                                                    <option value="AB-" @selected(old('blood_group',$blood_group) == 'AB-')>AB-</option>
                                                                </select>
                                                            </div>
                                                        </div>  
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label>Date of Join</label>
                                                                <input type="date" name="doj" value="{{ old('doj',$doj) }}" class="form-control @error('doj') is-invalid @enderror">
                                                                @error('doj')
                                                                    <div class="alert alert-danger">{{ $message }}</div>
                                                                @enderror
                                                            </div>
                                                        </div>                                                  
                                                    </div><!--end row-->
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
                                                    
                                                    <input type="button" value="Previous" class="btn btn-gradient-primary btn-sm text-light px-4 mt-3 float-left mb-0" onclick="document.getElementById('tab_contact').click()" />
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

            $('#role').change(function () {
                var role = $('#role').val();
                var value = '<?php echo old("reporting_head",$reporting_head); ?>';
                $('#reporting_head').children(`option:not(:first)`).remove();
                // $('#reporting_head').append(new Option("Admin", "0"));
                if(value.toString() == "0")
                    $('#reporting_head').append(`<option value="0" selected>Admin</option>`);
                else
                    $('#reporting_head').append(`<option value="0">Admin</option>`);
                if(role) {                    
                    $.get('/employee/manager/' + role, function (data) {
                        for(var i = 0; i <= data.managers.length; i++) {
                            var manager = data.managers[i];
                            // $('#reporting_head').append(new Option(manager['display'], manager['id']));
                            if(manager['id'].toString() == value.toString())
                                $('#reporting_head').append(`<option value="${manager['id']}" selected>${manager['display']}</option>`);
                            else
                                $('#reporting_head').append(`<option value="${manager['id']}">${manager['display']}</option>`);
                        }
                    });
                }
                // $('#reporting_head option[value="${value}"]').prop('selected',true);      
            });

            $('#district').change(function () {
                var district = $('#district').val();
                $('#lblstate').html('');
                $('#state').val('');
                if(district) {
                    let url = "{{ route('states.get', ':district') }}".replace(':district', district);
                    $.get(url, function (state) {
                        $('#lblstate').html(state);
                        $('#state').val(state);
                    });
                }
            });

            $("#role").trigger('change');

            var success = '<?php echo $isSuccess; ?>';
            if(success) {
                Swal.fire({
                                title:'Success!',
                                text:'<?php echo $msg; ?>',
                                type:'success'
                            }
                        )
                        .then(
                            function() { 
                                window.location.replace("{{ route('employees.index') }}");
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