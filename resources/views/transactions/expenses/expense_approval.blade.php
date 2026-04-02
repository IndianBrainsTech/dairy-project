@extends('app-layouts.admin-master')

@section('title', 'Expense Approval')

@section('headerStyle')
    <link href="{{ asset('plugins/datatables/dataTables.bootstrap4.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/css/my-style.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('assets/css/my-actxt.css') }}" rel="stylesheet" type="text/css">   
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.5.1/dist/sweetalert2.min.css" rel="stylesheet">    

    <style type="text/css">
        .my-control {
            padding: 6px 10px;
            margin-right: 16px;
        }
    </style>
@stop

@section('content')
    <div class="container-fluid">
        <!-- Page-Title -->
        <div class="row">
            <div class="col-sm-12">
                @component('app-components.breadcrumb-3')
                    @slot('title') Expense Approval @endslot
                    @slot('item1') Transactions @endslot
                    @slot('item2') Expenses @endslot
                @endcomponent
            </div><!--end col-->
        </div>
        <!-- end page title end breadcrumb -->
 
        <div class="row"> 
            <div class="col-lg-10 col-md-11 col-sm-12">
                <div class="card">
                    <div class="card-body">

                        {{-- <form method="post" action="{{ route('expense.entry.list') }}">
                            @csrf
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="row mb-2">
                                        <div class="col-md-9 col-sm-7">
                                            <input type="date" name="date" id="date" value="{{$date}}" class="my-control ml-2">
                                            <input type="submit" value="Submit" class="btn btn-primary btn-sm ml-3 px-3"/>     
                                        </div> 
                                        <div class="col-md-3 col-sm-5 text-right">
                                            @if($date == date('Y-m-d'))     
                                            <a href="{{ route('expense.entry') }}" class="btn btn-success">Add Expenses</a>
                                            @endif
                                        </div>                                  
                                    </div>
                                </div>
                            </div>
                        </form><!--end form-->
                        <hr/> --}}

                        <div class="table-responsive dash-social">
                            <table id="datatable" class="table table-sm table-bordered dt-responsive nowrap" style="border-collapse: collapse; border-spacing: 0; width: 100%">
                                <thead class="thead-light">
                                    <tr> 
                                        <th data-priority="6" class="text-center">S.No</th>
                                        <th data-priority="2" class="text-center">Date</th>                                        
                                        <th data-priority="1" class="text-center">Expense Name</th>
                                        <th data-priority="3" class="text-center">Amount</th>
                                        <th data-priority="4" class="text-center">Action</th>
                                        <th data-priority="5" class="text-center">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($expenses as $index => $expense)
                                        <tr id="expenseRow{{ $expense->id }}">
                                            <td class="text-center">{{ $loop->index + 1 }}</td>
                                            <td class="text-center">{{ getIndiaDate($expense->expense_date) }}</td>                                            
                                            <td class="text-left">{{ $expense->expense_name}}</td>
                                            <td class="text-center">{{ $expense->expense_amount}}</td>
                                            <td class="text-center">
                                                <a href="#" class="show" data-order="{{$expense->id}}"><i class="dripicons-preview text-primary font-20"></i></a>
                                            </td>
                                            <td class="d-flex justify-content-around">
                                                <button id="{{ $expense->id }}" class="btn btn-primary btn-sm btnApprove" data-status="Accepted">Accept</button>
                                                <button id="{{ $expense->id }}" class="btn btn-danger btn-sm btnApprove" data-status="Rejected">Reject</button>
                                                <button id="{{ $expense->id }}" class="btn btn-warning btn-sm btnApprove" data-status="Pending">Pending</button>
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
    </div>
@stop

@push('custom-scripts')
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.js"></script>
    <script src="{{ asset('assets/js/input-restriction.js') }}"></script>
    <script src="{{ asset('assets/js/helper.js') }}"></script>
    <script>
        $(document).ready(function () {
            $.ajaxSetup({
                headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
            });

            
            doInit();

            function doInit() {
                $('#datatable').dataTable( {
                    "lengthMenu": [[10, 25, 50, 100], [10, 25, 50, 100]],
                    "pageLength": 25,
                } );
            }
            $('body').on('click', '.show', function (event) {
                var entry_id = $(this).attr('data-order');
                var entryDate      = $('#date').val(); 
                // Create a form element
                var form = $('<form>', {
                    'method': 'POST',
                    'action': "{{ route('expense.entry.view') }}"
                });
 
                // Add CSRF token
                var csrfToken = $('meta[name="csrf-token"]').attr('content');
                form.append($('<input>', {
                    'type': 'hidden',
                    'name': '_token',
                    'value': csrfToken
                }));

                // Add the data as hidden inputs
                form.append($('<input>', {
                    'type': 'hidden',
                    'name': 'id',
                    'value': entry_id
                }));
                form.append($('<input>', {
                    'type': 'hidden',
                    'name': 'entryDate',
                    'value': entryDate
                }));
                // Append the form to the body and submit it
                $('body').append(form);
                form.submit();
            });
            $('.btnApprove').on('click', function() {
                var id = $(this).attr('id'); 
                var status = $(this).data('status'); 
                console.log("id: " + id);  
                console.log("status: " + status);  
                $.ajax({
                    url: '/expense/approval/store',  
                    type: 'POST', 
                    data: {
                        'id': id, 
                        'status': status  
                    },
                    success: function(response) {                        
                        // Show success message with icon
                        Swal.fire({
                            title: "Success!",
                            text: "Expense " + response.status + " Successfully", 
                            icon: 'success',  
                            confirmButtonText: 'OK'
                        }).then(() => {
                            if (response.status === 'Accepted' || response.status === 'Rejected') {
                                $('#expenseRow' + response.id).remove();
                                $('#datatable tbody tr').each(function (index) {
                                    $(this).find('td:first-child').text(index + 1); 
                                });
                            }
                        });
                    },
                    error: function(error) {                        
                        Swal.fire({
                            title: "Error!",
                            text: "There was a problem processing your request",
                            icon: 'error',  
                            confirmButtonText: 'Try Again'
                        });
                    }
                });
            });

        });
    </script>
@endpush

@section('footerScript')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.5.1/dist/sweetalert2.all.min.js"></script>
    <script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables/dataTables.bootstrap4.min.js') }}"></script>
@stop