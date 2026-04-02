@extends('app-layouts.admin-master')

@section('title', 'Routes')

@section('headerStyle')
    <link href="{{ asset('plugins/sweet-alert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('plugins/animate/animate.css') }}" rel="stylesheet" type="text/css">
    <!-- DataTables -->
    <link href="{{ asset('plugins/datatables/dataTables.bootstrap4.min.css') }}" rel="stylesheet" type="text/css" />
@stop

@section('content')
    <div class="container-fluid">
        <!-- Page-Title -->
        <div class="row">
            <div class="col-sm-12">
                @component('app-components.breadcrumb-3')
                    @slot('title') Routes @endslot
                    @slot('item1') Masters @endslot
                    @slot('item2') Places @endslot
                @endcomponent
            </div>
        </div>
        <!-- end page title end breadcrumb -->

        <div class="row">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-body">                                                    
                        <div style="width:100%">
                            <div style="width:60%;float:left">
                                <h4 class="header-title mt-0">Routes &nbsp;
                                    <button type="button" class="btn btn-pink btn-round " style="font-weight:500">
                                        {{ count($routes) }}
                                    </button>
                                </h4>
                            </div>
                            <div style="width:40%;float:left"><button type="button" id="add_route" class="btn btn-gradient-primary px-4 float-right mt-0 mb-3" data-toggle="modal" data-animation="bounce" data-target="#modal_route"><i class="mdi mdi-plus-circle-outline mr-2"></i>Add Route</button></div>
                        </div>
                        <div class="table-responsive dash-social">
                            <table id="datatable" class="table table-bordered dt-responsive nowrap" style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                                <thead class="thead-light">
                                <tr>
                                    <th class="text-center">S.No</th>
                                    <th>Route</th>
                                    <th>District</th>
                                    <th class="text-center">Action</th>
                                </tr>
                                </thead>
                                <tbody>
                                    @foreach($routes as $route)
                                        <tr>                                                
                                            <td class="text-center">{{ $loop->index + 1 }}</td>
                                            <td>{{ $route->name }}</td>
                                            <td>{{ $route->district->name }}</td>                                                
                                            <td class="text-center">
                                                <a href="" id="edit_route" class="mr-2" data-toggle="modal" data-animation="bounce" data-target="#modal_route" data-id="{{ $route->id }}"><i class="fas fa-edit text-info font-16"></i></a>
                                                <!-- @can('delete_route') -->
                                                <a href="" id="delete_route" data-id="{{ $route->id }}"><i class="fas fa-trash-alt text-danger font-16"></i></a>
                                                <!-- @endcan -->
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

    <!-- Start of Route Modal -->
    <div class="modal fade" id="modal_route" tabindex="-1" role="dialog" aria-labelledby="modalRouteLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title mt-0" id="modal_route_title">Add Route</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="form_route">
                    <input type="hidden" id="route_id" name="route_id" value="">
                    <div class="modal-body">                                                                
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group row">
                                    <label for="name" class="col-sm-4 col-form-label">Route Name <small class="text-danger font-13">*</small></label>
                                    <div class="col-sm-6">
                                        <input type="text" class="form-control" id="name" required="" name="name">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="district" class="col-sm-4 col-form-label">District <small class="text-danger font-13">*</small></label>
                                    <div class="col-sm-6">
                                        <select class="form-control" id="select_district">                                            
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>   
                    </div>
                    <div class="modal-footer">
                        <input type="reset" class="btn btn-secondary" data-dismiss="modal" value="Close" />
                        <input type="submit" class="btn btn-primary" id="submit" value="Add Route"/>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- End of Route Modal -->  
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

            $('#datatable').dataTable( {
                "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
                "pageLength": 25
            } );

            function loadDistricts() {
                if ($('#select_district').has('option').length == 0) {                    
                    $.get("{{ route('districts.list') }}", function (data) {                                       
                        data.districts.forEach(district => {
                            $('#select_district').append($('<option>', { value: district.id, text: district.name }));
                        });
                    })  
                }
            }
            
            $('body').on('click', '#add_route', function (event) {
                event.preventDefault();
                loadDistricts();
                $('#modal_route_title').html("Add Route");
                $('#route_id').val("");
                $('#name').val("");
                $('#submit').val("Add Route");
                $('#modal_route').modal('show');
            });

            $('body').on('click', '#edit_route', function (event) {
                event.preventDefault();                
                let id = $(this).data('id');
                let url = "{{ route('routes.edit', ':id') }}".replace(':id', id);
                $.get(url, function (data) {
                    loadDistricts();
                    $('#modal_route_title').html("Edit District");
                    $('#route_id').val(data.route.id);
                    $('#name').val(data.route.name);
                    $('#submit').val("Update");
                    $('#modal_route').modal('show');                    
                    $('#select_district').val(data.route.district_id);    
                })
            });

            $('body').on('click', '#delete_route', function (event) {
                event.preventDefault();  
                var id = $(this).data('id');                   
                Swal.fire({
                    title: 'Are you sure?',
                    text: "You won't be able to revert this!",
                    type: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '$success',
                    cancelButtonColor: '$danger',
                    confirmButtonText: 'Yes, delete it!'
                })
                .then(function(result) {                    
                    if (result.value) {
                        $.ajax({                            
                            url:"{{ route('routes.destroy', ':id') }}".replace(':id', id),
                            type: 'DELETE',
                            success: function (data) { 
                                Swal.fire('Deleted!','Route has been deleted.','success')
                                    .then(function() { window.location.reload(true);} );
                            }
                        })                        
                    }
                })
            });

            $('body').on('click', '#submit', function (event) {
                event.preventDefault();                             
                var id = $("#route_id").val();
                var name = $("#name").val();
                var district_id = $("#select_district").val();    
                var successText = "Route has been updated!";
                if(!name) {
                    Swal.fire('Attention','Please Enter Route','error');
                    return;
                }
                else if(!id) {
                    id="0";
                    successText = "Route has been added!";
                }
                //alert(name + ", " + id + ", " + district_id);            
                $.ajax({
                    url: "{{ route('routes.store', ':id') }}".replace(':id', id),
                    type: "POST",
                    data: {
                        id: id,
                        name: name,
                        district_id: district_id
                    },
                    dataType: 'json',
                    success: function (data) {              
                        $('#form_route').trigger("reset");
                        $('#modal_route').modal('hide');                                                
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
                        //var errorText = "An Error Occurred";
                        var errorText = data.responseText;
                        if(data.responseText.indexOf("Duplicate entry") !== -1) {                            
                            errorText = "Route Already Exists";
                        }
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