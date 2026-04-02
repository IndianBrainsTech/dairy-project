@extends('app-layouts.admin-master')

@section('title', 'Make Incentives')

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
                @component('app-components.breadcrumb-3')
                    @slot('title') Make Incentives @endslot
                    @slot('item1') Transactions @endslot
                    @slot('item2') Incentives @endslot
                @endcomponent
            </div><!--end col-->
        </div>
        <!-- end page title end breadcrumb -->
 
        <div class="row"> 
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">

                        <div class="table-responsive dash-social">
                            <table id="datatable" class="table table-bordered table-sm dt-responsive nowrap">
                                <thead class="thead-light">
                                    <tr class="text-center">
                                        <th>S.No</th>
                                        <th>Date</th>
                                        <th>Number</th>
                                        <th>Customer</th>
                                        <th>Period</th>
                                        <th>Amount</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($records as $record)
                                        <tr class="text-center">
                                            <td>{{ $loop->index + 1 }}</td>
                                            <td>{{ $record->date }}</td>
                                            <td>{{ $record->number }}</td>
                                            <td class="text-left pl-2">{{ $record->customer }}</td>
                                            <td>{{ $record->period }}</td>
                                            <td class="text-right pr-2">{{ $record->amount }}</td>
                                            <td>
                                                <button type="button" class="btn btn-success btn-sm px-2 py-0 mx-1" data-action="Accept" data-number="{{ $record->number }}">Accept</button>
                                                <button type="button" class="btn btn-gradient-primary btn-sm px-2 py-0 mx-1" data-action="View" data-number="{{ $record->number }}">View</button>
                                                <button type="button" class="btn btn-gradient-warning btn-sm px-2 py-0 mx-1" data-action="Cancel" data-number="{{ $record->number }}">Cancel</button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div><!--end card-body-->
                </div><!--end card-->
            </div> <!--end col-->
        </div><!--end row-->
    </div>

    <!-- Start of Incentive Modal -->
    <div class="modal fade" id="modal-form" tabindex="-1" role="dialog" aria-labelledby="modalIncentiveLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document" >
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title mt-0" id="modal-title">Incentive</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body py-1">
                    <div class="row">
                        <div class="col-md-12">
                            <div id="div-incentive" class="table-responsive dash-social px-2">
                                <h2 id="hdg-customer" class="app-h2"></h2>
                                <h3 id="hdg-period" class="app-h3 pb-2"></h3>
                                <table id="tbl-incentive" class="table table-bordered table-sm">
                                    <thead class="thead-light">
                                        <tr>
                                            <th class="text-center">S.No</th>
                                            <th class="text-left pl-2">Item Name</th>
                                            <th class="text-right pr-2">Qty</th>
                                            <th class="text-right pr-2">Inc Rate</th>
                                            <th class="text-right pr-2">Inc Amt</th>
                                            <th class="text-right pr-2">Lk Qty</th>
                                            <th class="text-right pr-2">Lk Amt</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                    <tfoot class="text-right">
                                        <tr class="thead-light">
                                            <th colspan="2" class="text-center">Total</th>
                                            <th id="th-tot-qty" class="calc-val pr-2"></th>
                                            <th></th>
                                            <th id="th-tot-inc-amt" class="calc-val pr-2"></th>
                                            <th id="th-tot-lkg-qty" class="calc-val pr-2"></th>
                                            <th id="th-tot-lkg-amt" class="calc-val pr-2"></th>
                                        </tr>
                                        <tr>
                                            <td colspan="6" class="pr-2">Incentive</td>
                                            <td id="td-inc-amt" class="calc-val pr-2"></td>
                                        </tr>
                                        <tr>
                                            <td colspan="6" class="pr-2">Leakage</td>
                                            <td id="td-lkg-amt" class="calc-val pr-2"></td>
                                        </tr>
                                        <tr>
                                            <td colspan="6" class="pr-2">Total</td>
                                            <td id="td-tot-amt" class="calc-val pr-2"></td>
                                        </tr>
                                        <tr>
                                            <td colspan="6" class="pr-2">TDS</td>
                                            <td id="td-tds-amt" class="calc-val pr-2"></td>
                                        </tr>
                                        <tr>
                                            <td colspan="6" class="pr-2">Round Off</td>
                                            <td id="td-round-off" class="calc-val pr-2"></td>
                                        </tr>
                                        <tr>
                                            <th colspan="6" class="pr-2">Net Amount</th>
                                            <th id="th-net-amt" class="calc-val pr-2"></th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer justify-content-center">
                    <button type="button" id="btn-accept" class="btn btn-success btn-sm px-3 mx-3" data-action="Accept" data-number="">Accept</button>
                    <button type="button" id="btn-cancel" class="btn btn-gradient-warning btn-sm px-3 mx-3" data-action="Cancel" data-number="">Cancel</button>
                </div>
            </div>
        </div>
    </div>
    <!-- End of Incentive Modal -->
@stop

@push('custom-scripts')
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.js"></script>
    <script>
        $(document).ready(function () {
            $.ajaxSetup({
                headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
            });

            $('#datatable').DataTable({
                paging: false,   // Disable pagination (show all rows)
                info: false,     // (Optional) Hide "Showing X of Y entries"
                searching: true, // (Optional) Keep search if needed
                dom: 'ft',       // Only show Filter (search box) and Table
            });

            $('body').on('click', 'button', function () {
                const action = $(this).attr('data-action');
                const number = $(this).attr('data-number');

                if(action == "View") {
                    doView(number);
                }
                else if(action == "Accept" || action == "Cancel") {
                    doAction(action, number);
                }
            });

            function doAction(action, number) {
                $.ajax({
                    url: "{{ route('incentives.action') }}",
                    method: "POST",
                    data: {
                        action: action,
                        number: number,
                    },
                    success: function(response) {
                        if(response.success) {
                            Swal.fire('Success', response.message, 'success')
                                .then(() => window.location.reload());
                        }
                        else {
                            Swal.fire('Sorry!', response.message, 'error');
                        }
                    },
                    error: function(xhr, status, error) {
                        let errorMessage = xhr.responseJSON?.message || 'Something went wrong. Please try again.';
                        Swal.fire('Error', errorMessage, 'error');
                    }
                });
            }

            function doView(number) {
                $.ajax({
                    url: "{{ route('incentives.show') }}",
                    method: "POST",
                    data: { incentive_number: number },
                    success: function(response) {
                        console.log(response);
                        generateIncentiveTable(response);
                        $('#hdg-customer').text(response.incentive.customer);
                        $('#hdg-period').text(response.incentive.period);
                        $('#btn-accept').attr('data-number', number);
                        $('#btn-cancel').attr('data-number', number);
                        $('#modal-form').modal('show');
                    },
                    error: function(xhr, status, error) {
                        let errorMessage = xhr.responseJSON?.message || 'Something went wrong.';
                        Swal.fire('Sorry', errorMessage, 'error');
                    }
                });
            }

            function generateIncentiveTable(response) {
                // Clear tbody
                let $tbody = $('#tbl-incentive tbody');
                $tbody.empty();

                // Populate rows
                response.records.forEach((record, index) => {
                    let row = `
                        <tr>
                            <td class="text-center">${index + 1}</td>
                            <td class="text-left pl-2">${record.item_name}</td>
                            <td class="text-right pr-2">${record.qty}</td>
                            <td class="text-right pr-2">${record.inc_rate}</td>
                            <td class="text-right pr-2">${record.inc_amt}</td>
                            <td class="text-right pr-2">${record.lkg_qty}</td>
                            <td class="text-right pr-2">${record.lkg_amt}</td>
                        </tr>`;
                    $tbody.append(row);
                });

                // Update totals in footer
                let summary = response.summary;
                $('#th-tot-qty').text(summary.qty);
                $('#th-tot-inc-amt').text(summary.inc_amt);
                $('#th-tot-lkg-qty').text(summary.lkg_qty);
                $('#th-tot-lkg-amt').text(summary.lkg_amt);

                $('#td-inc-amt').text(summary.inc_amt);
                $('#td-lkg-amt').text(summary.lkg_amt);
                $('#td-tot-amt').text(summary.tot_amt);
                $('#td-tds-amt').text(summary.tds_amt);
                $('#td-round-off').text(summary.round_off);
                $('#th-net-amt').text(summary.net_amt);
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