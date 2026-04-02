@extends('app-layouts.admin-master')

@section('title', 'Cancel Invoice')

@section('headerStyle')
    <link href="{{ asset('plugins/sweet-alert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('assets/css/show-invoice.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('assets/css/print-invoice.css') }}" rel="stylesheet" type="text/css" media="print">
@stop

@section('content')
    <div class="container-fluid">
        <!-- Page-Title -->
        <div class="row">
            <div class="col-sm-12">
                @component('app-components.breadcrumb-3')
                    @slot('title') Cancel Invoice @endslot
                    @slot('item1') Transactions @endslot
                    @slot('item2') Invoices @endslot
                @endcomponent
            </div><!--end col-->
        </div>
        <!-- end page title end breadcrumb -->
 
        <div class="row"> 
            <div class="col-lg-10 mx-auto">
                <div class="card">
                    <div class="card-body">

                        <div class="row">
                            <div class="col-md-4 d-flex align-items-center">
                                <div class="form-group mb-0 w-100">
                                    @if($sales_invoice)
                                        <div class="checkbox checkbox-pink">
                                            <input id="chkSalesInvoice" type="checkbox" checked value="{{ $sales_invoice['invoice']['invoice_num'] }}">
                                            <label for="chkSalesInvoice">Sales Invoice ({{ $sales_invoice['invoice']['invoice_num'] }})</label>
                                        </div>
                                    @endif
                                    @if($tax_invoice)
                                        <div class="checkbox checkbox-pink">
                                            <input id="chkTaxInvoice" type="checkbox" checked value="{{ $tax_invoice['invoice']['invoice_num'] }}">
                                            <label for="chkTaxInvoice">Tax Invoice ({{ $tax_invoice['invoice']['invoice_num'] }})</label>
                                        </div>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-5 d-flex align-items-center">
                                <div class="form-group mb-0 w-100">
                                    <textarea id="remarks" rows="2" placeholder="Cancel Reason / Remarks" class="form-control"></textarea>
                                </div>
                            </div>
                            <div class="col-md-3 d-flex align-items-center">
                                <div class="form-group mb-0 w-100 text-center">
                                    <button id="submit" type="button" class="btn btn-primary btn-sm px-3">Cancel Invoice</button>
                                </div>
                            </div>
                        </div>

                    </div><!--end card-body-->
                </div><!--end card-->
            </div><!--end col-->
        </div><!--end row-->

        @if($sales_invoice)
            @php($invoice = $sales_invoice['invoice'])
            @php($invoiceItems = $sales_invoice['invoiceItems'])
            <div class="row"> 
                <div class="col-lg-10 mx-auto">
                    <div class="card">
                        <div class="card-body">
                            
                            <div id="sales-invoice-wrapper">
                                <hr class="hr1"/>
                                <div class="invoice-header">
                                    <div></div>
                                    <div style="font-weight:700">DELIVERY CUM INVOICE</div>
                                    <div><!--UserName &emsp;--> {{ getIndiaDateTime($invoice->order_dt) }}</div>
                                </div>
                                <hr class="hr1 mb-0"/>

                                <div class="row">
                                    <div class="col-lg-12">
                                        <table class="info-table" width="100%">
                                            <tr>
                                                <td width="30%">
                                                    <span class="text-bold">Aasaii Food Productt</span><br/>
                                                    14-A, Vaiyapurigoundanoor,<br/>
                                                    Uppidamangalam P.O.,<br/>
                                                    Karur 639 114.<br/>
                                                    GST No : 33AANFA9261A1ZP<br/>
                                                    Cell No : 9842089525
                                                </td>
                                                <td width="35%">
                                                    <span class="text-bold">Billing Address:</span><br/>
                                                    <span class="text-bold">{{ $invoice->customer_name }}</span><br/>
                                                    {{ $invoice->billing_address }}<br/>
                                                    GST No : {{ $invoice->gst_number }}<br/>
                                                    Cell No : {{ $invoice->mobile_num }}
                                                </td>
                                                <td width="35%">
                                                    <span class="text-bold">Delivery Address:</span><br/>
                                                    <span class="text-bold">{{ $invoice->customer_name }}</span><br/>
                                                    {{ $invoice->delivery_address }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    Route : <span class="text-bold">{{ $invoice->route_name }}</span>
                                                </td>
                                                <td>
                                                    Vehicle Number : <span class="text-bold">{{ $invoice->vehicle_num }}</span><br/>
                                                    Driver Name : <span class="text-bold">{{ $invoice->driver_name }}</span>
                                                </td>
                                                <td class="pb-2">
                                                    Invoice Number : <span class="text-bold">{{ $invoice->invoice_num }}</span><br/>
                                                    Invoice Date : <span class="text-bold">{{ displayDate($invoice->invoice_date) }}</span>
                                                </td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                                
                                <div class="row mb-2">
                                    <div class="col-lg-12">
                                        <div class="table-responsive">
                                            <table class="invoice-table">
                                                <thead>
                                                    <tr class="text-right">
                                                        <td class="text-center" width="60px">S.No</td>
                                                        <td class="text-left">Particulars</td>
                                                        <td class="text-center" width="120px">HSN Code</td>
                                                        <td width="120px">Crates</td>
                                                        <td width="120px">Qty</td>
                                                        <td width="120px">Amount</td>
                                                    </tr><!--end tr-->
                                                </thead>
                                                <tbody>
                                                    @foreach($invoiceItems as $invoiceItem)
                                                        <tr class="text-right" style="height:0; border-bottom: 1px">
                                                            <td class="text-center">{{ $loop->index + 1 }}</td>
                                                            <td class="text-left">{{ $invoiceItem->product_name }}
                                                                @if($invoiceItem->item_category != "Regular")
                                                                    &nbsp;[{{ $invoiceItem->item_category }}]
                                                                @endif
                                                            </td>
                                                            <td class="text-center">{{ $invoiceItem->hsn_code }}</td>
                                                            <td>{{ getNumberOrEmpty($invoiceItem->crates) }}</td>
                                                            <td>{{ getTwoDigitPrecision($invoiceItem->qty) }}</td>
                                                            <td>{{ getTwoDigitPrecision($invoiceItem->amount) }}</td>
                                                        </tr>
                                                    @endforeach
                                                    <!-- Empty Row -->
                                                    <tr class="print-visible">
                                                        <td></td><td></td><td></td><td></td><td></td>
                                                        <!-- <td colspan="5"></td> -->
                                                    </tr>
                                                </tbody>
                                                <tfoot>
                                                    <tr class="text-right">
                                                        <td colspan="3" class="text-center">Total</td>
                                                        <td>{{ getTwoDigitPrecision($invoice->crates) }}</td>
                                                        <td>{{ getTwoDigitPrecision($invoice->qty) }}</td>
                                                        <td>{{ getTwoDigitPrecision($invoice->amount) }}</td>
                                                    </tr><!--end tr-->
                                                </tfoot>
                                            </table><!--end table-->
                                        </div>  <!--end /div-->
                                    </div>  <!--end col-->
                                </div><!--end row-->

                                <div class="row">
                                    <div class="col-md-4">
                                        <span class="label">Last Inamt. Issued</span>: {{ $invoice->last_in_amount }}<br>
                                        <span class="label">Empty Crates Received</span>: {{ $invoice->empty_crates_received }}<br>
                                        <span class="label">Amount Received</span>: {{ $invoice->amount_received }}<br>
                                        <span class="label">Last Receipt</span>: {{ $invoice->last_receipt }}<br>
                                        <span class="label">Last Crates Received</span>: {{ $invoice->last_crates_received }}
                                    </div>
                                    <div class="col-md-4">
                                        <span class="sign-line1">For Aasaii Food Productt,</span>
                                        <span class="sign-line2">Distributor / Dealer Authorized Signatory</span>
                                    </div>
                                    <div class="col-md-4">
                                        <table width="100%">
                                            <tr>
                                                <td class="title-col">Amount</td>
                                                <td class="amt-col">{{ getTwoDigitPrecision($invoice->amount) }}</td>
                                            </tr>
                                            <tr>
                                                <td class="title-col">TCS</td>
                                                <td class="amt-col">{{ getTwoDigitPrecision($invoice->tcs) }}</td>
                                            </tr>
                                            @if($invoice->discount)
                                                <tr>
                                                    <td class="title-col">Discount</td>
                                                    <td class="amt-col">{{ getTwoDigitPrecision($invoice->discount) }}</td>
                                                </tr>
                                            @endif
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

                                <hr class="hr2"/>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <p class="footer-text">Please make sure the consignment is intact and check the weight before taking delivery of the consignment. Our responsibility ceases on delivery of goods to the customers or their representatives or carriers. Delayed payment will be charged interest @ 24% per annum. All disputes will be settled at Karur jurisdiction.</p>
                                    </div>
                                </div><!--end row-->
                                <hr class="hr2"/>

                            </div><!--end sales-invoice-wrapper-->
                            
                        </div><!--end card-body-->
                    </div><!--end card-->
                </div><!--end col-->
            </div><!--end row-->
        @endif

        @if($tax_invoice)
            @php($invoice = $tax_invoice['invoice'])
            @php($invoiceItems = $tax_invoice['invoiceItems'])
            @php($gstTable = $tax_invoice['gstTable'])
            <div class="row">
                <div class="col-lg-10 mx-auto">
                    <div class="card">
                        <div class="card-body">

                            <div id="tax-invoice-wrapper" class="second-page">
                                <hr class="hr1"/>
                                <div class="invoice-header">
                                    <div></div>
                                    <div style="font-weight:700">DELIVERY CUM INVOICE</div>
                                    <div><!--UserName &emsp;--> {{ getIndiaDateTime($invoice->order_dt) }}</div>
                                </div>
                                <hr class="hr1 mb-0"/>

                                <div class="row">
                                    <div class="col-lg-12">
                                        <table class="info-table" width="100%">
                                            <tr>
                                                <td width="30%">
                                                    <span class="text-bold">Aasaii Food Productt</span><br/>
                                                    14-A, Vaiyapurigoundanoor,<br/>
                                                    Uppidamangalam P.O.,<br/>
                                                    Karur 639 114.<br/>
                                                    GST No : 33AANFA9261A1ZP<br/>
                                                    Cell No : 9842089525
                                                </td>
                                                <td width="35%">
                                                    <span class="text-bold">Billing Address:</span><br/>
                                                    <span class="text-bold">{{ $invoice->customer_name }}</span><br/>
                                                    {{ $invoice->billing_address }}<br/>
                                                    GST No : {{ $invoice->gst_number }}<br/>
                                                    Cell No : {{ $invoice->mobile_num }}
                                                </td>
                                                <td width="35%">
                                                    <span class="text-bold">Delivery Address:</span><br/>
                                                    <span class="text-bold">{{ $invoice->customer_name }}</span><br/>
                                                    {{ $invoice->delivery_address }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    Route : <span class="text-bold">{{ $invoice->route_name }}</span>
                                                </td>
                                                <td>
                                                    Vehicle Number : <span class="text-bold">{{ $invoice->vehicle_num }}</span><br/>
                                                    Driver Name : <span class="text-bold">{{ $invoice->driver_name }}</span>
                                                </td>
                                                <td class="pb-2">
                                                    Invoice Number : <span class="text-bold">{{ $invoice->invoice_num }}</span><br/>
                                                    Invoice Date : <span class="text-bold">{{ displayDate($invoice->invoice_date) }}</span>
                                                </td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>

                                <div class="row mb-2">
                                    <div class="col-lg-12">
                                        <div class="table-responsive">
                                            <table class="invoice-table">
                                                <thead>
                                                    <tr class="text-right">
                                                        <td class="text-center" width="60px">S.No</td>
                                                        <td class="text-left">Particulars</td>
                                                        <td class="text-center" width="100px">HSN Code</td>
                                                        <td width="70px">Crates</td>
                                                        <td width="70px">Qty</td>
                                                        <td width="90px">Amount</td>
                                                        <td width="50px">GST</td>
                                                        <td width="90px">Tax Amt</td>
                                                        <td width="90px">Tot Amt</td>
                                                    </tr><!--end tr-->
                                                </thead>
                                                <tbody @class(['adjust-size' => $invoice->discount])>
                                                    @foreach($invoiceItems as $invoiceItem)
                                                        <tr class="text-right" style="height:0; border-bottom: 1px"> 
                                                            <td class="text-center">{{ $loop->index + 1 }}</td>
                                                            <td class="text-left">{{ $invoiceItem->product_name }}
                                                                @if($invoiceItem->item_category != "Regular")
                                                                    &nbsp;[{{ $invoiceItem->item_category }}]
                                                                @endif
                                                            </td>
                                                            <td class="text-center">{{ $invoiceItem->hsn_code }}</td>
                                                            <td>{{ getNumberOrEmpty($invoiceItem->crates) }}</td>
                                                            <td>{{ getTwoDigitPrecision($invoiceItem->qty) }}</td>
                                                            <td>{{ getTwoDigitPrecision($invoiceItem->amount) }}</td>
                                                            <td>{{ $invoiceItem->gst }}%</td>
                                                            <td>{{ getTwoDigitPrecision($invoiceItem->tax_amt) }}</td>
                                                            <td >{{ getTwoDigitPrecision($invoiceItem->tot_amt) }}</td>
                                                        </tr>
                                                    @endforeach
                                                    <!-- Empty Row -->
                                                    <tr class="print-visible">
                                                        <td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td>
                                                        <!-- <td colspan="9"></td> -->
                                                    </tr>
                                                </tbody>
                                                <tfoot>
                                                    <tr class="text-right">
                                                        <td colspan="3" class="text-center">Total</td>
                                                        <td>{{ getTwoDigitPrecision($invoice->crates) }}</td>
                                                        <td>{{ getTwoDigitPrecision($invoice->qty) }}</td>
                                                        <td>{{ getTwoDigitPrecision($invoice->amount) }}</td>
                                                        <td></td>
                                                        <td>{{ getTwoDigitPrecision($invoice->tax_amt) }}</td>
                                                        <td>{{ getTwoDigitPrecision($invoice->tot_amt) }}</td>
                                                    </tr><!--end tr-->
                                                </tfoot>
                                            </table><!--end table-->
                                        </div>  <!--end /div-->
                                    </div>  <!--end col-->
                                </div><!--end row-->

                                <div class="row">
                                    <div class="col-md-4">
                                        <span class="label">Last Inamt. Issued</span>: {{ $invoice->last_in_amount }}<br>
                                        <span class="label">Empty Crates Received</span>: {{ $invoice->empty_crates_received }}<br>
                                        <span class="label">Amount Received</span>: {{ $invoice->amount_received }}<br>
                                        <span class="label">Last Receipt</span>: {{ $invoice->last_receipt }}<br>
                                        <span class="label">Last Crates Received</span>: {{ $invoice->last_crates_received }}
                                    </div>
                                    <div class="col-md-5">
                                        <div class="table-responsive">
                                            <table class="tax-table">
                                                <thead>
                                                    <tr class="text-right">
                                                        <td>GST%</td>
                                                        <td>Taxable</td>
                                                        <td>GST</td>
                                                        <td>SGST</td>
                                                        <td>CGST</td>
                                                        <td>IGST</td>
                                                    </tr><!--end tr-->
                                                </thead>
                                                <tbody>
                                                    @foreach($gstTable['rows'] as $gstRow)
                                                        <tr class="text-right"> 
                                                            <td>{{ $gstRow['gst_perc'] }}%</td>
                                                            <td>{{ getTwoDigitPrecision($gstRow['amount']) }}</td>
                                                            <td>{{ getTwoDigitPrecision($gstRow['gst']) }}</td>
                                                            <td>{{ getNumberOrHyphen($gstRow['sgst']) }}</td>
                                                            <td>{{ getNumberOrHyphen($gstRow['cgst']) }}</td>
                                                            <td>{{ getNumberOrHyphen($gstRow['igst']) }}</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                                @if(count($gstTable['rows'])>1)
                                                    <tfoot>
                                                        <tr class="text-right">
                                                            <td>Total</td>
                                                            <td>{{ getTwoDigitPrecision($gstTable['total']['amount']) }}</td>
                                                            <td>{{ getTwoDigitPrecision($gstTable['total']['gst']) }}</td>
                                                            <td>{{ getNumberOrHyphen($gstTable['total']['sgst']) }}</td>
                                                            <td>{{ getNumberOrHyphen($gstTable['total']['cgst']) }}</td>
                                                            <td>{{ getNumberOrHyphen($gstTable['total']['igst']) }}</td>
                                                        </tr><!--end tr-->
                                                    </tfoot>
                                                @endif
                                            </table><!--end table-->
                                        </div>  <!--end /div-->
                                        @if(count($gstTable['rows'])==1)
                                            <span class="sign-line1" style="margin-top:32px;">For Aasaii Food Productt,</span>
                                            <span class="sign-line2">Distributor / Dealer Authorized Signatory</span>
                                        @endif
                                    </div>
                                    <div class="col-md-3">
                                        <table width="100%">
                                            <tr>
                                                <td class="title-col">Amount</td>
                                                <td class="amt-col">{{ getTwoDigitPrecision($invoice->amount) }}</td>
                                            </tr>
                                            <tr>
                                                <td class="title-col">Tax Amount</td>
                                                <td class="amt-col">{{ getTwoDigitPrecision($invoice->tax_amt) }}</td>
                                            </tr>
                                            <tr>
                                                <td class="title-col">TCS</td>
                                                <td class="amt-col">{{ getTwoDigitPrecision($invoice->tcs) }}</td>
                                            </tr>
                                            @if($invoice->discount)
                                                <tr>
                                                    <td class="title-col">Discount</td>
                                                    <td class="amt-col">{{ getTwoDigitPrecision($invoice->discount) }}</td>
                                                </tr>
                                            @endif
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

                                <hr class="hr2"/>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <p class="footer-text">Please make sure the consignment is intact and check the weight before taking delivery of the consignment. Our responsibility ceases on delivery of goods to the customers or their representatives or carriers. Delayed payment will be charged interest @ 24% per annum. All disputes will be settled at Karur jurisdiction.</p>
                                    </div>
                                </div><!--end row-->
                                <hr class="hr2"/>

                            </div><!--end tax-invoice-wrapper-->
                            
                        </div><!--end card-body-->
                    </div><!--end card-->
                </div><!--end col-->
            </div><!--end row-->
        @endif              

    </div>
@stop

@push('custom-scripts')
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.js"></script>
    <script src="{{ asset('assets/js/print-invoice2.js') }}"></script>
    <script>
        $(document).ready(function () {
            $.ajaxSetup({
                headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
            });

            function getSelectedInvoices() {
                let selectedInvoices = []; // Array to store selected invoice details

                // Check if Sales Invoice checkbox exists and is checked
                if ($('#chkSalesInvoice').length && $('#chkSalesInvoice').is(':checked')) {
                    selectedInvoices.push({
                        type: 'SALES',
                        invoice_num: $('#chkSalesInvoice').val(), // Retrieve value from the checkbox
                    });
                }

                // Check if Tax Invoice checkbox exists and is checked
                if ($('#chkTaxInvoice').length && $('#chkTaxInvoice').is(':checked')) {
                    selectedInvoices.push({
                        type: 'TAX',
                        invoice_num: $('#chkTaxInvoice').val(), // Retrieve value from the checkbox
                    });
                }

                return selectedInvoices;
            }

            $('#submit').click(function () { 
                let invoices = getSelectedInvoices();
                let remarks = $("#remarks").val();                
                if(invoices.length == 0) {
                    Swal.fire('Attention','Please Select Invoice for Cancel','warning');
                }
                else if(!remarks) {
                    Swal.fire('Attention','Please Enter Cancel Reason / Remarks','warning');
                }
                else {
                    $.ajax({
                        url: "{{ route('invoices.cancel.') }}",
                        type: "POST",
                        data: {                            
                            invoice_data : invoices,
                            remarks : remarks
                        },
                        dataType: 'json',
                        success: function (data) {
                            Swal.fire('Success!', data.message, 'success')                            
                                .then(function() { window.location.replace("{{ route('invoices.cancel.load') }}"); });
                        },
                        error: function (data, textStatus, errorThrown) {
                            console.log(data.responseText);
                            Swal.fire('Sorry!', data.responseText, 'error');                            
                        }
                    });
                }
            });
        });
    </script>
@endpush

@section('footerScript')
    <!-- Sweet-Alert  -->
    <script src="{{ asset('plugins/sweet-alert2/sweetalert2.min.js') }}"></script>
    <script src="{{ asset('assets/pages/jquery.sweet-alert.init.js') }}"></script>
@stop