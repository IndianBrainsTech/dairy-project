@extends('app-layouts.admin-master')

@section('title', 'Competitors')

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
                    @slot('title') Competitors @endslot
                    @slot('item1') Masters @endslot
                    @slot('item2') Profiles @endslot                    
                @endcomponent
            </div>
        </div>
        <!-- end page title end breadcrumb -->

        <div class="row">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-body">
                        <div style="width:100%">                                                  
                            <div style="width:60%;float:left"><h4 class="header-title mt-0">Competitors</h4></div>
                            <div style="width:40%;float:left"><button type="button" id="add_competitor" class="btn btn-gradient-primary px-4 float-right mt-0 mb-3" data-toggle="modal" data-animation="bounce" data-target="#modal_competitor"><i class="mdi mdi-plus-circle-outline mr-2"></i>Add Competitor</button></div>
                        </div>
                        <div class="table-responsive dash-social">
                            <table id="datatable" class="table table-bordered">
                                <thead class="thead-light">
                                <tr>
                                    <th class="text-center">S.No</th>
                                    <th>Competitor</th>
                                    <th>Display Name</th>                                    
                                    <th class="text-center">Action</th>
                                </tr>
                                </thead>
                                <tbody>
                                    @foreach($competitors as $competitor)
                                        <tr>                                                
                                            <td class="text-center">{{ $loop->index + 1 }}</td>
                                            <td>{{ $competitor->comp_name }}</td>
                                            <td>{{ $competitor->display_name }}</td>
                                            <td class="text-center">
                                                <a href="" id="edit_competitor" class="mr-2" data-toggle="modal" data-animation="bounce" data-target="#modal_competitor" data-id="{{ $competitor->id }}"><i class="fas fa-edit text-info font-16"></i></a>
                                                <a href="" id="delete_competitor" data-id="{{ $competitor->id }}"><i class="fas fa-trash-alt text-danger font-16"></i></a>
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

    <!-- Start of Competitor Modal -->
    <div class="modal fade" id="modal_competitor" tabindex="-1" role="dialog" aria-labelledby="modalCompetitorLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title mt-0" id="modal_title">Add Competitor</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="form_competitor">
                    <input type="hidden" id="competitor_id" name="competitor_id" value="">
                    <div class="modal-body">                                                                
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group row">
                                    <label for="competitor_name" class="col-sm-4 col-form-label">Competitor Name <small class="text-danger font-13">*</small></label>
                                    <div class="col-sm-6">
                                        <input type="text" name="comp_name" id="comp_name" class="form-control" required="">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="display_text" class="col-sm-4 col-form-label">Display Name <small class="text-danger font-13">*</small></label>
                                    <div class="col-sm-6">
                                        <input type="text" name="display_name" id="display_text" class="form-control" required="">
                                    </div>
                                </div>
                            </div>
                        </div>   
                    </div>
                    <div class="modal-footer">
                        <input type="reset" class="btn btn-secondary" data-dismiss="modal" value="Close" />
                        <input type="submit" class="btn btn-primary" id="submit" value="Add Competitor"/>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- End of Competitor Modal -->  
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
            
            $('#comp_name').focusout(function () {
                var name = $("#comp_name").val();
                $('#display_text').val(name); 
            });

            $('body').on('click', '#add_competitor', function (event) {
                event.preventDefault();   
                $('#modal_title').html("Add Competitor");
                $('#competitor_id').val("");                
                $('#comp_name').val("");
                $('#display_text').val("");                
                $('#submit').val("Add Competitor");
                $('#modal_competitor').modal('show');
            });

            $('body').on('click', '#edit_competitor', function (event) {
                event.preventDefault();   
                var id = $(this).data('id');
                $.get('/competitor/' + id, function (data) {
                    $('#modal_title').html("Edit Competitor");
                    $('#competitor_id').val(data.competitor.id);
                    $('#comp_name').val(data.competitor.comp_name);
                    $('#display_text').val(data.competitor.display_name);                    
                    $('#submit').val("Update");
                    $('#modal_competitor').modal('show');
                })
            });

            $('body').on('click', '#delete_competitor', function (event) {
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
                            url:'/competitor/' + id,
                            type: 'DELETE',
                            success: function (data) { 
                                Swal.fire('Deleted!','Competitor has been deleted.','success')
                                    .then(function() { window.location.reload(true);} );
                            }
                        }) 
                    }
                })
            });

            $('body').on('click', '#submit', function (event) {
                event.preventDefault();                             
                var id = $("#competitor_id").val();
                var comp_name = $("#comp_name").val();    
                var display_name = $("#display_text").val();    
                var successText = "Competitor has been updated!";

                if(!comp_name) {
                    Swal.fire('Attention','Please Enter Competitor Name','error');
                    return;
                }
                else if(!display_name) {
                    Swal.fire('Attention','Please Enter Display Name','error');
                    return;
                }
                else if(!id) {
                    id = "0";
                    successText = "Competitor has been added!";
                }

                $.ajax({
                    url: '/competitor/' + id,
                    type: "POST",
                    data: {
                        id: id,
                        comp_name: comp_name,
                        display_name: display_name
                    },
                    dataType: 'json',
                    success: function (data) {
                        $('#form_competitor').trigger("reset");
                        $('#modal_competitor').modal('hide');
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
                        if(data.responseText.indexOf("comp_name_unique") !== -1)
                            errorText = "Competitor Name Already Exists";                        
                        else if(data.responseText.indexOf("display_name_unique") !== -1)
                            errorText = "Display Name Already Assigned";                        
                        
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
    <script src="{{ asset('plugins/sweet-alert2/sweetalert2.min.js') }}"></script>
    <script src="{{ asset('assets/pages/jquery.sweet-alert.init.js') }}"></script>  
    <script src="{{ asset('assets/js/jquery.core.js') }}"></script>
@stop