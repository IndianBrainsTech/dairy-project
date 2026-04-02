@extends('app-layouts.admin-master')

@section('title', 'View Job Work')

@section('headerStyle')
    <link href="{{ asset('plugins/sweet-alert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('assets/css/my-style.css') }}" rel="stylesheet" type="text/css">
@stop

@section('content')
    <div class="container-fluid">
        <!-- Page-Title -->
        <div class="row">
            <div class="col-sm-12">
                @component('app-components.breadcrumb-3')
                    @slot('title') View Job Work @endslot
                    @slot('item1') Transactions @endslot
                    @slot('item2') Job Work @endslot
                @endcomponent
            </div><!--end col-->
        </div>
        <!-- end page title end breadcrumb -->

        <div class="row"> 
            <div class="col-lg-10 mx-auto">
                <div class="card">
                    <div class="card-body">

                        <!-- Order Info -->
                        <div class="px-2">
                            <div class="row my-2">
                                <div class="col-md-3">
                                    Job Work Number <br/>
                                    <div class="mt-2">Date Time</div>
                                </div>
                                <div class="col-md-6">
                                    <div class="my-bold blue-text">{{ $job_work['job_work_num'] }}</div>
                                    <div class="mt-2">{{ getIndiaDateTime($job_work['job_work_dt']) }}</div>
                                </div>
                                <div class="col-md-3 text-right">
                                    @if($job_work['job_work_status'] != "Cancelled" && $job_work['invoice_status'] == "Not Generated")
                                        <a href="#" id="btnPrint" class="btn btn-pink py-1 mr-2" data-toggle="tooltip" data-placement="top" title="Ctrl + P"><i class="fa fa-print"></i></a>
                                    @endif
                                    <a href="#" id="btnPrev" class="btn btn-info py-1 mr-2" data-toggle="tooltip" data-placement="top" title="Left Arrow (<)">Prev</a>
                                    <a href="#" id="btnNext" class="btn btn-secondary py-1" data-toggle="tooltip" data-placement="top" title="Right Arrow (>)">Next</a>
                                </div>
                            </div>
                            <div class="row my-2">
                                <div class="col-md-3">Job Work Date</div>
                                <div class="col-md-9">{{ getIndiaDate($job_work['job_work_date']) }}</div>
                            </div>
                            <div class="row my-2">
                                <div class="col-md-3">
                                    {{ $job_work['job_work_status'] == "Cancelled" ? 'Job Work Status' : 'DC Status' }}
                                </div>
                                <div class="col-md-9">
                                    {{ getJobWorkStatus($job_work['job_work_status'] == "Cancelled" ? $job_work['job_work_status'] : $job_work['invoice_status']) }}
                                    @if($job_work['cancel_remarks'])
                                        [{{ $job_work['cancel_remarks'] }}]
                                    @endif
                                </div>
                            </div>
                            <div class="row my-2">
                                <div class="col-md-3">Customer</div>
                                <div class="col-md-9 my-bold">{{ $job_work['customer'] }}</div>
                            </div>
                            <div class="row my-2">
                                <div class="col-md-3">Route</div>
                                <div class="col-md-9">{{ $job_work['route'] }}</div>
                            </div>
                            <div class="row my-2">
                                <div class="col-md-3">Vehicle Number</div>
                                <div class="col-md-9">{{ $job_work['vehicle_num'] }}</div>
                            </div>
                            <div class="row my-2">
                                <div class="col-md-3">Driver Name</div>
                                <div class="col-md-9">{{ $job_work['driver_name'] }}</div>
                            </div>
                            <div class="row my-2">
                                <div class="col-md-3">Mobile Number</div>
                                <div class="col-md-9">{{ $job_work['driver_mobile_num'] }}</div>
                            </div>
                            <div class="row my-2">
                                <div class="col-md-3">Billing Address</div>
                                <div class="col-md-9">{{ $job_work['billing_address'] }}</div>
                            </div>
                            <div class="row my-2">
                                <div class="col-md-3">Delivery Address</div>
                                <div class="col-md-9">{{ $job_work['delivery_address'] }}</div>
                            </div>
                        </div>

                        <!-- Job Work Table -->
                        <h6 class="my-heading pl-2 pt-3">Job Work Data :</h6>
                        <div class="table-responsive dash-social px-2">
                            <table id="tableOrderedItems" class="table table-bordered table-sm">
                                <thead class="thead-light">
                                    <tr class="thead-light" style='height:36px'>
                                        <th class="text-center">S.No</th>
                                        <th class="pl-2">Product</th>
                                        <th class="text-center">HSN Code</th>
                                        <th class="text-center">Qty (Kg)</th>
                                        <th class="text-center">CLR</th>
                                        <th class="text-center">FAT</th>
                                        <th class="text-right">SNF</th>
                                        <th class="text-right">Qty (Ltr)</th>
                                        <th class="text-right">TS</th>
                                        <th class="text-right">TS Rate</th>
                                        <th class="text-right">Rate / Ltr</th>
                                        <th class="text-right pr-2">Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($job_work_items as $jobWorkItem)
                                        <tr style='height:32px'>
                                            <td class="text-center">{{ $loop->iteration }}</td>
                                            <td class="pl-2">{{ $jobWorkItem->product_name }}</td>
                                            <td class="text-center">{{ $jobWorkItem->hsn_code }}</td>
                                            <td class="text-center">{{ $jobWorkItem->qty_kg }}</td>
                                            <td class="text-center">{{ $jobWorkItem->clr }}</td>
                                            <td class="text-center">{{ $jobWorkItem->fat }}</td>
                                            <td class="text-right">{{ $jobWorkItem->snf }}</td>
                                            <td class="text-right">{{ $jobWorkItem->qty_ltr }}</td>
                                            <td class="text-right">{{ $jobWorkItem->ts }}</td>
                                            <td class="text-center">{{ $jobWorkItem->ts_rate }}</td>
                                            <td class="text-right">{{ $jobWorkItem->rate }}</td>
                                            <td class="text-right pr-2">{{ getTwoDigitPrecision($jobWorkItem->amount) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr id="totalRow">
                                        <td class="text-center" colspan="3">Total</td>
                                        <th id="totQtyKg" class="text-center">{{ $job_work_items->sum('qty_kg') }}</th>
                                        <td colspan="3"></td>
                                        <th id="totQtyLtr" class="text-right">{{ $job_work_items->sum('qty_ltr') }}</th>
                                        <td colspan="3"></td>
                                        <th id="totAmt" class="text-right pr-2">{{ getTwoDigitPrecision($job_work_items->sum('amount')) }}</th>
                                    </tr>                                    
                                    <tr>
                                        <td colspan="11" class="text-right pr-2 border-top-0 border-bottom-0">Round Off</td>
                                        <td id="roundOff" class="text-right pr-2">{{ getTwoDigitPrecision($job_work['round_off']) }}</td>
                                    </tr>
                                    <tr>
                                        <td colspan="11" class="text-right pr-2 border-top-0">Net Amount</td>
                                        <th id="netAmt" class="text-right pr-2">{{ getTwoDigitPrecision($job_work['net_amt']) }}</th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        <hr/>

                        @if($job_work['job_work_status'] != "Cancelled" && $job_work['invoice_status'] == "Not Generated")
                            <div class="text-center">
                                <button type="button" class="btn btn-dark px-3 py-1 mr-3" id="btnEdit">Edit Order</button>
                                <button type="button" class="btn btn-danger px-3 py-1 ml-3" data-toggle="modal" data-animation="bounce" data-target="#modal_form">Cancel Job Work</button>
                            </div>
                        @endif

                    </div><!--end card-body-->
                </div><!--end card-->
            </div><!--end col-->
        </div><!--end row-->
    </div>

    <!-- Start of Job Work Cancel Modal -->
    <div class="modal fade" id="modal_form" tabindex="-1" role="dialog" aria-labelledby="modalOrderCancelLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm" role="document">
            <div class="modal-content" style="min-width:400px">
                <div class="modal-header">
                    <h5 class="modal-title mt-0" id="modal_title">Job Work Cancel</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group row mb-0">
                                    <textarea id="remarks" rows="3" class="form-control mx-2" placeholder="Reason / Remarks for Cancellation"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <input type="button" class="btn btn-secondary mr-2" data-dismiss="modal" value="Close" />
                        <input type="button" class="btn btn-primary ml-3" id="btnJobWorkCancel" value="Submit"/>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- End of Job Work Cancel Modal -->
@stop

@push('custom-scripts')
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.js"></script>
    <script src="{{ asset('assets/js/helper.js') }}"></script>
    <script>
        $(document).ready(function () {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            let jobWorks = "{{ $job_works }}";
            let jobWorksArray = jobWorks.split(',');

            $(document).on('keydown', function(event) {
                if (event.ctrlKey && event.key.toUpperCase() === 'P') {
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
                doPrint();
            });

            $('#btnPrev').on("click", function () {
                let index = jobWorksArray.indexOf("{{ $job_work['job_work_num'] }}");
                if(index == 0) {
                    Swal.fire('Sorry!','No Previous Job Work!','warning');
                }
                else {
                    let jobWorkNum = jobWorksArray[index - 1];
                    showJobWork(jobWorkNum);
                }
            });

            $('#btnNext').on("click", function () {
                var index = jobWorksArray.indexOf("{{ $job_work['job_work_num'] }}");
                if(index == jobWorksArray.length-1) {
                    Swal.fire('Sorry!','No Next Order!','warning');
                }
                else {
                    var jobWorkNum = jobWorksArray[index + 1];
                    showJobWork(jobWorkNum);
                }
            });

            function showJobWork(jobWorkNum) {
                // Create a form element
                let form = $('<form>', {
                    'method': 'POST',
                    'action': "{{ route('job-work.show') }}"
                });

                // Add CSRF token
                let csrfToken = $('meta[name="csrf-token"]').attr('content');
                form.append($('<input>', {
                    'type': 'hidden',
                    'name': '_token',
                    'value': csrfToken
                }));

                // Add the data as hidden inputs
                form.append($('<input>', {
                    'type': 'hidden',
                    'name': 'job_work_num',
                    'value': jobWorkNum
                }));

                form.append($('<input>', {
                    'type': 'hidden',
                    'name': 'job_works',
                    'value': jobWorks
                }));

                // Append the form to the body and submit it
                $('body').append(form);
                form.submit();
            }

            $('#btnJobWorkCancel').on("click", function () {
                let remarks = $("#remarks").val();
                if(!remarks) {
                    Swal.fire('Attention','Please Enter Reason for Job Work Cancel','warning');
                    return;
                }
                else {
                    $.ajax({
                        url: "{{ route('job-work.cancel') }}",
                        type: "GET",
                        data: { 
                            job_work_num : "{{ $job_work['job_work_num'] }}",
                            remarks : remarks
                        },
                        dataType: 'json',
                        success: function (data) {
                            Swal.fire('Success!', data.message, 'success')
                                .then(() => window.location.replace("{{ route('job-work.index') }}"));
                        },
                        error: function (data) {
                            Swal.fire('Attention', data.responseText, 'warning');
                            console.log(data.responseText);
                        }
                    });
                }
            });

            $('#btnEdit').on("click", function () {
                let jobWorkNum = "{{ $job_work['job_work_num'] }}";

                // Create a form element
                let form = $('<form>', {
                    'method': 'POST',
                    'action': "{{ route('job-work.edit') }}"
                });

                // Add CSRF token
                let csrfToken = $('meta[name="csrf-token"]').attr('content');
                form.append($('<input>', { 'type': 'hidden', 'name': '_token', 'value': csrfToken }));

                // Add the data as hidden inputs
                form.append($('<input>', { 'type': 'hidden', 'name': 'job_work_num', 'value': jobWorkNum }));

                // Append the form to the body and submit it
                $('body').append(form);
                form.submit();
            });

            function doPrint() {
                let jobWorkNum = "{{ $job_work['job_work_num'] }}";
                $.ajax({
                    url: "{{ route('delivery-challan.print') }}",
                    type: "POST",
                    data: { job_work_num: jobWorkNum },
                    dataType: 'html',
                    success: function (data) {
                        var printWindow = window.open('', '_blank');
                        printWindow.document.write(data);
                        printWindow.document.close();
                        printWindow.onload = function() {
                            printWindow.doPrint();
                            printWindow.close();
                        };
                    },
                    error: function (data, textStatus, errorThrown) {
                        Swal.fire("Sorry!", data.responseText, 'warning');
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