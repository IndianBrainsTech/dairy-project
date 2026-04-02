@extends('app-layouts.admin-master')

@section('title', 'Employee')

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
                @slot('title') View Employee @endslot
                @slot('item1') Masters @endslot
                @slot('item2') Profiles @endslot
                @slot('item3') Employees @endslot
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
                                <img src="{{ asset('mystorage/employees/' . $employee->photo) }}" alt="" class=" mx-auto  d-block" height="200">
                                <p style="text-align:center;margin-top:4px">Photo &nbsp;&nbsp;&nbsp;<a href="" id="photo" class="mr-2" data-toggle="modal" data-animation="bounce" data-target="#modal_image_upload" data-id="photo"><i class="fas fa-edit text-info font-16"></i></a></p>
                            </div><!--end col-->

                            <div class="col-lg-8">
                                <div class="single-pro-detail">
                                    <h3 class="pro-title">{{ $employee->name }}</h3>
                                    <p class="mb-2 font-14 text-dark">{{ $employee->code }}</p>
                                    <p class="mb-2 font-16 text-dark">
                                        {{ $employee->role->role_name }}
                                        @if($employee->role->short_name)
                                            ({{ $employee->role->short_name }})
                                        @endif
                                    </p>
                                    @if($employee->remarks)
                                        <p class="text-muted mb-0">{{ $employee->remarks }}</p> <br/>
                                    @endif
                                    <p class="mb-2 font-14 text-dark">
                                        Reporting Head : {{ $reporting_head }}                                       
                                    </p>
                                </div>
                            </div><!--end col-->                                            
                        </div><!--end row-->

                        <div class="row">
                            <div class="col-lg-5">
                                <h6 class="font-14" style="color:#fd3c97; margin-top:16px; margin-bottom:16px">Profile Info :</h6>
                                <div class="row" style="margin-bottom:10px; margin-left:16px">
                                    <div class="col-md-4">User Name</div>
                                    <div class="col-md-8" style="color:blue">{{ $employee->user_name }}</div>
                                </div>
                                <div class="row" style="margin-bottom:10px; margin-left:16px">
                                    <div class="col-md-4">Father Name</div>
                                    <div class="col-md-8" style="color:blue">{{ $employee->father_name }}</div>
                                </div>
                                <div class="row" style="margin-bottom:10px; margin-left:16px">
                                    <div class="col-md-4">Gender</div>
                                    <div class="col-md-8" style="color:blue">{{ $employee->gender }}</div>
                                </div>
                                <div class="row" style="margin-bottom:10px; margin-left:16px">
                                    <div class="col-md-4">Date of Birth</div>
                                    <div class="col-md-8" style="color:blue">{{ displayDate($employee->dob) }}</div>
                                </div>
                                @if($employee->dob)
                                    <div class="row" style="margin-bottom:10px; margin-left:16px">
                                        <div class="col-md-4">Age</div>
                                        <div class="col-md-8" style="color:blue">{{ dateDifference($employee->dob) }}</div>
                                    </div>
                                @endif
                                <div class="row" style="margin-bottom:10px; margin-left:16px">
                                    <div class="col-md-5">Aadhaar Number</div>
                                    <div class="col-md-7" style="color:blue; margin-left:-32px">{{ $employee->aadhaar_num }}</div>
                                </div>         
                                <div class="row" style="margin-bottom:10px; margin-left:16px">
                                    <div class="col-md-6">Driving License Number</div>
                                    <div class="col-md-6" style="color:blue; margin-left:-24px">{{ $employee->license_num }}</div>
                                </div>
                                @if($employee->license_num)
                                    <div class="row" style="margin-bottom:10px; margin-left:16px">
                                        <div class="col-md-4">License Validity</div>
                                        <div class="col-md-8" style="color:blue">{{ displayDate($employee->license_validity) }}</div>
                                    </div>
                                @endif
                                <div class="row" style="margin-bottom:10px; margin-left:16px">
                                    <div class="col-md-4">Blood Group</div>
                                    <div class="col-md-8" style="color:blue">{{ $employee->blood_group }}</div>
                                </div>
                                <div class="row" style="margin-bottom:10px; margin-left:16px">
                                    <div class="col-md-4">Date of Join</div>
                                    <div class="col-md-8" style="color:blue">
                                        {{ displayDate($employee->doj) }}
                                        <!-- @if($employee->doj)                                            
                                            ({{ dateDifference($employee->doj) }})                                            
                                        @endif -->
                                    </div>
                                </div>
                            </div><!--end col-->  

                            <div class="col-lg-7">
                                <h6 class="font-14" style="color:#fd3c97; margin-top:16px; margin-bottom:16px">Address & Contacts :</h6>
                                <div class="row" style="margin-bottom:10px; margin-left:16px">
                                    <div class="col-md-4">Address</div>
                                    <div class="col-md-8" style="color:blue">{{ $employee->address }}</div>
                                </div>
                                <div class="row" style="margin-bottom:10px; margin-left:16px">
                                    <div class="col-md-4">District</div>
                                    <div class="col-md-8" style="color:blue">{{ $employee->district }}</div>
                                </div>
                                <div class="row" style="margin-bottom:10px; margin-left:16px">
                                    <div class="col-md-4">State</div>
                                    <div class="col-md-8" style="color:blue">{{ $employee->state }}</div>
                                </div>
                                <div class="row" style="margin-bottom:10px; margin-left:16px">
                                    <div class="col-md-4">Landmark</div>
                                    <div class="col-md-8" style="color:blue">{{ $employee->landmark }}</div>
                                </div>
                                <div class="row" style="margin-bottom:10px; margin-left:16px">
                                    <div class="col-md-4">Pin Code</div>
                                    <div class="col-md-8" style="color:blue">{{ $employee->pincode }}</div>
                                </div>
                                <div class="row" style="margin-bottom:10px; margin-left:16px">
                                    <div class="col-md-4">Email ID</div>
                                    <div class="col-md-8" style="color:blue">{{ $employee->email_id }}</div>
                                </div>
                                <div class="row" style="margin-bottom:10px; margin-left:16px">
                                    <div class="col-md-4">Mobile Number</div>
                                    <div class="col-md-8" style="color:blue">{{ $employee->mobile_num }}</div>
                                </div>
                                <div class="row" style="margin-bottom:10px; margin-left:16px">
                                    <div class="col-md-4">Alternate Number</div>
                                    <div class="col-md-8" style="color:blue">{{ $employee->alternate_num }}</div>
                                </div>

                                <h6 class="font-14" style="color:#fd3c97; margin-top:32px; margin-bottom:16px">Account Info :</h6>
                                <div class="row" style="margin-bottom:10px; margin-left:16px">
                                    <div class="col-md-4">Bank Name</div>
                                    <div class="col-md-8" style="color:blue">{{ $employee->bank_name }}</div>
                                </div>
                                <div class="row" style="margin-bottom:10px; margin-left:16px">
                                    <div class="col-md-4">Branch</div>
                                    <div class="col-md-8" style="color:blue">{{ $employee->branch }}</div>
                                </div>
                                <div class="row" style="margin-bottom:10px; margin-left:16px">
                                    <div class="col-md-4">IFSC</div>
                                    <div class="col-md-8" style="color:blue">{{ $employee->ifsc }}</div>
                                </div>
                                <div class="row" style="margin-bottom:10px; margin-left:16px">
                                    <div class="col-md-4">Account Holder</div>
                                    <div class="col-md-8" style="color:blue">{{ $employee->acc_holder }}</div>
                                </div>
                                <div class="row" style="margin-bottom:10px; margin-left:16px">
                                    <div class="col-md-4">Account Number</div>
                                    <div class="col-md-8" style="color:blue">{{ $employee->acc_number }}</div>
                                </div>
                            </div><!--end col-->                                            
                        </div><!--end row-->
                        <hr/>

                        <a href="{{ route('employees.edit',['id'=>$employee->id]) }}"><button class="btn btn-gradient-primary" type="button" style="width:90px; margin-right:20px">Edit</button></a>
                        @if($employee->status == "Active")
                            <a href="{{ route('employees.status',['id'=>$employee->id]) }}"><button class="btn btn-gradient-danger" type="button" style="width:120px">Set Inactive</button></a>
                        @else
                            <a href="{{ route('employees.status',['id'=>$employee->id]) }}"><button class="btn btn-gradient-primary" type="button" style="width:110px">Set Active</button></a>
                        @endif
                        
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
                    <input type="hidden" id="id" name="id" value="{{ $employee->id }}">
                    <input type="hidden" id="user" name="user" value="employee">
                    <input type="hidden" id="tag" name="tag" value="NIL">
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