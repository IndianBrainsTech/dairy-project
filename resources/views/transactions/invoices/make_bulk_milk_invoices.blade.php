@extends('app-layouts.admin-master')

@section('title', 'Make Bulk Milk Invoices')

@section('headerStyle')
    <link href="{{ asset('plugins/sweet-alert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('plugins/animate/animate.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('plugins/datatables/dataTables.bootstrap4.min.css') }}" rel="stylesheet" type="text/css">
@stop

@section('content')
    <div class="container-fluid">
        <!-- Page-Title -->
        <div class="row">
            <div class="col-sm-12">
                @component('app-components.breadcrumb-3')
                    @slot('title') Make Bulk Milk Invoices @endslot
                    @slot('item1') Transactions @endslot
                    @slot('item2') Invoices @endslot
                @endcomponent
            </div><!--end col-->
        </div>
        <!-- end page title end breadcrumb -->
 
        <div class="row"> 
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">

                        <div class="table-responsive dash-social">
                            <table id="datatable" class="table table-bordered table-sm dt-responsive nowrap text-center" style="width: 100%;">
                                <thead class="thead-light">
                                    <tr>
                                        <th data-priority="6">S.No</th>
                                        <th data-priority="3">Invoice Date</th>
                                        <th data-priority="1">Invoice Number</th>
                                        <th data-priority="4" class="text-left pl-2">Customer</th>
                                        <th data-priority="5" class="text-right pr-2">Invoice Amount</th>
                                        <th data-priority="2">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($orders as $order)
                                        <tr>
                                            <td>{{ $loop->index + 1 }}</td>
                                            <td>{{ displayDate($order->invoice_date) }}</td>
                                            <td>{{ $order->invoice_num }}</td>
                                            <td class="text-left pl-2">{{ $order->customer_name }}</td>
                                            <td class="text-right pr-2">{{ getTwoDigitPrecision($order->net_amt) }}</td>
                                            <td><a href="#" class="show mr2 text-warning" data-invoice="{{$order->invoice_num}}">Generate Invoice</a></td>
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
    <script>
        $(document).ready(function () {
            $.ajaxSetup({
                headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
            });
            
            $('#datatable').dataTable( {
                "lengthMenu": [[10, 25, 50, 100], [10, 25, 50, 100]],
                "pageLength": 25,
            } );

            $('body').on('click', '.show', function (event) {
                let invoiceNum = $(this).attr('data-invoice');
                $.ajax({
                    url: "{{ route('bulk-milk.invoices.build') }}",
                    method: "POST",
                    data: { invoice_num: invoiceNum },
                    success: function(data) {
                        Swal.fire('Success', data.message, 'success')
                            .then(() => window.location.reload());
                    },
                    error: function(xhr, status, error) {
                        let errorMessage = xhr.responseJSON?.message || 'Something went wrong. Please try again.';
                        Swal.fire({
                            title: 'Error',
                            text: errorMessage,
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                    }
                });
            });
        });
    </script>
@endpush

@section('footerScript')
    <script src="{{ asset('plugins/sweet-alert2/sweetalert2.min.js') }}"></script>
    <script src="{{ asset('assets/pages/jquery.sweet-alert.init.js') }}"></script>
    <script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables/dataTables.bootstrap4.min.js') }}"></script>
@stop