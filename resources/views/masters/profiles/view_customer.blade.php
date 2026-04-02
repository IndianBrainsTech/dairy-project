@extends('app-layouts.admin-master')

@section('title', 'Customer')

@section('headerStyle')
    <link href="{{ asset('plugins/dropify/css/dropify.min.css') }}" rel="stylesheet">
    <link href="{{ asset('plugins/sweet-alert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('plugins/animate/animate.css') }}" rel="stylesheet" type="text/css">
@stop

@section('content')
    <div class="container-fluid">
        <!-- Page-Title -->
        <div class="row">
            <div class="col-sm-12">
            @component('app-components.breadcrumb-4')
                @slot('title') View Customer @endslot
                @slot('item1') Masters @endslot
                @slot('item2') Profiles @endslot
                @slot('item3') Customers @endslot
            @endcomponent
            </div><!--end col-->
        </div>
        <!-- end page title end breadcrumb -->

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">

                        <div class="row">
                            <div class="col-lg-4">
                                <img src="{{ asset('mystorage/customers/shop/' . $customer->shop_photo) }}" alt="" class=" mx-auto d-block" height="200">
                                <p style="text-align:center;margin-top:4px">Shop Photo &nbsp;&nbsp;&nbsp;<a href="" id="shop_photo" class="mr-2" data-toggle="modal" data-animation="bounce" data-target="#modal_image_upload" data-id="shop"><i class="fas fa-edit text-info font-16"></i></a></p>
                                <hr/>
                                <img src="{{ asset('mystorage/customers/profile/' . $customer->profile_image) }}" alt="" class=" mx-auto  d-block" height="200">
                                <p style="text-align:center;margin-top:4px">Profile Image &nbsp;&nbsp;&nbsp;<a href="" id="profile_image" class="mr-2" data-toggle="modal" data-animation="bounce" data-target="#modal_image_upload" data-id="profile"><i class="fas fa-edit text-info font-16"></i></a></p>
                            </div><!--end col-->

                            <div class="col-lg-8">
                                <div class="single-pro-detail">
                                    <h3 class="pro-title">{{ $customer->customer_name }}</h3>
                                    <p class="mb-2 font-16 text-dark">{{ $customer->group }}</p>
                                    <p class="text-muted mb-0">{{ $customer->remarks }}</p> <br/>
                                    <div class="row" style="margin-bottom:10px;">
                                        <div class="col-md-3 text-dark">Customer ID</div>
                                        <div class="col-md-9" style="color:blue; margin-left:-30px">{{ $customer->customer_code }}</div>
                                    </div>
                                    <div class="row" style="margin-bottom:10px;">
                                        <div class="col-md-3 text-dark">Staff Incharge</div>
                                        <div class="col-md-9" style="color:blue; margin-left:-30px">{{ $staff_incharge }}</div>
                                    </div>
                                    <div class="row" style="margin-bottom:10px;">
                                        <div class="col-md-3">Route</div>
                                        <div class="col-md-9" style="color:blue; margin-left:-30px">{{ $customer->route->name }}</div>
                                    </div>
                                    <div class="row" style="margin-bottom:10px;">
                                        <div class="col-md-3">Area</div>
                                        <div class="col-md-9" style="color:blue; margin-left:-30px">{{ $customer->area->name }}</div>
                                    </div>

                                    <h6 class="font-14" style="color:#fd3c97; margin-top:26px; margin-bottom:16px">
                                        Address & Contacts :
                                        <a href="" id="add_address" class="mx-1" data-toggle="modal" data-animation="bounce" data-target="#modal_address"><i class="fas fa-plus-square text-info font-20"></i></a>
                                    </h6>
                                    <div class="row" style="margin-bottom:10px; margin-left:16px">
                                        <div class="col-md-2">Address</div>
                                        <div class="col-md-10" style="color:blue">{{ $customer->address_lines }}</div>
                                    </div>
                                    <div class="row" style="margin-bottom:10px; margin-left:16px">
                                        <div class="col-md-2">District</div>
                                        <div class="col-md-10" style="color:blue">{{ $customer->district }}</div>
                                    </div>
                                    <div class="row" style="margin-bottom:10px; margin-left:16px">
                                        <div class="col-md-2">State</div>
                                        <div class="col-md-10" style="color:blue">{{ $customer->state }}</div>
                                    </div>
                                    <div class="row" style="margin-bottom:10px; margin-left:16px">
                                        <div class="col-md-2">Landmark</div>
                                        <div class="col-md-10" style="color:blue">{{ $customer->landmark }}</div>
                                    </div>
                                    <div class="row" style="margin-bottom:10px; margin-left:16px">
                                        <div class="col-md-2">Pin Code</div>
                                        <div class="col-md-10" style="color:blue">{{ $customer->pincode }}</div>
                                    </div>
                                    <div class="row" style="margin-bottom:10px; margin-left:16px">
                                        <div class="col-md-2">Email ID</div>
                                        <div class="col-md-10" style="color:blue">{{ $customer->email_id }}</div>
                                    </div>
                                    <div class="row" style="margin-bottom:10px; margin-left:16px">
                                        <div class="col-md-3">Contact Number</div>
                                        <div class="col-md-9" style="color:blue; margin-left:-30px">
                                            {{ $customer->contact_num }}
                                            @if(!is_null($customer->contact_name))
                                                ({{ $customer->contact_name }})
                                            @endif
                                        </div>
                                    </div>
                                    @if(!is_null($customer->alternate_num))
                                        <div class="row" style="margin-bottom:10px; margin-left:16px; margin-top:-4px">
                                            <div class="col-md-3"></div>
                                            <div class="col-md-9" style="color:blue; margin-left:-30px">
                                                {{ $customer->alternate_num }}
                                                @if(!is_null($customer->alternate_name))
                                                    ({{ $customer->alternate_name }})
                                                @endif
                                            </div>
                                        </div>
                                    @endif

                                    @foreach($addresses as $address)
                                        <h6 class="font-14" style="color:#fd3c97; margin-top:26px; margin-bottom:16px">
                                            Address {{ $loop->index + 2 }}
                                            <a href="" class="edit_address mx-1" data-address-id="{{$address->id}}" data-toggle="modal" data-animation="bounce" data-target="#modal_address"><i class="fas fa-edit text-info font-16"></i></a>
                                        </h6>
                                        <div class="row" style="margin-bottom:10px; margin-left:16px">
                                            <div class="col-md-2">Address</div>
                                            <div class="col-md-10" style="color:blue">{{ $address->address_lines }}</div>
                                        </div>
                                        <div class="row" style="margin-bottom:10px; margin-left:16px">
                                            <div class="col-md-2">District</div>
                                            <div class="col-md-10" style="color:blue">{{ $address->district }}</div>
                                        </div>
                                        <div class="row" style="margin-bottom:10px; margin-left:16px">
                                            <div class="col-md-2">State</div>
                                            <div class="col-md-10" style="color:blue">{{ $address->state }}</div>
                                        </div>
                                        <div class="row" style="margin-bottom:10px; margin-left:16px">
                                            <div class="col-md-2">Pin Code</div>
                                            <div class="col-md-10" style="color:blue">{{ $address->pincode }}</div>
                                        </div>
                                    @endforeach
                                </div>
                            </div><!--end col-->
                        </div><!--end row-->

                        <div class="row">
                            <div class="col-lg-6">
                                <h6 class="font-14" style="color:#fd3c97; margin-top:16px; margin-bottom:16px">Settings :</h6>
                                <div class="row" style="margin-bottom:10px; margin-left:16px">
                                    <div class="col-md-4">Billing Name</div>
                                    <div class="col-md-8" style="color:blue">{{ $customer->billing_name }}</div>
                                </div>
                                <div class="row" style="margin-bottom:10px; margin-left:16px">
                                    <div class="col-md-4">Credit Limit</div>
                                    <div class="col-md-8" style="color:blue">
                                        @if($customer->credit_limit)
                                            Rs. {{ $customer->credit_limit }}
                                        @endif
                                    </div>
                                </div>
                                <div class="row" style="margin-bottom:10px; margin-left:16px">
                                    <div class="col-md-4">GST Type</div>
                                    <div class="col-md-8" style="color:blue">{{ $customer->gst_type }}</div>
                                </div>
                                @if($customer->gst_number)
                                    <div class="row" style="margin-bottom:10px; margin-left:16px">
                                        <div class="col-md-4">GST Number</div>
                                        <div class="col-md-8" style="color:blue">{{ $customer->gst_number }}</div>
                                    </div>
                                @endif
                                <div class="row" style="margin-bottom:10px; margin-left:16px">
                                    <div class="col-md-4">PAN Number</div>
                                    <div class="col-md-8" style="color:blue">{{ $customer->pan_number }}</div>
                                </div>
                                <div class="row" style="margin-bottom:10px; margin-left:16px">
                                    <div class="col-md-5">Outstanding Amount</div>
                                    <div class="col-md-7" style="color:blue; margin-left:-40px">
                                        @if($customer->outstanding)
                                            Rs. {{ $customer->outstanding }}
                                        @endif
                                    </div>
                                </div>
                                <div class="row" style="margin-bottom:10px; margin-left:16px">
                                    <div class="col-md-4">TCS Status</div>
                                    <div class="col-md-8" style="color:blue">{{ $customer->tcs_status }}</div>
                                </div>
                                <div class="row" style="margin-bottom:10px; margin-left:16px">
                                    <div class="col-md-4">TDS Status</div>
                                    <div class="col-md-8" style="color:blue">{{ $customer->tds_status }}</div>
                                </div>
                                <div class="row" style="margin-bottom:10px; margin-left:16px">
                                    <div class="col-md-4">Payment Mode</div>
                                    <div class="col-md-8" style="color:blue">{{ $customer->payment_mode }}</div>
                                </div>
                                <div class="row" style="margin-bottom:10px; margin-left:16px">
                                    <div class="col-md-4">Incentive Mode</div>
                                    <div class="col-md-8" style="color:blue">{{ $customer->incentive_mode }}</div>
                                </div>                                                                
                                <div class="row" style="margin-bottom:10px; margin-left:16px">
                                    <div class="col-md-4">Link Customer</div>
                                    <div class="col-md-8" style="color:blue">
                                        {{ $customer->link_customer ? "Yes" : "No" }}
                                        @if($customer->link_customer)
                                            [{{ $link_customer->customer_name }}]
                                        @endif
                                    </div>
                                </div>
                                <div class="row" style="margin-bottom:10px; margin-left:16px">
                                    <div class="col-md-4">Customer Since</div>
                                    <div class="col-md-8" style="color:blue">
                                        {{ displayDate($customer->customer_since) }}
                                        @if($customer->customer_since)
                                            ({{ dateDifference($customer->customer_since) }})                                            
                                        @endif
                                    </div>
                                </div>
                            </div><!--end col-->

                            <div class="col-lg-6">
                                <h6 class="font-14" style="color:#fd3c97; margin-top:16px; margin-bottom:16px">Profile :</h6>
                                <div class="row" style="margin-bottom:10px; margin-left:16px">
                                    <div class="col-md-4">Owner Name</div>
                                    <div class="col-md-8" style="color:blue">{{ $customer->owner_name }}</div>
                                </div>
                                <div class="row" style="margin-bottom:10px; margin-left:16px">
                                    <div class="col-md-4">Gender</div>
                                    <div class="col-md-8" style="color:blue">
                                        @if($customer->owner_name)
                                            {{ $customer->gender }}
                                        @endif    
                                    </div>
                                </div>
                                <div class="row" style="margin-bottom:10px; margin-left:16px">
                                    <div class="col-md-4">Date of Birth</div>
                                    <div class="col-md-8" style="color:blue">{{ displayDate($customer->dob) }}</div>
                                </div>
                                @if($customer->dob)
                                    <div class="row" style="margin-bottom:10px; margin-left:16px">
                                        <div class="col-md-4">Age</div>
                                        <div class="col-md-8" style="color:blue">{{ dateDifference($customer->dob) }}</div>
                                    </div>
                                @endif
                                <div class="row" style="margin-bottom:10px; margin-left:16px">
                                    <div class="col-md-4">Aadhaar Number</div>
                                    <div class="col-md-8" style="color:blue">{{ $customer->aadhaar }}</div>
                                </div>                                

                                <h6 class="font-14" style="color:#fd3c97; margin-top:32px; margin-bottom:16px">Account Info :</h6>
                                <div class="row" style="margin-bottom:10px; margin-left:16px">
                                    <div class="col-md-4">Bank Name</div>
                                    <div class="col-md-8" style="color:blue">{{ $customer->bank_name }}</div>
                                </div>
                                <div class="row" style="margin-bottom:10px; margin-left:16px">
                                    <div class="col-md-4">Branch</div>
                                    <div class="col-md-8" style="color:blue">{{ $customer->branch }}</div>
                                </div>
                                <div class="row" style="margin-bottom:10px; margin-left:16px">
                                    <div class="col-md-4">IFSC</div>
                                    <div class="col-md-8" style="color:blue">{{ $customer->ifsc }}</div>
                                </div>
                                <div class="row" style="margin-bottom:10px; margin-left:16px">
                                    <div class="col-md-4">Account Holder</div>
                                    <div class="col-md-8" style="color:blue">{{ $customer->acc_holder }}</div>
                                </div>
                                <div class="row" style="margin-bottom:10px; margin-left:16px">
                                    <div class="col-md-4">Account Number</div>
                                    <div class="col-md-8" style="color:blue">{{ $customer->acc_number }}</div>
                                </div>
                            </div><!--end col-->
                        </div><!--end row-->
                        <hr/>

                        <div class="col-lg-12">
                            <div class="card">
                                <div class="card-body">        
                                    <h4 class="mt-0 header-title">Customer Location</h4>
                                    @if($customer->latitude && $customer->longitude)
                                        <div id="gmaps-markers" class="gmaps" style="height:400px"></div>
                                    @else
                                        <p style="color:red; padding-left:20px">Location Not Set Yet</p>
                                    @endif
                                </div><!--end card-body-->
                            </div><!--end card-->
                        </div><!--end col-->
                        <hr/>

                        <a href="{{ route('customers.edit',['id'=>$customer->id]) }}"><button class="btn btn-gradient-primary" type="button" style="width:90px; margin-right:20px">Edit</button></a>
                        @if($customer->status == "Active")
                            <a href="{{ route('customers.status',['id'=>$customer->id]) }}"><button class="btn btn-gradient-danger" type="button" style="width:120px">Set Inactive</button></a>
                        @else
                            <a href="{{ route('customers.status',['id'=>$customer->id]) }}"><button class="btn btn-gradient-primary" type="button" style="width:110px">Set Active</button></a>
                        @endif
                        <button id="delete_customer" data-id="{{ $customer->id }}" class="btn btn-gradient-danger" type="button" style="width:120px;float:right"><i class="fas fa-trash-alt"></i>&nbsp; Delete</button>

                        @if(Session::has('success'))
                            <div class="alert alert-success" style="width:60%;align:center;margin-top:20px">
                                {{ Session::get('success') }}
                            </div>
                        @endif

                    </div><!--end card-body-->
                </div><!--end card-->                
            </div><!--end col-->
        </div><!--end row-->        
        
    </div><!-- container -->

    <!-- Start of Image Upload Modal -->
    <div class="modal fade" id="modal_image_upload" tabindex="-1" role="dialog" aria-labelledby="modalImageUploadLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title mt-0" id="modal_title">Photo Update</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="form_image_upload" method="post" action="{{ route('photos.update') }}" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" id="id" name="id" value="{{ $customer->id }}">
                    <input type="hidden" id="user" name="user" value="customer">
                    <input type="hidden" id="tag" name="tag">
                    <div class="modal-body">                                                                
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group row">
                                    <input type="file" name="image_file" id="image_file" accept="image/*" class="dropify" />                                    
                                </div>
                            </div>
                        </div>   
                    </div>
                    <div class="modal-footer">
                        <input type="submit" class="btn btn-primary" id="submit" value="Upload"/>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- End of Image Upload Modal -->

    <!-- Start of Address Modal -->
    <div class="modal fade" id="modal_address" tabindex="-1" role="dialog" aria-labelledby="modalAddressLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title mt-0" id="modal_address_title">Add Address</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="form_address">
                    <input type="hidden" id="address_id" name="address_id" value="">
                    <div class="modal-body">                                                                
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group row">
                                    <label for="address_lines" class="col-sm-4 col-form-label">Address Lines <small class="text-danger font-13">*</small></label>
                                    <div class="col-sm-6">
                                        <textarea id="address_lines" rows="3" class="form-control"></textarea>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="district" class="col-sm-4 col-form-label">District <small class="text-danger font-13">*</small></label>
                                    <div class="col-sm-6">                                        
                                        <select class="form-control" id="district">
                                            <option value="">Select</option>
                                            @foreach($districts as $district)
                                                <option value="{{ $district->name }}">{{ $district->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="state" class="col-sm-4 col-form-label">State <small class="text-danger font-13">*</small></label>
                                    <div class="col-sm-6">
                                        <input type="text" id="state" class="form-control" disabled>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="pincode" class="col-sm-4 col-form-label">Pin Code <small class="text-danger font-13">*</small></label>
                                    <div class="col-sm-6">
                                        <input type="text" id="pincode" maxlength="6" class="form-control">
                                    </div>
                                </div>
                            </div>
                        </div>   
                    </div>
                    <div class="modal-footer">
                        <input type="button" id="close_address" class="btn btn-secondary" data-dismiss="modal" value="Close" />
                        <input type="button" id="submit_address" class="btn btn-primary mx-3" value="Add Address"/>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- End of Address Modal -->
 
@stop

@push('custom-scripts')
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.js"></script>
    <script src="{{ asset('assets/js/helper.js') }}"></script>
    <script>
        $(document).ready(function () {
            $.ajaxSetup({
                headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
            });
            
            $('body').on('click', '#shop_photo', function (event) {
                event.preventDefault();                
                $('#modal_title').html("Shop Photo Update");
                $('#tag').val("shop");
                $('#image_file').val("");
            });

            $('body').on('click', '#profile_image', function (event) {
                event.preventDefault();
                $('#modal_title').html("Profile Image Update");
                $('#tag').val("profile");
                $('#image_file').val("");
            });

            $('body').on('click', '#submit', function (event) {
                var image_name = $("#image_file").val();
                if(image_name) {
                    if(!isExtensionValid(image_name)) {
                        Swal.fire('Attention','Uploaded file is not an image','error');
                        event.preventDefault();
                    }                    
                }
                else {
                    Swal.fire('Attention','Please Select Image to Update','error');
                    event.preventDefault();
                }
            });

            $('body').on('click', '#add_address', function (event) {
                event.preventDefault();
                $('#modal_address_title').html("Add Address");
                $('#address_id').val("");
                $('#address_lines').val("");
                $('#state').val("");
                $('#district').val("");
                $('#pincode').val("");
                $('#submit_address').val("Add Address");
                $('#modal_address').modal('show');
            });

            $('body').on('click', '.edit_address', function (event) {
                event.preventDefault();
                var id = $(this).data('address-id');
                let url = "{{ route('address.edit', ':id') }}".replace(':id', id);
                $.get(url, function (data) {
                    var address = data.address;
                    $('#modal_address_title').html("Edit Address");
                    $('#address_id').val(address.id);
                    $('#address_lines').val(address.address_lines);
                    $('#district').val(address.district);
                    $('#state').val(address.state);
                    $('#pincode').val(address.pincode);
                    $('#submit_address').val("Update Address");
                    $('#modal_address').modal('show');
                });                                
            });

            $('body').on('change', '#district', function (event) {            
                var district = $('#district').val();
                $('#state').val('');                
                if(district) {
                    let url = "{{ route('states.get', ':district') }}".replace(':district', district);
                    $.get(url, function (state) {
                        $('#state').val(state);
                    });
                }
            });

            $('body').on('keypress', '#pincode', function (event) {
                const key = String.fromCharCode(event.keyCode);
                if (key.match(/[^0-9]/g)) return false;
                return true;
            });

            $("#submit_address").click(function() {                      
                var id = $("#address_id").val();
                var addressLines = $("#address_lines").val();
                var district = $("#district").val();
                var state = $("#state").val();
                var pincode = $("#pincode").val();
                var successText = "Address has been updated!";
                if(!addressLines) {
                    Swal.fire('Attention','Please Enter Address','error');
                    return;
                }
                else if(!district) {
                    Swal.fire('Attention','Please Select District','error');
                    return;
                }
                else if(!pincode) {
                    Swal.fire('Attention','Please Enter Pin Code','error');
                    return;
                }
                else if(pincode.length != 6) {
                    Swal.fire('Attention','Pin Code should be in 6 digits','error');
                    return;
                }
                else if(!id) {
                    id="0";
                    successText = "Address has been added!";
                }
                
                var cust_id = "{{ $customer->id }}";
                var cust_name = "{{ $customer->customer_name }}";
                $.ajax({                    
                    url: "{{ route('address.store') }}",
                    type: "POST",
                    data: {
                        id            : id,
                        customer_id   : cust_id,
                        customer_name : cust_name,
                        address_lines : addressLines,
                        district      : district,
                        state         : state,
                        pincode       : pincode
                    },
                    dataType: 'json',
                    success: function (data) {
                        $('#form_address').trigger("reset");
                        $('#modal_address').modal('hide');
                        Swal.fire({
                                title:'Success!',
                                text:successText,
                                type:'success'
                            }
                        )
                        .then(
                            function() { 
                                window.location.reload(true);
                            }
                        );  
                    },
                    error: function (data, textStatus, errorThrown) {
                        var errorText = data.responseText;                        
                        Swal.fire({
                                title:'Sorry!',
                                text:errorText,
                                type:'warning',                                
                                confirmButtonColor: '#FF0000'
                            }
                        );
                    }
                });
            });
            
            $('#delete_customer').click(function (event) {
                event.preventDefault();
                var id = $(this).data('id');
                Swal.fire({
                    title: 'Are you sure?',
                    text: "Do you want to delete this customer?",
                    type: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '$success',
                    cancelButtonColor: '$danger',
                    confirmButtonText: 'Yes, delete!'
                })
                .then(function(result) {
                    if (result.value) {                        
                        $.ajax({
                            url:"{{ route('customers.destroy', ':id') }}".replace(':id', id),
                            type: 'DELETE',
                            success: function (data) { 
                                Swal.fire('Deleted!','Customer has been deleted.','success')
                                    .then(function() { window.location = document.referrer;} );
                            }
                        })
                    }
                })
            });

            !function($) {
                "use strict";
                var GoogleMap = function() {};

                //creates map with markers
                GoogleMap.prototype.createMarkers = function($container,$latitude,$longitude,$title,$content) {
                    var map = new GMaps({
                        div: $container,
                        lat: $latitude,
                        lng: $longitude
                    });

                    map.addMarker({
                        lat: $latitude,
                        lng: $longitude,
                        title: $title,
                        infoWindow: {
                        content: '<p>' + $content +'</p>'
                        }
                    });

                    return map;
                },

                //init
                GoogleMap.prototype.init = function($container,$latitude,$longitude,$title,$content) {
                    var $this = this;
                    $(document).ready(function(){
                        $this.createMarkers($container,$latitude,$longitude,$title,$content);
                    });
                },
            //init
            $.GoogleMap = new GoogleMap, $.GoogleMap.Constructor = GoogleMap
            }(window.jQuery),

            //initializing 
            function($) {
                "use strict";
                var $container = '#gmaps-markers';
                var $latitude = '0';
                var $longitude = '0';
                @if($customer->latitude) $latitude = {{ $customer->latitude }}; @endif
                @if($customer->longitude) $longitude = {{ $customer->longitude }}; @endif
                var $title = '{{ $customer->customer_name }}';
                var $content = '{{ $customer->customer_name }}';
                $.GoogleMap.init($container,$latitude,$longitude,$title,$content);
            }(window.jQuery);

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
    <!-- google maps api -->
    <script async defer src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDeyGhUI-IMTft5Z_O342XQ4oyZdlGcvs8"></script>
    <!-- Gmaps file -->
    <script src="{{ asset('plugins/gmaps/gmaps.min.js') }}"></script> 
@stop