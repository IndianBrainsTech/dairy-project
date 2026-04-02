@extends('app-layouts.admin-master')

@section('title', 'Sales Return')

@section('headerStyle')
    <link href="{{ asset('plugins/sweet-alert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('plugins/animate/animate.css') }}" rel="stylesheet" type="text/css">
@stop

@section('content')
    <div class="container-fluid">
        <!-- Page-Title -->
        <div class="row">
            <div class="col-sm-12">
                @component('app-components.breadcrumb-2')
                    @slot('title') Sales Return @endslot
                    @slot('item1') Transactions @endslot
                @endcomponent
            </div>
        </div>
        <!-- end page title end breadcrumb -->

        <div class="row">
            <div class="col-lg-10">
                <div class="card">
                    <div class="card-body">
                        <div style="width:100%">
                            <div style="width:60%;float:left"><h4 class="header-title mt-0">Sales Return</h4></div>
                            <div style="width:40%;float:left"><a href="{{ route('sales-return.create') }}" class="btn btn-gradient-primary float-right mb-4"><i class="mdi mdi-plus-circle-outline mr-2"></i>Sales Return</a></div>
                        </div>
                        <div class="table-responsive dash-social">
                            <table id="datatable" class="table table-bordered table-sm text-center">
                                <thead class="thead-light">
                                    <tr>
                                        <th>S.No</th>
                                        <th>Txn ID</th>
                                        <th class="text-left">Route</th>
                                        <th class="text-left">Customer</th>
                                        <th>Invoice Number</th>
                                        <th>Return Value</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($sales_returns as $salesReturn)
                                        <tr>
                                            <td>{{ $loop->index + 1 }}</td>
                                            <td>{{ $salesReturn->txn_id }}</td>
                                            <td class="text-left">{{ $salesReturn->route->name }}</td>
                                            <td class="text-left">{{ $salesReturn->customer->customer_name }}</td>
                                            <td>{{ $salesReturn->invoice_num }}</td>
                                            <td>{{ $salesReturn->net_amt }}</td>
                                            <td>
                                                <a href="#" class="show mr2" data-id="{{$salesReturn->txn_id}}"><i class="dripicons-preview text-primary font-20"></i></a>
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
            
            $('body').on('click', '.show', function (event) {
                let id = $(this).attr('data-id');
                let table = $('#datatable').DataTable();
                let ids = table.column(1).data().toArray();
                
                // Create a form element
                var form = $('<form>', {
                    'method': 'POST',
                    'action': '{{ route("sales-return.show") }}'
                });
 
                // Add CSRF token
                var csrfToken = $('meta[name="csrf-token"]').attr('content');
                form.append($('<input>', { 'type': 'hidden', 'name': '_token', 'value': csrfToken }));

                // Add the data as hidden inputs
                form.append($('<input>', { 'type': 'hidden', 'name': 'txn_id', 'value': id }));
                form.append($('<input>', { 'type': 'hidden', 'name': 'id_list', 'value': ids }));

                // Append the form to the body and submit it
                $('body').append(form);
                form.submit();
            });
        });
    </script>
@endpush

@section('footerScript')
    <!-- Sweet-Alert  -->
    <script src="{{ asset('plugins/sweet-alert2/sweetalert2.min.js') }}"></script>
    <script src="{{ asset('assets/pages/jquery.sweet-alert.init.js') }}"></script>
    <script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables/dataTables.bootstrap4.min.js') }}"></script>
@stop
