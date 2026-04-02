@extends('app-layouts.admin-master')

@section('title', 'List Invoices')

@section('headerStyle')
    <link href="{{ asset('plugins/sweet-alert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('plugins/animate/animate.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('plugins/datatables/dataTables.bootstrap4.min.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('assets/css/my-style.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('assets/css/my-actxt.css') }}" rel="stylesheet" type="text/css">
@stop

@section('content')
    <div class="container-fluid">
        <!-- Page-Title -->
        <div class="row">
            <div class="col-sm-12">
                @component('app-components.breadcrumb-3')
                    @slot('title') List Invoices @endslot
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

                        <form method="post" action="{{ route('invoices.index') }}">
                            @csrf
                            <div class="row mb-4 mx-2">
                                <label for="from-date" class="my-text">From</label>
                                <input type="date" name="from_date" id="from-date" value="{{ $dates['from'] }}" class="my-control" tabindex="1">
                                <label for="to-date" class="my-text ml-2">To</label>
                                <input type="date" name="to_date" id="to-date" value="{{ $dates['to'] }}" class="my-control" tabindex="2">
                                <label for="invoice-type" class="my-text ml-3">Invoice Type</label>
                                <select name="invoice_type" id="invoice-type" class="my-control" tabindex="3">
                                    <option value="Sales & Tax" @selected($invoice_type=="Sales & Tax")>Sales & Tax Invoices</option>
                                    <option value="Sales" @selected($invoice_type=="Sales")>Sales Invoices</option>
                                    <option value="Tax" @selected($invoice_type=="Tax")>Tax Invoices</option>
                                </select>
                                <label for="print-filter" class="my-text ml-3">Print Filter</label>
                                <select name="print_filter" id="print-filter" class="my-control" tabindex="4">
                                    <option value="All" @selected($print_filter=="All")>All</option>
                                    <option value="Unprinted" @selected($print_filter=="Unprinted")>Unprinted</option>
                                    <option value="Printed" @selected($print_filter=="Printed")>Printed</option>
                                </select>
                            </div>
                            <div class="row mx-2">
                                <div class="input-group" style="width:346px">
                                    <span class="input-group-prepend">
                                        <button type="button" class="btn btn-info py-1" tabindex="-1"><i class="fas fa-search"></i></button>
                                    </span>
                                    <input type="hidden" name="route_id" id="route-id">
                                    <input type="text" name="route_name" id="route-name" placeholder="Route" class="my-control" style="width:286px" tabindex="5">
                                </div>
                                <div class="input-group" style="width:456px">
                                    <span class="input-group-prepend">
                                        <button type="button" class="btn btn-info py-1" tabindex="-1"><i class="fas fa-search"></i></button>
                                    </span>
                                    <input type="hidden" name="customer_id" id="customer-id">
                                    <input type="text" name="customer_name" id="customer-name" placeholder="Customer" class="my-control" style="width:396px" tabindex="6">
                                </div>
                                <input type="submit" value="Submit" class="btn btn-primary btn-sm px-3 ml-3"/>
                                <button id="btn-print" type="button" class="btn btn-info py-1 mx-3" aria-label="Print" data-toggle="tooltip" data-placement="top" title="Print All Invoices">&nbsp;<i class="fa fa-print"></i>&nbsp; All</button>
                            </div>
                        </form>
                        <hr/>

                        <div class="table-responsive dash-social">
                            <table id="datatable" class="table table-bordered table-sm dt-responsive nowrap">
                                <thead class="thead-light">
                                    <tr class="text-center"> 
                                        <th data-priority="1">S.No</th>
                                        <th data-priority="8">Date</th>
                                        <th data-priority="5">Route</th>
                                        <th data-priority="3">Customer</th>
                                        <th data-priority="4">Order No</th>
                                        @if($invoice_type != "Tax")
                                            <th data-priority="6">Sales Invoice</th>
                                        @endif
                                        @if($invoice_type != "Sales")
                                            <th data-priority="7">Tax Invoice</th>
                                        @endif
                                        <th data-priority="2">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($orders as $order)
                                        <tr>
                                            <td class="text-center">{{ $loop->index + 1 }}</td>
                                            <td class="text-center">{{ displayDate($order->invoice_date) }}</td>
                                            <td class="text-left pl-2">{{ $order->route->name }}</td>
                                            <td class="text-left pl-2">{{ $order->customer->customer_name }}</td>
                                            <td class="text-center">{{ $order->order_num }}</td>
                                            @if($invoice_type != "Tax")
                                                <td class="text-center">
                                                    @if($order->sales_invoice)
                                                        {{ $order->sales_invoice['invoice_num'] }}
                                                        <a href="#" class="print-inv" style="color:{{ $order->sales_invoice['is_printed'] ? 'gray' : 'orange' }}" data-order-num="{{ $order->order_num }}" data-inv-type="Sales"><i class="fa fa-print font-14 mx-2"></i></a>
                                                    @endif
                                                </td>
                                            @endif
                                            @if($invoice_type != "Sales")
                                                <td class="text-center">
                                                    @if($order->tax_invoice)
                                                        {{ $order->tax_invoice['invoice_num'] }}
                                                        <a href="#" class="print-inv" style="color:{{ $order->tax_invoice['is_printed'] ? 'gray' : 'orange' }}" data-order-num="{{ $order->order_num }}" data-inv-type="Tax"><i class="fa fa-print font-14 mx-2"></i></a>
                                                    @endif
                                                </td>
                                            @endif 
                                            <td class="text-center">
                                                <a href="#" class="show mr2" data-order="{{ $order->order_num }}"><i class="dripicons-preview text-primary font-20"></i></a>
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
    <script>
        const ROUTES_LIST_URL = @json(route('routes.list'));
        const CUSTOMERS_BY_ROUTE_URL = @json(route('customers.get.route', ':id'));
        const selectedRoute = @json($route);
        const selectedCustomer = @json($customer);
    </script>
    <script src="{{ asset('assets/js/customer-selection3.js') }}"></script>
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

            $('#from-date').change(function() {
                let date = $(this).val();
                $('#to-date').attr('min',date);
            });

            $("#from-date").trigger('change');

            $('body').on('click', '.print-inv', function (event) { 
                let orderNums = [];
                orderNums[0] = $(this).attr("data-order-num");
                let invoiceType = $(this).attr("data-inv-type");
                doPrint(orderNums, invoiceType);
            });

            $('#btn-print').on("click", function () {
                let orderNums = getOrderNumbers();                
                let invoiceType = @json($invoice_type);
                doPrint(orderNums, invoiceType);
            });

            function getOrderNumbers() {
                var table = $('#datatable').DataTable();
                var orderNums = table.column(4,{search:'applied'}).data().toArray();
                return orderNums;
            }

            function doPrint(orderNums, invoiceType) {                
                $.ajax({
                    url: "{{ route('invoices.print') }}",
                    type: "POST",
                    data: {
                        order_nums: orderNums,
                        invoice_type: invoiceType
                    },
                    dataType: 'html',
                    success: function (data) {
                        var printWindow = window.open('', '_blank');
                        printWindow.document.write(data);
                        printWindow.document.close();
                        // Wait for the window to finish loading before calling functions
                        printWindow.onload = function() {
                            printWindow.doPrint(); // Call the custom function defined in the view
                            printWindow.close(); // Close the window
                            updatePrintIconColor(orderNums, invoiceType);
                        };
                    },
                    error: function (data, textStatus, errorThrown) {
                        Swal.fire('Sorry!', data.responseText, 'error');
                    }
                });
            }

            function updatePrintIconColor(orderNums, invoiceType) {
                if(orderNums.length == 1) {
                    // Select the row containing the order number
                    let $row = $(`#datatable tbody tr td:nth-child(5):contains("${orderNums[0]}")`).parent();                    
                    applyToRow($row, invoiceType);
                }
                else {
                    // Get the DataTable instance
                    let table = $('#datatable').DataTable();
                    // Use the DataTable API to get all rows
                    table.rows().every(function() {
                        let $row = $(this.node());                        
                        let orderNum = $row.find('td:nth-child(5)').text().trim();                        
                        if (orderNums.includes(orderNum)) {                            
                            applyToRow($row, invoiceType);
                        }
                    });
                }
            }

            function applyToRow($row, type) {
                if (type === 'Sales' || type === 'Sales & Tax') {
                    let $salesIcon = $row.find('td:nth-child(6) a');                    
                    if ($salesIcon.length) {
                        $salesIcon.css('color', 'gray');
                    }
                }
                if (type === 'Tax' || type === 'Sales & Tax') {
                    let $taxIcon = $row.find('td:nth-child(7) a');
                    if ($taxIcon.length) {
                        $taxIcon.css('color', 'gray');
                    }
                }
            }

            $('body').on('click', '.show', function (event) {
                let orderNum = $(this).attr('data-order');
                let orders = getOrderNumbers();

                // Create a form element
                let form = $('<form>', {
                    'method': 'POST',
                    'action': "{{ route('invoices.show') }}"
                });

                // Add CSRF token
                let csrfToken = $('meta[name="csrf-token"]').attr('content');
                form.append($('<input>', { 'type': 'hidden', 'name': '_token',    'value': csrfToken }));

                // Add the data as hidden inputs
                form.append($('<input>', { 'type': 'hidden', 'name': 'order_num', 'value': orderNum }));
                form.append($('<input>', { 'type': 'hidden', 'name': 'orders',    'value': orders }));
                form.append($('<input>', { 'type': 'hidden', 'name': 'type',      'value': '{{ $invoice_type }}' }));

                // Append the form to the body and submit it
                $('body').append(form);
                form.submit();
            });
        });
    </script>
@endpush

@section('footerScript')    
    <script src="{{ asset('plugins/sweet-alert2/sweetalert2.min.js') }}"></script>
    <script src="{{ asset('assets/pages/jquery.sweet-alert.init.js') }}"></script>
    <script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables/dataTables.bootstrap4.min.js') }}"></script>
    <script type="text/javascript">
        $(window).on('load', function() {
            $("body").toggleClass("enlarge-menu");
        });
    </script>
@stop