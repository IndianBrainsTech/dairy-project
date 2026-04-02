@extends('app-layouts.admin-master')

@section('title', 'Zero Value Items Report')

@section('headerStyle')
    <link href="{{ asset('plugins/sweet-alert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('plugins/animate/animate.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('assets/css/report-style.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('assets/css/my-actxt.css') }}" rel="stylesheet" type="text/css">
@stop

@section('content')
    <div class="container-fluid">
        <!-- Page-Title -->
        <div class="row">
            <div class="col-sm-12">
                @component('app-components.breadcrumb-3')
                    @slot('title') Zero Value Items Report @endslot
                    @slot('item1') Reports @endslot
                    @slot('item2') Sales Reports @endslot
                @endcomponent
            </div>
        </div>
        <!-- end page title end breadcrumb -->

        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                         
                        <div class="row">
                            <input type="date" id="from-date" value="{{ $dates['from'] }}" class="my-control mx-2" style="max-width:120px">
                            <input type="date" id="to-date" value="{{ $dates['to'] }}" class="my-control mr-3" style="max-width:120px">
                            <label class="my-text">Report Type</label>
                            <select name="report_type" id="report-type" class="my-control">
                                <option value="Itemized" @selected($report_type=="Itemized")>Itemized</option>
                                <option value="Summary" @selected($report_type=="Summary")>Summary</option>
                            </select>
                            <button id="btn-submit" type="button" class="btn btn-primary py-0 px-3 mx-3" aria-label="Submit" title="Submit">Submit</button>
                            <button id="btn-print" type="button" class="btn btn-pink py-0 px-2 mr-2" aria-label="Print" title="Print">&nbsp;<i class="fa fa-print"></i>&nbsp;</button>
                            <!-- <button id="btn-export" type="button" class="btn btn-pink py-0 px-2" aria-label="Excel" title="Excel"><i class="mdi mdi-file-excel font-18"></i></button> -->
                        </div>
                        <hr/>

                        @if(is_null($records) || count($records) == 0)
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
                                            <h3 class="text-center pb-2" style="color:maroon">Aasaii Food Productt</h3>
                                            <h3 class="text-center pb-2" style="color:blue">Zero Value Items Report</h3>
                                            <h4 class="text-center pb-3">{{ formatDateRange($dates['from'],$dates['to']) }}</h4>
                                        </div>
                                    </div>
                                </div>
    
                                <table id="reportTable" class="text-nowrap">
                                    <thead>
                                        <tr class="text-center no-print" style="background-color: white">
                                            <td></td>
                                            <td></td>
                                            <td>
                                                <select id="invoice-type" class="my-control">
                                                    <option value="Both" @selected($invoice_type=="Both")></option>
                                                    <option value="Sales" @selected($invoice_type=="Sales")>Sales Invoices</option>
                                                    <option value="Tax" @selected($invoice_type=="Tax")>Tax Invoices</option>
                                                </select>
                                            </td>
                                            <td>
                                                <div class="input-group" style="width:100%; min-width:260px">
                                                    <span class="input-group-prepend">
                                                        <button type="button" class="btn btn-info py-1" tabindex="-1"><i class="fas fa-search"></i></button>
                                                    </span>
                                                    <input type="text" id="customer-name" placeholder="Customer" class="my-control pl-2" style="width:84%">
                                                    <input type="hidden" id="customer-id">
                                                </div>
                                            </td>                                        
                                            <td>
                                                <div class="input-group" style="width:100%; min-width:310px">
                                                    <span class="input-group-prepend">
                                                        <button type="button" class="btn btn-info py-1" tabindex="-1"><i class="fas fa-search"></i></button>
                                                    </span>
                                                    <input type="text" id="item-name" placeholder="Item" class="my-control pl-2" style="width:84%">
                                                    <input type="hidden" id="item-id">
                                                </div>
                                            </td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td><button id="btn-filter" class="btn btn-primary py-0 px-3">Filter</button></td>
                                        </tr>
                                        <tr class="thead-light text-center">
                                            <th>S.No</th>
                                            <th>Date</th>
                                            <th>Invoice Number</th>
                                            <th>Customer</th>
                                            <th>Item</th>
                                            <th>Sample<br/>Qty</th>
                                            <th>Damage<br/>Qty</th>
                                            <th>Spoilage<br/>Qty</th>
                                            <th>Free<br/>Qty</th>
                                            <th>Total<br/>Qty</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($records as $record)
                                            <tr>
                                                <td class="text-center">{{ $record['sno'] }}</td>
                                                <td class="text-center">{{ $record['date'] }}</td>
                                                <td class="text-center">{{ $record['number'] }}</td>                                            
                                                <td class="text-left pl-2">{{ $record['customer'] }}</td>
                                                <td class="text-left pl-2">{{ $record['item'] }}</td>
                                                <td class="text-right pr-2">{{ getTwoDigitPrecision($record['sample']) }}</td>
                                                <td class="text-right pr-2">{{ getTwoDigitPrecision($record['damage']) }}</td>
                                                <td class="text-right pr-2">{{ getTwoDigitPrecision($record['spoilage']) }}</td>
                                                <td class="text-right pr-2">{{ getTwoDigitPrecision($record['free']) }}</td>
                                                <td class="text-right pr-2">{{ getTwoDigitPrecision($record['total']) }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot class="thead-light">
                                        <tr class="text-right pr-2">
                                            <th colspan="5" class="text-center">Grand Total</th>
                                            <th class="text-right pr-2">{{ getTwoDigitPrecision($totals['sample']) }}</th>
                                            <th class="text-right pr-2">{{ getTwoDigitPrecision($totals['damage']) }}</th>
                                            <th class="text-right pr-2">{{ getTwoDigitPrecision($totals['spoilage']) }}</th>
                                            <th class="text-right pr-2">{{ getTwoDigitPrecision($totals['free']) }}</th>
                                            <th class="text-right pr-2">{{ getTwoDigitPrecision($totals['total']) }}</th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        @endif
                    </div><!--end card-body--> 
                </div><!--end card--> 
            </div> <!--end col-->
        </div><!--end row-->
    </div><!--container-->
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

            $('#btn-print').on("click", function () {
                var originalContents = $('body').html();
                var printContents = $('#report-div').html();
                $('body').html(printContents);
                window.print();
                $('body').html(originalContents);
            });

            $('#btn-export').click(function(event) {
                event.preventDefault();
                @if(is_null($records))
                    Swal.fire('Sorry','No data found to download','warning');
                @else
                    var query = {
                        from_date   : $("#from-date").val(),
                        to_date     : $("#to-date").val(),
                        report_type : $("#report-type").val()
                    };
                    var url = "{{ route('exports.zero-value') }}?" + $.param(query);
                    window.location = url;
                @endif                
            });

            $('#btn-submit').on("click", function () {
                doSubmit();
            });

            $('#btn-filter').on("click", function () {
                doSubmit();
            });

            $("#from-date").trigger('change');

            function doSubmit() {                
                const fields = {
                    from_date     : $("#from-date").val(),
                    to_date       : $("#to-date").val(),
                    report_type   : $("#report-type").val(),
                    invoice_type  : $("#invoice-type").val(),
                    customer_id   : $("#customer-id").val(),
                    customer_name : $("#customer-name").val(),
                    item_id       : $("#item-id").val(),
                    item_name     : $("#item-name").val(),
                };

                // Create a form element
                let form = $('<form>', { method: 'POST', action: "{{ route('reports.sales.zero-value') }}" });
 
                // Add CSRF token
                let csrfToken = $('meta[name="csrf-token"]').attr('content');
                form.append($('<input>', { type: 'hidden', name: '_token', value: csrfToken }));

                // Add the data as hidden inputs
                $.each(fields, (name, value) => {
                    form.append($('<input>', { type: 'hidden', name: name, value: value }));
                });

                // Append the form to the body and submit it
                $('body').append(form);
                form.submit(); 
            }

            @if($report_type === "Itemized")
                $(window).on('load', function() {
                    $("body").toggleClass("enlarge-menu");
                });
            @endif
        });
    </script>
@endpush 

@section('footerScript')
    <script src="{{ asset('plugins/sweet-alert2/sweetalert2.min.js') }}"></script>
    <script src="{{ asset('assets/pages/jquery.sweet-alert.init.js') }}"></script>
@stop