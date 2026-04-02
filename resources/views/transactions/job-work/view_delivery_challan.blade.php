@extends('app-layouts.admin-master')

@section('title', 'Delivery Challan')

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
                @component('app-components.breadcrumb-3')
                    @slot('title') View Delivery Challan @endslot
                    @slot('item1') Transactions @endslot
                    @slot('item2') Job Work @endslot
                    @slot('item3') Delivery Challans @endslot
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
                                            <div class="text-center my-2"><span class="invoice-title">DELIVERY CHALLAN</span></div>
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
                                                <span class="fw-bold">{{ $job_work->customer_name }}</span> <br/>
                                                {!! nl2br(e($job_work->customer_data->billAddr)) !!}<br/>
                                                GST No : {{ $job_work->customer_data->gst }} <br/>
                                                Cell No : {{ $job_work->customer_data->mobile }}
                                            </div>
                                        </div>
                                        <div class="col-md-4 py-2" style="border-right: 1px solid;">
                                            <span class="mb-1 fw-bold">Delivery Address</span> <br/>
                                            <div class="ml-2">
                                                <span class="fw-bold">{{ $job_work->customer_name }}</span> <br/>
                                                {!! nl2br(e($job_work->customer_data->deliAddr)) !!} <br/>
                                            </div>
                                        </div>
                                        <div class="col-md-4 py-2">
                                            <span class="detail-label ml-1 mt-2">DC Number</span> : <span class="ml-1 fw-bold">{{ $job_work->job_work_num }}</span><br>
                                            <span class="detail-label ml-1">DC Date</span>        : <span class="ml-1">{{ displayDate($job_work->job_work_date) }}</span><br>
                                            <span class="detail-label ml-1">Vehicle No.</span>    : <span class="ml-1">{{ $job_work->vehicle_num }}</span><br>
                                            <span class="detail-label ml-1">Driver Name</span>    : <span class="ml-1">{{ $job_work->driver_name }}</span><br>
                                            <span class="detail-label ml-1">Driver Mobile</span>  : <span class="ml-1">{{ $job_work->driver_mobile_num }}</span><br>
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
                                                        @foreach($job_work_items as $job_work_item)
                                                            <tr class="text-right" style="height:0; border-bottom: 1px"> 
                                                                <td class="text-center">{{ $loop->index + 1 }}</td>
                                                                <td class="text-left">{{ $job_work_item->product_name . " - " . $job_work_item->hsn_code }}</td>
                                                                <td>{{ getTwoDigitPrecision($job_work_item->qty_ltr) }}</td>
                                                                <td>{{ getTwoDigitPrecision($job_work_item->fat) }}</td>
                                                                <td>{{ getTwoDigitPrecision($job_work_item->snf) }}</td>
                                                                <td>{{ number_format($job_work_item->ts, 3, '.', '') }}</td>
                                                                <td>0.00</td>                                                                
                                                                <td class="pr-2">0.00</td>
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
                                                            <td>{{ getTwoDigitPrecision($job_work_items->sum('qty_ltr')) }}</td>
                                                            <td></td>
                                                            <td></td>
                                                            <td></td>
                                                            <td></td>                                                            
                                                            <td class="pr-2">0.00</td>
                                                        </tr><!--end tr-->
                                                    </tfoot>
                                                </table><!--end table-->
                                            </div>  <!--end /div-->
                                        </div>  <!--end col-->
                                    </div><!--end row-->

                                    <!-- Bank Details -->
                                    <div class="row mx-0 border-line">
                                        <div class="col-md-6">
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
                                <div class="row mt-3">
                                    <div class="col-lg-12">
                                        <span class="sign-line text-left">REMARKS: MILK NOT FOR SALES / ONLY CONVERSION</span>
                                    </div>
                                </div><!--end row-->
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
             
            var jobNums = "{{$job_work_nums}}";
            var jobs = jobNums.split(',');

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
                var labels = [ "ORIGINAL FOR CONSIGNEE", "DUPLICATE FOR TRANSPORTER", "TRIPLICATE FOR CONSIGNOR" ];
                // Print Invoices with the labels
                printInvoice(labels);
            });

            $('#btnPrev').on("click", function () {
                var index = jobs.indexOf("{{$job_work->job_work_num}}");
                if(index == 0) {
                    Swal.fire('Sorry!','No Previous Delivery Challan!','warning');
                }
                else {
                    var jobNum = jobs[index - 1];
                    showDeliveryChallan(jobNum);
                }
            });

            $('#btnNext').on("click", function () {
                var index = jobs.indexOf("{{$job_work->job_work_num}}");
                if(index == jobs.length-1) {
                    Swal.fire('Sorry!','No Next Delivery Challan!','warning');
                }
                else {
                    var jobNum = jobs[index + 1];
                    showDeliveryChallan(jobNum);
                }
            });

            function showDeliveryChallan(jobNum) {
                // Create a form element
                var form = $('<form>', {
                    'method': 'POST',
                    'action': '{{ route("job-work.show") }}'
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
                    'name': 'job_work_num',
                    'value': jobNum
                }));

                form.append($('<input>', {
                    'type': 'hidden',
                    'name': 'job_work_nums',
                    'value': jobNums
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