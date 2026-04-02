@extends('app-layouts.admin-master')

@section('title', 'Cash Register')

@section('headerStyle')
    <link href="{{ asset('plugins/sweet-alert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('plugins/animate/animate.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('assets/css/my-style.css') }}" rel="stylesheet" type="text/css">
@stop

@section('content')
    <div class="container-fluid">
        <!-- Page-Title -->
        <div class="row">
            <div class="col-sm-12">
                @component('app-components.breadcrumb-2')
                    @slot('title') Cash Register @endslot
                    @slot('item1') Explorer @endslot                    
                @endcomponent
            </div><!--end col-->
        </div>
        <!-- end page title end breadcrumb -->

        <div class="row">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-6">
                                <div class="text-center mb-3">
                                    <button id="btn-prev" class="btn btn-info px-2" style="padding:3px" data-toggle="tooltip" data-placement="top" title="Left Arrow (<)"> < </button>
                                    <input type="date" id="date" value="{{ date('Y-m-d') }}" class="my-control text-center" min="2025-04-01" max="{{ date('Y-m-d') }}">
                                    <button id="btn-next" class="btn btn-info px-2" style="padding:3px" data-toggle="tooltip" data-placement="top" title="Right Arrow (>)"> > </button>
                                    <button id="btn-regenerate" class="btn btn-gradient-dark px-2 ml-3" style="padding:3px" data-toggle="tooltip" data-placement="top" title="Re-register"><i class="mdi mdi-refresh"></i></button>
                                </div>
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead class="thead-light">
                                            <tr>
                                                <th class="text-left pl-2">Particulars</th>
                                                <th class="text-right pr-2">Amount</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr id="row-opening" style="cursor: pointer;">
                                                <td class="text-left pl-2">Opening Balance</td>
                                                <td id="opening-amount" class="text-right pr-2 text-dark"></td>
                                            </tr>
                                            <tr id="row-receipt" style="cursor: pointer;">
                                                <td class="text-left pl-2">Receipt Amount</td>
                                                <td id="receipts-amount" class="text-right pr-2 text-dark"></td>
                                            </tr>
                                            <tr id="row-expense" style="cursor: pointer;">
                                                <td class="text-left pl-2">Expense Amount</td>
                                                <td id="expenses-amount"  class="text-right pr-2 text-dark"></td>
                                            </tr>
                                            <tr id="row-closing" class="border-bottom" style="cursor: pointer;">
                                                <td class="text-left pl-2">Closing Balance</td>
                                                <td id="closing-amount" class="text-right pr-2 text-dark"></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div class="col-6">
                                <h6 id="denom-title" class="my-heading text-center">Denomination</h6>
                                <div class="table-responsive">
                                    <table id="denomTable" class="table table-sm table-bordered nowrap text-right">
                                        <thead class="thead-light">
                                            <tr>
                                                <th colspan="3" class="text-center">Denomination</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($notes as $note)
                                                <tr>
                                                    <td width="70px" style="border-right-width:0px"> {{ $note }} &ensp; X </td>
                                                    <td width="70px" style="border-right-width:0px; border-left-width:0px"><span id="note{{$note}}"></span> &ensp; = </td>
                                                    <td width="70px" style="border-left-width:0px; padding-right:20px" id="note-amt-{{$note}}"></td>
                                                </tr>
                                            @endforeach
                                            <tr>
                                                <td width="70px" style="border-right-width:0px"> Coins </td>
                                                <td width="70px" style="border-right-width:0px; border-left-width:0px">&ensp; = </td>
                                                <td width="70px" style="border-left-width:0px; padding-right:20px" id="note-amt-1"></td>
                                            </tr>
                                        </tbody>
                                        <tfoot class="thead-light">
                                            <tr>
                                                <th colspan="2">Total</th>
                                                <th id="denom-total" style="padding-right:20px"></th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div><!--end card-body-->
                </div><!--end card-->
            </div><!--end col-->
        </div><!--end row-->
    </div>
@stop
 
@push('custom-scripts')
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.js"></script>
    <script src="{{ asset('assets/js/helper.js') }}"></script>
    <script>
        $(document).ready(function () {
            $.ajaxSetup({
                headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
            });

            let openingDenomination  = [];
            let receiptsDenomination = [];
            let expensesDenomination = [];
            let closingDenomination  = [];
            let denominationModel;
            let minDate, maxDate;
            doInit();

            function doInit() {
                minDate = new Date($('#date').attr('min'));
                maxDate = new Date($('#date').attr('max'));
                denominationModel = "Opening";
                $('#denom-title').text("Opening Balance");
                loadCashRegister();

                $('#date').change(loadCashRegister);
                $('#btn-prev').click(() => changeDateByDays(-1));
                $('#btn-next').click(() => changeDateByDays(1));
                $('#btn-regenerate').click(() => regenerateRecords());
            }

            $(document).on('keydown', ({ key }) => {
                if (key === 'ArrowLeft') changeDateByDays(-1);
                else if (key === 'ArrowRight') changeDateByDays(1);
            });

            $('#row-opening').on("click", function () {
                $('#denom-title').text("Opening Balance");
                denominationModel = "Opening";
                loadDenomination();
            });

            $('#row-receipt').on("click", function () {
                $('#denom-title').text("Receipt Amount");
                denominationModel = "Receipt";
                loadDenomination();
            });

            $('#row-expense').on("click", function () {
                $('#denom-title').text("Expense Amount");
                denominationModel = "Expense";
                loadDenomination();
            });

            $('#row-closing').on("click", function () {
                $('#denom-title').text("Closing Balance");
                denominationModel = "Closing";
                loadDenomination();
            });

            function changeDateByDays(offset) {
                let currentDate = $('#date').val();
                let date = currentDate ? new Date(currentDate) : new Date();
                date.setDate(date.getDate() + offset);
                if(date < minDate || date > maxDate)
                    return;
                let formatted = date.toISOString().split('T')[0];
                $('#date').val(formatted);
                loadCashRegister();
            }

            function getDenomination() {
                if(denominationModel == "Opening") return openingDenomination;
                else if(denominationModel == "Receipt") return receiptsDenomination;
                else if(denominationModel == "Expense") return expensesDenomination;
                else return closingDenomination;
            }

            function loadDenomination() {
                $("[id^='note']").text('');
                $("[id^='note-amt']").text('');
                $('#denom-total').text('');
                let total = 0;
                let denomination = getDenomination();
                for(let note in denomination) {
                    let count = denomination[note];
                    let amount = note * count;
                    $(`#note${note}`).text(count);
                    $(`#note-amt-${note}`).text(formatIndianNumber(amount));
                    total += amount;
                }
                $('#denom-total').text(total > 0 ? formatIndianNumber(total) : '');
            }

            function loadCashRegister() {
                let date = $('#date').val();
                let url = "{{ route('cash.register.get') }}?date=" + date;
                clearData();
                $.get(url, function (response) {                    
                    let openingAmount  = formatIndianNumber(response.opening.amount);
                    let receiptsAmount = formatIndianNumber(response.receipts.amount);
                    let expensesAmount = formatIndianNumber(response.expenses.amount);
                    let closingAmount  = formatIndianNumber(response.closing.amount);
                    openingDenomination  = response.opening.denomination;
                    receiptsDenomination = response.receipts.denomination;
                    expensesDenomination = response.expenses.denomination;
                    closingDenomination  = response.closing.denomination;
                    $('#opening-amount').text(openingAmount);
                    $('#receipts-amount').text(receiptsAmount);
                    $('#expenses-amount').text(expensesAmount);
                    $('#closing-amount').text(closingAmount); 
                    loadDenomination();
                });
            }

            function regenerateRecords() {
                $.get("{{ route('cash.register.re-register') }}")
                    .done(function(data) {
                        console.log(data);
                        if (data.success) {
                            Swal.fire('Success!', data.message, 'success')
                                .then(() => { $('#date').trigger('change'); });
                        } 
                        else {
                            Swal.fire('Sorry!', data.message, 'warning');
                        }
                    })
                    .fail(function(xhr) {
                        Swal.fire('Sorry!', xhr.responseText, 'error');
                    });
            }

            function clearData() {
                $('#opening-amount').text('');
                $('#receipts-amount').text('');
                $('#expenses-amount').text('');
                $('#closing-amount').text('');
                $("[id^='note']").text('');
                $("[id^='note-amt']").text('');
                $('#denom-total').text('');
                openingDenomination  = [];
                receiptsDenomination = [];
                expensesDenomination = [];
                closingDenomination  = [];
            }
        });
    </script>
@endpush

@section('footerScript')
    <script src="{{ asset('plugins/sweet-alert2/sweetalert2.min.js') }}"></script>
    <script src="{{ asset('assets/pages/jquery.sweet-alert.init.js') }}"></script>
@stop