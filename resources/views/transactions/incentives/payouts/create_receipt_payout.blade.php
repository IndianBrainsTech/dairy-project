@extends('app-layouts.admin-master')

@section('title', 'Create Receipt Payout')

@section('headerStyle')
    <link href="{{ asset('plugins/sweet-alert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('plugins/animate/animate.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('assets/css/app-style-v1.css') }}" rel="stylesheet" type="text/css">
@stop

@section('content')
    <div class="container-fluid">
        <!-- Page-Title -->
        <div class="row">
            <div class="col-sm-12">
                @component('app-components.breadcrumb-4')
                    @slot('title') Create Receipt Payout @endslot
                    @slot('item1') Transactions @endslot
                    @slot('item2') Incentives @endslot
                    @slot('item3') Payouts @endslot
                @endcomponent
            </div><!--end col-->
        </div>
        <!-- end page title end breadcrumb -->

        <div class="row"> 
            <div class="col-lg-8 mx-auto">
                <div class="card">
                    <div class="card-body m-2">
 
                        <div class="row">
                            <form method="post" action="{{route('incentives.payouts.receipt.create')}}">
                                @csrf
                                <label for="from-date" class="app-text ml-2">From</label>
                                <input type="date" name="from_date" id="from-date" value="{{ $dates['from'] }}" class="app-control mr-2">
                                <label for="to-date" class="app-text">To</label>
                                <input type="date" name="to_date" id="to-date" value="{{ $dates['to'] }}" class="app-control mr-2">
                                <input type="submit" value="Load" class="btn btn-secondary py-1 px-3 mx-3" aria-label="Load" title="Load"/>                                
                            </form>
                        </div>
                        <hr/>

                        <div class="row">
                            <div class="col-sm-12">
                                <div class="form-group row justify-content-center">
                                    <button type="button" id="btn-prev" class="btn btn-gradient-primary mr-2" data-toggle="tooltip" data-placement="top" title="Left Arrow (<)"><i class="dripicons-media-previous"></i></button>
                                    <input type="text" id="txt-inc-num" class="form-control text-center" style="max-width:130px" readonly>
                                    <div class="input-group" style="width:400px">
                                        <span class="input-group-prepend">
                                            <button type="button" class="btn btn-info"><i class="fas fa-search"></i></button>
                                        </span>
                                        <input type="text" id="act-customer-name" class="form-control" placeholder="Customer">
                                        <input type="hidden" id="hdn-customer-id">
                                    </div>
                                    <button type="button" id="btn-next" class="btn btn-gradient-primary ml-2" data-toggle="tooltip" data-placement="top" title="Right Arrow (>)"><i class="dripicons-media-next"></i></button>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-sm-12">
                                <div class="table-responsive">
                                    <table id="tbl-incentive" class="table table-sm table-bordered nowrap text-center">
                                        <thead class="thead-light">
                                            <tr>
                                                <th>Period</th>
                                                <th>Generated on</th>
                                                <th>Amount</th>
                                                <th>Payable</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td id="td-inc-period"></td>
                                                <td id="td-inc-date"></td>
                                                <td id="td-inc-amount"></td>
                                                <td id="td-inc-payable"></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="row p-2">
                            <div class="col-sm-6">
                                <div class="form-group row mb-2">
                                    <label for="dt-rcpt" class="col-form-label ml-2 mr-2">Receipt Date <small class="text-danger font-13">*</small></label>
                                    <input type="date" id="dt-rcpt" value="{{ date('Y-m-d') }}" class="form-control" style="max-width:130px">
                                </div>
                            </div> 
                            <div class="col-sm-6">
                                <div class="form-group row mb-2 float-right">
                                    <label for="txt-rcpt-amt" class="col-form-label mr-2">Amount <small class="text-danger font-13">*</small></label>
                                    <input type="text" id="txt-rcpt-amt" class="form-control text-center int-input mr-3" style="max-width:110px">
                                </div>
                            </div>
                        </div>

                        <div class="row p-2">
                            <div class="table-responsive">
                                <table id="tbl-receipt" class="table table-sm table-bordered nowrap text-center">
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
                                            <th id="th-oustd-amt"></th>
                                            <th id="th-tot-amt"></th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-sm-6">
                                <div class="form-group row">
                                    <label for="txt-advance-amt" class="col-form-label ml-3 mr-2">Advance Amount</label>
                                    <input type="text" id="txt-advance-amt" class="form-control text-center" style="max-width:110px" readonly>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group row float-right">
                                    <label for="txt-excess-amt" class="col-form-label mx-2">Excess Balance</label>
                                    <input type="text" id="txt-excess-amt" class="form-control text-center mr-3" style="max-width:110px" readonly>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-sm-12">
                                <div class="form-group row float-right">
                                    <input type="button" id="btn-clear" class="btn btn-secondary mx-2" value="Clear" />
                                    <input type="button" id="btn-submit" class="btn btn-primary mx-3" value="Submit" data-toggle="tooltip" data-placement="top" title="Alt+S"/>
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
    <script src="{{ asset('assets/js/date-helper.js') }}"></script>
    <script>
        $(document).ready(function() {
            // Setup CSRF token for AJAX requests
            $.ajaxSetup({
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
            });

            let customers = new Map();
            let incentives = [];
            let currentIndex = 0;
            doInit();

            function doInit() {
                $('a[href="#MenuTransactions"]').click();
                // restrictDates('#dt-rcpt', getYesterday(), getToday());
                restrictDates('#dt-rcpt', '2025-07-31', getToday());
                loadIncentives();

                $('#from-date').change(function() {
                    let date = $(this).val();
                    $('#to-date').attr('min',date);
                });

                $("#from-date").trigger('change');

                // Events
                $('#hdn-customer-id').on('change', handleCustomerChange);
                $('#act-customer-name').on('input', handleCustomerClear);
                $('#btn-prev').on('click', prevIncentive);
                $('#btn-next').on('click', nextIncentive);
                $('#txt-rcpt-amt').on('keypress', focusFirstAmountInput);
                $('#txt-rcpt-amt').on('change', updateReceivedAmount);
                $('#tbl-receipt tbody').on('keypress', '.amt-input', focusNextOnAmountEnter);
                $('#tbl-receipt tbody').on('change', '.amt-input', validateAmountInput);
                $('#btn-clear').on('click', clearFields);
                $('#btn-submit').on('click', submitForm);

                $(document)
                    .on('keypress', '.int-input', restrictToInteger)
                    .on('keydown', shortcutActions);
            }

            // Functions
            function loadIncentives() {
                incentives = @json($incentives);
                console.log(incentives);
                if (incentives.length > 0) {
                    currentIndex = 0;
                    loadCustomers();
                    updateIncentiveFields(currentIndex);
                }
                else {
                    Swal.fire('Sorry!', 'No outstanding incentives found', 'warning');
                }                
            }

            function handleCustomerChange() {
                // Arrow function
                const getIncentiveIndexByCustomerId = (id) => incentives.findIndex(i => i.customer_id == id);

                const id = $('#hdn-customer-id').val();
                currentIndex = getIncentiveIndexByCustomerId(id);
            
                updateIncentiveFields(currentIndex);
            }

            function handleCustomerClear() {
                if(!$('#act-customer-name').val()) {
                    $('#tbl-receipt tbody').empty();
                    clearFields();
                }
            }

            function prevIncentive() {
                if (currentIndex > 0) {
                    currentIndex--;
                    updateIncentiveFields(currentIndex);
                }
                else {
                    Swal.fire('Sorry!','No Previous Incentive!','warning');
                }
            }

            function nextIncentive() {
                if (currentIndex < incentives.length - 1) {
                    currentIndex++;
                    updateIncentiveFields(currentIndex);
                }
                else {
                    Swal.fire('Sorry!','No Next Incentive!','warning');
                }
            }

            function focusFirstAmountInput(event) {
                if (event.keyCode == 13) $('#txt-amount1').focus();
            }

            function updateReceivedAmount() {
                const receivedAmount = parseFloat($('#txt-rcpt-amt').val());
                if (!isNaN(receivedAmount)) {
                    const advanceAmount = parseFloat($('#txt-advance-amt').val()) || 0; // Default to 0 if NaN
                    distributeAmount(receivedAmount + advanceAmount);
                    updateAmountTotal();
                }
            }

            function focusNextOnAmountEnter(event) {
                if (event.which === 13) {
                    event.preventDefault();
                    let currentId = $(this).attr('id');
                    let nextIdNumber = parseInt(currentId.replace('txt-amount', '')) + 1;
                    let nextInput = $('#txt-amount' + nextIdNumber);

                    if (nextInput.length) nextInput.focus();
                    else note1.focus();
                }
            }

            function validateAmountInput() {
                const input = $(this);
                const amount = parseFloat(input.val());
                const row = input.closest('tr');
                const outstandingAmount = parseFloat(row.find('td[id^="td-oustd"]').text());

                if (amount > outstandingAmount) 
                    $input.val(outstandingAmount);
                updateAmountTotal();
            }

            function clearFields() {
                $('#txt-rcpt-amt').val('');
                $('#txt-advance-amt').val('');
                $('#txt-excess-amt').val('');
                $('#th-tot-amt').text('');
                $('#tbl-receipt tbody [id^=txt-amount]').val('');
            }

            function submitForm() {
                if (isValidated()) {
                    let requestData = {
                        cust_id        : $("#hdn-customer-id").val(),
                        cust_name      : $('#act-customer-name').val(),
                        rcpt_date      : $("#dt-rcpt").val(),
                        amount         : $("#txt-rcpt-amt").val(),
                        aggr_amt       : $("#th-tot-amt").text(),
                        adv_amt        : $("#txt-advance-amt").val(),
                        excess_amt     : $("#txt-excess-amt").val(),
                        mode           : 'Incentive',
                        incentive_num  : $("#txt-inc-num").val(),
                        receipt_data   : getReceiptData(),
                    };

                    $('#btn-submit').prop('disabled', true); // Disable submit button
                    console.log(requestData);

                    // Send AJAX request
                    $.ajax({
                        url: "{{ route('receipts.store') }}",
                        type: "POST",
                        data: requestData,
                        dataType: 'json',
                        success: function(response) {
                            Swal.fire('Success!', response.message, 'success')
                                .then(postSubmit);
                            $('#btn-submit').prop('disabled', false); // Enable submit button
                        },
                        error: function(xhr, status, error) {
                            let response = JSON.parse(xhr.responseText);
                            Swal.fire('Sorry!', response.message, response.icon || 'error');
                            $('#btn-submit').prop('disabled', false);
                        }
                    });
                }
            }

            function shortcutActions(event) {
                // ALT + S to submit
                if (event.altKey && event.key === 's')
                    submitForm();

                // Arrow navigation
                if (event.key === 'ArrowLeft')
                    prevIncentive();
                else if (event.key === 'ArrowRight')
                    nextIncentive();
            }

            function loadCustomers() {
                $('#hdn-customer-id').val(0);
                $('#act-customer-name').val('');
                customers = new Map();
                incentives.forEach(function (incentive) {
                    customers.set(incentive.customer_name, incentive.customer_id); // key = name, value = id
                });

                $('#act-customer-name').autocomplete({
                    source: autocompleteSource(customers),
                    autoFocus: true,
                    minLength: 0,
                    select: function(event, ui) {
                        let name = ui.item.value;
                        let id = customers.get(name);
                        console.log("Selected Customer => ID: " + id + ", Name: " + name);
                        $('#hdn-customer-id').val(id).trigger('change');
                    }
                });

                if ($('#act-customer-name').data('ui-autocomplete')) {
                    $('#act-customer-name').autocomplete('option', 'source', autocompleteSource(customers));
                    $("#hdn-customer-id").val(0);
                }
            }

            $('#act-customer-name').on('blur', function () {
                let customerName = $('#act-customer-name').val();
                if(customerName == "") {
                    $('#hdn-customer-id').val(0);
                }
                else if(!customers.has(customerName)){
                    const customerId = $('#hdn-customer-id').val();
                    customerName = getKeyByValue(customers, customerId);
                    $('#act-customer-name').val(customerName);
                }
            });

            function autocompleteSource(sourceMap) {
                return function(request, response) {
                    let results = Array.from(sourceMap.keys()).map(function(key) {
                        return {
                            label: key,
                            value: key
                        };
                    }).filter(function(item) {
                        return item.label.toLowerCase().startsWith(request.term.toLowerCase());
                    });
                    response(results);
                };
            }

            function getKeyByValue(map, searchValue) {
                for (let [key, value] of map.entries()) {
                    if (value == searchValue) {
                        return key;
                    }
                }
                return null; // Not found
            }

            function updateIncentiveFields(index) {
                const incentive = incentives[index];
                $('#txt-inc-num').val(incentive.incentive_number);
                $('#hdn-customer-id').val(incentive.customer_id);
                $('#act-customer-name').val(incentive.customer_name);
                $('#td-inc-period').text(incentive.period);
                $('#td-inc-date').text(incentive.incentive_date);
                $('#td-inc-amount').text(incentive.net_amount);
                $('#td-inc-payable').text(incentive.payable);
                constructTable();
            }

            function constructTable() {
                const id = $('#hdn-customer-id').val();
                $('#tbl-receipt tbody').empty();
                $('#th-oustd-amt').text('');
                clearFields();
                let $oustd = 0;
                let url = "{{ route('receipts.receivables', ':id') }}".replace(':id', id);
                $.get(url, function(data) {
                    let i = 1;
                    let invoices = data.invoices;
                    invoices.forEach(function(item) {
                        const newRow = $("<tr>")
                            .append(`<td>${item.invoice_date}</td>`)
                            .append(`<td>${item.invoice_num}</td>`)
                            .append(`<td id='td-oustd${i}'>${item.outstanding}</td>`)
                            .append(`<td><input type='text' id='txt-amount${i}' class='app-control int-input amt-input text-center mr-0' style='width:90px'></td>`);
                        $("#tbl-receipt tbody").append(newRow);
                        $oustd += item.outstanding;
                        i++;
                    });
                    $('#th-oustd-amt').text($oustd);
                    if(data.amount)
                        $('#txt-advance-amt').val(data.amount.excess_amt);
                    $('#txt-rcpt-amt').focus();
                });
            }

            function distributeAmount(receivedAmount) {
                let remainingAmount = receivedAmount;
                $('#tbl-receipt tbody tr').each(function(index, row) {
                    // Skip the first row if it is 'Opening Amount'
                    if (index === 0 && $(row).find('td').eq(1).text().trim() === 'Opening Amount') {
                        return true; // Skip this iteration
                    }

                    const $row = $(row);
                    const outstandingAmount = parseFloat($row.find('td[id^="td-oustd"]').text());
                    const amountInput = $row.find('input[id^="txt-amount"]');

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
                $('#tbl-receipt tbody [id^=txt-amount]').each(function() {
                    var amount = $(this).val();
                    if (amount) total += Number(amount);
                });
                $("#th-tot-amt").text(total);
                
                let advAmt = parseFloat($('#txt-advance-amt').val()) || 0;
                let rcptAmt = parseFloat($('#txt-rcpt-amt').val()) || 0;
                let excess = advAmt + rcptAmt - total;
                if(excess == 0)
                    $('#txt-excess-amt').val('');
                else
                    $('#txt-excess-amt').val(excess);
            }

            function isValidated(mode) {
                const amount    = parseFloat($("#txt-rcpt-amt").val()) || 0;
                const oustdAmt  = parseFloat($("#th-oustd-amt").text()) || 0;
                const totalAmt  = parseFloat($("#th-tot-amt").text()) || 0;
                const advAmt    = parseFloat($("#txt-advance-amt").val()) || 0;
                const excessAmt = parseFloat($("#txt-excess-amt").val()) || 0;
                const payable   = parseFloat($('#td-inc-payable').text()) || 0;

                if (amount == 0) {
                    Swal.fire('Sorry!', 'Please Enter Amount', 'warning');
                    return false;
                }
                else if(amount > payable) {
                    Swal.fire('Sorry!', 'Amount can not Exceeds Payable Incentive', 'warning');
                    return false;
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

                $('#tbl-receipt tbody tr').each(function() {
                    const row = $(this);
                    const invDate  = row.find('td:nth-child(1)').text();
                    const invNum   = row.find('td:nth-child(2)').text().trim();
                    const oustdAmt = parseInt(row.find('td:nth-child(3)').text());
                    const amount   = parseInt(row.find('td:nth-child(4) [id^=txt-amount]').val());

                    receiptData.push({
                        inv_date  : invDate,
                        inv_num   : invNum,
                        oustd_amt : oustdAmt,
                        rcvd_amt  : amount
                    });
                });

                return JSON.stringify(receiptData);
            }

            function postSubmit() {
                $('#hdn-customer-id').val(0);
                $('#act-customer-name').val('');
                $('#tbl-receipt tbody').empty();
                $('#td-inc-period').text('');
                $('#td-inc-date').text('');
                $('#td-inc-amount').text('');
                $('#td-inc-payable').text('');
                clearFields();
                // Remove current incentive
                incentives.splice(currentIndex, 1);
                // Loads next incentive
                updateIncentiveFields(currentIndex);
            }
        });
    </script>
@endpush

@section('footerScript')
    <script src="{{ asset('plugins/sweet-alert2/sweetalert2.min.js') }}"></script>
    <script src="{{ asset('assets/pages/jquery.sweet-alert.init.js') }}"></script>
@stop