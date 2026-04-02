@extends('app-layouts.admin-master')

@section('title', 'Bulk Milk Invoices')

@section('headerStyle')
    <link href="{{ asset('plugins/sweet-alert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('plugins/animate/animate.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('plugins/datatables/dataTables.bootstrap4.min.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('assets/css/my-actxt.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('assets/css/my-style.css') }}" rel="stylesheet" type="text/css">
    <style type="text/css">
        .my-text {
            font-size: 14px;
            padding: 4px;
            display: flex;
            align-items: center;
        }
    </style>
@stop

@section('content')
    <div class="container-fluid">
        <!-- Page-Title -->
        <div class="row">
            <div class="col-sm-12">
                @component('app-components.breadcrumb-3')
                    @slot('title') Bulk Milk Invoices @endslot
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

                        <form method="post" action="{{ route('bulk-milk.invoices.index') }}">
                            @csrf
                            <div class="row">
                                <label for="fromDate" class="my-text">From</label>
                                <input type="date" name="fromDate" id="fromDate" value="{{$fromDate}}" class="my-control">
                                <label for="toDate" class="my-text ml-2">To</label>
                                <input type="date" name="toDate" id="toDate" value="{{$toDate}}" class="my-control">
                                <div class="input-group ml-4 mr-2" style="width:400px">
                                    <span class="input-group-prepend">
                                        <button type="button" class="btn btn-info"><i class="fas fa-search"></i></button>
                                    </span>
                                    <input type="text" name="customer" id="customer" placeholder="Customer" class="form-control">
                                    <input type="hidden" name="customerId" id="customerId">
                                </div>
                                <input type="submit" value="Submit" class="btn btn-gradient-primary btn-sm px-3 ml-3"/>
                            </div>
                        </form>
                        <hr/>

                        <div class="table-responsive dash-social">
                            <table id="datatable" class="table table-bordered table-sm dt-responsive nowrap text-center" style="width: 100%;">
                                <thead class="thead-light">
                                    <tr>
                                        <th data-priority="6">S.No</th>
                                        <th data-priority="4">Invoice Date</th>
                                        <th data-priority="1">Invoice Number</th>
                                        <th data-priority="3" class="text-left pl-2">Customer</th>
                                        <th data-priority="5" class="text-right pr-2">Invoice Amount</th>
                                        <th data-priority="2">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($invoices as $invoice)
                                        <tr>
                                            <td>{{ $loop->index + 1 }}</td>
                                            <td>{{ displayDate($invoice->invoice_date) }}</td>
                                            <td>{{ $invoice->invoice_num }}</td>
                                            <td class="text-left pl-2">{{ $invoice->customer_name }}</td>
                                            <td class="text-right pr-2">{{ getTwoDigitPrecision($invoice->net_amt) }}</td>
                                            <td><a href="#" class="show mr2" data-invoice="{{ $invoice->invoice_num }}"><i class="dripicons-preview text-primary font-20"></i></a></td>
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
    <script src="{{ asset('assets/js/customer-autocomplete.js') }}"></script>
    <script>
        $(document).ready(function () {
            $.ajaxSetup({
                headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
            });

            doInit();

            function doInit() {
                let customerJson = @json($customers);
                initCustomerAutocomplete(customerJson);
                @if($customerId != 0)
                    $("#customerId").val('{{ $customerId }}');
                    $("#customer").val('{{ $customer }}');
                @endif

                $('#datatable').dataTable( {
                    "lengthMenu": [[10, 25, 50, 100], [10, 25, 50, 100]],
                    "pageLength": 25,
                } );

                $('#fromDate').change(function() {
                    var date = $(this).val();
                    $('#toDate').attr('min',date);
                });

                $("#fromDate").trigger('change');
            }

            function getInvoiceNumbers() {
                var table = $('#datatable').DataTable();
                var invoiceNums = table.column(2,{search:'applied'}).data().toArray();
                return invoiceNums;
            }

            $('body').on('click', '.show', function (event) {
                var invoiceNum = $(this).attr('data-invoice');
                var invoiceNums = getInvoiceNumbers();

                // Create a form element
                var form = $('<form>', {
                    'method': 'POST',
                    'action': '{{ route("bulk-milk.invoices.show") }}'
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
                    'name': 'invoice_num',
                    'value': invoiceNum
                }));

                form.append($('<input>', {
                    'type': 'hidden',
                    'name': 'invoice_nums',
                    'value': invoiceNums
                }));

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
@stop