@extends('app-layouts.admin-master')

@section('title', 'Edit Receipt')

@section('headerStyle')
    <link href="{{ asset('plugins/sweet-alert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('plugins/animate/animate.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('assets/css/my-actxt.css') }}" rel="stylesheet" type="text/css">
    <style type="text/css">
        .my-control {
            border: 1px solid #e8ebf3;
            padding:6px;
            border-radius: 0.25rem;
            border-bottom: 1px solid #e8ebf3;
            transition: border-color 0s ease-out;
            background-color: #fff;
            margin-right:20px;
        }
        .my-border {
            padding: 2px 20px;
            border: 1px solid #e8ebf3;
        }
        .content-wrapper {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .center-content {
            flex: 1;
            text-align: center;
        }
    </style>
@stop

@section('content')
    <div class="container-fluid">
        <!-- Page-Title -->
        <div class="row">
            <div class="col-sm-12">
                @component('app-components.breadcrumb-3')
                    @slot('title') Edit Receipt @endslot
                    @slot('item1') Transactions @endslot
                    @slot('item2') Receipts @endslot
                @endcomponent
            </div><!--end col-->
        </div>
        <!-- end page title end breadcrumb -->
  
        <div class="row"> 
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
 
                        <div class="row">
                            <div class="col-sm-3">
                                <div class="form-group row mb-2">
                                    <label for="rcptDate" class="col-form-label ml-2 mr-1">Receipt Date <small class="text-danger font-13">*</small></label>
                                    <input type="date" id="rcptDate" value="{{ $receipt->receipt_date }}" class="form-control" style="max-width:130px">
                                </div>
                            </div>
                            <div class="col-sm-3">
                                <div class="form-group row mb-2">
                                    <label for="rcptNum" class="col-form-label ml-2 mr-1">Receipt Number <small class="text-danger font-13">*</small></label>
                                    <input type="text" id="rcptNum" value="{{ $receipt->receipt_num }}" class="form-control" style="max-width:100px" readonly>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group row mb-2">
                                    <label for="mode" class="col-form-label ml-3 mr-3">Mode <small class="text-danger font-13">*</small></label>
                                    <div class="my-border">
                                        <div class="form-check-inline my-1 pr-2">
                                            <div class="custom-control custom-radio">
                                                <input type="radio" name="mode" id="rdoCash" class="custom-control-input">
                                                <label class="custom-control-label" for="rdoCash">Cash</label>
                                            </div>
                                        </div>
                                        <div class="form-check-inline my-1 pr-2">
                                            <div class="custom-control custom-radio">
                                                <input type="radio" name="mode" id="rdoBank" class="custom-control-input">
                                                <label class="custom-control-label" for="rdoBank">Bank</label>
                                            </div>
                                        </div>
                                        <div class="form-check-inline my-1 pr-2">
                                            <div class="custom-control custom-radio">
                                                <input type="radio" name="mode" id="rdoIncentive" class="custom-control-input">
                                                <label class="custom-control-label" for="rdoIncentive">Incentive</label>
                                            </div>
                                        </div>
                                        <div class="form-check-inline my-1">
                                            <div class="custom-control custom-radio">
                                                <input type="radio" name="mode" id="rdoDeposit" class="custom-control-input">
                                                <label class="custom-control-label" for="rdoDeposit">Deposit</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>                            
                        </div>

                        <div class="row">
                            <div class="col-sm-7">
                                <div class="row">
                                    <div class="col-sm-9">
                                        <div class="form-group">
                                            <label for="customer" class="col-form-label">Customer <small class="text-danger font-13">*</small></label>
                                            <div class="input-group mr-2" style="max-width:410px">
                                                <span class="input-group-prepend">
                                                    <button type="button" class="btn btn-info"><i class="fas fa-search"></i></button>
                                                </span>
                                                <input type="text" id="customer" class="form-control" value="{{ $receipt->customer_name }}" readonly>                                                
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-3">
                                        <div class="form-group float-right">
                                            <label for="rcptAmt" class="col-form-label">Amount <small class="text-danger font-13">*</small></label>
                                            <input type="text" id="rcptAmt" value="{{ $receipt->amount }}" class="form-control text-center int-input" style="max-width:110px">
                                        </div>
                                    </div>
                                </div>

                                <div class="table-responsive">
                                    <table id="receiptTable" class="table table-sm table-bordered nowrap text-center">
                                        <thead class="thead-light">
                                            <tr>
                                                <th style="min-width:110px">Invoice<br/>Date</th>
                                                <th style="min-width:110px">Invoice<br/>Number</th>
                                                <th>Outstanding<br/>Amount</th>
                                                <th>Received<br/>Amount <small class="text-danger font-13">*</small></th>
                                            </tr>
                                        </thead>
                                        <tbody>                                            
                                        </tbody>
                                        <tfoot class="thead-light">
                                            <tr>
                                                <th></th>
                                                <th>Total</th>
                                                <th id="oustd"></th>
                                                <th id="total">{{ $receipt->aggregate_amt }}</th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>

                                <div class="row">
                                    <div class="col-sm-6">
                                        <div class="form-group row">
                                            <label for="advanceAmt" class="col-form-label ml-3 mr-2">Advance Amount</label>
                                            <input type="text" id="advanceAmt" value="{{ $receipt->advance_amt }}" class="form-control text-center" style="max-width:110px" readonly>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="form-group row float-right">
                                            <label for="excessAmt" class="col-form-label mx-2">Excess Balance</label>
                                            <input type="text" id="excessAmt" value="{{ $receipt->excess_amt }}" class="form-control text-center mr-3" style="max-width:110px" readonly>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-sm-5">
                                <br/>
                                <div id="divModeCash" class="mx-5">
                                   <div class="table-responsive">
                                        <table id="denomTable" class="table table-sm table-bordered nowrap text-right">
                                            <thead class="thead-light">
                                                <tr>
                                                    <th colspan="3" class="text-center">Denomination</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($notes as $note)
                                                    <tr>
                                                        <td width="70px" style="border-right-width:0px"> {{ $note }} &ensp; X </td>
                                                        <td width="90px" style="border-right-width:0px; border-left-width:0px"> <input type="text" id="note{{$note}}" class="my-control text-center int-input mr-0" style="width:70px"> &ensp; = </td>
                                                        <td width="70px" style="border-left-width:0px; padding-right:20px" id="noteAmt{{$note}}"></td>
                                                    </tr>
                                                @endforeach
                                                <tr>
                                                    <td width="70px" style="border-right-width:0px"> Coins </td>
                                                    <td width="90px" style="border-right-width:0px; border-left-width:0px"> <input type="text" id="note1" class="my-control text-center int-input mr-0" style="width:70px"> &ensp; = </td>
                                                    <td width="70px" style="border-left-width:0px; padding-right:20px" id="noteAmt1"></td>
                                                </tr>
                                            </tbody>
                                            <tfoot class="thead-light">
                                                <tr>
                                                    <th colspan="2">Total</th>
                                                    <th id="denomTotal" style="padding-right:20px"></th>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>

                                <div id="divModeBank" style="display: none">
                                    <div class="row">
                                        <div class="col-sm-12">
                                            <div class="form-group row">
                                                <label for="bank" class="col-form-label col-sm-4">Bank <small class="text-danger font-13">*</small></label>
                                                <select id="bank" class="form-control col-sm-7">
                                                    @foreach($banks as $bank)
                                                        <option value="{{ $bank->id }}" @selected($receipt->bank_id == $bank->id)>{{ $bank->display_name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="form-group row">
                                                <label for="transNum" class="col-form-label col-sm-4">Transaction No.</label>
                                                <input type="text" id="transNum" value="{{ $receipt->trans_num }}" class="form-control col-sm-7">
                                            </div>
                                            <div class="form-group row">
                                                <label for="remarks" class="col-form-label col-sm-4">Remarks</label>
                                                <textarea id="remarks" rows="3" class="form-control col-sm-7">{{ $receipt->remarks }}</textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div id="divModeIncentive" style="display: none">
                                    <div class="row">
                                        <div class="col-sm-12">
                                            <div class="table-responsive">
                                                <table id="incentiveTable" class="table table-sm table-bordered nowrap text-center">
                                                    <thead class="thead-light">
                                                        <tr>
                                                            <th>Duration</th>
                                                            <th>Generated on</th>
                                                            <th>Amount</th>
                                                            <th>Available</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                    </tbody>
                                                    <tfoot class="thead-light">
                                                        <tr>
                                                            <th colspan="3">Total</th>
                                                            <th id="incentiveAmount"></th>
                                                        </tr>
                                                    </tfoot>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>

                        <div class="row">
                            <div class="col-sm-12">
                                <div class="form-group row float-right">
                                    <input type="button" id="clear" class="btn btn-secondary mr-3" value="Clear" />
                                    <input type="button" id="submit" class="btn btn-primary mr-3" value="Update" data-toggle="tooltip" data-placement="top" title="Alt+S"/>
                                </div>
                            </div>
                        </div>

                    </div><!--end card-body--> 
                </div><!--end card--> 
            </div> <!--end col-->
        </div><!--end row-->   
    </div><!-- container -->
@stop

@push('custom-scripts')
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.js"></script>
    <script src="{{ asset('assets/js/input-restriction.js') }}"></script>    
    <script>
        $(document).ready(function() {
            // Setup CSRF token for AJAX requests
            $.ajaxSetup({
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
            });
            
            let incentives;
            let note1;
            doInit();

            // Initialization
            function doInit() {
                // restrictDate('#rcptDate');
                restrictToYesterdayAndToday('#rcptDate');    
                            
                note1 = $('#denomTable tbody').find('input[type="text"][id^="note"]').first();
                
                // Events
                $('input[name="mode"]').on('change', handleModeChange);
                loadData();
                $('#rcptAmt').on('keypress', focusFirstAmountInput);
                $('#rcptAmt').on('change', updateReceivedAmount);
                $('#receiptTable tbody').on('keypress', '.amt-input', focusNextOnAmountEnter);
                $('#receiptTable tbody').on('change', '.amt-input', validateAmountInput);
                $('#denomTable tbody').on('keypress', '[id^=note]', focusNextOnDenominationEnter);
                $('#denomTable tbody').on('change', '[id^=note]', updateDenomination);                
                $('#clear').on('click', clearFields);
                $('#submit').on('click', submitForm);
                
                $(document)
                    .on('keypress', '.int-input', restrictToInteger)
                    .on('keydown', (event) => { if (event.altKey && event.key === 's') submitForm(); });
            }

            // Functions
            function loadData() {                
                loadInvoiceData();
                @if($receipt->mode == "Cash")                    
                    loadDenomination();                    
                    $('#rdoCash').prop('checked', true).trigger('change');
                @elseif($receipt->mode == "Bank" || $receipt->mode == "Deposit")                    
                    $('#rdo{{$receipt->mode}}').prop('checked', true).trigger('change');
                @elseif($receipt->mode == "Incentive")
                    loadIncentiveData();
                    $('#rdoIncentive').prop('checked', true).trigger('change');                    
                @endif                
            }

            function loadInvoiceData() {
                let invoices = @json($receipt->receipt_data);
                let $oustd = 0;                
                let i = 1;                
                invoices.forEach(function(item) {
                    const newRow = $("<tr>")
                        .append(`<td>${item.inv_date}</td>`)
                        .append(`<td>${item.inv_num}</td>`)
                        .append(`<td id='oustd${i}'>${item.oustd_amt}</td>`)
                        .append(`<td><input type='text' id='amount${i}' class='my-control int-input amt-input text-center mr-0' style='width:90px'></td>`);
                    $("#receiptTable tbody").append(newRow);
                    if(item.rcvd_amt)
                        $(`#amount${i}`).val(item.rcvd_amt);
                    $oustd += item.oustd_amt;
                    i++;
                });
                $('#oustd').text($oustd);
            }

            function loadDenomination() {
                @isset($receipt->denomination)
                    @if(!is_int($receipt->denomination))
                        let note, count;
                        @foreach($receipt->denomination as $item)
                            @foreach($item as $note => $count)
                                note = {{$note}};
                                count = {{$count}};
                                $(`#note${note}`).val(count);
                                $(`#noteAmt${note}`).text(note*count);
                            @endforeach
                        @endforeach
                        updateDenominationTotal();
                    @endif
                @endisset
            }

            function loadIncentiveData() {
                let incentives = @json($receipt->incentive_data);
                let incAmt = 0;
                incentives.forEach(function(item) {
                    const newRow = $("<tr>")
                        .append(`<td>${item.duration}</td>`)
                        .append(`<td>${item.date}</td>`)
                        .append(`<td>${item.amount}</td>`)
                        .append(`<td>${item.available}</td>`);
                    $("#incentiveTable tbody").append(newRow);
                    incAmt += item.available;
                });
                $('#incentiveAmount').text(incAmt==0 ? "" : incAmt);
            }

            function handleModeChange() {                
                let mode = $(this).attr('id').slice(3);
                console.log('Selected mode:', mode);
                if(mode == "Deposit") 
                    mode = "Bank";
                // Hide all divs
                $('[id^="divMode"]').hide();
                // Show the selected div
                $(`#divMode${mode}`).show();

                if(mode == "Cash") {
                    restrictToYesterdayAndToday('#rcptDate');
                    const today = new Date().toISOString().split('T')[0];
                    $('#rcptDate').val(today);
                }
                else {
                    restrictDate('#rcptDate');
                }
            }

            function focusFirstAmountInput(e) {
                if (e.keyCode == 13) $('#amount1').focus();
            }

            function updateReceivedAmount() {
                const receivedAmount = parseFloat($('#rcptAmt').val());
                if (!isNaN(receivedAmount)) {
                    const advanceAmount = parseFloat($('#advanceAmt').val()) || 0; // Default to 0 if NaN
                    distributeAmount(receivedAmount + advanceAmount);
                    updateAmountTotal();
                }
            }

            function focusNextOnAmountEnter(e) {
                if (e.which === 13) {
                    e.preventDefault();
                    var currentId = $(this).attr('id');
                    var nextIdNumber = parseInt(currentId.replace('amount', '')) + 1;
                    var nextInput = $('#amount' + nextIdNumber);

                    if (nextInput.length) nextInput.focus();
                    else note1.focus();
                }
            }

            function validateAmountInput() {
                const input = $(this);
                const amount = parseFloat(input.val());
                const row = input.closest('tr');
                const outstandingAmount = parseFloat(row.find('td[id^="oustd"]').text());

                if (amount > outstandingAmount) $input.val(outstandingAmount);
                updateAmountTotal();
            }

            function focusNextOnDenominationEnter(e) {
                if (e.which === 13) {
                    e.preventDefault();
                    const currentId = $(this).attr('id');
                    const currentRow = $(this).closest('tr');
                    const nextInput = currentRow.next().find(`input[id^=note]`).eq(0);

                    if (nextInput.length) nextInput.focus();
                    else $('#submit').focus();

                }
            }

            function updateDenomination() {
                const input = $(this);
                const inputValue = parseInt(input.val());
                const id = input.attr('id');
                const note = parseInt(id.replace('note', ''));
                const amount = !isNaN(inputValue) ? inputValue * note : "";
                $('#noteAmt' + note).text(amount);
                updateDenominationTotal();
            }

            function clearFields() {
                $('#rcptAmt').val('');
                $('#oustd').text('');
                $('#total').text('');                
                $('#excessAmt').val('');                
                $('#receiptTable tbody [id^=amount]').val('');
                $('#denomTable tbody [id^=note]').val('');
                $('#denomTable tbody [id^=noteAmt]').text('');
                $('#denomTotal').text('');                
                $("#bank").val($("#bank option:first").val());
                $('#transNum').val('');
                $('#remarks').val('');
            }

            function submitForm() {
                const mode = $('input[name="mode"]:checked').attr('id').slice(3);
                if (isValidated(mode)) {
                    const rcptNum     = "{{ $receipt->receipt_num }}";
                    const rcptDate    = $("#rcptDate").val();
                    const rcptAmt     = $("#rcptAmt").val();
                    const aggrAmt     = $("#total").text();                    
                    const excessAmt   = $("#excessAmt").val();
                    const receiptData = getReceiptData();

                    // Prepare data based on mode
                    let requestData = {
                        rcpt_num     : rcptNum,
                        rcpt_date    : rcptDate,
                        amount       : rcptAmt,
                        aggr_amt     : aggrAmt,                        
                        excess_amt   : excessAmt,
                        mode         : mode,
                        receipt_data : receiptData,
                    };

                    if (mode === 'Cash') {
                        requestData.denomination = getDenominationData();
                    } 
                    else if (mode === 'Bank' || mode === 'Deposit') {                        
                        requestData.bank_id   = $("#bank").val();
                        requestData.trans_num = $("#transNum").val();
                        requestData.remarks   = $("#remarks").val();                        
                    }
                    else if (mode === 'Incentive') {
                        requestData.incentive_data = JSON.stringify(incentives);
                    }
                    console.log(requestData);
                    
                    // Send AJAX request
                    $.ajax({
                        url: "{{ route('receipts.update') }}",
                        type: "POST",
                        data: requestData,
                        dataType: 'json',
                        success: function(data) {                            
                            Swal.fire('Success!', data.message, 'success').then(() => {
                                window.location.href = "{{ route('receipts.index') }}";
                            });
                        },
                        error: function(data) {
                            console.log(data.responseText);
                            Swal.fire('Sorry!', data.responseText, 'error');
                        }
                    });
                }
            }                        

            function distributeAmount(receivedAmount) {
                let remainingAmount = receivedAmount;
                $('#receiptTable tbody tr').each(function(index, row) {
                    const $row = $(row);
                    const outstandingAmount = parseFloat($row.find('td[id^="oustd"]').text());
                    const amountInput = $row.find('input[id^="amount"]');

                    if (remainingAmount > outstandingAmount) {
                        amountInput.val(outstandingAmount);
                        remainingAmount -= outstandingAmount;
                    } else {
                        amountInput.val(remainingAmount > 0 ? remainingAmount : '');
                        remainingAmount = 0;
                    }
                });
            }

            function updateAmountTotal() {
                let total = 0;
                $('#receiptTable tbody [id^=amount]').each(function() {
                    var amount = $(this).val();
                    if (amount) total += Number(amount);
                });
                $("#total").text(total);
                
                let advAmt = parseFloat($('#advanceAmt').val()) || 0;
                let rcptAmt = parseFloat($('#rcptAmt').val()) || 0;
                let excess = advAmt + rcptAmt - total;
                if(excess == 0)
                    $('#excessAmt').val('');
                else
                    $('#excessAmt').val(excess);
            }

            function updateDenominationTotal() {
                let total = 0;
                $('#denomTable tbody [id^=noteAmt]').each(function() {
                    var amount = $(this).text();
                    if (amount) total += Number(amount);
                });
                $("#denomTotal").text(total || "");
            }

            function isValidated(mode) {
                const amount = parseFloat($("#rcptAmt").val()) || 0;
                const oustdAmt = parseFloat($("#oustd").text()) || 0;
                const totalAmt = parseFloat($("#total").text()) || 0;
                const advAmt = parseFloat($("#advanceAmt").val()) || 0;
                const excessAmt = parseFloat($("#excessAmt").val()) || 0;
                const denomTotal = parseFloat($("#denomTotal").text()) || 0;
                const incentive = parseFloat($('#incentiveAmount').text()) || 0;
                
                if (amount == 0) {
                    Swal.fire('Sorry!', 'Please Enter Amount', 'warning');
                    return false;
                }                
                else if (mode == "Cash" && denomTotal && denomTotal != amount) {
                    Swal.fire('Sorry!', 'Denomination Total Mismatch', 'warning');
                    return false;
                }
                else if (mode == "Incentive") {                    
                    if(incentive === 0) {
                        Swal.fire('Sorry!', 'Incentive Amount Not Available', 'warning');
                        return false;
                    }
                    if(amount > incentive) {
                        Swal.fire('Sorry!', 'Amount can not Exceeds Available Incentive', 'warning');
                        return false;
                    }
                }

                if (excessAmt < 0) {
                    Swal.fire('Sorry!', 'Excess Amount can not be Negative', 'warning');
                    return false;
                }
                else if ( (excessAmt > advAmt) && (oustdAmt != totalAmt)  ) {
                    Swal.fire('Sorry!', 'Please Check Received Amount Values', 'warning');
                    return false;
                }
                
                return true;
            }

            function getReceiptData() {
                let receiptData = [];

                $('#receiptTable tbody tr').each(function() {
                    const row = $(this);
                    const invDate  = row.find('td:nth-child(1)').text();
                    const invNum   = row.find('td:nth-child(2)').text();
                    const oustdAmt = parseInt(row.find('td:nth-child(3)').text());
                    const amount   = parseInt(row.find('td:nth-child(4) [id^=amount]').val());

                    receiptData.push({
                        inv_date  : invDate,
                        inv_num   : invNum,
                        oustd_amt : oustdAmt,
                        rcvd_amt  : amount
                    });
                });

                return JSON.stringify(receiptData);
            }

            function getDenominationData() {
                let data = [];
                $('#denomTable tbody tr').each(function () {
                    let $txtNote = $(this).find('td:nth-child(2) [id^=note]');
                    let note = parseInt($txtNote.attr('id').replace('note', '')); // Extract the denomination
                    let value = parseInt($txtNote.val()); // Parse the value as an integer
                    if (!isNaN(value) && value !== 0) { // Ensure value is a valid number and not zero
                        data.push({ [note]: value });
                    }
                });
                return data.length > 0 ? JSON.stringify(data) : null;
            }

            function restrictDate(dateControl) {
                // Get today's date (local time)
                let today = new Date();
                let day1 = new Date('2025-02-01');

                // Format date as 'YYYY-MM-DD' (ensuring two-digit month and day)
                function formatDate(date) {
                    let yyyy = date.getFullYear();
                    let mm = String(date.getMonth() + 1).padStart(2, '0'); // Months are 0-based
                    let dd = String(date.getDate()).padStart(2, '0');
                    return `${yyyy}-${mm}-${dd}`;
                }

                // Get formatted dates
                let todayFormatted = formatDate(today);
                let day1Formatted = formatDate(day1);

                // Set the min and max attributes on the date input
                $(dateControl).attr('min', day1Formatted);
                $(dateControl).attr('max', todayFormatted); 
            }

        });
    </script>
@endpush

@section('footerScript')
    <script src="{{ asset('plugins/sweet-alert2/sweetalert2.min.js') }}"></script>
    <script src="{{ asset('assets/pages/jquery.sweet-alert.init.js') }}"></script>
@stop