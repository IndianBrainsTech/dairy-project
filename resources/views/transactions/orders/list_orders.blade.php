@extends('app-layouts.admin-master')

@section('title', 'View Orders')

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
                    @slot('title') View Orders @endslot
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

                        <form method="post" action="{{ route('orders.index') }}">
                            @csrf
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="row mb-2">
                                        <input type="date" name="invoice_date" id="invoiceDate" value="{{$invoiceDate}}" class="my-control ml-2">
                                        <select name="route" id="route" class="my-control">
                                            <option value="0" @selected($routeId==0)>Select Route</option>
                                            @foreach($routes as $route)
                                                <option value="{{$route->id}}" @selected($routeId==$route->id)>{{$route->name}}</option>
                                            @endforeach
                                        </select>
                                        <div class="input-group mr-3" style="width:375px">
                                            <span class="input-group-prepend">
                                                <button type="button" class="btn btn-info"><i class="fas fa-search"></i></button>
                                            </span>
                                            <input type="text" id="customer" class="form-control" placeholder="Customer">
                                            <input type="hidden" name="customer" id="customerId" value="{{$customerId}}">
                                        </div>
                                        <input type="submit" value="Submit" class="btn btn-primary btn-sm ml-3 px-3"/>
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
                                        <th data-priority="2" class="text-center">Invoice Date</th>
                                        <th data-priority="3">Route</th>
                                        <th data-priority="1" class="text-center">Order No</th>
                                        <th data-priority="4">Customer</th>
                                        <th data-priority="6">Status</th>
                                        <th data-priority="5" class="text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($orders as $order)
                                        <tr>
                                            <td class="text-center">{{ $loop->index + 1 }}</td>
                                            <td class="text-center">{{ getIndiaDate($order->invoice_date) }}</td>
                                            <td>{{ $order->route->name }}</td>
                                            <td class="text-center">{{ $order->order_num }}</td>
                                            <td>{{ $order->customer->customer_name }}</td>
                                            <td>{!! getOrderStatusWithBadge($order->invoice_status) !!}</td>
                                            <td class="text-center">
                                                <a href="#" class="show" data-order="{{$order->order_num}}"><i class="dripicons-preview text-primary font-20"></i></a>
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

            let customers = new Map();
            doInit();

            function doInit() {
                restrictMaxToTomorrow('#invoiceDate');
                loadCustomers();

                $('#datatable').dataTable( {
                    "lengthMenu": [[10, 25, 50, 100], [10, 25, 50, 100]],
                    "pageLength": 25,
                } );
            }

            $('#route').change(function () {
                $("#customerId").val(0);
                loadCustomers();
            });

            function loadCustomers() {
                let routeId = $('#route').val();
                customers = new Map();
                $("#customer").val('');
                let url = "{{ route('customers.get.route', ':id') }}".replace(':id', routeId);
                $.get(url, function (data) {
                    var customerList = data.customers;
                    customerList.forEach(function(customer) {
                        customers.set(customer.id, customer.customer_name); // key, value
                    });

                    // Update the autocomplete source after updating customers
                    $("#customer").autocomplete('option', 'source', autocompleteSource1(customers));

                    // Show customer name
                    let custId = parseInt($("#customerId").val());
                    if(custId) {
                        const customer = customers.get(custId);
                        $("#customer").val(customer);
                    }
                });
            }

            $("#customer").autocomplete({
                source: autocompleteSource1(customers),
                autoFocus: true,
                minLength: 0,
                select: function(event, ui) {
                    var name = ui.item.value;
                    var id = getKeyByValue(customers, name);
                    console.log("Customer => Selected ID: " + id + ", Name: " + name);
                    $("#customerId").val(id);
                }
            });

            $('#customer').blur(function () {
                if(!$("#customer").val())
                    $("#customerId").val(0);
            });

            function getOrderNumbers() {
                var table = $('#datatable').DataTable();
                var orderNums = table.column(3,{search:'applied'}).data().toArray();
                return orderNums;
            }

            $('body').on('click', '.show', function (event) {
                var orderNum = $(this).attr('data-order');
                var orders = getOrderNumbers();
                
                // Create a form element
                var form = $('<form>', {
                    'method': 'POST',
                    'action': "{{ route('orders.show') }}"
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