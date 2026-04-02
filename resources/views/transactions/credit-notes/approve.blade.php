@extends('app-layouts.admin-master')

@section('title', 'Approve Credit Notes')

@section('headerStyle')
    <link href="{{ asset('plugins/datatables/dataTables.bootstrap4.min.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('plugins/sweet-alert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('assets/css/app-style-v1.css') }}" rel="stylesheet" type="text/css">
    <style type="text/css">
        .mdl-field {
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
                @component('app-components.breadcrumb-3')
                    @slot('title') Approve Credit Notes @endslot
                    @slot('item1') Transactions @endslot
                    @slot('item2') Credit Notes @endslot                    
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
                                        <th>Document</th>
                                        <th>Date</th>
                                        <th>Customer</th>
                                        <th>Reason</th>
                                        <th>Amount</th>
                                        <th>Action</th>                                        
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
                                            <td class="text-center">{{ $record->document_date_for_display }}</td>
                                            <td class="text-left pl-2">{{ $record->customer->customer_name }}</td>
                                            <td class="text-left pl-2">{{ $record->reason->label() }}</td>
                                            <td class="text-right pr-2">{{ $record->amount }}</td>

                                            <td class="text-center">
                                                <button type="button" class="btn btn-success btn-approve btn-sm px-2 py-0 mx-1" 
                                                    data-id="{{ $record->id }}" title="Approve {{ $record->document_number }}" aria-label="Approve {{ $record->document_number }}">
                                                        Approve
                                                </button>

                                                <button type="button" class="btn btn-gradient-primary btn-view btn-sm px-2 py-0 mx-1" 
                                                    data-id="{{ $record->id }}" title="View {{ $record->document_number }}" aria-label="View {{ $record->document_number }}">
                                                        View
                                                </button>

                                                @can('cancel_credit_note')
                                                    <button type="button" class="btn btn-gradient-warning btn-cancel btn-sm px-2 py-0 mx-1" 
                                                        data-id="{{ $record->id }}" title="Cancel {{ $record->document_number }}" aria-label="Cancel {{ $record->document_number }}">
                                                            Cancel
                                                    </button>
                                                @endcan
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                @if($records->count() > 0)
                                    <tfoot class="thead-light">
                                        <tr>
                                            <th colspan="7" class="text-center">
                                                <button type="button" class="btn btn-pink btn-sm py-1" 
                                                    id="btn-approve-selected" title="Approve selected credit notes">
                                                        Approve Selected
                                                </button>
                                            </th>
                                        </tr>
                                    </tfoot>
                                @endif
                            </table>
                        </div>

                    </div><!--end card-body-->
                </div><!--end card-->
            </div> <!--end col-->
        </div><!--end row-->
    </div><!-- container -->

    @include('transactions.diesel-bills.entries.show-modal')
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

            const $dataTable        = $('#datatable');
            const $divDocumentDate  = $('#div-document-date');
            const $divBunkName      = $('#div-bunk-name');
            const $divBillNumber    = $('#div-bill-number');
            const $divBillDate      = $('#div-bill-date');
            const $divRouteName     = $('#div-route-name');
            const $divVehicleNumber = $('#div-vehicle-number');
            const $divDriverName    = $('#div-driver-name');
            const $divFuel          = $('#div-fuel');
            const $divRate          = $('#div-rate');
            const $divAmount        = $('#div-amount');
            const $divOpeningKm     = $('#div-opening-km');
            const $divClosingKm     = $('#div-closing-km');
            const $divRunningKm     = $('#div-running-km');
            const $divKmpl          = $('#div-kmpl');
            const $mdlForm          = $('#mdl-form');
            const $mdlFields        = $('.modal-field');

            const $chkBox           = $('.chk-box');
            const $chkAll           = $('#chk-all');
            const $btnApprove       = $('.btn-approve');
            const $btnApproveSelected = $('#btn-approve-selected');
            const $trFooter         = $('#tr-footer');

            doInit();

            function doInit() {
                $dataTable.dataTable( {
                    paging: false,
                    info: false,
                    searching: true,
                    dom: 'ft',
                } );                

                $chkAll.change(function () {
                    // Get the checked state of the "Select All" checkbox
                    let isChecked = $(this).is(':checked');
                    // Set the checked state of all checkboxes in the table body
                    $chkBox.prop('checked', isChecked).trigger('change');
                });

                $dataTable.on('click', '.btn-approve', function () {
                    const id = $(this).data('id');
                    approveRecords([id]);
                });

                $btnApproveSelected.on("click", function () {
                    const selectedIds = $chkBox.filter(':checked').map(function() {
                        return $(this).val();
                    }).get();

                    if (selectedIds.length === 0) {
                        Swal.fire('Sorry!', 'Please select records', 'warning');
                        return;
                    }

                    approveRecords(selectedIds);
                });

                $dataTable.on('click', '.btn-view', function () {
                    let id = $(this).data('id');
                    // viewRecord(id);
                });            
            }

            function viewRecord(id) {
                $.ajax({
                    url: "{{ route('diesel-bills.entries.fetch', ['bill' => '__ID__']) }}".replace('__ID__', id),
                    type: 'GET',
                    dataType: 'json'
                })
                .done(response => {
                    console.log("AJAX Success:", response);
                    let record = response.record;
                    $mdlFields.text('');
                    $divDocumentDate.text(record.document_date);
                    $divBunkName.text(record.bunk_name);
                    $divBillNumber.text(record.bill_number);
                    $divBillDate.text(record.bill_date);
                    $divRouteName.text(record.route_name);
                    $divVehicleNumber.text(record.vehicle_number);
                    $divDriverName.text(record.driver_name);
                    $divFuel.text(record.fuel);
                    $divRate.text(record.rate);
                    $divAmount.text(record.amount);
                    $divOpeningKm.text(record.opening_km);
                    $divClosingKm.text(record.closing_km);
                    $divRunningKm.text(record.running_km);
                    $divKmpl.text(record.kmpl);
                    $mdlForm.modal('show');
                })
                .fail((xhr, status, error) => {
                    handleAjaxError(xhr, status, error);
                });
            }

            function approveRecords(ids) {
                $.ajax({
                    url: "{{ route('credit-notes.approve.create') }}",
                    type: 'PATCH',
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
        });
    </script>
@endpush

@section('footerScript')
    <script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('plugins/sweet-alert2/sweetalert2.min.js') }}"></script>
    <script src="{{ asset('assets/pages/jquery.sweet-alert.init.js') }}"></script>
@stop