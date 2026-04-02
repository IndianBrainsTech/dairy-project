@extends('app-layouts.admin-master')

@section('title', 'Districts')

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
                    @slot('title') Districts @endslot
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
                                <h4 class="header-title mt-0">Districts &nbsp;
                                    <button type="button" class="btn btn-pink btn-round " style="font-weight:500">
                                        {{ count($districts) }}
                                    </button>
                                </h4>
                            </div>
                            <div style="width:40%;float:left"><button type="button" id="add_district" class="btn btn-gradient-primary px-4 float-right mt-0 mb-3" data-toggle="modal" data-animation="bounce" data-target="#modal_district"><i class="mdi mdi-plus-circle-outline mr-2"></i>Add District</button></div>
                        </div>
                        <div class="table-responsive dash-social">
                            <table id="datatable" class="table table-bordered dt-responsive nowrap" style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                                <thead class="thead-light">
                                    <tr>
                                        <th class="text-center">S.No</th>
                                        <th>District</th>
                                        <th>State</th>
                                        <th class="text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($districts as $district)
                                        <tr>
                                            <td class="text-center">{{ $loop->index + 1 }}</td>
                                            <td>{{ $district->name }}</td>
                                            <td>{{ $district->state->name }}</td>
                                            <td class="text-center">
                                                <a href="" id="edit_district" class="mr-2" data-toggle="modal" data-animation="bounce" data-target="#modal_district" data-id="{{ $district->id }}"><i class="fas fa-edit text-info font-16"></i></a>
                                                <!-- @can('delete_district') -->
                                                    <a href="" id="delete_district" data-id="{{ $district->id }}"><i class="fas fa-trash-alt text-danger font-16"></i></a>
                                                <!-- @endcan     -->
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

    <!-- Start of District Modal -->
    <div class="modal fade" id="modal_district" tabindex="-1" role="dialog" aria-labelledby="modalDistrictLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title mt-0" id="modal_district_title">Add District</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="form_district">
                    <input type="hidden" id="district_id" name="district_id" value="">
                    <div class="modal-body">                                                                
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group row">
                                    <label for="name" class="col-sm-4 col-form-label">District Name <small class="text-danger font-13">*</small></label>
                                    <div class="col-sm-6">
                                        <input type="text" class="form-control" id="name" required="" name="name">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="state" class="col-sm-4 col-form-label">State <small class="text-danger font-13">*</small></label>
                                    <div class="col-sm-6">
                                        <select class="form-control" id="select_state">                                            
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>   
                    </div>
                    <div class="modal-footer">
                        <input type="reset" class="btn btn-secondary" data-dismiss="modal" value="Close" />
                        <input type="submit" class="btn btn-primary" id="submit" value="Add District"/>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- End of District Modal -->  
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
                
            function loadStates() {
                if ($('#select_state').has('option').length == 0) {
                    $.get("{{ route('states.list') }}", function (data) {                                       
                        for(let i=0; i<data.states.length; i++) {
                            var state = data.states[i];                        
                            // Create New Option
                            var newOption = $('<option>');
                            newOption.attr('value', state.id).text(state.name);
                            // Append that to the DropDownList
                            $('#select_state').append(newOption);
                        }
                    })  
                }
            }
            
            $('body').on('click', '#add_district', function (event) {
                event.preventDefault();
                loadStates();
                $('#modal_district_title').html("Add District");
                $('#district_id').val("");
                $('#name').val("");
                $('#submit').val("Add District");
                $('#modal_district').modal('show');
            });

            $('body').on('click', '#edit_district', function (event) {
                event.preventDefault();                
                var id = $(this).data('id');
                let url = "{{ route('districts.edit', ['id' => '__ID__']) }}".replace('__ID__', id);
                $.get(url, function (data) {
                    loadStates();
                    $('#modal_district_title').html("Edit District");
                    $('#district_id').val(data.district.id);
                    $('#name').val(data.district.name);
                    $('#submit').val("Update");
                    $('#modal_district').modal('show');                    
                    $('#select_state').val(data.district.state_id);    
                })
            });

            $('body').on('click', '#delete_district', function (event) {
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
                        let url = "{{ route('districts.destroy', ['id' => '__ID__']) }}".replace('__ID__', id);
                        $.ajax({                                
                            url: url,
                            type: 'DELETE',
                            success: function (data) { 
                                Swal.fire('Deleted!','District has been deleted.','success')
                                    .then(function() { window.location.reload(true);} );
                            }
                        })
                    }
                })
            });

            $('body').on('click', '#submit', function (event) {
                event.preventDefault();                             
                var id = $("#district_id").val();
                var name = $("#name").val();
                var state_id = $("#select_state").val();    
                var successText = "District has been updated!";
                if(!name) {
                    Swal.fire('Attention','Please Enter District Name','error');
                    return;
                }
                else if(!id) {
                    id = 0;
                    successText = "District has been added!";
                }
                //alert(name + ", " + id + ", " + state_id);
                let url = "{{ route('districts.store', ['id' => '__ID__']) }}".replace('__ID__', id);
                $.ajax({                    
                    url: url,
                    type: "POST",
                    data: {
                        id: id,
                        name: name,
                        state_id: state_id
                    },
                    dataType: 'json',
                    success: function (data) {              
                        $('#form_district').trigger("reset");
                        $('#modal_district').modal('hide');
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
                            errorText = "District Already Exists";
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
