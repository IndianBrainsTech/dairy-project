@extends('app-layouts.admin-master')

@section('title', 'Bulk Milk Orders')

@section('headerStyle')
    <link href="{{ asset('plugins/datatables/dataTables.bootstrap4.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/css/my-style.css') }}" rel="stylesheet" type="text/css">
@stop

@section('content')
    <div class="container-fluid">
        <!-- Page-Title -->
        <div class="row">
            <div class="col-sm-12">
                @component('app-components.breadcrumb-3')
                    @slot('title') Bulk Milk Orders @endslot
                    @slot('item1') Transactions @endslot
                    @slot('item2') Orders @endslot
                @endcomponent
            </div><!--end col-->
        </div>
        <!-- end page title end breadcrumb -->
 
        <div class="row"> 
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">

                        <form method="post" action="{{ route('bulk-milk.orders.index') }}">
                            @csrf
                            <label class="my-text">From</label>
                            <input type="date" name="fromDate" id="fromDate" value="{{ $fromDate }}" class="my-control">
                            <label class="my-text">To</label>
                            <input type="date" name="toDate" id="toDate" value="{{ $toDate }}" class="my-control">
                            <input type="submit" value="Submit" class="btn btn-gradient-primary btn-sm px-3 ml-3"/>
                        </form>
                        <hr/>

                        <div class="table-responsive dash-social">
                            <table id="datatable" class="table table-bordered table-sm dt-responsive nowrap text-center" style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                                <thead class="thead-light">
                                <tr> 
                                    <th data-priority="1">S.No</th>
                                    <th data-priority="5">Order Date</th>
                                    <th data-priority="3">Order Number</th>
                                    <th data-priority="4" class="text-left pl-2">Customer</th>
                                    <th data-priority="6" class="text-right pr-2">Order Amount</th>
                                    <th data-priority="7">Status</th>
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
                                            <td>{!! getBulkMilkOrderStatusWithBadge($order->invoice_status) !!}</td>
                                            <td><a href="#" class="show mr2" data-order="{{ $order->invoice_num }}"><i class="dripicons-preview text-primary font-20"></i></a></td>
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

            $('#fromDate').change(function() {
                let date = $(this).val();
                $('#toDate').attr('min',date);
            });

            $("#fromDate").trigger('change');

            function getOrderNumbers() {
                let table = $('#datatable').DataTable();
                let orderNums = table.column(2,{search:'applied'}).data().toArray();
                return orderNums;
            }

            $('body').on('click', '.show', function (event) {
                let orderNum = $(this).attr('data-order');
                let orders = getOrderNumbers();

                // Create a form element
                let form = $('<form>', {
                    'method': 'POST',
                    'action': "{{ route('bulk-milk.orders.show') }}"
                });

                // Add CSRF token
                let csrfToken = $('meta[name="csrf-token"]').attr('content');
                form.append($('<input>', {
                    'type': 'hidden',
                    'name': '_token',
                    'value': csrfToken
                }));

                // Add the data as hidden inputs
                form.append($('<input>', {
                    'type': 'hidden',
                    'name': 'order_num',
                    'value': orderNum
                }));

                form.append($('<input>', {
                    'type': 'hidden',
                    'name': 'orders',
                    'value': orders
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