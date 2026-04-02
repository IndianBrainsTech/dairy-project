@extends('app-layouts.admin-master')

@section('title', 'Vehicles')

@section('headerStyle')
    <link href="{{ asset('plugins/sweet-alert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('plugins/animate/animate.css') }}" rel="stylesheet" type="text/css">    
    <link href="{{ asset('plugins/datatables/dataTables.bootstrap4.min.css') }}" rel="stylesheet" type="text/css" />
@stop

@section('content')
    <div class="container-fluid">
        <!-- Page-Title -->
        <div class="row">
            <div class="col-sm-12">
                @component('app-components.breadcrumb-3')
                    @slot('title') Vehicles @endslot
                    @slot('item1') Masters @endslot
                    @slot('item2') Transport @endslot
                @endcomponent
            </div>
        </div>
        <!-- end page title end breadcrumb -->

        <div class="row">
            <div class="col-lg-10">
                <div class="card">
                    <div class="card-body">
                        <div style="width:100%">
                            <div style="width:60%; float:left">
                                <h4 class="header-title mt-0">Vehicles &nbsp;
                                    <button type="button" class="btn btn-pink btn-round " style="font-weight:500">
                                        {{ count($vehicles) }}
                                    </button>
                                </h4>
                            </div>
                            <div style="width:40%; float:left">
                                <button type="button" id="add_vehicle" class="btn btn-gradient-primary px-4 float-right mt-0 mb-3" data-toggle="modal" data-animation="bounce" data-target="#modal_vehicle"><i class="mdi mdi-plus-circle-outline mr-2"></i>Add Vehicle</button>
                            </div>
                        </div>
                        <div class="table-responsive dash-social">
                            <table id="datatable" class="table table-bordered dt-responsive nowrap" style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                                <thead class="thead-light">
                                <tr>
                                    <th>S.No</th>
                                    <th>Vehicle Number</th>
                                    <th>Vehicle Type</th>
                                    <th>Make</th>
                                    <th>Model</th>
                                    <th>Action</th>
                                </tr>
                                </thead>
                                <tbody>
                                    @foreach($vehicles as $vehicle)
                                        <tr>
                                            <td>{{ $loop->index + 1 }}</td>
                                            <td>{{ $vehicle->vehicle_number }}</td>
                                            <td>{{ $vehicle->vehicle_type }}</td>
                                            <td>{{ $vehicle->make }}</td>
                                            <td>{{ $vehicle->model }}</td>
                                            <td>
                                                <a href="" id="edit_vehicle" class="mr-2" data-toggle="modal" data-animation="bounce" data-target="#modal_vehicle" data-id="{{ $vehicle->id }}"><i class="fas fa-edit text-info font-16"></i></a>
                                                <a href="" id="delete_vehicle" data-id="{{ $vehicle->id }}"><i class="fas fa-trash-alt text-danger font-16"></i></a>
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

    <!-- Start of Vehicle Modal -->
    <div class="modal fade" id="modal_vehicle" tabindex="-1" role="dialog" aria-labelledby="modalVehicleLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title mt-0" id="modal_title">Add Vehicle</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="form_vehicle">
                    <input type="hidden" id="vehicle_id" name="vehicle_id" value="">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group row">
                                    <label for="vehicle_number" class="col-sm-4 col-form-label">Vehicle Number <small class="text-danger font-13">*</small></label>
                                    <div class="col-sm-6">
                                        <input type="text" id="vehicle_number" class="form-control" oninput="this.value = this.value.toUpperCase()">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="vehicle_type" class="col-sm-4 col-form-label">Vehicle Type <small class="text-danger font-13">*</small></label>
                                    <div class="col-sm-6">
                                        <select id="vehicle_type" class="form-control">
                                            <option value="">Select Type</option>
                                            <option value="Lorry">Lorry</option>
                                            <option value="Truck">Truck</option>
                                            <option value="Van">Van</option>
                                            <option value="Two Wheeler">Two Wheeler</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="make" class="col-sm-4 col-form-label">Make</label>
                                    <div class="col-sm-6">
                                        <input type="text" id="make" class="form-control">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="model" class="col-sm-4 col-form-label">Model</label>
                                    <div class="col-sm-6">
                                        <input type="text" id="model" class="form-control">
                                    </div>
                                </div>
                            </div>
                        </div>   
                    </div>
                    <div class="modal-footer">
                        <input type="reset" class="btn btn-secondary" data-dismiss="modal" value="Close" />
                        <input type="submit" class="btn btn-primary mx-2" id="submit" value="Add Vehicle"/>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- End of Vehicle Modal -->
@stop

@push('custom-scripts')
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.js"></script>
    <script src="{{ asset('assets/js/input-restriction.js') }}"></script>
    <script>
        $(document).ready(function () {
            $.ajaxSetup({
                headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
            });

            $('#datatable').dataTable( {
                "lengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
                "pageLength": 25
            } );

            $('#vehicle_number').on('keypress', restrictToVehicleNumber);

            $('body').on('click', '#add_vehicle', function (event) {
                event.preventDefault();
                $('#modal_title').html("Add Vehicle");
                $('#vehicle_id').val("");
                $('#vehicle_number').val("");
                $('#vehicle_type').val("");
                $('#make').val("");
                $('#model').val("");
                $('#submit').val("Add Vehicle");
                $('#modal_vehicle').modal('show');
            });

            $('body').on('click', '#edit_vehicle', function (event) {
                event.preventDefault();
                var id = $(this).data('id');
                $.get('/vehicle/' + id, function (data) {
                    $('#modal_title').html("Edit Vehicle");
                    $('#vehicle_id').val(data.vehicle.id);
                    $('#vehicle_number').val(data.vehicle.vehicle_number);
                    $('#vehicle_type').val(data.vehicle.vehicle_type);
                    $('#make').val(data.vehicle.make);
                    $('#model').val(data.vehicle.model);
                    $('#submit').val("Update Vehicle");
                    $('#modal_vehicle').modal('show');
                })
            });

            $('body').on('click', '#delete_vehicle', function (event) {
                event.preventDefault();
                var id = $(this).data('id');
                Swal.fire({
                    title: 'Are you sure?',
                    text: "You won't be able to revert this!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '$success',
                    cancelButtonColor: '$danger',
                    confirmButtonText: 'Yes, delete it!'
                })
                .then(function(result) {
                    if (result.value) {
                        $.ajax({
                            url:'/vehicle/delete/' + id,
                            type: 'GET',
                            success: function (data) { 
                                Swal.fire('Deleted!','Vehicle has been deleted.','success')
                                    .then(function() { window.location.reload(true);} );
                            }
                        })
                    }
                })
            });

            $('body').on('click', '#submit', function (event) {
                event.preventDefault();
                var id = $("#vehicle_id").val();
                var number = $("#vehicle_number").val();
                var type = $("#vehicle_type").val();
                var make = $("#make").val();
                var model = $("#model").val();
                var successText = "Vehicle has been updated!";
                if(!number) {
                    Swal.fire('Attention','Please Enter Vehicle Number','error');
                    return;
                }
                if(!type) {
                    Swal.fire('Attention','Please Select Vehicle Type','error');
                    return;
                }
                else if(!id) {
                    id = 0;
                    successText = "Vehicle has been added!";
                }
                $.ajax({
                    url: '/vehicle/' + id,
                    type: "POST",
                    data: {
                        id              : id,
                        vehicle_number  : number,
                        vehicle_type    : type,
                        make            : make,
                        model           : model
                    },
                    dataType: 'json',
                    success: function (data) {
                        $('#form_vehicle').trigger("reset");
                        $('#modal_vehicle').modal('hide');
                        Swal.fire({
                                title : 'Success!',
                                text  : successText,
                                type  : 'success'
                            }
                        )
                        .then(
                            function() { 
                                window.location.reload(true);
                            }
                        );
                    },
                    error: function (data, textStatus, errorThrown) {
                        // var errorText = data.responseText;
                        console.log(data.responseText);
                        var errorText = "An Error Occurred";
                        if(data.responseText.indexOf("Duplicate entry") !== -1) {
                            errorText = "Vehicle Number Already Exists";
                        }
                        Swal.fire({
                                title : 'Sorry!',
                                text  : errorText,
                                type  : 'warning',
                                confirmButtonColor: '#FF0000'
                            }
                        );
                    }
                });
            });

        });  
    </script> 
@endpush 

@section('footerScript')
    <!-- Sweet-Alert  -->
    <script src="{{ asset('plugins/sweet-alert2/sweetalert2.min.js') }}"></script>
    <script src="{{ asset('assets/pages/jquery.sweet-alert.init.js') }}"></script>
    <!-- Required datatable js -->
    <script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables/dataTables.bootstrap4.min.js') }}"></script>
    <!-- Responsive examples -->
    <script src="{{ asset('plugins/datatables/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables/responsive.bootstrap4.min.js') }}"></script>
@stop