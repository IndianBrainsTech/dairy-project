@extends('app-layouts.admin-master')

@section('title', 'Batch Denomination')

@section('headerStyle')
    <link href="{{ asset('plugins/sweet-alert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('plugins/animate/animate.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('plugins/datatables/dataTables.bootstrap4.min.css') }}" rel="stylesheet" type="text/css" />    
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
    </style>
@stop

@section('content')
    <div class="container-fluid">
        <!-- Page-Title -->
        <div class="row">
            <div class="col-sm-12">
                @component('app-components.breadcrumb-3')
                    @slot('title') Batch Denomination @endslot
                    @slot('item1') Transactions @endslot
                    @slot('item2') Receipts @endslot
                @endcomponent
            </div><!--end col-->
        </div>
        <!-- end page title end breadcrumb -->        
  
        <div class="row"> 
            <div class="col-lg-10">
                <div class="card">
                    <div class="card-body">
 
                        <!-- Nav tabs --> 
                        <ul class="nav nav-tabs" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active px-3 py-2" id="tab_pending" data-toggle="tab" href="#pending-tab" role="tab">Pending Denominations</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link px-3 py-2" id="tab_batch" data-toggle="tab" href="#batch-tab" role="tab">Batch Denominations</a>
                            </li>
                        </ul>
 
                        <!-- Tab panes -->
                        <div class="tab-content">
                            <div class="tab-pane active p-3" id="pending-tab" role="tabpanel">
                                <div class="row mt-2">
                                    <div class="table-responsive dash-social"> 
                                        <table id="datatable" class="table table-sm table-bordered dt-responsive nowrap" style="border-collapse: collapse; border-spacing: 0; width: 100%">
                                            <thead class="thead-light">
                                                <tr class="text-center">
                                                    <th>S.No</th>
                                                    <th>Receipt No.</th>
                                                    <th class="text-left pl-2">Route</th>
                                                    <th class="text-left pl-2">Customer</th>
                                                    <th class="text-right pr-2">Amount</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($receipts as $receipt)
                                                    <tr class="text-center">
                                                        <td>{{ $loop->index + 1 }}</td>
                                                        <td>{{ $receipt->receipt_num }}</td>
                                                        <td class="text-left pl-2">{{ $receipt->route->name }}</td>
                                                        <td class="text-left pl-2">{{ $receipt->customer_name }}</td>
                                                        <td class="text-right pr-2">{{ $receipt->amount }}</td>
                                                        <td><a href="#" class="batch" data-id="{{$receipt->route->id}}" data-name="{{$receipt->route->name}}"><i class="mdi mdi-view-list text-primary font-20"></i></a></td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <div class="tab-pane p-3" id="batch-tab" role="tabpanel">
                                <div class="row mt-2">
                                    <div class="table-responsive dash-social">
                                        <table id="datatable" class="table table-bordered table-sm text-center">
                                            <thead class="thead-light">
                                                <tr>
                                                    <th>S.No</th>
                                                    <th class="text-left pl-3">Route</th>
                                                    <th>No of Receipts</th>
                                                    <th class="text-right pr-3">Amount</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($denominations as $record)
                                                    <tr>
                                                        <td>{{ $loop->index + 1 }}</td>
                                                        <td class="text-left pl-3">{{ $record->route->name }}</td>
                                                        <td>{{ $record->receipt_count }}</td>
                                                        <td class="text-right pr-3">{{ $record->amount }}</td>
                                                        <td><a href="#" class="show" data-id="{{$record->id}}"><i class="dripicons-preview text-primary font-16"></i></a></td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div><!--end card-body--> 
                </div><!--end card--> 
            </div> <!--end col-->
        </div><!--end row-->   
    </div><!-- container -->

    <!-- Start of Denomination Modal -->
    <div class="modal fade" id="modalDenomination" tabindex="-1" role="dialog" aria-labelledby="modalDenominationLabel" aria-hidden="true">
        <div class="modal-dialog" role="document" style="max-width:860px">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title mt-0" id="modalTitle">Batch Denomination</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="formDenomination">
                    <input type="hidden" id="routeId">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-12">
                                <h5 class="mt-0 mb-3">Route : <span id="routeName" style="color:darkBlue"></span></h5>
                                <div class="row">
                                    <div class="col-sm-7">
                                        <div class="table-responsive">
                                            <table id="selectionTable" class="table table-sm table-bordered nowrap text-center" style="overflow-y:auto; width:100%">
                                                <thead class="thead-light">
                                                    <tr>
                                                        <th class="text-right">
                                                            <div class="checkbox checkbox-primary checkbox-single">
                                                                <input type="checkbox" id="chkSelectAll">
                                                                <label style="margin-bottom:0px"></label>
                                                            </div>
                                                        </th>
                                                        <th>Receipt</th>
                                                        <th>Customer</th>
                                                        <th>Amount</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                </tbody>
                                                <tfoot class="thead-light">
                                                    <tr>
                                                        <th></th>
                                                        <th></th>
                                                        <th class='d-none'></th>
                                                        <th>Total</th>
                                                        <th id="selectionTotal"></th>
                                                    </tr>
                                                </tfoot>
                                            </table>
                                        </div>
                                    </div>
                                    <div class="col-sm-5">
                                        <div class="table-responsive">
                                            <table id="denomTable" class="table table-sm table-bordered nowrap text-right" >
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
                                </div>
                            </div>
                        </div>   
                    </div>
                    <div class="modal-footer">
                        <input type="button" class="btn btn-secondary mr-3" data-dismiss="modal" value="Close" />
                        <input type="button" class="btn btn-primary" id="denomSubmit" value="Submit"/>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- End of Denomination Modal -->
@stop

@push('custom-scripts')
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.js"></script>
    <script>
        $(document).ready(function() {
            // Set up AJAX to include CSRF token in the header
            $.ajaxSetup({
                headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
            });

            doInit();

            function doInit() {
                $('#datatable').dataTable( {
                    "lengthMenu": [[10, 25, 50, 100], [10, 25, 50, 100]],
                    "pageLength": 25,
                } );

                // Events
                $('body').on('click', '.batch', loadReceiptData);
                $('#chkSelectAll').on('change', toggleSelectAll);
                $('#selectionTable tbody').on('change', 'input[type="checkbox"]', updateSelectionTotal);
                $('#denomTable tbody').on('keypress', '.int-input', restrictToNumbers);
                $('#denomTable tbody').on('keypress', '[id^=note]', focusNextOnKeyEnter);
                $('#denomTable tbody').on('change', '[id^=note]', handleDenominationChange);
                $('#denomSubmit').on('click', submitDenominationData);
            }

            // Loads denomination data and displays the modal
            function loadReceiptData() {
                var id = $(this).data('id');
                var name = $(this).data('name');
                $("#routeId").val(id);
                $("#routeName").text(name);
                let url = "{{ route('receipts.batch-denomination.get', ':id') }}".replace(':id', id);
                $.get(url, function(data) {
                    if(data.length == 0) {
                        Swal.fire('Info!', 'No data found for denomination', 'info');
                        return;
                    }

                    clearDenominationModalFields();

                    data.forEach(function(item) {
                        const newRow = $("<tr>")
                            .append(`<td class="text-right">
                                        <div class="checkbox checkbox-primary checkbox-single">
                                            <input type="checkbox">
                                            <label style="margin-bottom:0px"></label>
                                        </div>
                                    </td>`)
                            .append(`<td>${item.receipt_num}</td>`)
                            .append(`<td class='text-left'>${item.customer_name}</td>`)
                            .append(`<td>${item.amount}</td>`)
                        $("#selectionTable tbody").append(newRow);
                    });

                    $('#modalDenomination').modal('show');
                });
            }

            // Toggles selection of all checkboxes in the selectionTable
            function toggleSelectAll() {
                var isChecked = $(this).is(':checked'); // Get the checked state of the "Select All" checkbox
                $('#selectionTable tbody input[type="checkbox"]').prop('checked', isChecked).trigger('change'); // Set the checked state of all checkboxes in the table body
                updateSelectionTotal(); // Update total amount
            }

            // Updates the total selected amount
            function updateSelectionTotal() {
                let total = 0;
                $('#selectionTable tbody input[type="checkbox"]:checked').each(function() {
                    let amount = parseFloat($(this).closest('tr').find('td:eq(3)').text());
                    total += amount;
                });            
                $('#selectionTotal').text(total || "");
            }

            // Restricts input to numbers only
            function restrictToNumbers(e) {
                const key = String.fromCharCode(e.keyCode);
                if (key.match(/[^0-9]/g)) return false;
                return true;
            }

            // Handles 'enter' on denomination inputs
            function focusNextOnKeyEnter(e) {
                if (e.which === 13) { // Enter key
                    e.preventDefault(); // Prevent form submission if inside a form
                    const currentId = $(this).attr('id');
                    const currentRow = $(this).closest('tr');
                    const nextInput = currentRow.next().find(`input[id^=note]`).eq(0);

                    if (nextInput.length) {
                        nextInput.focus();
                    } else {
                        $('#denomSubmit').focus();
                    }
                }
            }

            // Handles change in denomination inputs
            function handleDenominationChange() {
                const $input = $(this);
                const inputValue = parseInt($input.val());
                const id = $input.attr('id');
                const note = parseInt(id.replace('note', ''));
                const amount = !isNaN(inputValue) ? inputValue * note : "";
                $(`#noteAmt${note}`).text(amount);
                updateDenominationTotal();
            }

            // Submits denomination data via AJAX
            function submitDenominationData() {
                const selectionTotal = $("#selectionTotal").text();
                const denomTotal = $("#denomTotal").text();

                if (!selectionTotal) {
                    Swal.fire('Sorry!', 'Please Select Customer / Amount', 'warning');
                }
                else if (!denomTotal) {
                    Swal.fire('Sorry!', 'Please Enter Denomination', 'warning');
                }
                else if (denomTotal != selectionTotal) {
                    Swal.fire('Sorry!', 'Total Mismatch!', 'warning');
                }
                else {
                    const routeId        = $("#routeId").val();
                    const receiptNumbers = getReceiptNumbers();
                    const denomination   = getDenominationData();
                    
                    $.ajax({
                        url: "{{ route('receipts.batch-denomination.store') }}",
                        type: "POST",
                        data: {
                            route_id        : routeId,
                            receipt_numbers : receiptNumbers,
                            amount          : denomTotal,
                            denomination    : denomination
                        },
                        dataType: 'json',
                        success: function(data) {
                            Swal.fire('Success!', data.message, 'success')
                                .then(function() { window.location.reload(true);} );
                        },
                        error: function(data) {
                            Swal.fire('Sorry!', data.responseText, 'error');
                        }
                    });
                }
            }

            // Clears fields in the denomination modal
            function clearDenominationModalFields() {
                $('#chkSelectAll').prop('checked', false);
                $('#selectionTable tbody').empty();
                $('#selectionTotal').text('');
                $('#denomTable tbody [id^=note]').val('');
                $('#denomTable tbody [id^=noteAmt]').text('');
                $('#denomTotal').text('');
            }

            // Updates the total denomination amount
            function updateDenominationTotal() {
                let total = 0;
                $('#denomTable tbody [id^=noteAmt]').each(function() {
                    const value = parseInt($(this).text());
                    if (!isNaN(value)) {
                        total += value;
                    }
                });
                $('#denomTotal').text(total || "");
            }

            // Gets selected receipt numbers
            function getReceiptNumbers() {
                var selectedReceipts = [];
                $('#selectionTable tbody input[type="checkbox"]:checked').each(function() {
                    const receiptNum = $(this).closest('tr').find('td:eq(1)').text();
                    selectedReceipts.push(receiptNum);
                });
                return JSON.stringify(selectedReceipts);
            }

            // Gets entered denomination data
            function getDenominationData() {
                let data = [];
                $('#denomTable tbody tr').each(function() {
                    let $txtNote = $(this).find('td:nth-child(2) [id^=note]');
                    let note = parseInt($txtNote.attr('id').replace('note', ''));
                    let value = $txtNote.val();
                    if (value && parseInt(value) !== 0) {
                        data.push({ [note]: parseInt(value) });
                    }
                });
                return JSON.stringify(data);
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