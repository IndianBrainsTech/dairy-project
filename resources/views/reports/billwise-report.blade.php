@extends('app-layouts.admin-master')

@section('title', 'Bill Wise Report')

@section('headerStyle')
    <link href="{{ asset('plugins/sweet-alert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('plugins/animate/animate.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('assets/css/report-style.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('assets/css/my-actxt.css') }}" rel="stylesheet" type="text/css">
    <style type="text/css">
        .ui-menu-item .ui-menu-item-wrapper.ui-state-active {
            background: #506ee4 !important;
            font-weight: bold !important;
            color: #ffffff !important;
        }

        .ts1 {
            display: block;
            text-align: center;
            font-size: 18px;
            font-weight: 600;
            padding-bottom: 4px;
        }

        .ts2 {
            display: block;
            text-align: center;
            font-size: 15px;
        }

        .ts3 {
            display: block;
            text-align: center;
            font-size: 15px;
            font-weight: 600;
            color: black;
        }

        @media screen {
             #reportTable thead th {
                background-color: #f1f5fa;
                z-index: 1000;
            }

            /* First row (topmost sticky) */
            #reportTable thead tr.header-row-1 th {
                position: sticky;
                top: 70px;
                z-index: 1010; /* Ensure it's above the second row */
            }

            /* Second row (below the first sticky row) */
            #reportTable thead tr.header-row-2 th {
                position: sticky;
                top: calc(70px + 36px);
                z-index: 1000;
            }
        }
    </style>
@stop

@section('content')
    <div class="container-fluid">
        <!-- Page-Title -->
        <div class="row">
            <div class="col-sm-12">
                @component('app-components.breadcrumb-2')
                    @slot('title') Bill Wise Report @endslot
                    @slot('item1') Reports @endslot
                @endcomponent
            </div>
        </div>
        <!-- end page title end breadcrumb -->

        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">

                        <form method="post" action="{{ route('reports.bill-wise') }}">
                            <div class="row">
                                @csrf
                                <label class="my-text mx-2">From</label>
                                <input type="date" name="from_date" id="from-date" value="{{ $dates['from'] }}" class="my-control mr-2">
                                <label class="my-text mx-2">To</label>
                                <input type="date" name="to_date" id="to-date" value="{{ $dates['to'] }}" class="my-control mr-3">
                                <div class="input-group mx-2" style="width:350px">
                                    <span class="input-group-prepend">
                                        <button type="button" class="btn btn-info"><i class="fas fa-search"></i></button>
                                    </span>
                                    <input type="text" name="customer_name" id="customer-name" class="form-control" placeholder="Customer">
                                    <input type="hidden" name="customer_id" id="customer-id">
                                </div>
                                <label class="my-text">Status</label>
                                <select name="status_type" id="status-type" class="my-control mr-2">
                                    <option value="All" @selected($status_type=="All")>All</option>
                                    <option value="Paid" @selected($status_type=="Paid")>Paid</option>
                                    <option value="Outstanding" @selected($status_type=="Outstanding")>Outstanding</option>
                                    <option value="Zero Value" @selected($status_type=="Zero Value")>Zero Value</option>
                                </select>
                                <input type="submit" value="Submit" class="btn btn-gradient-primary btn-sm px-3 mx-2"/>
                                <button id="btn-print" type="button" class="btn btn-pink py-1 mx-2" title="Print Report"><i class="fa fa-print"></i></button>
                                <!-- <button id="btn-export" type="button" class="btn btn-pink py-0 px-2" title="Export Report"><i class="mdi mdi-file-excel font-18"></i></button> -->
                            </div>
                        </form>
                        <hr/>

                        @if(count($records) == 0)
                            <div class="alert alert-outline-warning alert-warning-shadow mb-0 alert-dismissible fade show" role="alert" style="width:50%; text-align:center; margin:auto">
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true"><i class="mdi mdi-close"></i></span>
                                </button>
                                <strong>Sorry!</strong> No Data Found!
                            </div>
                        @else
                            <div id="report-div">
                                <div class="row">
                                    <div class="col-lg-12">
                                        <div class="print-header">
                                            <span class="ts1" style="color:maroon">Aasaii Food Productt</span>
                                            <span class="ts2">14-A, Vaiyapurigoundanoor, Uppidamangalam P.O., Karur - 639114</span>
                                            <span class="ts2">GST No : 33AANFA9261A1ZP &ensp; </span>
                                            <span class="ts2">Cell No : 9842089525</span>
                                            <hr/>
                                            <span class="ts1 pb-2" style="color:blue"><u>Bill Wise Report</u></span>
                                        </div>
                                    </div>
                                </div>

                                <table id="reportTable" class="text-nowrap">
                                    <thead class="thead-light text-center">
                                        <tr class="header-row-1">
                                            <th rowspan="2">S.No</th>
                                            <th colspan="3">Invoice</th>
                                            <th rowspan="2">Customer</th>
                                            <th colspan="3">Receipts</th>
                                            <th rowspan="2">Outstanding</th>
                                            <th rowspan="2">Status</th>
                                        </tr>
                                        <tr class="header-row-2">
                                            <th>Date</th>
                                            <th>Number</th>
                                            <th>Amount</th>
                                            <th>Date</th>
                                            <th>Number</th>
                                            <th>Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($records as $index => $record)
                                            <tr>
                                                <td class="text-center">{{ $index + 1 }}</td>
                                                <td class="text-center">{{ displayDate($record->invoice_date) }}</td>
                                                <td class="text-center">{{ $record->invoice_num }}</td>
                                                <td class="text-right pr-2">{{ $record->net_amt }}</td>
                                                <td class="text-left pl-2">{{ $record->customer_name }}</td>

                                                {{-- Receipt Date Cell --}}
                                                <td class="text-center">
                                                    @foreach($record->receipts as $receipt)
                                                        <div>{{ displayDate($receipt->receipt_date) }}</div>
                                                    @endforeach
                                                </td>

                                                {{-- Receipt Number Cell --}}
                                                <td class="text-center">
                                                    @foreach($record->receipts as $receipt)
                                                        <div>{{ $receipt->receipt->receipt_num }}</div>
                                                    @endforeach
                                                </td>

                                                {{-- Receipt Amount Cell --}}
                                                <td class="text-right pr-2">
                                                    @foreach($record->receipts as $receipt)
                                                        <div>{{ $receipt->amount }}</div>
                                                    @endforeach
                                                </td>

                                                <td class="text-right pr-2">{{ getEmptyForZero($record->outstanding, '') }}</td>
                                                <td class="text-center">{{ $record->status }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    @if($status_type != "Zero Value")
                                        <tfoot class="thead-light">
                                            <tr style="font-weight:600">
                                                <td colspan="3" class="text-center">Totals</td>
                                                <td class="text-right pr-2">{{ $totals['inv_amt'] }}</td>
                                                <td></td>
                                                <td colspan="2"></td>
                                                <td class="text-right pr-2">{{ getEmptyForZero($totals['rcpt_amt']) }}</td>
                                                <td class="text-right pr-2">{{ getEmptyForZero($totals['oustd_amt']) }}</td>
                                                <td></td>
                                            </tr>
                                        </tfoot>
                                    @endif
                                </table>

                            </div>
                        @endif
                    </div><!--end card-body-->
                </div><!--end card-->
            </div> <!--end col-->
        </div><!--end row-->
    </div><!-- container -->
@stop

@push('custom-scripts')
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.js"></script>
    <script>
        const CUSTOMERS_BY_ROUTE_URL = @json(route('customers.get.route', ':id'));
        const selectedCustomer = @json($customer);
    </script>
    <script src="{{ asset('assets/js/customer-selection4.js') }}"></script>
    <script>
        $(document).ready(function () {
            $.ajaxSetup({
                headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
            }); 

            $('#from-date').change(function() {
                var date = $(this).val();
                $('#to-date').attr('min',date);
            });

            $("#from-date").trigger('change');

            $('#btn-print').on("click", function () {
                const count = "{{ count($records) }}";
                if(count == 0) {
                    Swal.fire('Sorry','No data found to print','warning');
                }
                else {
                    var originalContents = $('body').html();
                    var printContents = $('#report-div').html();
                    $('body').html(printContents);
                    window.print();
                    $('body').html(originalContents);
                }
            });

            $('#btn-export').click(function(event) {
                event.preventDefault();
                const count = "{{ count($records) }}";
                if(count == 0) {
                    Swal.fire('Sorry','No data found to download','warning');
                }
                else {
                    var query = {
                        from_date    : $("#from-date").val(),
                        to_date      : $("#to-date").val(),  
                        customer_id  : $("#customer-id").val(),
                        status_type  : $("#status-type").val(),
                    };
                    var url = "{{ route('export.customer.account') }}?" + $.param(query);
                    window.location = url;
                }
            });
        });
    </script>
@endpush 

@section('footerScript')
    <script src="{{ asset('plugins/sweet-alert2/sweetalert2.min.js') }}"></script>
    <script src="{{ asset('assets/pages/jquery.sweet-alert.init.js') }}"></script>
    <script type="text/javascript">
        $(window).on('load', function() {
            $("body").toggleClass("enlarge-menu");
        });
    </script>
@stop