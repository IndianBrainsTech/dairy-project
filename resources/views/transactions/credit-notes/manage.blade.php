@extends('app-layouts.admin-master')

@section('title', $page_title)

@section('headerStyle')
    <link href="{{ asset('plugins/sweet-alert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('plugins/animate/animate.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('assets/css/app-style-v1.css') }}" rel="stylesheet" type="text/css">
@stop

@section('content')
    <div class="container-fluid">
        <!-- Page Header: Title & Breadcrumb Navigation -->
        <div class="row">
            <div class="col-12">
                @component('app-components.breadcrumb-3')
                    @slot('title') {{ $page_title }} @endslot
                    @slot('item1') Transactions @endslot
                    @slot('item2') Credit Note @endslot
                @endcomponent
            </div>
        </div>
        <!-- /Page Header -->

        <div class="row">
            <div class="col-lg-10">
                <div class="card">
                    <div class="card-body">

                        {{-- Document Number, Document Date, Customer --}}
                        <div class="row">
                            <div class="col-3">
                                <div class="form-group">
                                    <label for="txt-document-number" class="form-label">
                                        Document Number
                                    </label>
                                    <input type="text" 
                                        name="document_number" 
                                        id="txt-document-number" 
                                        value="{{ old('document_number', $record->document_number ?? '') }}" 
                                        class="form-control @error('document_number') is-invalid @enderror" 
                                        tabindex="1" 
                                        readonly>
                                    @error('document_number')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-3">
                                <div class="form-group">
                                    <label for="dt-document">
                                        Document Date <small class="text-danger font-13">*</small>
                                    </label>
                                    <input type="date" 
                                        name="document_date" 
                                        id="dt-document"
                                        value="{{ old('document_date', $record->document_date->format('Y-m-d')) }}"
                                        class="form-control @error('document_date') is-invalid @enderror" 
                                        tabindex="2">
                                    @error('document_date')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-6">
                                <div class="form-group">
                                    <label for="act-customer-name">
                                        Customer <small class="text-danger font-13">*</small>
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-prepend">
                                            <button type="button" class="btn btn-info">
                                                <i class="fas fa-search"></i>
                                            </button>
                                        </span>
                                        <input type="text" 
                                            name="customer_name" 
                                            id="act-customer-name" 
                                            class="form-control" 
                                            placeholder="Customer" 
                                            tabindex="3">
                                        <input type="hidden" 
                                            name="customer_id" 
                                            id="hdn-customer-id">
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        {{-- Narration, Reason & Amount --}}
                        <div class="row">
                            <div class="col-6">
                                <div class="form-group">
                                    <label for="txt-narration" class="form-label">
                                        Narration
                                    </label>
                                    <textarea 
                                        name="narration" 
                                        id="txt-narration" 
                                        rows="2" 
                                        class="form-control @error('narration') is-invalid @enderror" 
                                        tabindex="4" 
                                        >{{ old('narration', $record->narration ?? '') }}</textarea>
                                    @error('narration')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-4">
                                <div class="form-group">
                                    <label for="ddl-reason" class="form-label">
                                        Reason <small class="text-danger font-13">*</small>
                                    </label>
                                    <select name="reason" id="ddl-reason" 
                                        class="form-control @error('reason') is-invalid @enderror" 
                                        tabindex="5">
                                            <option value="">Select</option>                                            
                                            @foreach(\App\Enums\CreditNoteReason::cases() as $option)
                                                <option value="{{ $option->value }}" 
                                                    @selected(old('reason', $record->reason?->value) === $option->value)>
                                                    {{ $option->label() }}
                                                </option>
                                            @endforeach
                                    </select>
                                    @error('reason')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-2">
                                <div class="form-group">
                                    <label for="txt-amount" class="form-label">
                                        Amount <small class="text-danger font-13">*</small>
                                    </label>
                                    <input type="text" 
                                        name="amount" 
                                        id="txt-amount"
                                        value="{{ old('amount', isset($record->amount) ? (float) $record->amount : '') }}"
                                        class="form-control text-center @error('amount') is-invalid @enderror" 
                                        tabindex="6" 
                                        maxlength="18">
                                    @error('amount')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="table-responsive mb-2">
                            <table id="tbl-write-off" class="table table-sm table-bordered nowrap text-center">
                                <thead class="thead-light">
                                    <tr>
                                        <th style="min-width:110px">Invoice<br/>Date</th>
                                        <th style="min-width:110px">Invoice<br/>Number</th>
                                        <th>Invoice<br/>Amount</th>
                                        <th>Paid<br/>Amount</th>
                                        <th>Draft<br/>Amount</th>
                                        <th>Outstanding<br>Amount</th>
                                        <th>Adjusted<br/>Amount <small class="text-danger font-13">*</small></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if ($form_mode === \App\Enums\FormMode::EDIT)
                                        @foreach ($rows as $row)
                                            <tr data-record-id="{{ $row['record_id'] }}">
                                                <td>{{ $row['invoice_date'] }}</td>
                                                <td>{{ $row['invoice_num'] }}</td>
                                                <td>{{ $row['net_amt'] }}</td>
                                                <td>{{ $row['paid_amt'] === 0 ? "" : $row['paid_amt'] }}</td>
                                                <td>{{ $row['draft_amt'] == 0 ? "" : $row['draft_amt'] }}</td>
                                                <td>{{ $row['outstanding'] == 0 ? "" : $row['outstanding'] }}</td>
                                                <td>
                                                    <input type="text"
                                                        value="{{ $row['adjustment'] }}" 
                                                        data-invoice-number="{{ $row['invoice_num'] }}" 
                                                        data-outstanding="{{ $row['outstanding'] }}"
                                                        class="app-control amount-field text-center" 
                                                        style="width:90px"
                                                        tabindex="{{ $loop->iteration + 6 }}"
                                                        {{ $row['outstanding'] == 0 ? "disabled" : "" }}>
                                                </td>
                                            </tr>
                                        @endforeach
                                    @endif
                                </tbody>
                                <tfoot class="thead-light">
                                    <tr>
                                        <th colspan="4"></th>
                                        <th class="text-center">Total</th>
                                        <th id="th-outstanding">
                                            @if($form_mode === \App\Enums\FormMode::EDIT)
                                                {{ (float) $rows->sum('outstanding') }}
                                            @endif
                                        </th>
                                        <th id="th-total">
                                            @if($form_mode === \App\Enums\FormMode::EDIT)
                                                {{ (float) $record->amount }}
                                            @endif
                                        </th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        @if($form_mode === \App\Enums\FormMode::CREATE)
                            <button type="submit" id="btn-submit" class="btn btn-primary btn-sm px-3 float-right">
                                Submit
                            </button>
                            <button type="reset" id="btn-reset" class="btn btn-warning btn-sm px-3 mr-3 float-right">
                                Clear
                            </button>                            
                        @else
                            <button type="submit" id="btn-submit" class="btn btn-primary btn-sm px-3 float-right" 
                                tabindex="{{ $rows->count() + 7 }}">
                                Update
                            </button>
                            <button type="reset" id="btn-reset" class="btn btn-warning btn-sm px-3 mr-3 float-right" 
                                tabindex="{{ $rows->count() + 8 }}">
                                Reset
                            </button>
                        @endif

                    </div><!--end card-body-->
                </div><!--end card-->
            </div> <!--end col-->
        </div><!--end row-->
    </div><!-- container -->
@stop

@push('custom-scripts')
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.js"></script>
    <script src="{{ asset('assets/js/helper-v1.js')}}"></script>
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

            const $hdnCustomerId   = $('#hdn-customer-id');
            const $actCustomerName = $('#act-customer-name');
            const $txtDocumentNumber = $('#txt-document-number');
            const $dtDocument      = $('#dt-document');
            const $ddlReason       = $('#ddl-reason');
            const $txtNarration    = $('#txt-narration');
            const $txtAmount       = $('#txt-amount');
            const $tblWriteOff     = $('#tbl-write-off');
            const $thOutstanding   = $('#th-outstanding');
            const $thTotal         = $('#th-total');
            const $btnSubmit       = $('#btn-submit');
            const $btnReset        = $('#btn-reset');

            const showWarning = msg => Swal.fire('Sorry!', msg, 'warning');

            doInit();

            function doInit() {
                setMenuItemActive('Transactions','ul-credit-notes','li-credit-notes-create');                
                restrictToFloatNumbers('#txt-amount');
                restrictToFloatNumbers('.amount-field');                
                $hdnCustomerId.on('change', handleCustomerChange);
                $actCustomerName.on('input', handleCustomerClear);
                $txtAmount
                    .on('keypress', focusNextOnEnter)
                    .on('change', handleAmountChange);                    
                $tblWriteOff.find('tbody')
                    .on('keypress', 'input[type="text"]', focusNextOnEnter)
                    .on('change', 'input[type="text"]', validateAmountInput);                
                $btnSubmit.on('click', doSubmit);
                $btnReset.on('click', doReset);                
            }

            function handleCustomerChange() {
                $tblWriteOff.find('tbody').empty();
                $thTotal.text('');
                $thOutstanding.text('');
                constructTable();
            }

            function handleCustomerClear() {
                if(!$actCustomerName.val()) {
                    $hdnCustomerId.val(0);
                    $tblWriteOff.find('tbody').empty();
                    $thTotal.text('');
                    $thOutstanding.text('');
                }
            }

            function handleAmountChange() {
                const amount = parseFloat($txtAmount.val());
                if (!isNaN(amount)) {                    
                    distributeAmount(amount);
                    updateAmountTotal();
                }
                else {
                    $tblWriteOff.find('.amount-field').val('');
                    $thTotal.text('');
                }
            }

            function focusNextOnEnter(e) {
                if (e.which === 13) {  // Enter key
                    e.preventDefault();
                    let currentTabIndex = $(this).attr('tabindex');
                    let nextTabIndex = parseInt(currentTabIndex) + 1;
                    let nextInput = $('[tabindex="' + nextTabIndex + '"]');

                    if (nextInput.length)
                        nextInput.focus();
                }
            }

            function validateAmountInput() {                
                const $input = $(this);
                const amount = parseFloat($input.val());
                const outstandingAmount = parseFloat($input.data('outstanding'));

                if (amount > outstandingAmount) 
                    $input.val(outstandingAmount);

                updateAmountTotal();
            }

            function constructTable() {
                const id = $hdnCustomerId.val();
                $.ajax({
                    url: "{{ route('receivables', ['customer_id' => '__ID__']) }}".replace('__ID__', id),
                    type: 'GET',
                    dataType: 'json'
                })
                .done(response => {
                    console.log("AJAX Success (constructTable):", response);
                    let tabIndex = 7;
                    let outstanding = 0;                    
                    response.invoices.forEach(function(item) {
                        const newRow = $("<tr data-record-id='-1'>")
                            .append(`<td>${item.invoice_date}</td>`)
                            .append(`<td>${item.invoice_num}</td>`)
                            .append(`<td>${item.net_amt}</td>`)
                            .append(`<td>${item.paid_amt === 0 ? "" : item.paid_amt}</td>`)
                            .append(`<td>${item.draft_amt === 0 ? "" : item.draft_amt}</td>`)
                            .append(`<td>${item.outstanding === 0 ? "" : item.outstanding}</td>`)
                            .append(`<td>
                                        <input type="text"
                                            data-invoice-number="${item.invoice_num}" 
                                            data-outstanding="${item.outstanding}"
                                            class="app-control amount-field text-center" 
                                            style="width:90px"
                                            tabindex="${tabIndex++}" 
                                            ${item.outstanding === 0 ? "disabled" : ""}>                                            
                                    </td>`);
                        $tblWriteOff.find('tbody').append(newRow);
                        outstanding += item.outstanding;
                    });

                    $thOutstanding.text(outstanding);
                    $btnSubmit.attr("tabindex", tabIndex++);
                    $btnReset.attr("tabindex", tabIndex);
                })
                .fail((xhr, status, error) => {
                    handleAjaxError(xhr, status, error);
                });
            }

            function distributeAmount(amount) {
                let remainingAmount = amount;
                $tblWriteOff.find('tbody tr').each(function(index, row) {
                    // Skip the first row if it is 'Opening Amount'                    
                    if (index === 0 && $(row).find('td').eq(1).text().trim() === 'Opening Amount') {                        
                        return true; // Skip this iteration
                    }

                    const $amountField = $(row).find('.amount-field');
                    if ($amountField.prop('disabled')) {
                        return true; // Skip this iteration
                    }

                    const outstandingAmount = $amountField.data('outstanding');
                    if (remainingAmount > outstandingAmount) {
                        $amountField.val(outstandingAmount);
                        remainingAmount -= outstandingAmount;
                    } else {
                        $amountField.val(remainingAmount > 0 ? remainingAmount : '');
                        remainingAmount = 0;
                    }
                });
            }

            function updateAmountTotal() {                
                let total = 0;
                $tblWriteOff.find('tbody').find('.amount-field').each(function() {
                    let amount = $(this).val();
                    if (amount) 
                        total += Number(amount);
                });
                $thTotal.text(total);
                $txtAmount.val(total);
            }

            function doSubmit() {
                const documentDate = $dtDocument.val();
                const customerId   = $hdnCustomerId.val();
                const reason       = $ddlReason.val();
                const narration    = $txtNarration.val().trim();
                const amount       = $txtAmount.val();

                if(!documentDate)   return showWarning('Please enter document date');
                if(customerId == 0) return showWarning('Please select customer');
                if(!reason)         return showWarning('Please select reason');
                if(!amount)         return showWarning('Please enter amount');

                const records = getRecords();
                if(records.length === 0)
                    return showWarning('No adjusted amount entered');

                if(parseFloat(amount) != parseFloat($thTotal.text()))
                    return showWarning('Total mismatch with the entered amount');

                console.log("Records => " + JSON.stringify(records));

                $btnSubmit.prop('disabled', true);
                $.ajax({
                    url: "{{ $form_action }}",
                    type: "{{ $form_method }}",
                    data: {
                        document_date : documentDate,
                        customer_id   : customerId,
                        reason        : reason,
                        narration     : narration,
                        amount        : amount,  
                        items         : records,
                    },
                    dataType: 'json',
                })
                .done(response => {
                    console.log("AJAX Success (doSubmit):", response);
                    if(response.success) {
                        Swal.fire('Success!', response.message, 'success')
                            .then(() => {
                                @if($form_mode === \App\Enums\FormMode::CREATE)
                                    clearFields();
                                    $txtDocumentNumber.val(response.new_document);
                                    $actCustomerName.focus();
                                @else
                                    window.location.href = "{{ route('credit-notes.index') }}";
                                @endif
                            });
                    }
                    else {
                        Swal.fire('Sorry!', response.message, 'error');
                    }
                })
                .fail((xhr, status, error) => {
                    handleAjaxError(xhr, status, error);
                })
                .always(() => {
                    $btnSubmit.prop('disabled', false);
                });
            }

            function doReset() {
                @if($form_mode === \App\Enums\FormMode::EDIT)
                    window.location.reload();
                @else
                    clearFields();
                @endif
            }

            function getRecords() {
                let records = [];

                $tblWriteOff.find('tbody tr').each(function() {
                    const $row = $(this);
                    const amount = $row.find('.amount-field').val();
                    if(amount) {
                        records.push({
                            record_id          : $row.data('record-id'),
                            invoice_number     : $row.find('td:nth-child(2)').text(),
                            invoice_date       : $row.find('td:nth-child(1)').text(),
                            invoice_amount     : $row.find('td:nth-child(3)').text(),
                            paid_amount        : $row.find('td:nth-child(4)').text(),
                            outstanding_amount : $row.find('td:nth-child(6)').text(),
                            adjusted_amount    : amount,
                        });
                    }
                });

                return records;
            }

            function clearFields() {
                $hdnCustomerId.val(0);
                $actCustomerName.val('')                
                $txtNarration.val('');
                $ddlReason.val('');
                $txtAmount.val('');
                $tblWriteOff.find('tbody').empty();
                $thTotal.text('');
                $thOutstanding.text('');
            }
        });
    </script>
@endpush 

@section('footerScript')
    <script src="{{ asset('plugins/sweet-alert2/sweetalert2.min.js') }}"></script>
    <script src="{{ asset('assets/pages/jquery.sweet-alert.init.js') }}"></script>
@stop