@extends('app-layouts.admin-master')

@section('title', 'Product Groups')

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
                    @slot('title') Product Groups @endslot
                    @slot('item1') Masters @endslot
                    @slot('item2') Products @endslot
                @endcomponent
            </div>
        </div>
        <!-- end page title end breadcrumb -->

        <div class="row">
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-body">                        
                        
                        <div class="table-responsive dash-social">
                            <table id="datatable" class="table table-bordered">
                                <thead class="thead-light">
                                <tr>
                                    <th class="text-center">S.No</th>
                                    <th>Product Group</th>                                    
                                    <!-- <th>Action</th> -->
                                </tr>
                                </thead>
                                <tbody>
                                    @foreach($product_groups as $prgroup)
                                        <tr>                                                
                                            <td class="text-center">{{ $loop->index + 1 }}</td>
                                            <td>{{ $prgroup->name }}</td>                                            
                                            <!-- <td>
                                                <a href="" id="edit_group" class="mr-2" data-toggle="modal" data-animation="bounce" data-target="#modal_prgroup" data-id="{{ $prgroup->id }}"><i class="fas fa-edit text-info font-16"></i></a>
                                                <a href="" id="delete_group" data-id="{{ $prgroup->id }}"><i class="fas fa-trash-alt text-danger font-16"></i></a>
                                            </td> -->
                                        </tr>
                                    @endforeach                                         
                                </tbody>
                            </table>                    
                        </div>  
                        
                        <button type="button" id="add_group" class="btn btn-gradient-pink px-3 py-1 mt-1" data-toggle="modal" data-animation="bounce" data-target="#modal_prgroup"><i class="mdi mdi-plus-circle-outline mr-2"></i>Add</button>
                        
                    </div><!--end card-body--> 
                </div><!--end card--> 
            </div> <!--end col-->                               
        </div><!--end row--> 
    </div><!-- container -->

    <!-- Start of Product Group Modal -->
    <div class="modal fade" id="modal_prgroup" tabindex="-1" role="dialog" aria-labelledby="modalProductGroupLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title mt-0" id="modal_prgroup_title">Add Group</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="form_group">
                    <input type="hidden" id="prgroup_id" name="prgroup_id" value="">
                    <div class="modal-body">                                                                
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group row">
                                    <label for="name" class="col-sm-5 col-form-label">Product Group <small class="text-danger font-13">*</small></label>
                                    <div class="col-sm-6">
                                        <input type="text" class="form-control" id="name" required="" name="name">
                                    </div>
                                </div>
                            </div>
                        </div>   
                    </div>
                    <div class="modal-footer">
                        <input type="reset" class="btn btn-secondary" data-dismiss="modal" value="Close" />
                        <input type="submit" class="btn btn-primary" id="submit" value="Add Group"/>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- End of Product Group Modal -->  
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
            
            $('body').on('click', '#add_group', function (event) {
                event.preventDefault();
                $('#modal_prgroup_title').html("Add Group");
                $('#prgroup_id').val("");
                $('#name').val("");
                $('#submit').val("Add Group");
                $('#modal_prgroup').modal('show');
            });

            $('body').on('click', '#edit_group', function (event) {
                event.preventDefault();                
                var id = $(this).data('id');                            
                $.get('/product_group/' + id, function (data) {                    
                    $('#modal_prgroup_title').html("Edit Group");
                    $('#prgroup_id').val(data.prgroup.id);
                    $('#name').val(data.prgroup.name);
                    $('#submit').val("Update");
                    $('#modal_prgroup').modal('show');                    
                    $('#select_hsn_code').val(data.prgroup.tax_id);    
                })
            });

            $('body').on('click', '#delete_group', function (event) {
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
                            url:'/product_group/' + id,
                            type: 'DELETE',
                            success: function (data) { 
                                Swal.fire('Deleted!','Product Group has been deleted.','success')
                                    .then(function() { window.location.reload(true);} );
                            }
                        })                         
                    }
                })
            });

            $('body').on('click', '#submit', function (event) {
                event.preventDefault();                             
                var id = $("#prgroup_id").val();
                var name = $("#name").val();                
                var successText = "Product Group has been updated!";
                if(!name) {
                    Swal.fire('Attention','Please Enter Product Group Name','error');
                    return;
                }
                else if(!id) {
                    id = "0";
                    successText = "Product Group has been added!";
                }
                
                $.ajax({
                    url: '/product_group/' + id,
                    type: "POST",
                    data: {
                        id: id,
                        name: name                        
                    },
                    dataType: 'json',
                    success: function (data) {              
                        $('#form_group').trigger("reset");
                        $('#modal_prgroup').modal('hide');                                                
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
                        // var errorText = "An Error Occurred";
                        if(data.responseText.indexOf("Duplicate entry") !== -1) {                            
                            errorText = "Product Group Already Exists";
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
@stop
