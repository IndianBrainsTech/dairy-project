@extends('app-layouts.admin-master')

@section('title', 'Units')

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
                    @slot('title') Units @endslot
                    @slot('item1') Masters @endslot
                    @slot('item2') Products @endslot
                @endcomponent
            </div>
        </div>
        <!-- end page title end breadcrumb -->

        <div class="row">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-body">
                        <div style="width:100%">                                                  
                            <div style="width:60%;float:left"><h4 class="header-title mt-0">Unit Master</h4></div>
                            <div style="width:40%;float:left"><button type="button" id="add_unit" class="btn btn-gradient-primary px-4 float-right mt-0 mb-3" data-toggle="modal" data-animation="bounce" data-target="#modal_unit"><i class="mdi mdi-plus-circle-outline mr-2"></i>Add Unit</button></div>
                        </div>
                        <div class="table-responsive dash-social">
                            <table id="datatable" class="table table-bordered">
                                <thead class="thead-light">
                                <tr>
                                    <th>S.No</th>
                                    <th>Unit</th>
                                    <th>Display</th>
                                    <th>Hot Key</th>
                                    <th>Action</th>
                                </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $keys = array()
                                    @endphp
                                    @foreach($units as $unit)
                                        <tr>                                                
                                            <td>{{ $loop->index + 1 }}</td>
                                            <td>{{ $unit->unit_name }}</td>
                                            <td>{{ $unit->display_name }}</td>
                                            <td>{{ $unit->hot_key }}</td>                                            
                                            <td>                                                       
                                                <a href="" id="edit_unit" class="mr-2" data-toggle="modal" data-animation="bounce" data-target="#modal_unit" data-id="{{ $unit->id }}"><i class="fas fa-edit text-info font-16"></i></a>
                                                <a href="" id="delete_unit" data-id="{{ $unit->id }}"><i class="fas fa-trash-alt text-danger font-16"></i></a>
                                            </td>                                                
                                        </tr>
                                        @php 
                                            $keys[$loop->index] = $unit->hot_key 
                                        @endphp
                                    @endforeach                                         
                                </tbody>
                            </table>                    
                        </div>                                      
                    </div><!--end card-body--> 
                </div><!--end card--> 
            </div> <!--end col-->                               
        </div><!--end row--> 
    </div><!-- container -->

    <!-- Start of Unit Modal -->
    <div class="modal fade" id="modal_unit" tabindex="-1" role="dialog" aria-labelledby="modalUnitLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title mt-0" id="modal_unit_title">Add Unit</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="form_unit">
                    <input type="hidden" id="unit_id" name="unit_id" value="">
                    <div class="modal-body">                                                                
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group row">
                                    <label for="unit_name" class="col-sm-4 col-form-label">Unit Name <small class="text-danger font-13">*</small></label>
                                    <div class="col-sm-6">
                                        <input type="text" class="form-control" id="unit_name" required="" name="unit_name">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="display_text" class="col-sm-4 col-form-label">Display Text <small class="text-danger font-13">*</small></label>
                                    <div class="col-sm-6">
                                        <input type="text" class="form-control" id="display_text" required="" name="display_text">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="hot_key" class="col-sm-4 col-form-label">Hot Key <small class="text-danger font-13">*</small></label>
                                    <div class="col-sm-3">
                                        <select class="form-control" id="select_hot_key">                                            
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>   
                    </div>
                    <div class="modal-footer">
                        <input type="reset" class="btn btn-secondary" data-dismiss="modal" value="Close" />
                        <input type="submit" class="btn btn-primary" id="submit" value="Add Unit"/>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- End of Unit Modal -->  
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
            
            function loadHotKeys() {    
                var keys = <?php echo json_encode($keys); ?>;                
                for(let ch=65; ch<=90; ch++) {                                        
                    var alphabet = String.fromCharCode(ch);
                    if(!keys.includes(alphabet)) {
                        var newOption = $('<option>');
                        newOption.attr('value', alphabet).text(alphabet);
                        $('#select_hot_key').append(newOption);
                    }
                }                
            }

            $('#unit_name').focusout(function () {
                var name = $("#unit_name").val();
                $('#display_text').val(name); 
            });

            $('body').on('click', '#add_unit', function (event) {
                event.preventDefault();
                loadHotKeys();
                $('#modal_unit_title').html("Add Unit");
                $('#unit_id').val("");
                $('#unit_name').val("");
                $('#display_text').val("");
                $('#submit').val("Add Unit");
                $('#modal_unit').modal('show');
            });

            $('body').on('click', '#edit_unit', function (event) {
                event.preventDefault();   
                loadHotKeys();             
                var id = $(this).data('id');                             
                $.get('/unit/' + id, function (data) {
                    $('#modal_unit_title').html("Edit Unit");
                    $('#unit_id').val(data.unit.id);
                    $('#unit_name').val(data.unit.unit_name);
                    $('#display_text').val(data.unit.display_name);                    
                    $('#submit').val("Update");
                    $('#modal_unit').modal('show');

                    var alphabet = data.unit.hot_key;                    
                    var newOption = $('<option>');
                    newOption.attr('value', alphabet).text(alphabet);
                    $('#select_hot_key').append(newOption);
                    $('#select_hot_key').val(alphabet);
                })
            });

            $('body').on('click', '#delete_unit', function (event) {
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
                            url:'/unit/' + id,
                            type: 'DELETE',
                            success: function (data) { 
                                Swal.fire('Deleted!','Unit has been deleted.','success')
                                    .then(function() { window.location.reload(true);} );
                            }
                        }) 
                    }
                })
            });

            $('body').on('click', '#submit', function (event) {
                event.preventDefault();                             
                var id = $("#unit_id").val();
                var unit_name = $("#unit_name").val();    
                var display_name = $("#display_text").val();    
                var hot_key = $("#select_hot_key").val();
                var successText = "Unit has been updated!";

                if(!unit_name) {
                    Swal.fire('Attention','Please Enter Unit Name','error');
                    return;
                }
                else if(!display_name) {
                    Swal.fire('Attention','Please Enter Display Text','error');
                    return;
                }
                else if(!id) {
                    id = "0";
                    successText = "Unit has been added!";
                }

                $.ajax({
                    url: '/unit/' + id,
                    type: "POST",
                    data: {
                        id: id,
                        unit_name: unit_name,
                        display_name: display_name,
                        hot_key: hot_key
                    },
                    dataType: 'json',
                    success: function (data) {               
                        $('#form_unit').trigger("reset");
                        $('#modal_unit').modal('hide');                                                
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
                        var errorText = "An Error Occurred";
                        if(data.responseText.indexOf("unit_name_unique") !== -1)
                            errorText = "Unit Already Exists";                        
                        else if(data.responseText.indexOf("display_name_unique") !== -1)
                            errorText = "Display Text Already Assigned";                        
                        
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

    <script src="{{ asset('assets/js/jquery.core.js') }}"></script>
@stop
