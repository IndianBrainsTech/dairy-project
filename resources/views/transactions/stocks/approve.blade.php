@extends('app-layouts.admin-master')

@section('title', 'Approve Stocks')

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
            <div class="col-12">
                @component('app-components.breadcrumb-3')
                    @slot('title') Approve Stocks @endslot
                    @slot('item1') Transactions @endslot
                    @slot('item2') Stocks @endslot
                @endcomponent
            </div><!--end col-->
        </div>
        <!-- end page title end breadcrumb -->

        <div class="row"> 
            <div class="col-12 col-md-9 col-lg-6">
                <div class="card">
                    <div class="card-body">

                        <div class="table-responsive dash-social">
                            <table id="datatable" class="table table-bordered table-sm dt-responsive nowrap">
                                <thead class="thead-light">
                                    <tr class="text-center">
                                        <th>S.No</th>
                                        <th>Date</th>
                                        <th>Document</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($stocks as $stock)
                                        <tr class="text-center">
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ displayDate($stock->document_date) }}</td>
                                            <td>{{ $stock->document_number }}</td>
                                            <td>
                                                <button type="button"
                                                        class="btn btn-link btn-icon"
                                                        data-number="{{ $stock->document_number }}"
                                                        aria-label="View stock {{ $stock->document_number }}">
                                                    <i class="dripicons-preview text-primary font-20"></i>
                                                </button>
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

    <!-- Start of Stock Modal -->
    <div class="modal fade" id="mdl-stock" tabindex="-1" role="dialog" aria-labelledby="modalStockLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content" style="min-width:600px">
                <div class="modal-header">
                    <h5 class="modal-title mt-0" id="modal-title">Approve Stock</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body py-1">
                    <div class="row my-2">
                        <div class="col-md-4">Document Number</div>
                        <div class="col-md-8"><span id="spn-doc-num" class="app-bold"></span></div>
                    </div>
                    <div class="row my-2">
                        <div class="col-md-4">Document Date</div>
                        <div class="col-md-8"><span id="spn-doc-date" class="app-bold"></span></div>
                    </div>
                    <div class="row my-2">
                        <div class="col-md-4">Created by</div>
                        <div class="col-md-8"><span id="spn-creation-by" class="app-bold"></span> at <span id="spn-creation-at"></span></div>
                    </div>
                    <div class="row my-2" id="div-updated-by">
                        <div class="col-md-4">Updated by</div>
                        <div class="col-md-8"><span id="spn-updation-by" class="app-bold"></span> at <span id="spn-updation-at"></span></div>
                    </div>
                    <div class="table-responsive dash-social pt-2">
                        <table id="tbl-stocks" class="table table-bordered table-sm mb-2">
                            <thead class="thead-light">
                                <tr>
                                    <th class="text-center">S.No</th>
                                    <th class="text-center">Item Name</th>
                                    <th class="text-right pr-2">Qty</th>
                                    <th class="text-left pl-2">Unit</th>
                                    <th class="text-center">Batch Number</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer justify-content-center">
                    <button type="button" id="btn-reject" class="btn btn-warning btn-sm px-3 mx-3" data-action="Reject">Reject</button>
                    <button type="button" id="btn-approve" class="btn btn-success btn-sm px-3 mx-3" data-action="Approve">Approve</button>
                </div>
            </div>
        </div>
    </div>
    <!-- End of Stock Modal -->
@stop

@push('custom-scripts')
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.js"></script>
    <script src="{{ asset('assets/js/script-helper.js') }}"></script>
    <script>
        $(document).ready(function () {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            const $docNum       = $('#spn-doc-num');
            const $docDate      = $('#spn-doc-date');
            const $creationBy   = $('#spn-creation-by');
            const $creationAt   = $('#spn-creation-at');
            const $updationBy   = $('#spn-updation-by');
            const $updationAt   = $('#spn-updation-at');
            const $divUpdatedBy = $('#div-updated-by');
            const $tbody        = $('#tbl-stocks tbody');
            doInit();

            function doInit() {
                $('#datatable').DataTable({
                    paging: false,
                    info: false,
                    searching: true,
                    dom: 'ft',                    
                    language: {
                        emptyTable: "No stocks found for approval."
                    }
                });

                $('#datatable').on('click', 'button', viewStockInfo);
                $('#btn-reject').on('click', doAction);
                $('#btn-approve').on('click', doAction);
            }

            function viewStockInfo() {
                const number = $(this).data('number');
                $.ajax({
                    url: "{{ route('stocks.fetch') }}",
                    type: "GET",
                    data: { number : number },
                    dataType: 'json'
                })
                .done(response => {
                    console.log("AJAX Success:", response);
                    if(response.success) {
                        loadStockInfo(response.stock);
                        $('#mdl-stock').modal('show');
                    }
                    else {
                        Swal.fire('Sorry!', response.message, 'error');
                    }
                })
                .fail((xhr, status, error) => {
                    handleAjaxError(xhr, status, error);
                });
            }

            function loadStockInfo(stock) {
                $('[id^="spn-"]').text('');
                $tbody.empty();

                $docNum.text(stock.document_number);
                $docDate.text(stock.document_date_formatted);
                $creationBy.text(stock.creation_by);
                $creationAt.text(stock.creation_at);
                if(stock.updation_by) {
                    $updationBy.text(stock.updation_by);
                    $updationAt.text(stock.updation_at);
                    $divUpdatedBy.show();
                }
                else {
                    $updationBy.text('');
                    $updationAt.text('');
                    $divUpdatedBy.hide();
                }

                let rows = '';
                stock.items.forEach((record, index) => {
                    rows += `
                        <tr>
                            <td class="text-center">${index + 1}</td>
                            <td class="text-left pl-2">${record.item_name}</td>
                            <td class="text-right pr-2">${parseFloat(record.quantity)}</td>
                            <td class="text-left pl-2">${record.unit.display_name}</td>
                            <td class="text-left pl-2">${record.batch_number || ''}</td>
                        </tr>`;
                });
                $tbody.html(rows);
            }

            function doAction() {
                $.ajax({
                    url: "{{ route('stocks.approval.update') }}",
                    method: "PUT",
                    data: {
                        number: $docNum.text(),
                        action: $(this).data('action'),
                    },
                    dataType: 'json',
                })
                .done(response => {
                    console.log("AJAX Success:", response);
                    if(response.success) {
                        Swal.fire('Success!', response.message, 'success')
                            .then(() => window.location.reload());
                    }
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
    <script src="{{ asset('plugins/sweet-alert2/sweetalert2.min.js') }}"></script>
    <script src="{{ asset('assets/pages/jquery.sweet-alert.init.js') }}"></script>
    <script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables/dataTables.bootstrap4.min.js') }}"></script>
@stop