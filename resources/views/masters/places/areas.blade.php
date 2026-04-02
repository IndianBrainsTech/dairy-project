@extends('app-layouts.admin-master')

@section('title', 'Areas')

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
                    @slot('title') Areas @endslot
                    @slot('item1') Masters @endslot
                    @slot('item2') Places @endslot
                @endcomponent
            </div>
        </div>
        <!-- end page title end breadcrumb -->

        <div class="row">
            <div class="col-lg-10">
                <div class="card">
                    <div class="card-body">                                                    
                        <div style="width:100%">
                            <div style="width:60%;float:left">
                                <h4 class="header-title mt-0">Areas &nbsp;
                                    <button type="button" class="btn btn-pink btn-round " style="font-weight:500">
                                        {{ count($areas) }}
                                    </button>
                                </h4>
                            </div>
                            @can('list_areas')
                                <div style="width:40%;float:left"><button type="button" id="add_area" class="btn btn-gradient-primary px-4 float-right mt-0 mb-3" data-toggle="modal" data-animation="bounce" data-target="#modal_area"><i class="mdi mdi-plus-circle-outline mr-2"></i>Add Area</button></div>                            
                            @endcan
                        </div>                        
                        <div class="table-responsive dash-social">
                            <table id="datatable" class="table table-bordered dt-responsive nowrap" style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                                <thead class="thead-light">
                                    <tr>
                                        <th class="text-center">S.No</th>
                                        <th>Area</th>
                                        <th>Route</th>
                                        <th>District</th>
                                        <th>State</th>
                                        <th class="text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($areas as $area)
                                        <tr>                                                
                                            <td class="text-center">{{ $loop->index + 1 }}</td>
                                            <td>{{ $area->area }}</td>
                                            <td>{{ $area->route }}</td>
                                            <td>{{ $area->district }}</td>
                                            <td>{{ $area->state }}</td>                                               
                                            <td class="text-center">                                                       
                                                <a href="" id="edit_area" class="mr-2" data-toggle="modal" data-animation="bounce" data-target="#modal_area" data-id="{{ $area->area_id }}"><i class="fas fa-edit text-info font-16"></i></a>
                                                <a href="" id="delete_area" data-id="{{ $area->area_id }}"><i class="fas fa-trash-alt text-danger font-16"></i></a>
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

    <!-- Start of Area Modal -->
    <div class="modal fade" id="modal_area" tabindex="-1" role="dialog" aria-labelledby="modalAreaLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title mt-0" id="modal_area_title">Add Area</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="form_area">
                    <input type="hidden" id="area_id" name="area_id" value="">
                    <div class="modal-body">                                                                
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group row">
                                    <label for="name" class="col-sm-4 col-form-label">Area Name <small class="text-danger font-13">*</small></label>
                                    <div class="col-sm-6">
                                        <input type="text" class="form-control" id="name" required="" name="name">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="area" class="col-sm-4 col-form-label">Route <small class="text-danger font-13">*</small></label>
                                    <div class="col-sm-6">
                                        <select class="form-control" id="select_route">                                            
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>   
                    </div>
                    <div class="modal-footer">
                        <input type="reset" class="btn btn-secondary" data-dismiss="modal" value="Close" />
                        <input type="submit" class="btn btn-primary" id="submit" value="Add Area"/>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- End of Area Modal -->
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
                "lengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
                "pageLength": 25
                } );

            function loadRoutes() {
                if ($('#select_route').has('option').length == 0) {                    
                    $.get("{{ route('routes.list') }}", function (data) {                                       
                        data.routes.forEach(route => {
                            $('#select_route').append($('<option>', { value: route.id, text: route.name }));
                        });
                    })  
                }
            }
            
            $('body').on('click', '#add_area', function (event) {
                event.preventDefault();
                loadRoutes();
                $('#modal_area_title').html("Add Area");
                $('#route_id').val("");
                $('#name').val("");
                $('#submit').val("Add Area");
                $('#modal_area').modal('show');
            });

            $('body').on('click', '#edit_area', function (event) {
                event.preventDefault();
                var id = $(this).data('id');
                let url = "{{ route('areas.edit', ':id') }}".replace(':id', id);
                $.get(url, function (data) {
                    loadRoutes();
                    $('#modal_area_title').html("Edit Area");
                    $('#area_id').val(data.area.id);
                    $('#name').val(data.area.name);
                    $('#submit').val("Update");
                    $('#modal_area').modal('show');
                    $('#select_route').val(data.area.route_id);
                })
            });

            $('body').on('click', '#delete_area', function (event) {
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
                            url: "{{ route('areas.destroy', ':id') }}".replace(':id', id),
                            type: 'DELETE',
                            success: function (data) { 
                                Swal.fire('Deleted!','Area has been deleted.','success')
                                    .then(function() { window.location.reload(true);} );
                            }
                        })                         
                    }
                })
            });

            $('body').on('click', '#submit', function (event) {
                event.preventDefault();                             
                var id = $("#area_id").val();
                var name = $("#name").val();
                var route_id = $("#select_route").val();    
                var successText = "Area has been updated!";
                if(!name) {
                    Swal.fire('Attention','Please Enter Area Name','error');
                    return;
                }
                else if(!id) {
                    id="0";
                    successText = "Area has been added!";
                }                           
                $.ajax({                    
                    url: "{{ route('areas.store', ':id') }}".replace(':id', id),
                    type: "POST",
                    data: {
                        id: id,
                        name: name,
                        route_id: route_id
                    },
                    dataType: 'json',
                    success: function (data) {              
                        $('#form_area').trigger("reset");
                        $('#modal_area').modal('hide');                                                
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
                        // var errorText = data.responseText;
                        var errorText = "An Error Occurred";
                        if(data.responseText.indexOf("Duplicate entry") !== -1) {                            
                            errorText = "Area Already Defined in the Route";
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