@extends('app-layouts.admin-master')

@section('title', 'Bank Accounts')

@section('headerStyle')
    <link href="{{ asset('plugins/sweet-alert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('plugins/animate/animate.css') }}" rel="stylesheet" type="text/css">
@stop

@section('content')
    <div class="container-fluid">
        <!-- Page-Title -->
        <div class="row">
            <div class="col-sm-12">
                @component('app-components.breadcrumb-2')
                    @slot('title') Bank Accounts @endslot
                    @slot('item1') Masters @endslot
                @endcomponent
            </div>
        </div>
        <!-- end page title end breadcrumb -->

        <div class="row">
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-body">
                        <div style="width:100%">
                            <div style="width:60%;float:left"><h4 class="header-title mt-0">Bank Account Masters</h4></div>
                            <div style="width:40%;float:left"><button type="button" id="add_account" class="btn btn-gradient-primary px-4 float-right mt-0 mb-3" data-toggle="modal" data-animation="bounce" data-target="#modal_account"><i class="mdi mdi-plus-circle-outline mr-2"></i>Add</button></div>
                        </div>
                        <div class="table-responsive dash-social">
                            <table id="datatable" class="table table-bordered">
                                <thead class="thead-light">
                                <tr>
                                    <th class="text-center">S.No</th>
                                    <th>Bank</th>
                                    <th class="text-center">Action</th>
                                </tr>
                                </thead>
                                <tbody>
                                    @foreach($bank_masters as $bank_master)
                                        <tr>
                                            <td class="text-center">{{ $loop->index + 1 }}</td>
                                            <td>{{ $bank_master->display_name }}</td>
                                            <td class="text-center">
                                                <a href="" id="view_account" class="mr-2" data-toggle="modal" data-animation="bounce" data-target="#modal_account" data-id="{{ $bank_master->id }}"><i class="dripicons-preview text-primary font-20"></i></a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div><!--end card-body--> 
                </div><!--end card--> 
            </div> <!--end col-->
        </div><!--end row--> 
    </div><!-- container -->

    <!-- Start of Account Modal -->
    <div class="modal fade" id="modal_account" tabindex="-1" role="dialog" aria-labelledby="modalAccountLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title mt-0" id="modal_title">Add Bank Account</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="form_account">
                    <input type="hidden" id="account_id" name="account_id" value="0">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group row">
                                    <label for="bank_name" class="col-sm-4 col-form-label">Bank Name <small class="text-danger font-13">*</small></label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control" id="bank_name">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="acc_holder" class="col-sm-4 col-form-label">Account Holder <small class="text-danger font-13">*</small></label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control" id="acc_holder">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="acc_number" class="col-sm-4 col-form-label">Account Number <small class="text-danger font-13">*</small></label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control alpha-num" id="acc_number" maxlength="20">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="ifsc" class="col-sm-4 col-form-label">IFSC <small class="text-danger font-13">*</small></label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control alpha-num" id="ifsc" maxlength="11">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="branch" class="col-sm-4 col-form-label">Branch <small class="text-danger font-13">*</small></label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control" id="branch">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="display_name" class="col-sm-4 col-form-label">Bank (Nick) Name<small class="text-danger font-13">*</small></label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control" id="display_name">
                                    </div>
                                </div>
                            </div>
                        </div>   
                    </div>
                    <div class="modal-footer">
                        <input type="reset" class="btn btn-secondary" data-dismiss="modal" value="Close" />
                        <input type="submit" class="btn btn-primary" id="submit" value="Save"/>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- End of Account Modal -->  
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
            
            $('body').on('click', '#add_account', function (event) {
                event.preventDefault();
                $('#modal_title').html("Add Bank Account");
                $('#account_id').val("0");
                $('#bank_name').val("");
                $('#acc_holder').val("");
                $('#acc_number').val("");
                $('#ifsc').val("");
                $('#branch').val("");
                $('#display_name').val("");
                $('#submit').val("Save");
                $('#modal_account').modal('show');
                setControlsDisabled(false);
            });

            $('body').on('click', '#view_account', function (event) {
                event.preventDefault();
                var id = $(this).data('id');
                $.get('/bank_account/' + id, function (data) {
                    data = data.bank_account;
                    $('#modal_title').html("View Bank Account");
                    $('#account_id').val(data.id);
                    $('#bank_name').val(data.bank_name);
                    $('#acc_holder').val(data.acc_holder);
                    $('#acc_number').val(data.acc_number);
                    $('#ifsc').val(data.ifsc);
                    $('#branch').val(data.branch);
                    $('#display_name').val(data.display_name);
                    $('#submit').val("Edit");
                    $('#modal_account').modal('show');
                    setControlsDisabled(true);
                })
            });

            function setControlsDisabled(flag) {
                $('#bank_name').prop('disabled', flag);
                $('#acc_holder').prop('disabled', flag);
                $('#acc_number').prop('disabled', flag);
                $('#ifsc').prop('disabled', flag);
                $('#branch').prop('disabled', flag);
                $('#display_name').prop('disabled', flag);
            }

            $(".alpha-num").keypress(function(e) {
                var key = String.fromCharCode(e.keyCode);

                // Check the maxlength attribute of the input field
                var maxLength = $(this).attr('maxlength');
                if (maxLength && $(this).val().length >= maxLength) {
                    return false;
                }

                // Check if the entered character matches the regular expression for alphanumeric characters
                if (key.match(/[^a-zA-Z0-9]/g))
                    return false;

                // Convert the entered character to uppercase
                if (key.match(/[a-zA-Z]/g)) {
                    e.preventDefault();
                    if ($(this).val().length < maxLength) {
                        $(this).val($(this).val() + key.toUpperCase());
                    }
                }

                return true;
            });

            $('body').on('click', '#submit', function (event) {
                event.preventDefault();

                let action = $("#submit").val();
                if(action == "Edit") {
                    $('#modal_title').html("Edit Bank Account");
                    $('#submit').val("Update");
                    setControlsDisabled(false);
                }
                else {
                    let id = $("#account_id").val();
                    let bank_name = $("#bank_name").val();
                    let acc_holder = $("#acc_holder").val();
                    let acc_number = $("#acc_number").val();
                    let ifsc = $("#ifsc").val();
                    let branch = $("#branch").val();
                    let display_name = $("#display_name").val();

                    if(bank_name === "" || bank_name === undefined) {
                        Swal.fire('Sorry!','Please Enter Bank Name','warning');
                    }
                    else if(acc_holder === "" || acc_holder === undefined) {
                        Swal.fire('Sorry!','Please Enter Account Holder Name','warning');
                    }
                    else if(acc_number === "" || acc_number === undefined) {
                        Swal.fire('Sorry!','Please Enter Account Number','warning');
                    }
                    else if(ifsc === "" || ifsc === undefined) {
                        Swal.fire('Sorry!','Please Enter IFSC','warning');
                    }
                    else if(ifsc.length != 11) {
                        Swal.fire('Sorry!','Please Check IFSC','warning');
                    }
                    else if(branch === "" || branch === undefined) {
                        Swal.fire('Sorry!','Please Enter Branch','warning');
                    }
                    else if(display_name === "" || display_name === undefined) {
                        Swal.fire('Sorry!','Please Enter Bank Name (Short Form)','warning');
                    }
                    else {
                        let successText = "";
                        if(action == "Save")
                            successText = "Bank Account Added Successfully!";
                        else if (action == "Update")
                            successText = "Bank Account Updated Successfully!";

                        $.ajax({
                            url: '/bank_account/' + id,
                            type: "POST",
                            data: {
                                id: id,
                                bank_name: bank_name,
                                acc_holder: acc_holder,
                                acc_number: acc_number,
                                ifsc: ifsc,
                                branch: branch,
                                display_name: display_name
                            },
                            dataType: 'json',
                            success: function (data) {
                                Swal.fire({
                                        title: 'Success!',
                                        text: successText,
                                        icon: 'success'
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
                                if(errorText.indexOf("Duplicate entry") !== -1) {
                                    if(errorText.indexOf("key 'acc_number_unique'") !== -1)
                                        errorText = "Account Number Already Exists";
                                    else if(errorText.indexOf("key 'display_name_unique'") !== -1)
                                        errorText = "Bank (Nick) Name Already Exists";
                                }
                                Swal.fire({
                                        title: 'Sorry!',
                                        text: errorText,
                                        icon: 'warning',
                                        confirmButtonColor: '#FF0000'
                                    }
                                );
                            }
                        });
                    }
                }
            });

        });  
    </script> 
@endpush 

@section('footerScript')
    <!-- Sweet-Alert  -->
    <script src="{{ asset('plugins/sweet-alert2/sweetalert2.min.js') }}"></script>
    <script src="{{ asset('assets/pages/jquery.sweet-alert.init.js') }}"></script>
    <script src="{{ asset('assets/js/jquery.core.js') }}"></script>
@stop
