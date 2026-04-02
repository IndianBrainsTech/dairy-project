@extends('app-layouts.admin-master')

@section('title', 'View Receipt')

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
                    @slot('title') View Receipt @endslot
                    @slot('item1') Transactions @endslot
                    @slot('item2') Receipts @endslot
                @endcomponent
            </div><!--end col-->
        </div>
        <!-- end page title end breadcrumb -->
 
        <div class="row"> 
            <div class="col-lg-9 mx-auto">
                <div class="card">
                    <div class="card-body">

                        <!-- Receipt Info -->
                        <div class="px-2">
                            <div class="row my-2">
                                <div class="col-md-3">
                                    Receipt Number <br/>
                                    <div class="mt-2">Receipt Date</div>
                                </div>
                                <div class="col-md-6">
                                    <div class="my-bold blue-text">{{ $receipt->receipt_num }}</div>
                                    <div class="my-bold mt-2">{{ displayDate($receipt->receipt_date) }}</div>
                                </div>
                                <div class="col-md-3 text-right">
                                    <a href="#" id="btnPrev" class="btn btn-info py-1 mr-2" data-toggle="tooltip" data-placement="top" title="Left Arrow (<)">Prev</a>
                                    <a href="#" id="btnNext" class="btn btn-secondary py-1" data-toggle="tooltip" data-placement="top" title="Right Arrow (>)">Next</a>
                                </div>
                            </div>
                            <div class="row my-2">
                                <div class="col-md-3">Status</div>
                                <div class="col-md-9 my-bold">{{ $receipt->status }}</div>
                            </div>
                            <div class="row my-2">
                                <div class="col-md-3">Route</div>
                                <div class="col-md-9 my-bold">{{ $receipt->route->name }}</div>
                            </div>
                            <div class="row my-2">
                                <div class="col-md-3">Customer</div>
                                <div class="col-md-9 my-bold">{{ $receipt->customer_name }}</div>
                            </div>
                            <div class="row my-2">
                                <div class="col-md-3">Amount</div>
                                <div class="col-md-9 my-bold">{{ $receipt->amount }}</div>
                            </div>
                            <div class="row my-2">
                                <div class="col-md-3">Mode of Payment</div>
                                <div class="col-md-9 my-bold">{{ $receipt->mode }}</div>
                            </div>
                        </div>

                        <!-- Receipt Table -->
                        <h6 class="my-heading p-2 pt-3 mb-1">Receipt Data :</h6>
                        <div class="table-responsive dash-social px-2">
                            <table id="receiptTable" class="table table-bordered table-sm mb-2">
                                <thead class="thead-light">
                                    <tr>
                                        <th class="text-center">S.No</th>
                                        <th class="text-center">Invoice Date</th>
                                        <th class="text-center">Invoice Number</th>
                                        <th class="text-right pr-2">Outstanding</th>
                                        <th class="text-right pr-2">Received</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($receipt->receipt_data as $item)
                                        <tr>
                                            <td class="text-center">{{ $loop->index + 1 }}</td>
                                            <td class="text-center">{{ $item['inv_date'] }}</td>
                                            <td class="text-center">{{ $item['inv_num'] }}</td>
                                            <td class="text-right pr-2">{{ $item['oustd_amt'] }}</td>
                                            <td class="text-right pr-2">{{ $item['rcvd_amt'] }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr class="thead-light">
                                        <th colspan="3" class="text-center">Total</th>
                                        <th id="oustdTotal" class="text-right pr-2"></th>
                                        <th id="rcvdTotal" class="text-right pr-2"></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        <div class="px-2">
                            <div class="row my-2">
                                <div class="col-md-3">Advance Amount</div>
                                <div class="col-md-9 my-bold">{{ getZeroForEmpty($receipt->advance_amt) }}</div>
                            </div>
                            <div class="row my-2">
                                <div class="col-md-3">Excess Amount</div>
                                <div class="col-md-9 my-bold">{{ getZeroForEmpty($receipt->excess_amt) }}</div>
                            </div>
                        </div>

                        @if($receipt->denomination && !is_int($receipt->denomination))
                            <h6 class="my-heading p-2 pt-3 mb-1">Denomination :</h6>
                            <div class="table-responsive dash-social px-2">
                                <table class="table table-bordered table-sm mb-2 text-right pr-2" style="width:250px">
                                    <thead class="thead-light">
                                        <tr>
                                            <th class="nrb">Note</th>
                                            <th class="nlrb"></th>
                                            <th class="nlrb">Count</th>
                                            <th class="nlrb"></th>
                                            <th class="nlb">Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($receipt->denomination as $item)
                                            @foreach($item as $note => $count)
                                                <tr>
                                                    @if($note != 1)
                                                        <td class="nrb">{{ $note }}</td>
                                                        <td class="nlrb"> X </td>
                                                    @else
                                                        <td colspan="2" class="nrb">Coins</td>
                                                    @endif    
                                                    <td class="nlrb">{{ $count }} </td>
                                                    <td class="nlrb"> = </td>
                                                    <td class="nlb">{{ intval($note) * intval($count) }} </td>
                                                </tr>
                                            @endforeach
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                        <tr class="thead-light">
                                            <th colspan="4" class="nrb">Total</th>
                                            <th class="nlb">{{ $receipt->amount }}</th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        @endif

                        @if($receipt->mode == "Bank" || $receipt->mode == "Deposit")
                            <h6 class="my-heading p-2 pt-3 mb-1">{{ $receipt->mode }} Details :</h6>
                            <div class="px-2">
                                <div class="row my-2">
                                    <div class="col-md-3">Bank</div>
                                    <div class="col-md-9 my-bold">{{ $receipt->bank->display_name }}</div>
                                </div>
                                <div class="row my-2">
                                    <div class="col-md-3">Transaction No.</div>
                                    <div class="col-md-9 my-bold">{{ $receipt->trans_num }}</div>
                                </div>
                                <div class="row my-2">
                                    <div class="col-md-3">Remarks</div>
                                    <div class="col-md-9 my-bold">{{ $receipt->remarks }}</div>
                                </div>
                            </div>
                        @endif

                        @if($receipt->mode == "Incentives")
                            <h6 class="my-heading p-2 pt-3 mb-1">Incentive Data :</h6>
                            <div class="table-responsive px-2">
                                <table id="incentiveTable" class="table table-sm table-bordered nowrap text-center">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>Duration</th>
                                            <th>Generated on</th>
                                            <th>Amount</th>
                                            <th>Available</th>
                                            <th>Receipt</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($receipt->incentive_data as $data)
                                            <tr>
                                                <td>{{ str_replace('<br/>', '', $data['duration']) }}</td>
                                                <td>{{ $data['date'] }}</td>
                                                <td>{{ $data['amount'] }}</td>
                                                <td>{{ $data['available'] }}</td>
                                                <td></td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif

                        @if($receipt->status == "Pending" && $receipt->mode != "Incentive")
                            <hr/>
                            <div class="text-center">
                                <button type="button" class="btn btn-dark px-3 py-1 mr-3" id="btnEdit">Edit Receipt</button>
                            </div>
                        @endif
                    </div><!--end card-body-->
                </div><!--end card-->
            </div><!--end col-->
        </div><!--end row-->
    </div>
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

        let receipts = "{{ $receipts }}";
        let receiptsArray = receipts.split(',');
        doInit();

        function doInit() {
            calculateTotals();
            @if($receipt->mode == "Incentive")
                distributeIncentiveAmount();
            @endif
        }

        $(document).on('keydown', function(event) {
            if (event.key === 'ArrowLeft') {
                $('#btnPrev').click();
            }
            else if (event.key === 'ArrowRight') {
                $('#btnNext').click();
            }
        });

        $('#btnPrev').on("click", function () {
            let index = receiptsArray.indexOf("{{ $receipt->receipt_num }}");
            if(index == 0) {
                Swal.fire('Sorry!','No Previous Receipt!','warning');
            }
            else {
                let receiptNum = receiptsArray[index - 1];
                showReceipt(receiptNum);
            }
        });

        $('#btnNext').on("click", function () {
            let index = receiptsArray.indexOf("{{ $receipt->receipt_num }}");
            if(index == receiptsArray.length-1) {
                Swal.fire('Sorry!','No Next Receipt!','warning');
            }
            else {
                let receiptNum = receiptsArray[index + 1];
                showReceipt(receiptNum);
            }
        });

        function showReceipt(receiptNum) {
            // Create a form element
            var form = $('<form>', {
                'method': 'POST',
                'action': "{{ route('receipts.show') }}"
            });

            // Add CSRF token
            var csrfToken = $('meta[name="csrf-token"]').attr('content');
            form.append($('<input>', {
                'type': 'hidden',
                'name': '_token',
                'value': csrfToken
            }));

            // Add the data as hidden inputs
            form.append($('<input>', { 'type': 'hidden', 'name': 'receipt_num', 'value': receiptNum }));
            form.append($('<input>', { 'type': 'hidden', 'name': 'receipts', 'value': receipts }));
            
            // Append the form to the body and submit it
            $('body').append(form);
            form.submit();
        }

        $('#btnEdit').on("click", function () {
            let receiptNum = "{{ $receipt->receipt_num }}";

            // Create a form element
            var form = $('<form>', {
                'method': 'POST',
                'action': "{{ route('receipts.edit') }}"
            });

            // Add CSRF token
            var csrfToken = $('meta[name="csrf-token"]').attr('content');
            form.append($('<input>', { 'type': 'hidden', 'name': '_token', 'value': csrfToken }));

            // Add the data as hidden inputs
            form.append($('<input>', { 'type': 'hidden', 'name': 'receipt_num', 'value': receiptNum }));
            
            // Append the form to the body and submit it
            $('body').append(form);
            form.submit();
        });

        function calculateTotals() {
            let oustdTotal = 0;
            let rcvdTotal = 0;

            // Iterate through each row in the table body
            $('#receiptTable tbody tr').each(function () {
                // Parse the Outstanding and Received amounts
                let oustdAmt = parseFloat($(this).find('td:nth-child(4)').text()) || 0;
                let rcvdAmt = parseFloat($(this).find('td:nth-child(5)').text()) || 0;
                
                // Add to totals
                oustdTotal += oustdAmt;
                rcvdTotal += rcvdAmt;
            });

            // Display totals
            $('#oustdTotal').text(oustdTotal);
            $('#rcvdTotal').text(rcvdTotal);
        }

        function distributeIncentiveAmount() {
            let remainingAmount = "{{ $receipt->amount }}";
            $('#incentiveTable tbody tr').each(function(index, row) {
                const $row = $(row);
                const availableAmount = parseFloat($(this).find('td:nth-child(4)').text());

                if (remainingAmount > availableAmount) {
                    $(this).find('td:nth-child(5)').text(availableAmount);
                    remainingAmount -= availableAmount;
                } 
                else {
                    $(this).find('td:nth-child(5)').text(remainingAmount > 0 ? remainingAmount : '');
                    remainingAmount = 0;
                }
            });
        }
    });
</script>
@endpush

@section('footerScript')
    <!-- Sweet-Alert  -->
    <script src="{{ asset('plugins/sweet-alert2/sweetalert2.min.js') }}"></script>
    <script src="{{ asset('assets/pages/jquery.sweet-alert.init.js') }}"></script>
@stop