@extends('app-layouts.admin-master')

@section('title', 'Create Bank Payout')

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
                    @slot('title') Create Bank Payout @endslot
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

                        <div class="row">
                            <form method="post" action="{{route('incentives.payouts.bank.create')}}">
                                @csrf
                                <label for="from-date" class="app-text ml-2">From</label>
                                <input type="date" name="from_date" id="from-date" value="{{ $dates['from'] }}" class="app-control mr-2">
                                <label for="to-date" class="app-text">To</label>
                                <input type="date" name="to_date" id="to-date" value="{{ $dates['to'] }}" class="app-control mr-2">
                                <input type="submit" value="Load" class="btn btn-gradient-primary py-1 px-3 mx-3" aria-label="Load" title="Load"/>                                
                            </form>
                        </div>
                        <hr/>

                        <div class="table-responsive dash-social">
                            <table id="datatable" class="table table-bordered table-sm dt-responsive nowrap text-center">
                                <thead class="thead-light">
                                    <tr class="text-center">
                                        <th>S.No</th>
                                        <th>Date</th>
                                        <th>Number</th>
                                        <th>Period</th>
                                        <th>Inc Amt</th>
                                        <th>Customer</th>
                                        <th>Payable</th>
                                        <th></th>
                                        <th>Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($incentives as $record)
                                        <tr>
                                            <td>{{ $loop->index + 1 }}</td>
                                            <td>{{ $record['incentive_date'] }}</td>
                                            <td>{{ $record['incentive_number'] }}</td>
                                            <td>
                                                {!! Str::contains($record['period'], ' to ')
                                                    ? str_replace(' to ', ' to<br/>', $record['period'])
                                                    : e($record['period'])
                                                !!}
                                            </td>
                                            <td class="text-right pr-2">{{ $record['net_amount'] }}</td>
                                            <td class="text-left pl-2">{{ $record['customer_name'] }}</td>
                                            <td class="text-right pr-2">{{ $record['payable'] }}</td>
                                            <td>
                                                <div class="checkbox checkbox-primary checkbox-single pt-1">
                                                    <input type="checkbox" id="chk{{$record['incentive_number']}}">
                                                    <label style="margin-bottom:0px"></label>
                                                </div>
                                            </td>
                                            <td>
                                                <input type="text" id="txt-amt-{{$record['incentive_number']}}" class="app-control app-focus text-center amt-input" data-payable="{{ $record['payable'] }}" style="max-width:90px">
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
    <script src="{{ asset('assets/js/helper.js')}}"></script>
    <script src="{{ asset('assets/js/input-restriction.js')}}"></script>
    <script>
        $(document).ready(function () {
            $.ajaxSetup({
                headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
            });

            doInit();

            function doInit() {
                $('a[href="#MenuTransactions"]').click();
                // $('body').toggleClass('enlarge-menu');

                $('#datatable').DataTable({
                    paging: false,   // Disable pagination (show all rows)
                    info: false,     // (Optional) Hide "Showing X of Y entries"
                    searching: true, // (Optional) Keep search if needed
                    dom: 'ft',       // Only show Filter (search box) and Table
                });

                $('#from-date').change(function() {
                    let date = $(this).val();
                    $('#to-date').attr('min',date);
                });

                $("#from-date").trigger('change');

                $('input[type="checkbox"][id^="chk"]').on('change', handleCheckBoxChange);                
                $('#btn-clear').on('click', clearForm);
                $('#btn-submit').on('click', submitForm);

                $(document)
                    .on('keypress', '.amt-input', restrictToInteger)
                    .on('change', '.amt-input', validateAndUpdate);
            }

            function validateAndUpdate() {
                let input = $(this);
                let amount = parseFloat(input.val());
                const incentiveNum = input.attr('id').replace('txt-amt-', '');
                const checkbox = $('#chk' + incentiveNum);

                // If amount is not valid or zero
                if (isNaN(amount) || amount === 0) {
                    checkbox.prop('checked', false);
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

                // Mark checkbox as checked
                checkbox.prop('checked', true);

                focusNextInput(input);
            }

            function handleCheckBoxChange() {
                let incentiveNum = $(this).attr('id').replace('chk', '');
                let $input = $('#txt-amt-' + incentiveNum);

                if ($(this).is(':checked')) {
                    let payable = $input.data('payable');
                    $input.val(payable);
                }
                else {
                    $input.val('');
                }

                updateTotals();
            }

            function clearForm() {
                $('input[type="checkbox"][id^="chk"]').prop('checked', false);
                $('input[type="text"][id^="txt-amt-"]').val('');
                $('#spn-total').text('0');
            }

            function submitForm() {
                let incentiveData = getIncentiveData();
                if (incentiveData.length === 0) {
                    Swal.fire('Sorry!', 'No amount is entered' , 'warning');
                    return;
                }

                $('#btn-submit').prop('disabled', true);

                $.ajax({
                    url: "{{ route('incentives.payouts.bank.store') }}",
                    type: "POST",
                    data: {'incentive_data' : incentiveData},
                    dataType: 'json',
                    success: function(response) {
                        Swal.fire('Success', response.message, 'success')
                            .then(() => window.location.reload());
                    },
                    error: function(xhr, status, error) {
                        let response = JSON.parse(xhr.responseText);
                        Swal.fire('Sorry!', response.message, 'error');
                        $('#btn-submit').prop('disabled', false);
                    }
                });
            }

            function getIncentiveData() {
                let data = [];
                $('.amt-input').each(function () {
                    let amount = parseFloat($(this).val());
                    if(amount) {
                        let number = $(this).attr('id').replace('txt-amt-', '');
                        data.push({
                            number : number,
                            amount : amount,
                        });
                    }
                });
                return data;
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

            function focusNextInput(input) {
                let inputs = $('.amt-input'); // all text inputs
                let index = inputs.index(input); // current input index
                if (index !== -1 && index + 1 < inputs.length) {
                    inputs.eq(index + 1).focus(); // focus next
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