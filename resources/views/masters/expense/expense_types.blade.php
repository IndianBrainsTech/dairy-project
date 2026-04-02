@extends('app-layouts.admin-master')

@section('title', 'Aasaii Admin')

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
                    @slot('title') Expenses  @endslot
                    @slot('item1') Masters @endslot
                    @slot('item2') Expense Types @endslot
                @endcomponent
            </div>
        </div>
        <!-- end page title end breadcrumb -->

        <div class="row">
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-body">
                        <div style="width:100%">                                                  
                            <div style="width:60%;float:left"><h4 class="header-title mt-0">Expenses</h4></div>
                            <div style="width:40%;float:left"><button type="button" id="add_expense" class="btn btn-gradient-primary px-4 float-right mt-0 mb-3" data-toggle="modal" data-animation="bounce" data-target="#modal_expense"><i class="mdi mdi-plus-circle-outline mr-2"></i>Add Expense</button></div>
                        </div>
                        <div class="table-responsive dash-social">
                            <table id="datatable" class="table table-bordered">
                                <thead class="thead-light">
                                <tr>
                                    <th>S.No</th>
                                    <th>Expense</th>
                                    <th>Action</th>
                                </tr>
                                </thead>
                                <tbody>
                                    @foreach($expenses as $expense)
                                        <tr>                                                
                                            <td>{{ $loop->index + 1 }}</td>
                                            <td>{{ $expense->name }}</td>                                                
                                            <td>                                                       
                                                <a href="" id="edit_expense" class="mr-2" data-toggle="modal" data-animation="bounce" data-target="#modal_expense" data-id="{{ $expense->id }}"><i class="fas fa-edit text-info font-16"></i></a>
                                                <a href="" id="delete_expense" data-id="{{ $expense->id }}"><i class="fas fa-trash-alt text-danger font-16"></i></a>
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

    <!-- Start of expense Modal -->
    <div class="modal fade" id="modal_expense" tabindex="-1" role="dialog" aria-labelledby="modalExpenseLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title mt-0" id="modal_expense_title">Add Expense</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="form_expense">
                    <input type="hidden" id="expense_id" name="expense_id" value="">
                    <div class="modal-body">                                                                
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group row">
                                    <label for="name" class="col-sm-4 col-form-label">Expense Name <small class="text-danger font-13">*</small></label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control" id="name" required="" name="name">
                                    </div>
                                </div>
                            </div>
                        </div>   
                    </div>
                    <div class="modal-footer">
                        <input type="reset" class="btn btn-secondary" data-dismiss="modal" value="Close" />
                        <input type="submit" class="btn btn-primary" id="submit" value="Add Expense"/>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- End of Expense Modal -->  
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

            // Open Add Expense Modal
            $('body').on('click', '#add_expense', function (event) {
                event.preventDefault();
                $('#modal_expense_title').html("Add Expense");
                $('#expense_id').val(""); 
                $('#name').val("");       
                $('#submit').val("Add Expense");
                $('#modal_expense').modal('show'); 
            });

            // Open Edit Expense Modal
            $('body').on('click', '#edit_expense', function (event) {
                event.preventDefault();
                var id = $(this).data('id'); 
                $.get('/expense/types/' + id, function (data) { 
                    $('#modal_expense_title').html("Edit Expense");
                    $('#expense_id').val(data.expense.id);
                    $('#name').val(data.expense.name);  
                    $('#submit').val("Update");
                    $('#modal_expense').modal('show'); 
                });
            });

            // Delete Expense
            $('body').on('click', '#delete_expense', function (event) {
                event.preventDefault();
                var id = $(this).data('id'); 

                Swal.fire({
                    title: 'Are you sure?',
                    text: "You won't be able to revert this!",
                    icon: 'warning', 
                    showCancelButton: true,
                    confirmButtonColor: '#28a745', 
                    cancelButtonColor: '#dc3545',  
                    confirmButtonText: 'Yes, delete it!'
                })
                .then(function (result) {
                    if (result.value) {
                        // Proceed with delete request
                        $.ajax({
                            url: '/expense/delete/' + id,
                            type: 'DELETE', // Use DELETE method
                            success: function (data) {
                                Swal.fire('Deleted!', 'Expense has been deleted.', 'success')
                                    .then(function () { 
                                        window.location.reload(true); 
                                    });
                            },
                            error: function (data) {
                                console.error(data); 
                                Swal.fire('Error!', 'An error occurred while deleting.', 'error');
                            }
                        });
                    }
                });
            });

            // Add or Update Expense
            $('body').on('click', '#submit', function (event) {
                event.preventDefault();
                var id = $("#expense_id").val();
                var name = $("#name").val();
                var successText = "Expense has been updated!";

                if (!name) {
                    Swal.fire('Attention', 'Please Enter Expense Name', 'error');
                    return;
                } else if (!id) {
                    id = "0"; 
                    successText = "Expense has been added!";
                }
                $.ajax({
                    url: '/expense/types/' + id, 
                    type: "POST",
                    data: {
                        id: id,
                        name: name
                    },
                    success: function (data) {
                        $('#form_expense').trigger("reset"); 
                        $('#modal_expense').modal('hide');  

                        Swal.fire({
                            title: 'Success!',
                            text: successText,
                            icon: 'success' 
                        })
                        .then(function () { 
                            window.location.reload(true); 
                        });
                    },
                    error: function (data) {
                        console.error(data); 
                        var errorText = "An Error Occurred";

                        if (data.responseText.indexOf("Duplicate entry") !== -1) {
                            errorText = "Expense Already Exists";
                        }

                        Swal.fire({
                            title: 'Sorry!',
                            text: errorText,
                            icon: 'warning', 
                            confirmButtonColor: '#dc3545' 
                        });
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
