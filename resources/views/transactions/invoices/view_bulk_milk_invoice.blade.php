@extends('app-layouts.admin-master')

@section('title', 'Bulk Milk Invoice')

@section('headerStyle')
    <link href="{{ asset('plugins/sweet-alert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('assets/css/show-invoice-bc.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('assets/css/print-invoice-bc.css') }}" rel="stylesheet" type="text/css" media="print">
@stop

@section('content')
    <div class="container-fluid">
        <!-- Page-Title -->
        <div class="row">
            <div class="col-sm-12">
                @component('app-components.breadcrumb-4')
                    @slot('title') View Bulk Milk Invoice @endslot
                    @slot('item1') Transactions @endslot
                    @slot('item2') Invoices @endslot
                    @slot('item3') Bulk Milk Invoices @endslot
                @endcomponent
            </div><!--end col-->
        </div>
        <!-- end page title end breadcrumb -->

        <div class="row">
            <div class="col-lg-10 mx-auto">
                <div class="card">
                    <div class="card-body">
                        <div class="row mb-4">
                            <div class="col-lg-12">
                                <div class="float-right">
                                    <a id="btnPrint" href="#" class="btn btn-pink py-1 mr-2"><i class="fa fa-print"></i></a>
                                    <a id="btnPrev" href="#" class="btn btn-info py-1 mr-1">Prev</a>
                                    <a id="btnNext" href="#" class="btn btn-secondary py-1">Next</a>
                                </div>
                            </div>
                        </div>

                        <!-- Invoice Wrapper -->
                        <div id="invoice-wrapper">
                            <div style="border: 1px solid;">
                                <div class="row mt-2">
                                    <div class="col-md-12">
                                        <div class="text-right"><span id="invoice-for" class="text-right mt-2 pr-3 d-none">ORIGINAL FOR RECIPIENT</span></div>
                                        <div class="text-center my-2"><span class="invoice-title px-5">INVOICE</span></div>
                                    </div>
                                </div>

                                <!-- Company Info -->
                                <div class="row mx-0 border-line">
                                    <div class="col-md-12">
                                        <img src="{{ asset('assets/images/logo-eng.png') }}" alt="Company Logo" class="company-logo">
                                        <span class="name">AASAII FOOD PRODUCTT</span>
                                        <span class="address">14-A, Vaiyapurigoundanoor, Uppidamangalam P.O., Karur - 639114.</span>
                                        <span class="number">
                                            GST No : 33AANFA9261A1ZP &emsp;&emsp;&emsp;
                                            Mobile No : 9842089525
                                        </span>
                                    </div>
                                </div>

                                <!-- Customer and Invoice Details -->
                                <div class="row mx-0 border-line">
                                    <div class="col-md-4 py-2" style="border-right: 1px solid;">
                                        <span class="mb-1 fw-bold">Billing Address</span> <br/>
                                        <div class="ml-2">
                                            <span class="fw-bold">{{ $invoice->customer_name }}</span> <br/>
                                            {!! nl2br(e($invoice->customer_data->billAddr)) !!}<br/>
                                            GST No : {{ $invoice->customer_data->gst }} <br/>
                                            Cell No : {{ $invoice->customer_data->mobile }}
                                        </div>
                                    </div>
                                    <div class="col-md-4 py-2" style="border-right: 1px solid;">
                                        <span class="mb-1 fw-bold">Delivery Address</span> <br/>
                                        <div class="ml-2">
                                            <span class="fw-bold">{{ $invoice->customer_name }}</span> <br/>
                                            {!! nl2br(e($invoice->customer_data->deliAddr)) !!} <br/>
                                        </div>
                                    </div>
                                    <div class="col-md-4 py-2">
                                        <span class="detail-label ml-1 mt-2">Invoice No.</span>  : <span class="ml-1 fw-bold">{{ $invoice->invoice_num }}</span><br>
                                        <span class="detail-label ml-1">Invoice Date</span>      : <span class="ml-1">{{ displayDate($invoice->invoice_date) }}</span><br>
                                        <span class="detail-label ml-1">Vehicle No.</span>       : <span class="ml-1">{{ $invoice->vehicle_num }}</span><br>
                                        <span class="detail-label ml-1">Driver Name</span>       : <span class="ml-1">{{ $invoice->driver_name }}</span><br>
                                        <span class="detail-label ml-1">Driver Mobile</span>     : <span class="ml-1">{{ $invoice->driver_mobile_num }}</span><br>
                                    </div>
                                </div>

                                <!-- Invoice Items Table -->
                                <div class="row mb-2">
                                    <div class="col-lg-12">
                                        <div class="table-responsive">
                                            <table class="invoice-table">
                                                <thead>
                                                    <tr class="text-right">
                                                        <td class="text-center">S.NO</td>
                                                        <td class="text-left">PARTICULARS</td>
                                                        <td>QTY</td>
                                                        <td>FAT</td>
                                                        <td>SNF</td>
                                                        <td>TS</td>
                                                        <td>RATE</td>
                                                        <td class="pr-2">AMOUNT</td>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($invoice_items as $invoiceItem)
                                                        <tr class="text-right" style="height:0; border-bottom: 1px">
                                                            <td class="text-center">{{ $loop->index + 1 }}</td>
                                                            <td class="text-left">{{ $invoiceItem->product_name . " - " . $invoiceItem->hsn_code }}</td>
                                                            <td>{{ getTwoDigitPrecision($invoiceItem->qty_ltr) }}</td>
                                                            <td>{{ getTwoDigitPrecision($invoiceItem->fat) }}</td>
                                                            <td>{{ getTwoDigitPrecision($invoiceItem->snf) }}</td>
                                                            <td>{{ number_format($invoiceItem->ts, 3, '.', '') }}</td>
                                                            <td>{{ getTwoDigitPrecision($invoiceItem->rate) }}</td>
                                                            <td class="pr-2">{{ getTwoDigitPrecision($invoiceItem->amount) }}</td>
                                                        </tr>
                                                    @endforeach
                                                    <!-- Empty Row -->
                                                    <tr class="print-visible">
                                                        <td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td>
                                                    </tr>
                                                </tbody>
                                                <tfoot>
                                                    <tr class="text-right">
                                                        <td colspan="2" class="text-center">Total</td>
                                                        <td>{{ getTwoDigitPrecision($invoice_items->sum('qty_ltr')) }}</td>
                                                        <td></td>
                                                        <td></td>
                                                        <td></td>
                                                        <td></td>
                                                        <td class="pr-2">{{ getTwoDigitPrecision($invoice->tot_amt) }}</td>
                                                    </tr><!--end tr-->
                                                </tfoot>
                                            </table><!--end table-->
                                        </div>  <!--end /div-->
                                    </div>  <!--end col-->
                                </div><!--end row-->

                                <!-- Totals -->
                                <div class="row mx-0 border-line">
                                    <div class="col-md-8">
                                    </div>
                                    <div class="col-md-4" style="padding-right: 8px; padding-bottom: 6px;">
                                        <table width="100%">
                                            <tr>
                                                <td class="title-col">Amount</td>
                                                <td class="amt-col">{{ getTwoDigitPrecision($invoice->tot_amt) }}</td>
                                            </tr>
                                            <tr>
                                                <td class="title-col">TCS</td>
                                                <td class="amt-col">{{ getTwoDigitPrecision($invoice->tcs) }}</td>
                                            </tr>
                                            <tr>
                                                <td class="title-col">Round Off</td>
                                                <td class="amt-col">{{ getRoundOffWithSign($invoice->round_off) }}</td>
                                            </tr>
                                            <tr>
                                                <td class="title-col">Net Amount</td>
                                                <td class="amt-col"><b>{{ getTwoDigitPrecision($invoice->net_amt) }}</b></td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>

                                <!-- Bank Details -->
                                <div class="row mx-0 border-line">
                                    <div class="col-md-6">
                                        <span class="bank-label mt-2">Bank</span>   : <span class="bank-text">HDFC BANK</span><br>
                                        <span class="bank-label">A/C No</span>      : <span class="bank-text">50200094066596</span><br>
                                        <span class="bank-label">Branch</span>      : <span class="bank-text">KARUR</span><br>
                                        <span class="bank-label">IFSC</span>        : <span class="bank-text">HDFC0000566</span><br>
                                    </div>
                                    <div class="col-md-6">
                                        <span class="sign-line mt-2">for AASAII FOOD PRODUCTT</span>
                                        <span class="sign-line sign-gap">Authorized Signatory&emsp;</span>
                                    </div>
                                </div>

                                <!-- Footer -->
                                <div class="row my-1">
                                    <div class="col-lg-12">
                                        <p class="footer-text text-center">We declare that this invoice shows the actual price of the goods description and that all particulars are true and correct.</p>
                                    </div>
                                </div><!--end row-->
                            </div>
                        </div><!--end invoice-wrapper-->

                    </div><!--end card-body-->
                </div><!--end card-->
            </div><!--end col-->
        </div><!--end row-->   
    </div>
@stop

@push('custom-scripts')
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.js"></script>
    <script src="{{ asset('assets/js/print-invoice-bc.js') }}"></script>
    <script>
        $(document).ready(function () {
            $.ajaxSetup({
                headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
            });
            
            var invoiceNums = "{{ $invoice_nums }}";
            var invoices = invoiceNums.split(',');

            $(document).on('keydown', function(event) {
                if (event.ctrlKey && event.key.toUpperCase() === 'P') {
                    // Prevent default print event
                    event.preventDefault();
                    // Trigger btnPrint click when Ctrl+P is pressed
                    $('#btnPrint').click();
                }
                else if (event.key === 'ArrowLeft') {
                    $('#btnPrev').click();
                }
                else if (event.key === 'ArrowRight') {
                    $('#btnNext').click();
                }
            });

            $('#btnPrint').on("click", function () {
                // Define the labels for each copy
                var labels = [ "ORIGINAL FOR RECIPIENT", "DUPLICATE FOR TRANSPORTER", "TRIPLICATE FOR SUPPLIER" ];
                printInvoice(labels);
            });

            $('#btnPrev').on("click", function () {
                var index = invoices.indexOf("{{$invoice->invoice_num}}");
                if(index == 0) {
                    Swal.fire('Sorry!','No Previous Invoice!','warning');
                }
                else {
                    var invoiceNum = invoices[index - 1];
                    showInvoice(invoiceNum);
                }
            });

            $('#btnNext').on("click", function () {
                var index = invoices.indexOf("{{$invoice->invoice_num}}");
                if(index == invoices.length-1) {
                    Swal.fire('Sorry!','No Next Invoice!','warning');
                }
                else {
                    var invoiceNum = invoices[index + 1];
                    showInvoice(invoiceNum);
                }
            });

            function showInvoice(invoiceNum) {
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
            }
        });

        function doPrint() {
            $('#btnPrint').click();
        }
    </script>
@endpush

@section('footerScript')
    <script src="{{ asset('plugins/sweet-alert2/sweetalert2.min.js') }}"></script>
    <script src="{{ asset('assets/pages/jquery.sweet-alert.init.js') }}"></script>
@stop