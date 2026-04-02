@extends('app-layouts.admin-master')

@section('title', 'Customer Account Report')

@section('headerStyle')
    <link href="{{ asset('plugins/sweet-alert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('plugins/animate/animate.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('assets/css/report-style.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('assets/css/app-style-v1.css') }}" rel="stylesheet" type="text/css">
    <style type="text/css">
        @media print {
            @page {
                size: A4;
                margin: 36pt;
            }
        }
    </style>
@stop

@section('content')
    <div class="container-fluid">
        <!-- Page-Title -->
        <div class="row">
            <div class="col-sm-12">
                @component('app-components.breadcrumb-3')
                    @slot('title') Customer Account Report @endslot
                    @slot('item1') Reports @endslot
                    @slot('item2') Customer Reports @endslot
                @endcomponent
            </div>
        </div>
        <!-- end page title end breadcrumb -->

        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                                                      
                        <form method="post" action="{{route('report.customer.account')}}">
                            <div class="row">
                                @csrf
                                <label class="my-text mx-2">From</label>
                                <input type="date" name="from_date" id="dt-from" value="{{ $dates['from'] }}" class="my-control mr-2">
                                <label class="my-text mx-2">To</label>
                                <input type="date" name="to_date" id="dt-to" value="{{ $dates['to'] }}" class="my-control mr-3">                                
                                <div class="input-group mx-2" style="width:350px">
                                    <span class="input-group-prepend">
                                        <button type="button" class="btn btn-info"><i class="fas fa-search"></i></button>
                                    </span>
                                    <input type="text" name="customer_name" id="act-customer-name" class="form-control" placeholder="Customer">
                                    <input type="hidden" name="customer_id" id="hdn-customer-id">
                                </div>
                                <input type="submit" value="Submit" class="btn btn-gradient-primary btn-sm px-3 mx-2"/>
                                <button id="btn-print" type="button" class="btn btn-pink btn-sm px-2 mx-2" aria-label="Print" title="Print">&nbsp;<i class="fa fa-print"></i>&nbsp;</button>
                                <button id="btn-export" class="btn btn-pink py-0 px-2 ml-1"><i class="mdi mdi-file-excel font-18"></i></button>
                            </div>
                        </form>                                                    
                        <hr/>

                        @if(!$records)
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
                                        @include('app-components.print-header-2')
                                        <h2 class="title print-only"><u>CUSTOMER LEDGER</u></h2>
                                        <h3 class="app-h3 dark-blue">{{ $customer->customer_name }}</h3>
                                        <h3 class="app-h3 ">{!! nl2br(e($customer->address_lines)) !!}</h3>
                                        <h3 class="app-h3 pb-2">{{ formatDateRangeAsDMY($dates['from'],$dates['to']) }}</h3>                                                                               
                                    </div>
                                </div>

                                <table class="app-table">
                                    <thead class="thead-light">
                                        <tr>                                            
                                            <th class="text-center">Date</th>
                                            <th class="text-center">Particulars</th>                                          
                                            <th class="text-left pl-2">Vch Type</th>
                                            <th class="text-right pr-2">Vch No.</th>                                            
                                            <th class="text-right pr-2">Debit</th>
                                            <th class="text-right pr-2">Credit</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            @php($balance = $balances['Opening Balance'])
                                            <td class="text-center">{{ $balance['date'] }}</td>
                                            <td class="pl-2" style="white-space: pre;">{{ $balance['debit'] ? "Cr   " : "Dr   " }} Opening Balance</td>
                                            <td></td>
                                            <td></td>
                                            <td class="text-right pr-2" style="font-weight:600">{{ $balance['debit'] }}</td>
                                            <td class="text-right pr-2" style="font-weight:600">{{ $balance['credit'] }}</td>
                                        </tr>
                                        @foreach($records as $record)
                                            <tr>                                                
                                                <td class="text-center">{{ $record['date'] }}</td>
                                                <td class="pl-2" style="white-space: pre;">{{ $record['particulars'] }}</td>
                                                <td class="pl-2">{{ $record['vtype'] }}</td>
                                                <td class="text-right pr-2">{{ $record['vnum'] }}</td>
                                                <td class="text-right pr-2">{{ $record['debit'] }}</td>
                                                <td class="text-right pr-2">{{ $record['credit'] }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot class="thead-light">
                                        <tr>
                                            <td colspan="4"></td>
                                            <td class="text-right pr-2" style="font-weight:600">{{ $totals['debit'] }}</td>
                                            <td class="text-right pr-2" style="font-weight:600">{{ $totals['credit'] }}</td>
                                        </tr>
                                        <tr>
                                            @php($balance = $balances['Closing Balance'])
                                            <td class="text-center">{{ $balance['date'] }}</td>
                                            <td class="pl-2" style="white-space: pre;">{{ $balance['debit'] ? "Cr   " : "Dr   " }} Closing Balance</td>
                                            <td></td>
                                            <td></td>
                                            <td class="text-right pr-2" style="font-weight:600">{{ $balance['debit'] }}</td>
                                            <td class="text-right pr-2" style="font-weight:600">{{ $balance['credit'] }}</td>
                                        </tr>
                                        <tr>                                            
                                            <td colspan="4"></td>
                                            <td class="text-right pr-2">{{ $totals['total'] }}</td>
                                            <td class="text-right pr-2">{{ $totals['total'] }}</td>
                                        </tr>
                                    </tfoot>
                                </table>

                                <div class="print-footer" style="margin-top: 40pt; width: 80%;">
                                    <table class="bank-table" border="1" style="border-collapse: collapse;">
                                        <thead class="thead-light">
                                            <tr>
                                                <th colspan="4" class="text-center p-2">Account Details</th>
                                            </tr>
                                        </thead>
                                        <tbody class="thead-light">
                                            <tr>
                                                <td class="label">Bank</td>
                                                <td class="content section-divider">{{ $banks[0]->bank_name }}</td>
                                                <td class="label">Bank</td>
                                                <td class="content">{{ $banks[1]->bank_name }}</td>
                                            </tr>
                                            <tr>
                                                <td class="label">A/C No</td>
                                                <td class="content section-divider">{{ $banks[0]->acc_number }}</td>
                                                <td class="label">A/C No</td>
                                                <td class="content">{{ $banks[1]->acc_number }}</td>
                                            </tr>
                                            <tr>
                                                <td class="label">Branch</td>
                                                <td class="content section-divider">{{ $banks[0]->branch }}</td>
                                                <td class="label">Branch</td>
                                                <td class="content">{{ $banks[1]->branch }}</td>
                                            </tr>
                                            <tr>
                                                <td class="label">IFSC</td>
                                                <td class="content section-divider">{{ $banks[0]->ifsc }}</td>
                                                <td class="label">IFSC</td>
                                                <td class="content">{{ $banks[1]->ifsc }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
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
    <script src="{{ asset('assets/js/customer-selection5.js') }}"></script>
    <script>
        $(document).ready(function () {
            $.ajaxSetup({
                headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
            });

            doInit();

            function doInit() {
                $("#dt-from").trigger('change');
                clearRepeatedDates();
            }

            $('#dt-from').change(function() {
                let date = $(this).val();
                $('#dt-to').attr('min',date);
            });

            $('#btn-print').on("click", function () {                
                var originalContents = $('body').html();
                var printContents = $('#report-div').html();
                $('body').html(printContents);
                window.print();
                $('body').html(originalContents);
            });

            $('#btn-export').click(function(event) {
                event.preventDefault();
                const count = @json(count($records));
                if(count === 0) {
                    Swal.fire('Sorry','No data found to download','warning');
                }
                else {
                    const query = {
                        fromDate: $("#fromDate").val(),
                        toDate: $("#toDate").val(),  
                        customerId : $("#customerId").val()                       
                    };
                    const url = "{{ route('export.customer.account') }}?" + $.param(query);
                    window.location = url;
                }
            });

            function clearRepeatedDates() {
                let lastDate = null; // Variable to hold the previous date
    
                // Loop through each table row in tbody
                $("tbody tr").slice(1).each(function() {
                    let currentDate = $(this).find("td:first").text().trim(); // Get the date in the first column
                    
                    if (currentDate === lastDate) {
                        // If the current date is the same as the last date, empty the cell
                        $(this).find("td:first").text('');
                    } else {
                        // Update lastDate with the current date
                        lastDate = currentDate;
                    }
                });
            }
        });  
    </script>
@endpush 

@section('footerScript')
    <script src="{{ asset('plugins/sweet-alert2/sweetalert2.min.js') }}"></script>
    <script src="{{ asset('assets/pages/jquery.sweet-alert.init.js') }}"></script>
@stop