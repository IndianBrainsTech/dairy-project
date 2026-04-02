@extends('app-layouts.admin-master')

@section('title', 'Accept Diesel Bills')

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
                @component('app-components.breadcrumb-4')
                    @slot('title') Accept Diesel Bills @endslot
                    @slot('item1') Transactions @endslot
                    @slot('item2') Diesel Bills @endslot
                    @slot('item3') Entry @endslot
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
                                        <th class="text-center">Date</th>
                                        <th class="text-left pl-2">Petrol Bunk</th>
                                        <th class="text-left pl-2">Vehicle Number</th>
                                        <th class="text-left pl-2">Route</th>
                                        <th class="text-left pl-2">Bill Number</th>
                                        <th class="text-right pr-2">Amount</th>
                                        <th class="text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($bills as $bill)
                                        <tr>
                                            <td class="text-right">
                                                <div class="checkbox checkbox-primary checkbox-single">
                                                    <input type="checkbox" class="chk-box" value="{{ $bill->id }}" aria-label="chk-{{ $bill->id }}">
                                                    <label class="mb-0 pl-0"></label>
                                                </div>
                                            </td>
                                            <td class="text-center">{{ $bill->document_date }}</td>
                                            <td class="text-left pl-2">{{ $bill->bunk_name }}</td>
                                            <td class="text-left pl-2">{{ $bill->vehicle_number }}</td>
                                            <td class="text-left pl-2">{{ $bill->route_name }}</td>
                                            <td class="text-left pl-2">{{ $bill->bill_number }}</td>
                                            <td class="text-right pr-2">{{ $bill->amount }}</td>
                                            <td class="text-center">
                                                <button type="button"
                                                        class="btn btn-link btn-icon btn-view"
                                                        data-id="{{ $bill->id }}"
                                                        aria-label="View diesel bill {{ $bill->id }}">
                                                    <i class="dripicons-preview text-primary font-20"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="row justify-content-end">
                            <button type="button" id="btn-accept" class="btn btn-sm btn-primary px-4 mr-3" title="Accept Diesel Bill">
                                Accept
                            </button>
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
            const $btnAccept        = $('#btn-accept');

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

                @if($bills->count() === 0)
                    $btnAccept.hide();
                @endif

                $btnAccept.on("click", acceptRecords);
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

            function acceptRecords() {
                const selectedIds = $chkBox.filter(':checked').map(function() {
                    return $(this).val();
                }).get();

                if (selectedIds.length === 0) {
                    Swal.fire('Sorry!', 'Please select records', 'warning');
                    return;
                }

                $.ajax({
                    url: "{{ route('diesel-bills.entries.accept.update') }}",
                    type: 'PUT',
                    data: { ids : selectedIds},
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