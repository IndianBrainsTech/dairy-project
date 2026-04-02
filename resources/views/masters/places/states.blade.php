@extends('app-layouts.admin-master')

@section('title', 'States')

@section('headerStyle')
    <link href="{{ asset('plugins/sweet-alert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('plugins/animate/animate.css') }}" rel="stylesheet" type="text/css">
@stop

@section('content')
    <div class="container-fluid">
        <!-- Page-Title -->
        <div class="row">
            <div class="col-sm-12">
                @component('app-components.breadcrumb-3')
                    @slot('title') States @endslot
                    @slot('item1') Masters @endslot
                    @slot('item2') Places @endslot
                @endcomponent
            </div>
        </div>
        <!-- end page title end breadcrumb -->

        <div class="row">
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-body">
                        <div style="width:100%">                                                  
                            <div style="width:60%;float:left"><h4 class="header-title mt-0">States</h4></div>
                            <div style="width:40%;float:left"><button type="button" id="add_state" class="btn btn-gradient-primary px-4 float-right mt-0 mb-3" data-toggle="modal" data-animation="bounce" data-target="#modal_state"><i class="mdi mdi-plus-circle-outline mr-2"></i>Add State</button></div>
                        </div>
                        <div class="table-responsive dash-social">
                            <table id="datatable" class="table table-bordered">
                                <thead class="thead-light">
                                <tr>
                                    <th class="text-center">S.No</th>
                                    <th>State</th>
                                    <th class="text-center">Action</th>
                                </tr>
                                </thead>
                                <tbody>
                                    @foreach($states as $state)
                                        <tr>                                                
                                            <td class="text-center">{{ $loop->index + 1 }}</td>
                                            <td>{{ $state->name }}</td>                                                
                                            <td class="text-center">                                                       
                                                <a href="" id="edit_state" class="mr-2" data-toggle="modal" data-animation="bounce" data-target="#modal_state" data-id="{{ $state->id }}"><i class="fas fa-edit text-info font-16"></i></a>
                                                <a href="" id="delete_state" data-id="{{ $state->id }}"><i class="fas fa-trash-alt text-danger font-16"></i></a>
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

    <!-- Start of State Modal -->
    <div class="modal fade" id="modal_state" tabindex="-1" role="dialog" aria-labelledby="modalStateLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title mt-0" id="modal_state_title">Add State</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="form_state">
                    <input type="hidden" id="state_id" name="state_id" value="">
                    <div class="modal-body">                                                                
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group row">
                                    <label for="name" class="col-sm-4 col-form-label">State Name <small class="text-danger font-13">*</small></label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control" id="name" required="" name="name">
                                    </div>
                                </div>
                            </div>
                        </div>   
                    </div>
                    <div class="modal-footer">
                        <input type="reset" class="btn btn-secondary" data-dismiss="modal" value="Close" />
                        <input type="submit" class="btn btn-primary" id="submit" value="Add State"/>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- End of State Modal -->  
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
            
            $('body').on('click', '#add_state', function (event) {
                event.preventDefault();                
                $('#modal_state_title').html("Add State");
                $('#state_id').val("");
                $('#name').val("");
                $('#submit').val("Add State");
                $('#modal_state').modal('show');
            });

            $('body').on('click', '#edit_state', function (event) {
                event.preventDefault();
                let id = $(this).data('id');
                let url = "{{ route('states.edit', ':id') }}".replace(':id', id);
                $.get(url, function (data) {
                    $('#modal_state_title').html("Edit State");
                    $('#state_id').val(data.state.id);
                    $('#name').val(data.state.name);
                    $('#submit').val("Update");
                    $('#modal_state').modal('show');
                })
            });

            $('body').on('click', '#delete_state', function (event) {
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
                            url:"{{ route('states.destroy', ':id') }}".replace(':id', id),
                            type: 'DELETE',
                            success: function (data) { 
                                Swal.fire('Deleted!','State has been deleted.','success')
                                    .then(function() { window.location.reload(true);} );
                            }
                        })
                    }
                })
            });

            $('body').on('click', '#submit', function (event) {
                event.preventDefault();                             
                var id = $("#state_id").val();
                var name = $("#name").val();    
                var successText = "State has been updated!";
                if(!name) {
                    Swal.fire('Attention','Please Enter State Name','error');
                    return;
                }
                else if(!id) {
                    id = 0;
                    successText = "State has been added!";
                }
                // alert(name + ", " + id);            
                $.ajax({                    
                    url: "{{ route('states.store', ':id') }}".replace(':id', id),
                    type: "POST",
                    data: {
                        id: id,
                        name: name
                    },
                    dataType: 'json',
                    success: function (data) {   
                        // console.log(data);
                        // alert('success : ' + data);             
                        $('#form_state').trigger("reset");
                        $('#modal_state').modal('hide');                                                
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
                        // console.log(data);
                        //alert(data.responseText);
                        var errorText = "An Error Occurred";
                        if(data.responseText.indexOf("Duplicate entry") !== -1) {                            
                            errorText = "State Already Exists";
                        }
                        Swal.fire({
                                title:'Sorry!',
                                text:errorText,
                                type:'warning',
                                // confirmButtonColor: '$danger'
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
@stop
