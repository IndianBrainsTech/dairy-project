@extends('app-layouts.admin-master')

@section('title', 'Expense Payment')

@section('headerStyle')
    <link href="{{ asset('plugins/sweet-alert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('plugins/animate/animate.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('plugins/datatables/dataTables.bootstrap4.min.css') }}" rel="stylesheet" type="text/css" />
    <style type="text/css">
        .my-text {
            font-size: 14px;
            padding: 4px;
        }
        .my-control {
            border: 1px solid #e8ebf3; 
            padding:6px;
            border-radius: 0.25rem;
            border-bottom: 1px solid #e8ebf3;
            transition: border-color 0s ease-out;
            background-color: #fff;
            margin-right:20px;
        }
        .mdi-cash {
            line-height: 1; /* Removes extra space */
            vertical-align: middle; /* Aligns the icon vertically with the text */
            font-size: 16px; /* Adjust the size if needed */
        }
    </style>
@stop

@section('content')
    <div class="container-fluid">
        <!-- Page-Title -->
        <div class="row">
            <div class="col-sm-12">
                @component('app-components.breadcrumb-3')
                    @slot('title') Expense Payment @endslot
                    @slot('item1') Transactions @endslot
                    @slot('item2') Expenses @endslot
                @endcomponent
            </div><!--end col-->
        </div>
        <!-- end page title end breadcrumb -->    
  
        <div class="row">             
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-body">
                        <div>
                            <button type="button" id="btnDenomination" class="btn btn-primary waves-effect waves-light btn-sm mb-3 px-3">
                                <i class="mdi mdi-group mr-2"></i>Denom
                            </button>
                        </div>         
                            <div class="table-responsive dash-social">
                                <table id="datatable" class="table table-bordered table-sm text-center">
                                    <thead class="thead-light">
                                        <tr>
                                            <th class="align-middle">S.No</th>
                                            <th class="align-middle">Date</th>
                                            <th class="align-middle">Expense Name</th>
                                            <th class="align-middle">Amount</th>                                            
                                            <th class="align-middle">Denomination</th>                                           
                                        </tr>                                       
                                    </thead>
                                    <tbody>
                                        @foreach($expenses as $index => $expense)
                                        <tr>                                            
                                            <td class="text-center">{{ $loop->index + 1 }}</td>
                                            <td class="text-center">{{ getIndiaDate($expense->expense_date) }}</td>                                            
                                            <td class="text-left">{{ $expense->expense_name}}</td>
                                            <td class="text-center">{{ $expense->expense_amount}}</td>
                                            <td class="text-center">
                                                <span class="p-1 badge badge-soft-{{ $expense->denomination ? 'success' : 'danger' }}">
                                                    {{ $expense->denomination ? 'Received' : 'Not Received' }}
                                                </span>
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
    </div><!-- container -->

    <!-- Start of Denomination Modal -->
    <div class="modal fade" id="modalDenomination" tabindex="-1" role="dialog" aria-labelledby="modalDenominationLabel" aria-hidden="true">
        <div class="modal-dialog" role="document" style="max-width:750px">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title mt-0" id="modalTitle">Denomination</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="formDenomination">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-12">
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
                                                        <th>Expense Name</th>
                                                        <th>Amount</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                </tbody>
                                                <tfoot class="thead-light">
                                                    <tr>
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
                                                            <td width="70px" style="border-right-width:0px"> {{ $note->note_value }} &ensp; X </td>
                                                            <td width="90px" style="border-right-width:0px; border-left-width:0px"> <input type="text" id="note{{$note->note_value}}" class="my-control text-center int-input mr-0" style="width:70px"> &ensp; = </td>
                                                            <td width="70px" style="border-left-width:0px; padding-right:20px" id="noteAmt{{$note->note_value}}"></td>
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
            $.ajaxSetup({
                headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
            });    
            initializeEventHandlers();       
            function initializeEventHandlers() {  
                $('#btnDenomination').click(loadDenominationData);
                $('#chkSelectAll').change(toggleSelectAll);
                $('#selectionTable tbody').on('change', 'input[type="checkbox"]', updateSelectionTotal);
                $('#denomTable tbody').on('keypress', '.int-input', restrictToNumbers);
                $('#denomTable tbody').on('keypress', '[id^=note]', handleDenominationKeypress);
                $('#denomTable tbody').on('change', '[id^=note]', handleDenominationChange);
                $('#denomSubmit').click(submitDenominationData);
            }            

            // Loads denomination data and displays the modal
            function loadDenominationData() {                
                $.ajax({
                    url: "{{ route('expense.non.denominatiion') }}", 
                    type: 'GET', 
                    dataType: 'json', 
                    success: function(data) {
                        console.log(data);
                        if (data.length === 0) {
                            Swal.fire('Info!', 'No data found for denomination', 'info');
                            return;
                        }
                        clearDenominationModalFields();
                        data.forEach(function(item) {
                            const newRow = $("<tr>")
                                .append(`<td class="text-right">
                                            <div class="checkbox checkbox-primary checkbox-single">
                                                <input type="checkbox" id="rcpt${item.id}">
                                                <label style="margin-bottom:0px"></label>
                                            </div>
                                        </td>`)
                                .append(`<td class='text-left'>${item.expense_name}</td>`)
                                .append(`<td>${item.expense_amount}</td>`)
                            $("#selectionTable tbody").append(newRow);
                        });

                        $('#modalDenomination').modal('show');
                    },
                    error: function(xhr, status, error) {
                        console.error('Error:', error);
                        Swal.fire('Error!', 'Failed to fetch data', 'error');
                    }
                });
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
                    let amount = parseFloat($(this).closest('tr').find('td:eq(2)').text());
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
            function handleDenominationKeypress(e) {
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
                    const receiptIds = getReceiptIds();                  
                    const denomination = getDenominationData();
                    
                    $.ajax({
                        url: "{{ route('expense.store-denomination') }}",
                        type: "POST",
                        data: {              
                            expense_ids: receiptIds,             
                            amount: denomTotal,
                            denomination: denomination
                        },
                        dataType: 'json',
                        success: function(data) {
                            console.log(data);
                            Swal.fire('Success!', data.message, 'success').then(function() {
                                $('#modalDenomination').modal('hide');
                                window.location.reload();
                            });
                        },
                        error: function(data) {
                            Swal.fire('Sorry!', data.responseText, 'error');
                        }
                    });
                }
            }       
            
            // Gets selected receipt IDs
            function getReceiptIds() {
                var receiptIds = [];
                $('#selectionTable tbody input[type="checkbox"]:checked').each(function() {
                    var rcptId = $(this).attr('id').replace('rcpt', '');
                    receiptIds.push(rcptId);
                });
                return JSON.stringify(receiptIds);
            }

            // Gets entered denomination data
            function getDenominationData() {
                let data = [];
                $('#denomTable tbody tr').each(function() {
                    let $txtNote = $(this).find('td:nth-child(2) [id^=note]');
                    let note = parseInt($txtNote.attr('id').replace('note', ''));
                    let value = $txtNote.val();
                    if (value && parseInt(value) !== 0) {
                        data.push({ [note]: value });
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