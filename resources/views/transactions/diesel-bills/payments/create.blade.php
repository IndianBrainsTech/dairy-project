@extends('app-layouts.admin-master')

@section('title', 'Create Payment Request')

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
                @component('app-components.breadcrumb-4')
                    @slot('title') Create Payment Request @endslot
                    @slot('item1') Transactions @endslot
                    @slot('item2') Diesel Bills @endslot
                    @slot('item3') Payment @endslot
                @endcomponent
            </div><!--end col-->
        </div>
        <!-- end page title end breadcrumb -->

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">

                        <div class="row">
                            <form method="get" action="{{ route('diesel-bills.payments.request.create') }}">
                                <label for="dt-from" class="app-text ml-2">From</label>
                                <input type="date" name="from_date" id="dt-from" value="{{ $dates['from'] }}" class="app-control mr-2">
                                <label for="dt-to" class="app-text">To</label>
                                <input type="date" name="to_date" id="dt-to" value="{{ $dates['to'] }}" class="app-control mr-2">
                                <input type="submit" value="Load" class="btn btn-gradient-primary py-1 px-3 mx-3" aria-label="Load" title="Load"/>
                            </form>
                        </div>
                        <hr/>

                        <div class="table-responsive dash-social">
                            <table id="datatable" class="table table-bordered table-sm dt-responsive nowrap">
                                <thead class="thead-light">
                                    <tr class="text-center">
                                        <th>S.No</th>
                                        <th>Date</th>
                                        <th>Document</th>
                                        <th>Period</th>
                                        <th>Amount</th>
                                        <th>Petrol Bunk</th>
                                        <th>Payable</th>
                                        <th></th>
                                        <th>Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($records as $record)
                                        <tr>
                                            <td class="text-center">{{ $loop->iteration }}</td>
                                            <td class="text-center">{{ $record->document_date }}</td>
                                            <td class="text-center">{{ $record->document_number }}</td>
                                            <td class="text-center">{{ $record->period }}</td>
                                            <td class="text-right pr-2">{{ $record->net_amount }}</td>
                                            <td class="text-left pl-2">{{ $record->bunk_name }}</td>
                                            <td class="text-right pr-2">{{ $record->payable }}</td>
                                            <td>
                                                <div class="checkbox checkbox-primary checkbox-single pt-1">
                                                    <input type="checkbox" id="chk-{{ $record->id }}" class="chk-select" data-id="{{ $record->id }}">
                                                    <label style="margin-bottom:0px"></label>
                                                </div>
                                            </td>
                                            <td>
                                                <input type="text" id="txt-{{ $record->id }}" class="app-control app-focus text-center amt-input" 
                                                    data-id="{{ $record->id }}" data-payable="{{ $record->payable }}" 
                                                    style="max-width:90px">
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="row mt-3">
                            <div class="col-sm-12">
                                <div class="form-group row float-right mr-2">
                                    <span class="bg-soft-pink rounded mr-4 p-2">Total: <b><span id="spn-total"></span></b></span>
                                    <button id="btn-clear" type="button" class="btn btn-secondary btn-sm px-3 mx-2" aria-label="Clear" title="Clear">Clear</button>
                                    <button id="btn-submit" type="button" class="btn btn-primary btn-sm px-3 mx-2" aria-label="Submit" title="Submit">Submit</button>
                                </div>
                            </div>
                        </div>

                    </div><!--end card-body-->
                </div><!--end card-->
            </div> <!--end col-->
        </div><!--end row-->
    </div>
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

            const $chkSelects = $('.chk-select');
            const $txtAmounts = $('.amt-input');
            const $spnTotal   = $('#spn-total');
            const $btnSubmit  = $('#btn-submit');

            doInit();

            function doInit() {
                $('a[href="#MenuTransactions"]').click();
                // $('body').toggleClass('enlarge-menu');

                $('#datatable').DataTable({
                    paging: false,
                    info: false,
                    searching: true,
                    dom: 'ft',
                });

                $('#dt-from').on('change', function () {
                    $('#dt-to').attr('min', this.value);
                }).trigger('change');

                restrictToNumbers('.amt-input');

                $(document).on('change', '.amt-input', validateAndUpdate);
                $chkSelects.on('change', handleCheckBoxChange);
                $btnSubmit.on('click', submitForm);

                $('#btn-clear').on('click', function() {
                    $chkSelects.prop('checked', false);
                    $txtAmounts.val('');
                    $spnTotal.text('0');
                });
            }

            function handleCheckBoxChange() {
                const id = $(this).data('id');
                const $input = $(`#txt-${id}`);

                if ($(this).is(':checked')) {
                    const payable = $input.data('payable');
                    $input.val(payable);
                }
                else {
                    $input.val('');
                }

                updateTotals();
            }

            function validateAndUpdate() {
                let $input = $(this);
                let amount = parseInt($input.val());
                const id = $input.data('id');
                const $checkbox = $(`#chk-${id}`);

                // If amount is not valid or zero
                if (isNaN(amount) || amount === 0) {
                    $checkbox.prop('checked', false);
                    $input.val(''); // clear the input
                    updateTotals();
                    return;
                }

                // Cap amount to maximum payable if necessary
                let maxPayable = parseInt($input.data('payable'));
                if (amount > maxPayable) {
                    amount = maxPayable;
                    $input.val(amount);
                }

                updateTotals();

                // Mark checkbox as checked
                $checkbox.prop('checked', true);

                focusNextInput($input);
            }

            function submitForm() {
                const paymentData = getPaymentData();
                if (paymentData.length === 0) {
                    Swal.fire('Sorry!', 'No amount is entered' , 'warning');
                    return;
                }

                $btnSubmit.prop('disabled', true);

                $.ajax({
                    url: "{{ route('diesel-bills.payments.request.store') }}",
                    type: 'POST',
                    data: {'payment_data' : paymentData},
                    dataType: 'json'
                })
                .done(response => {
                    console.log("AJAX Success:", response);
                    Swal.fire('Success', response.message, 'success')
                        .then(() => window.location.reload());
                })
                .fail((xhr, status, error) => {
                    handleAjaxError(xhr, status, error);
                })
                .always(() => {
                    $btnSubmit.prop('disabled', false);
                });
            }

            function getPaymentData() {
                let data = [];
                $txtAmounts.each(function () {
                    const amount = parseInt($(this).val());
                    if(amount) {
                        const id = $(this).data('id');
                        data.push({
                            id     : id,
                            amount : amount,
                        });
                    }
                });
                return data;
            }

            function updateTotals() {
                let total = 0;

                $txtAmounts.each(function () {
                    let amount = parseInt($(this).val());
                    if(amount)
                        total += amount;
                });

                $spnTotal.text(formatToIndianNumberFormat(total));
            }

            function focusNextInput(input) {
                let index = $txtAmounts.index(input); // current input index
                if (index !== -1 && index + 1 < $txtAmounts.length) {
                    $txtAmounts.eq(index + 1).focus(); // focus next
                }
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