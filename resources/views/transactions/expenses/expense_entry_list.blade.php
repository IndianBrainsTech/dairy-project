@extends('app-layouts.admin-master')

@section('title', 'Stock Entry')

@section('headerStyle')
    <link href="{{ asset('plugins/datatables/dataTables.bootstrap4.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/css/my-style.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('assets/css/my-actxt.css') }}" rel="stylesheet" type="text/css">
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
                    @slot('title') Expense Entry List @endslot
                    @slot('item1') Transactions @endslot
                    @slot('item2') Expenses @endslot
                @endcomponent
            </div><!--end col-->
        </div>
        <!-- end page title end breadcrumb -->
 
        <div class="row"> 
            <div class="col-lg-8 col-md-11 col-sm-12">
                <div class="card">
                    <div class="card-body">

                        <form method="post" action="{{ route('expense.entry.list') }}">
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
                        <hr/>

                        <div class="table-responsive dash-social">
                            <table id="datatable" class="table table-sm table-bordered dt-responsive nowrap" style="border-collapse: collapse; border-spacing: 0; width: 100%">
                                <thead class="thead-light">
                                    <tr> 
                                        <th data-priority="6" class="text-center">S.No</th>
                                        <th data-priority="2" class="text-center">Date</th>                                        
                                        <th data-priority="1" class="text-center">Expense Name</th>
                                        <th data-priority="3" class="text-center">Amount</th>
                                        <th data-priority="4" class="text-center">Status</th>
                                        <th data-priority="5" class="text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        function getStatusClass($status) {
                                            switch($status) {
                                                case 'Accepted':
                                                    return 'primary';    
                                                case 'Rejected':
                                                    return 'danger';   
                                                case 'Pending':
                                                    return 'warning';   
                                                default:
                                                    return 'secondary';   
                                            }
                                        }
                                    @endphp
                                    @foreach($expenses as $index => $expense)
                                        <tr>                                            
                                            <td class="text-center">{{ $loop->index + 1 }}</td>
                                            <td class="text-center">{{ getIndiaDate($expense->expense_date) }}</td>                                            
                                            <td class="text-left">{{ $expense->expense_name}}</td>
                                            <td class="text-center">{{ $expense->expense_amount}}</td>
                                            <td class="text-center"><span class="p-1 badge badge-soft-{{ getStatusClass($expense->expense_status) }}">
                                                {{ $expense->expense_status}}
                                            </span></td>
                                            <td class="text-center">
                                                <a href="#" class="show" data-order="{{$expense->id}}"><i class="dripicons-preview text-primary font-20"></i></a>
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
        });
    </script>
@endpush

@section('footerScript')
    <script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables/dataTables.bootstrap4.min.js') }}"></script>
@stop