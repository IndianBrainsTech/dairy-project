@extends('app-layouts.admin-master')

@section('title', 'Approve Bank Payout')

@section('headerStyle')
    <link href="{{ asset('plugins/datatables/dataTables.bootstrap4.min.css') }}" rel="stylesheet" type="text/css">
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
                    @slot('title') Approve Bank Payout @endslot
                    @slot('item1') Transactions @endslot
                    @slot('item2') Incentives @endslot
                    @slot('item3') Payouts @endslot
                @endcomponent
            </div><!--end col-->
        </div>
        <!-- end page title end breadcrumb -->
 
        <div class="row"> 
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">

                        <div class="table-responsive dash-social my-table">
                            <table id="datatable" class="table table-bordered table-sm dt-responsive nowrap text-center">
                                <thead class="thead-light">
                                    <tr>
                                        <th>S.No</th>
                                        <th class="d-none">ID</th>
                                        <th>Date</th>
                                        <th>Number</th>
                                        <th>Period</th>
                                        <th>Customer</th>
                                        <th>Amount</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($payouts as $record)
                                        <tr>
                                            <td>{{ $loop->index + 1 }}</td>
                                            <td class="d-none" data-id="{{ $record->id }}" data-details="{{ $record->incentiveNumber->customer_name . ' - ' . $record->period . ' - Rs.' . $record->amount }}">{{ $record->id }}</td>
                                            <td>{{ $record->document_date }}</td>
                                            <td>{{ $record->incentive_number }}</td>
                                            <td>{{ $record->period }}</td>
                                            <td class="text-left pl-2">{{ optional($record->incentiveNumber)->customer_name }}</td>
                                            <td class="text-right pr-2">
                                                <input type="text" id="txt-amt-{{ $record->id }}" value="{{ $record->amount }}" class="app-control app-focus text-center amt-input" data-payable="{{ $record->amount }}" style="max-width:90px">
                                            </td>
                                            <td>
                                                <button type="button" class="btn btn-gradient-info btn-sm px-2 py-0 mx-1" data-action="Pause" data-id="{{ $record->id }}">Pause</button>
                                                <button type="button" class="btn btn-gradient-warning btn-sm px-2 py-0 mx-1" data-action="Cancel" data-id="{{ $record->id }}">Cancel</button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="row mt-3">
                            <div class="col-sm-12">
                                <div class="form-group row float-right mr-2">
                                    <button id="btn-paused" type="button" class="btn btn-info btn-sm px-3 mr-3" aria-label="Approve" title="Approve"><b><span id="spn-paused"></span></b>&nbsp; Paused</button>
                                    <button id="btn-print" type="button" class="btn btn-pink py-1 px-2 mr-3" aria-label="Print" title="Print">&nbsp;<i class="fa fa-print"></i>&nbsp;</button>
                                    <span class="bg-soft-pink rounded mr-4 p-2">Total: <b><span id="spn-total"></span></b></span>                                    
                                    <label for="ddl-bank" class="app-text mr-2">Bank <small class="text-danger font-13">*</small></label>
                                    <select id="ddl-bank" class="app-control mr-4">
                                        <option value=""></option>
                                        @foreach($banks as $bank)
                                            <option value="{{ $bank->id }}">{{ $bank->display_name }}</option>
                                        @endforeach
                                    </select>                                    
                                    <button id="btn-approve" type="button" class="btn btn-primary btn-sm px-3 mx-2" aria-label="Approve" title="Approve">Approve</button>
                                </div>
                            </div>
                        </div>

                    </div><!--end card-body-->
                </div><!--end card-->
            </div> <!--end col-->
        </div><!--end row-->
    </div>

    <div id="div-print" class="table-responsive dash-social px-2 print-only">
        @include('app-components.print-header-1')

        <h2 class="app-h2 py-2">Incentive - Bank Payout List for Approval</h2>
        <table id="tbl-print" class="app-table">
            <thead class="thead-light">
                <tr class="text-center">
                    <th>S.No</th>
                    <th>Date</th>
                    <th>Document</th>
                    <th>Period</th>
                    <th>Customer</th>
                    <th>Amount</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
            </tbody>
            <tfoot class="text-right">
                <tr class="thead-light">
                    <th colspan="5" class="pr-2">Total</th>
                    <th class="pr-2" id="th-total"></th>
                </tr>
            </tfoot>
        </table>
    </div>

    <!-- Start of Paused Incentive Modal -->
    <div class="modal fade" id="modal-form" tabindex="-1" role="dialog" aria-labelledby="modalIncentiveLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document" >
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title mt-0" id="modal-title">Paused Incentives</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body py-1">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="table-responsive dash-social">
                                <table id="tbl-paused" class="table table-bordered table-sm dt-responsive nowrap text-center">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>S.No</th>
                                            <th class="d-none">ID</th>
                                            <th>Date</th>
                                            <th>Number</th>
                                            <th>Period</th>
                                            <th>Customer</th>
                                            <th>Amount</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($paused as $record)
                                            <tr>
                                                <td>{{ $loop->index + 1 }}</td>
                                                <td class="d-none" data-id="{{ $record->id }}" data-details="{{ $record->incentiveNumber->customer_name . ' - ' . $record->period . ' - Rs.' . $record->amount }}">{{ $record->id }}</td>
                                                <td>{{ $record->document_date }}</td>
                                                <td>{{ $record->incentive_number }}</td>
                                                <td>{{ $record->period }}</td>
                                                <td class="text-left pl-2">{{ optional($record->incentiveNumber)->customer_name }}</td>
                                                <td class="text-right pr-2">{{ $record->amount }}</td>
                                                <td>
                                                    <button type="button" class="btn btn-gradient-info btn-sm px-2 py-0 mx-1" data-action="Resume" data-id="{{ $record->id }}">Resume</button>
                                                    <button type="button" class="btn btn-gradient-warning btn-sm px-2 py-0 mx-1" data-action="Cancel" data-id="{{ $record->id }}">Cancel</button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- End of Paused Incentives Modal -->
@stop

@push('custom-scripts')
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.js"></script>
    <script src="{{ asset('assets/js/input-restriction.js')}}"></script>
    <script src="{{ asset('assets/js/file-helper.js')}}"></script>
    <script src="{{ asset('assets/js/helper.js')}}"></script>
    <script>
        $(document).ready(function () {
            $.ajaxSetup({
                headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
            });

            let datatable;
            let pausedCount;
            doInit();

            function doInit() {
                $('a[href="#MenuTransactions"]').click();
                pausedCount = {{ count($paused) }};

                datatable = $('#datatable').DataTable({
                    paging: false,   // Disable pagination (show all rows)
                    info: false,     // (Optional) Hide "Showing X of Y entries"
                    searching: true, // (Optional) Keep search if needed
                    dom: 'ft',       // Only show Filter (search box) and Table
                });

                $(document)
                    .on('keypress', '.amt-input', restrictToInteger)
                    .on('change', '.amt-input', validateAndUpdate);

                $('body').on('click', 'button', btnClick);
                $('#btn-print').on('click', btnPrintClick);
                $('#btn-paused').on('click', btnPausedClick);
                $('#btn-approve').on('click', btnApproveClick);

                updateTotals();
                loadPaused();
            }

            function validateAndUpdate() {
                let input = $(this);
                let amount = parseFloat(input.val());

                // If amount is not valid or zero
                if (isNaN(amount) || amount === 0) {
                    input.val(''); // clear the input
                    updateTotals();
                    return;
                }

                // Cap amount to maximum payable if necessary
                let maxPayable = parseFloat(input.data('payable'));
                if (amount > maxPayable) {
                    amount = maxPayable;
                    input.val(amount);
                }
                
                updateTotals();
            }

            function btnClick() {
                const id = $(this).attr('data-id');
                const action = $(this).attr('data-action');
                if(action == "Pause")
                    doPause(id);
                else if(action == "Resume")
                    doResume(id);
                else if(action == "Cancel")
                    doCancel(id);
            }

            function btnPrintClick() {
                generatePrintTableRows();                
                let printContents = $('#div-print').html();
                $('body').html(printContents);
                window.print();                
                window.location.reload();
            }
            
            function btnPausedClick(event) {
                event.stopPropagation(); // prevent the event from bubbling up to the body
                $('#modal-form').modal('show');
            }

            function btnApproveClick(event) {
                event.stopPropagation(); // prevent the event from bubbling up to the body
                const total = parseInt($('#spn-total').text());
                const bankId = $('#ddl-bank').val();

                if (total === 0)
                    Swal.fire('Sorry', 'No Records to Approve!', 'warning');
                else if (!bankId)
                    Swal.fire('Sorry', 'Please Choose Bank!', 'warning');
                else
                    doApprove();
            }

            function updateTotals() {
                let total = 0;

                $('.amt-input').each(function () {
                    let amount = parseFloat($(this).val());
                    if(amount)
                        total += amount;
                });

                $('#spn-total').text(formatToIndianNumberFormat(total));
            }

            function loadPaused() {
                if(pausedCount == 0) {
                    $('#btn-paused').hide();
                }
                else {
                    $('#spn-paused').text(pausedCount);
                    $('#btn-paused').show();
                }
            }

            function doPause(id) {
                console.log("doPause : id=" + id);
                $.ajax({
                    url: "{{ route('incentives.payouts.bank.update.status') }}",
                    method: "POST",
                    data: { 
                        id: id,
                        action: 'Pause',
                    },
                    success: function(response) {
                        console.log(response);
                        const $row = $(`#datatable tr td[data-id="${id}"]`).closest('tr');
                        const $clone = $row.clone();

                        const amount = $(`#txt-amt-${id}`).data('payable');
                        $clone.find("td:eq(6)").text(amount);

                        let $button = $clone.find(`button[data-action="Pause"]`);
                        $button.attr('data-action', 'Resume').text('Resume');
                        
                        $('#tbl-paused tbody').append($clone);
                        datatable.row($row).remove().draw(false);
                        pausedCount++;
                        refreshTablesAndData();
                    },
                    error: function(xhr, status, error) {
                        let errorMessage = xhr.responseJSON?.message || 'Something went wrong.';
                        Swal.fire('Sorry', errorMessage, 'error');
                    }
                });
            }

            function doResume(id) {
                $.ajax({
                    url: "{{ route('incentives.payouts.bank.update.status') }}",
                    method: "POST",
                    data: { 
                        id: id,
                        action: 'Resume',
                    },
                    success: function(response) {
                        console.log(response);
                        const $row = $(`#tbl-paused tr td[data-id="${id}"]`).closest('tr');
                        const $clone = $row.clone();

                        const amount = $clone.find("td:eq(6)").text();
                        let amountInputHtml = '<input type="text" id="txt-amt-' + id + '" value="' + amount + '" class="app-control text-center amt-input" data-payable="' + amount + '" style="max-width:90px">';
                        $clone.find("td:eq(6)").html(amountInputHtml);

                        let $button = $clone.find(`button[data-action="Resume"]`);
                        $button.attr('data-action', 'Pause').text('Pause');

                        datatable.row.add($clone).draw(false);
                        $row.remove();
                        pausedCount--;
                        refreshTablesAndData();

                        if(pausedCount == 0)
                            $('#modal-form').modal('hide');
                    },
                    error: function(xhr, status, error) {
                        let errorMessage = xhr.responseJSON?.message || 'Something went wrong.';
                        Swal.fire('Sorry', errorMessage, 'error');
                    }
                });
            }

            function doCancel(id) {
                let details = $('td[data-id="' + id + '"]').attr('data-details');
                let confirmMessage = `${details}<br/>Do you want to cancel the payout?`;
                Swal.fire({
                        title: 'Cancel?',
                        html: confirmMessage,
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonText: 'Yes, cancel!',
                        cancelButtonText: 'No, close',
                    })
                    .then((result) => {
                        if (result.value) {
                            $.ajax({
                                url: "{{ route('incentives.payouts.bank.update.status') }}",
                                method: "POST",
                                data: { 
                                    id: id,
                                    action: 'Cancel',
                                },
                                success: function(response) {
                                    console.log(response);

                                    // Find the row containing the td with data-id
                                    let rowElement = $('td[data-id="' + id + '"]').closest('tr');

                                    if (rowElement.closest('table').attr('id') === 'datatable') {
                                        console.log(rowElement);
                                        datatable.row(rowElement).remove().draw(false);
                                        reindexTable('#datatable');
                                        updateTotals();
                                    }
                                    else if (rowElement.closest('table').attr('id') === 'tbl-paused') {
                                        rowElement.remove();
                                        reindexTable('#tbl-paused');
                                        $('#modal-form').modal('hide');
                                        pausedCount--;
                                        loadPaused();
                                    }
                                    else {
                                        console.warn('Row not found in either table.');
                                        return;
                                    }

                                    Swal.fire('Success', 'Incentive payout cancelled successfully!', 'success');
                                },
                                error: function(xhr, status, error) {
                                    let errorMessage = xhr.responseJSON?.message || 'Something went wrong.';
                                    Swal.fire('Sorry', errorMessage, 'error');
                                }
                            });
                        }
                    });
            }

            function doApprove() {
                let records = [];
                $('#datatable tbody tr').each(function() {
                    let textbox = $(this).find('input[id^="txt-amt-"]');
                    if(textbox.val()) {
                        records.push({
                            payout_id        : $(this).find('td:nth-child(2)').text(),
                            incentive_number : $(this).find('td:nth-child(4)').text(),
                            payable          : textbox.data('payable'),
                            amount           : textbox.val(),
                        });
                    }
                });
                console.log(records);
                
                let total = 0;
                $('.amt-input').each(function () {
                    let amount = parseFloat($(this).val());
                    if(amount)
                        total += amount;
                });

                $('#btn-approve').prop('disabled', true);

                $.ajax({
                    url: "{{ route('incentives.payouts.bank.approve') }}",
                    method: "POST",
                    data: {
                        records      : records,
                        bank_id      : $('#ddl-bank').val(),
                        bank_name    : $('#ddl-bank option:selected').text(),
                        total_amount : total,
                    },
                    dataType: 'json',
                    success: function(response) {
                        console.log(response);
                        let payId = response.pay_id;
                        Swal.fire({
                            title: 'Incentives Approved Successfully!',
                            text: 'Do you want to download it in Excel?',
                            icon: 'question',
                            showCancelButton: true,
                            confirmButtonText: 'Yes',
                            cancelButtonText: 'No'
                        }).then((result) => {
                            if (result.value) {
                                const url = `{{ route('incentives.payouts.bank.download') }}?pay_id=${encodeURIComponent(payId)}`;
                                downloadExcel(url);

                                // Refresh page after a short delay
                                setTimeout(() => {
                                    window.location.reload(true);
                                }, 2000); // Wait 2 seconds before refresh
                            }
                            else {
                                window.location.reload(true);
                            }
                        });
                    },
                    error: function(xhr, status, error) {
                        let errorMessage = xhr.responseJSON?.message || 'Something went wrong.';
                        Swal.fire('Sorry', errorMessage, 'error');
                        $('#btn-approve').prop('disabled', false);
                    }
                });
            }

            function refreshTablesAndData() {
                reindexTable('#datatable');
                reindexTable('#tbl-paused');
                updateTotals();
                loadPaused();
            }

            function generatePrintTableRows() {
                let sno = 0;
                let total = 0;
                let $printTableBody = $('#tbl-print tbody');
                $printTableBody.empty(); // Clear existing rows

                $('#datatable tbody tr').each(function(index) {
                    let $row = $(this);
                    let amount = parseFloat($row.find('input.amt-input').val()) || 0;
                    if(amount > 0) {                        
                        let date = $row.find('td:eq(2)').text();
                        let document = $row.find('td:eq(3)').text();
                        let periodText = $row.find('td:eq(4)').text();
                        let customer = $row.find('td:eq(5)').text();

                         // Format period with line break after "to"
                        let formattedPeriod = periodText.includes('to')
                            ? periodText.split('to').map(p => p.trim()).join('<br>to ')
                            : periodText;

                        total += amount;

                        let html = `
                            <tr class="text-center">
                                <td>${++sno}</td>
                                <td>${date}</td>
                                <td>${document}</td>
                                <td>${formattedPeriod}</td>
                                <td class="text-left pl-2">${customer}</td>
                                <td class="text-right pr-2">${formatIndianNumber(amount)}</td>
                                <td></td>
                            </tr>
                        `;

                        $printTableBody.append(html); 
                    }                   
                });

                $('#th-total').text(formatIndianNumber(total));
            }
        });
    </script>
@endpush

@section('footerScript')
    <script src="{{ asset('plugins/sweet-alert2/sweetalert2.min.js') }}"></script>
    <script src="{{ asset('assets/pages/jquery.sweet-alert.init.js') }}"></script>
    <script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables/dataTables.bootstrap4.min.js') }}"></script>
@stop