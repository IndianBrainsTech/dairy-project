@extends('app-layouts.admin-master')

@section('title', 'Accept Diesel Bill Statements')

@section('headerStyle')
    <link href="{{ asset('plugins/datatables/dataTables.bootstrap4.min.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('plugins/sweet-alert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('assets/css/app-style-v1.css') }}" rel="stylesheet" type="text/css">
    <style type="text/css">
        .modal-field {
            font-weight: 600;
        }
        hr {
            margin-top: 8px;
            margin-bottom: 8px;
        }
    </style>
@stop

@section('content')
    <div class="container-fluid">
        <!-- Page-Title -->
        <div class="row">
            <div class="col-12">
                @component('app-components.breadcrumb-4')
                    @slot('title') Accept Diesel Bill Statements @endslot
                    @slot('item1') Transactions @endslot
                    @slot('item2') Diesel Bills @endslot
                    @slot('item3') Generation @endslot
                @endcomponent
            </div>
        </div>
        <!-- end page title end breadcrumb -->

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        
                        <div class="table-responsive dash-social mb-3">
                            <table id="datatable" class="table table-bordered table-sm dt-responsive nowrap w-100">
                                <thead class="thead-light">
                                    <tr class="text-center">
                                        <th class="text-right pr-1">
                                            <div class="checkbox checkbox-primary checkbox-single">
                                                <input type="checkbox" id="chk-all" value="All" aria-label="Select All">
                                                <label class="mb-0 pl-0"></label>
                                            </div>
                                        </th>
                                        <th class="text-center">Document</th>
                                        <th class="text-center">Date</th>
                                        <th class="text-left pl-2">Petrol Bunk</th>
                                        <th class="text-center">Period</th>
                                        <th class="text-right pr-2">Amount</th>
                                        <th class="text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($records as $record)
                                        <tr>
                                            <td class="text-right">
                                                <div class="checkbox checkbox-primary checkbox-single">
                                                    <input type="checkbox" class="chk-box" value="{{ $record->id }}" aria-label="chk-{{ $record->id }}">
                                                    <label class="mb-0 pl-0"></label>
                                                </div>
                                            </td>
                                            <td class="text-center">{{ $record->document_number }}</td>
                                            <td class="text-center">{{ $record->document_date }}</td>
                                            <td class="text-left pl-2">{{ $record->bunk_name }}</td>
                                            <td class="text-center">{{ $record->getPeriod("") }}</td>
                                            <td class="text-right pr-2">{{ $record->net_amount }}</td>
                                            <td class="text-center">
                                                <button type="button" 
                                                        class="btn btn-info btn-sm btn-view px-2 py-0 mx-1" 
                                                        data-id="{{ $record->id }}" 
                                                        aria-label="View diesel bill statement {{ $record->document_number }}">
                                                    View
                                                </button>
                                                <button type="button" 
                                                        class="btn btn-gradient-warning btn-sm btn-cancel px-2 py-0 mx-1" 
                                                        data-id="{{ $record->id }}" 
                                                        aria-label="Cancel diesel bill statement {{ $record->document_number }}">
                                                    Cancel
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="row justify-content-end">
                            <button type="button" id="btn-accept" class="btn btn-sm btn-primary px-3 mr-5" title="Accept Diesel Bill Statement(s)">
                                Accept
                            </button>
                        </div>

                    </div><!--end card-body-->
                </div><!--end card-->
            </div> <!--end col-->
        </div><!--end row-->
    </div><!-- container -->

    <!-- Start of Statement Modal -->
    <div class="modal fade" id="mdl-form" tabindex="-1" role="dialog" aria-labelledby="modalStatementLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title mt-0" id="modal-title">Diesel Bill Statement</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body py-1">
                    <div class="row">
                        <div class="col-md-12">
                            <div id="div-statement" class="table-responsive dash-social px-2">
                                <input id="hdn-id" type="hidden" value="">
                                <h2 id="hdg-bunk" class="app-h2 mdl-value"></h2>
                                <h3 id="hdg-duration" class="app-h3 pb-2 mdl-value"></h3>
                                <table id="tbl-statement" class="table table-bordered table-sm text-nowrap">
                                    <thead class="thead-light">
                                        <tr>
                                            <th class="text-center">S.No</th>
                                            <th class="text-center">Date</th>
                                            <th class="text-left pl-2">Vehicle</th>
                                            <th class="text-left pl-2">Driver</th>
                                            <th class="text-left pl-2">Route</th>
                                            <th class="text-right pr-2">Fuel</th>
                                            <th class="text-right pr-2">Pre KM</th>
                                            <th class="text-right pr-2">Cur KM</th>
                                            <th class="text-right pr-2">Run KM</th>
                                            <th class="text-right pr-2">KMPL</th>
                                            <th class="text-right pr-2">Rate</th>
                                            <th class="text-right pr-2">Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                    <tfoot class="text-right">
                                        <tr class="thead-light">
                                            <th colspan="5" class="text-center">Total / Average</th>
                                            <th id="th-total-fuel" class="mdl-value pr-2"></th>
                                            <th></th>
                                            <th></th>
                                            <th id="th-total-kilometer" class="mdl-value pr-2"></th>
                                            <th id="th-average-kmpl" class="mdl-value pr-2"></th>
                                            <th id="th-average-rate" class="mdl-value pr-2"></th>
                                            <th id="th-total-amount" class="mdl-value pr-2"></th>
                                        </tr>
                                        <tr id="tr-tds">
                                            <th colspan="11" class="pr-2">TDS</th>
                                            <th id="th-tds-amount" class='mdl-value pr-2'></th>
                                        </tr>
                                        <tr>
                                            <th colspan="11" class="pr-2">Round Off</th>
                                            <th id="th-round-off" class='mdl-value pr-2'></th>
                                        </tr>
                                        <tr>
                                            <th colspan="11" class="pr-2">Net Amount</th>
                                            <th id="th-net-amount" class='mdl-value pr-2'></th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer justify-content-center">
                    <button type="button" class="btn btn-gradient-primary btn-sm px-3 mx-3" data-action="ACCEPT">Accept</button>
                    <button type="button" class="btn btn-gradient-warning btn-sm px-3 mx-3" data-action="CANCEL">Cancel</button>
                </div>
            </div>
        </div>
    </div>
    <!-- End of Statement Modal -->
@stop

@push('custom-scripts')
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.js"></script>
    <script src="{{ asset('assets/js/helper-v1.js')}}"></script>
    <script>
        $(document).ready(function () {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            
            const $hdnId         = $('#hdn-id');
            const $hdgBunk       = $('#hdg-bunk');
            const $hdgDuration   = $('#hdg-duration');
            const $thTotalFuel   = $('#th-total-fuel');
            const $thTotalKm     = $('#th-total-kilometer');
            const $thAverageKmpl = $('#th-average-kmpl');
            const $thAverageRate = $('#th-average-rate');
            const $thTotalAmount = $('#th-total-amount');
            const $thTdsAmount   = $('#th-tds-amount');
            const $thRoundOff    = $('#th-round-off');
            const $thNetAmount   = $('#th-net-amount');
            const $trTds         = $('#tr-tds');
            const $tblBody       = $('#tbl-statement tbody');
            const $btnAccept     = $('#btn-accept');
            const $btnCancel     = $('#btn-cancel');
            const $mdlForm       = $('#mdl-form');
            const $mdlValues     = $('.mdl-value');
            const $chkBox        = $('.chk-box');
            const $chkAll        = $('#chk-all');
            const $dataTable     = $('#datatable');

            doInit();

            function doInit() {
                $dataTable.dataTable( {
                    paging: false,
                    info: false,
                    searching: true,
                    dom: 'ft',
                } );

                $('a[href="#MenuTransactions"]').click();
                
                $chkAll.change(function () {
                    // Get the checked state of the "Select All" checkbox
                    let isChecked = $(this).is(':checked');
                    // Set the checked state of all checkboxes in the table body
                    $chkBox.prop('checked', isChecked).trigger('change');
                });

                $dataTable.on('click', '.btn-view', function () {
                    let id = $(this).data('id');
                    viewRecord(id);
                });

                $dataTable.on('click', '.btn-cancel', function () {
                    let id = $(this).data('id');
                    cancelRecord(id);
                });

                $('body').on('click', 'button', function () {
                    const action = $(this).attr('data-action');
                    if(action === "ACCEPT") {
                        const id = $hdnId.val();
                        acceptRecord(id);
                    }
                    else if(action === "CANCEL") {
                        const id = $hdnId.val();
                        cancelRecord(id);
                    }
                });

                @if($records->count() === 0)
                    $btnAccept.hide();
                @endif

                $btnAccept.on("click", function() {
                    const selectedIds = $chkBox.filter(':checked').map(function() {
                        return $(this).val();
                    }).get();

                    if (selectedIds.length === 0) {
                        Swal.fire('Sorry!', 'Please select records', 'warning');
                        return;
                    }

                    acceptRecord(selectedIds);
                });
            }

            function viewRecord(id) {
                $.ajax({
                    url: "{{ route('diesel-bills.statements.fetch', ['stmt' => '__ID__']) }}".replace('__ID__', id),
                    type: 'GET',
                    dataType: 'json'
                })
                .done(response => {
                    console.log("AJAX Success:", response);
                    buildStatementModal(response);
                    $mdlForm.modal('show');
                })
                .fail((xhr, status, error) => {
                    handleAjaxError(xhr, status, error);
                });
            }

            function acceptRecord(ids) {
                $.ajax({
                    url: "{{ route('diesel-bills.statements.accept.update') }}",
                    type: 'PUT',
                    data: { ids : ids},
                    dataType: 'json'
                })
                .done(response => {
                    console.log("AJAX Success:", response);
                    if(response.success)
                        Swal.fire('Success!', response.message, 'success')
                            .then(() => window.location.reload());
                    else
                        Swal.fire('Sorry!', response.message, 'error');
                })
                .fail((xhr, status, error) => {
                    handleAjaxError(xhr, status, error);
                });
            }

            function cancelRecord(id) {
                Swal.fire({
                    title: 'Are you sure?',
                    text: 'Do you want to cancel the statement',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, cancel it!',
                    cancelButtonText: 'No, close',
                })
                .then((result) => {
                    if (result.value) {
                        $.ajax({
                            url: "{{ route('diesel-bills.statements.accept.cancel') }}",
                            type: 'PUT',
                            data: { id : id},
                            dataType: 'json'
                        })
                        .done(response => {
                            console.log("AJAX Success:", response);
                            if(response.success)
                                Swal.fire('Success!', response.message, 'success')
                                    .then(() => window.location.reload());
                            else
                                Swal.fire('Sorry!', response.message, 'error');
                        })
                        .fail((xhr, status, error) => {
                            handleAjaxError(xhr, status, error);
                        });
                    }
                });
            }

            function buildStatementModal(response) {
                $hdnId.val('');
                $mdlValues.text('');
                $tblBody.empty();

                const record = response.record;
                $hdnId.val(record.id);
                $hdgBunk.text(record.bunk_name);
                $hdgDuration.text(record.period);

                // Populate rows
                response.bills.forEach((bill, index) => {
                    let row = `
                        <tr>
                            <td class="text-center">${index + 1}</td>
                            <td class="text-center">${bill.bill_date}</td>
                            <td class="text-left pl-2">${bill.vehicle_number}</td>
                            <td class="text-left pl-2">${bill.driver_name}</td>
                            <td class="text-left pl-2">${bill.route_name}</td>
                            <td class="text-right pr-2">${bill.fuel}</td>
                            <td class="text-right pr-2">${bill.opening_km}</td>
                            <td class="text-right pr-2">${bill.closing_km}</td>
                            <td class="text-right pr-2">${bill.running_km}</td>
                            <td class="text-right pr-2">${bill.kmpl}</td>
                            <td class="text-right pr-2">${bill.rate}</td>
                            <td class="text-right pr-2">${bill.amount}</td>
                        </tr>`;
                    $tblBody.append(row);
                });

                // Update totals
                $thTotalFuel.text(record.total_fuel);
                $thTotalKm.text(record.total_running_km);
                $thAverageKmpl.text(record.average_kmpl);
                $thAverageRate.text(record.average_rate);
                $thTotalAmount.text(record.total_amount);
                $thTdsAmount.text(record.tds_amount);
                $thRoundOff.text(record.round_off);
                $thNetAmount.text(record.net_amount);

                // TDS row visibility
                $trTds.toggle(Number(record.tds_amount) !== 0);

                $btnAccept.attr('data-id', record.id);
                $btnCancel.attr('data-id', record.id);
            }
        });
    </script>
@endpush

@section('footerScript')
    <script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('plugins/sweet-alert2/sweetalert2.min.js') }}"></script>
    <script src="{{ asset('assets/pages/jquery.sweet-alert.init.js') }}"></script>
@stop