@extends('app-layouts.admin-master')

@section('title', 'Opening Cash')

@section('headerStyle')
    <link href="{{ asset('plugins/sweet-alert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('plugins/animate/animate.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('assets/css/my-actxt.css') }}" rel="stylesheet" type="text/css">
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
                    @slot('title') Cash @endslot
                    @slot('item1') Masters @endslot
                    @slot('item2') Openings @endslot
                @endcomponent
            </div><!--end col-->
        </div>
        <!-- end page title end breadcrumb -->
  
        <div class="row"> 
            <div class="col-lg-5">
                <div class="card">
                    <div class="card-body">

                        <div class="row">
                            <div class="col-sm-12">
                                <div class="row">
                                    <div class="col-sm-11">
                                        <div class="form-group row">
                                            <label for="openingAmt" class="col-form-label ml-3 mr-4">Opening Amount</label>
                                            <input type="text" id="openingAmt" class="form-control text-center int-input " style="max-width:130px">
                                        </div>
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
                                        <div class="form-group row float-right">
                                            <input type="button" id="clear" class="btn btn-secondary mr-3" value="Clear" />
                                            <input type="button" id="submit" class="btn btn-primary mr-3" value="Submit" />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                    </div><!--end card-body--> 
                </div><!--end card--> 
            </div> <!--end col-->
        </div><!--end row-->   
    </div><!-- container -->
@stop

@push('custom-scripts')
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.js"></script>
    <script src="{{ asset('assets/js/input-restriction.js') }}"></script>
    <script src="{{ asset('assets/js/customer-selection.js') }}"></script>
    <script>
        $(document).ready(function() {
            // Setup CSRF token for AJAX requests
            $.ajaxSetup({
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
            });
            
            let note1;
            doInit();

            function doInit() {
                note1 = $('#denomTable tbody').find('input[type="text"][id^="note"]').first();
                $('#denomTable tbody').on('keypress', '[id^=note]', focusNextOnDenominationEnter);
                $('#denomTable tbody').on('change', '[id^=note]', updateDenomination);
                $('#clear').on('click', clearFields);
                $('#submit').on('click', submitForm);
                $(document).on('keypress', '.int-input', restrictToInteger);
            }

            // Functions
            function focusNextOnDenominationEnter(e) {
                if (e.which === 13) {
                    e.preventDefault();
                    const currentId = $(this).attr('id');
                    const currentRow = $(this).closest('tr');
                    const nextInput = currentRow.next().find(`input[id^=note]`).eq(0);
                    if (nextInput.length) nextInput.focus();
                    else $('#submit').focus();
                }
            }

            function updateDenomination() {
                const input = $(this);
                const inputValue = parseInt(input.val());
                const id = input.attr('id');
                const note = parseInt(id.replace('note', ''));
                const amount = !isNaN(inputValue) ? inputValue * note : "";
                $('#noteAmt' + note).text(amount);
                updateDenominationTotal();
            }

            function updateDenominationTotal() {
                let total = 0;
                $('#denomTable tbody [id^=noteAmt]').each(function() {
                    var amount = $(this).text();
                    if (amount) total += Number(amount);
                });
                $("#denomTotal").text(total || "");
            }

            function clearFields() {
                $('#openingAmt').val('');
                $('#denomTable tbody [id^=note]').val('');
                $('#denomTable tbody [id^=noteAmt]').text('');
                $('#denomTotal').text('');
            }

            function submitForm() {
                if (isValidated()) {
                    const amount = $("#openingAmt").val();
                    const denomination = getDenominationData();
                    console.log(denomination);

                    $.ajax({
                        url: "{{ route('openings.cash') }}",
                        type: "POST",
                        data: {
                            amount: amount,
                            denomination: denomination,
                        },
                        dataType: 'json',
                        success: function(data) {
                            Swal.fire('Success!', data.message, 'success')
                                .then(function() { window.location.replace("{{ route('home') }}"); });
                        },
                        error: function(data) {
                            console.log(data.responseText);
                            Swal.fire('Sorry!', data.responseText, 'error');
                        }
                    });
                }
            }
        
            function isValidated() {
                const amount = parseFloat($("#openingAmt").val()) || 0;
                const denomTotal = parseFloat($("#denomTotal").text()) || 0;
                
                if (amount == 0) {
                    Swal.fire('Sorry!', 'Please Enter Amount', 'warning');
                    return false;
                }
                else if (denomTotal == 0) {
                    Swal.fire('Sorry!', 'Please Enter Denomination', 'warning');
                    return false;
                }
                else if (denomTotal != amount) {
                    Swal.fire('Sorry!', 'Denomination Total Mismatch', 'warning');
                    return false;
                }
                
                return true;
            }

            function getDenominationData() {
                let data = new Map();
                $('#denomTable tbody tr').each(function () {
                    let $txtNote = $(this).find('td:nth-child(2) [id^=note]');
                    let note = $txtNote.attr('id').replace('note', ''); // Keep the key it as string
                    let value = parseInt($txtNote.val()); // Parse the value as an integer
                    if (!isNaN(value) && value !== 0) { // Ensure value is a valid number and not zero
                        data.set(note, value);
                    }
                });

                if (data.size === 0) return null;

                // Build JSON string manually in insertion order
                let json = '{' + Array.from(data).map(([k, v]) => `"${k}":${v}`).join(',') + '}';
                return json;
            }
        });
    </script>
@endpush

@section('footerScript')
    <script src="{{ asset('plugins/sweet-alert2/sweetalert2.min.js') }}"></script>
    <script src="{{ asset('assets/pages/jquery.sweet-alert.init.js') }}"></script>
@stop