@extends('app-layouts.admin-master')

@section('title', 'Invoice')

@section('headerStyle')
    <link href="{{ asset('assets/css/show-invoice.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('assets/css/print-invoice.css') }}" rel="stylesheet" type="text/css" media="print">
@stop

@section('content')
    <div class="container-fluid">
        <!-- Page-Title -->
        <div class="row">
            <div class="col-sm-12">
                @component('app-components.breadcrumb-3')
                    @slot('title') Print Invoice @endslot
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
                            <div class="col-lg-12">
                                <div class="float-right">
                                    <a href="#" id="btnPrint" class="btn btn-pink py-1 mr-2"><i class="fa fa-print"></i></a>
                                </div>
                            </div><!--end col-->
                        </div><!--end row-->

                    </div><!--end card-body-->
                </div><!--end card-->
            </div><!--end col-->
        </div><!--end row-->

        @foreach($invoices as $invoiceRecord)
            @php($salesInvoice = $invoiceRecord['sales_invoice'])
            @php($taxInvoice = $invoiceRecord['tax_invoice'])
            <div class="invoice-wrapper">

                @if($salesInvoice)
                    @php($invoice = $salesInvoice['invoice'])
                    @php($invoiceItems = $salesInvoice['invoiceItems'])            
                    <div class="row"> 
                        <div class="col-lg-10 mx-auto">
                            <div class="card">
                                <div class="card-body pt-2">
                                    
                                    <div id="sales-invoice-wrapper">
                                        <hr class="hr1"/>
                                        <div class="invoice-header">
                                            <div></div>
                                            <div style="font-weight:700">DELIVERY CUM INVOICE</div>
                                            <div>{{ $invoice->orderedBy?->name }} &nbsp; {{ getIndiaDateTime($invoice->order_dt) }}</div>
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

                @if($taxInvoice)
                    @php($invoice = $taxInvoice['invoice'])
                    @php($invoiceItems = $taxInvoice['invoiceItems'])
                    @php($gstTable = $taxInvoice['gstTable'])
                    <div class="row">
                        <div class="col-lg-10 mx-auto">
                            <div class="card">
                                <div class="card-body">

                                    <div id="tax-invoice-wrapper" class="second-page">
                                        <hr class="hr1"/>
                                        <div class="invoice-header">
                                            <div></div>
                                            <div style="font-weight:700">DELIVERY CUM INVOICE</div>
                                            <div>{{ $invoice->orderedBy?->name }} &nbsp; {{ getIndiaDateTime($invoice->order_dt) }}</div>
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
                                                <table class="total-table" width="100%">
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
        @endforeach

    </div>
@stop

@push('custom-scripts')
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.js"></script>    
    <script src="{{ asset('assets/js/print-invoices.js') }}"></script>
    <script>
        function doPrint() {
            $('#btnPrint').click();
        }
    </script>
@endpush