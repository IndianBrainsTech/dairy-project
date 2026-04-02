@extends('app-layouts.admin-master')

@section('title', 'Create Incentive')

@section('headerStyle')
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
                    @slot('title') Create Incentive @endslot
                    @slot('item1') Transactions @endslot
                    @slot('item2') Incentives @endslot
                @endcomponent
            </div>
        </div>
        <!-- end page title end breadcrumb -->

        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">

                        <div class="row">
                            <div class="input-group mx-2" style="width:350px">
                                <span class="input-group-prepend">
                                    <button type="button" class="btn btn-info"><i class="fas fa-search"></i></button>
                                </span>
                                <input type="text" name="customer_name" id="act-customer-name" class="form-control" placeholder="Customer">
                                <input type="hidden" name="customer_id" id="hdn-customer-id">
                            </div>
                            <input type="date" name="from_date" id="dt-from" class="app-control mr-2" readonly>
                            <input type="date" name="to_date" id="dt-to" class="app-control mr-3">
                            <button id="btn-view" type="button" class="btn btn-dark btn-sm px-3 mx-2" aria-label="View" title="View">View</button>
                            <button id="btn-submit" type="button" class="btn btn-primary btn-sm px-3 mx-2" aria-label="Submit" title="Submit">Submit</button>
                        </div>
                        <hr/>

                        <div id="div-incentive" class="table-responsive dash-social px-2">
                            <h2 id="hdg-customer" class="app-h2"></h2>
                            <h3 id="hdg-duration" class="app-h3 pb-2"></h3>
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
                    </div><!--end card-body-->
                </div><!--end card-->
            </div> <!--end col-->
        </div><!--end row-->
    </div><!-- container -->
@stop

@push('custom-scripts')
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.js"></script>
    <script src="{{ asset('assets/js/helper.js') }}"></script>
    <script src="{{ asset('assets/js/date-helper.js') }}"></script>
    <script>
        const CUSTOMERS_BY_ROUTE_URL = @json(route('customers.get.route', ':id'));
        const selectedCustomer = null;
    </script>
    <script src="{{ asset('assets/js/customer-selection5.js') }}"></script>
    <script>
        $(document).ready(function () {
            $.ajaxSetup({
                headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
            }); 

            let data = [];
            doInit();

            function doInit() {
                $('#div-incentive').hide();
                $('#hdn-customer-id').on('change', handleCustomerChange);
                $('#btn-view').on('click', loadIncentive);
                $('#btn-submit').on('click', saveIncentive);
            }

            function handleCustomerChange() {
                clearIncentiveDiv();
                $('#div-incentive').hide();
                let customerId = $('#hdn-customer-id').val();
                if(customerId != 0) {
                    $.ajax({
                        url: "{{ route('incentives.date') }}",
                        type: 'GET',
                        data: { customer_id : customerId },
                        dataType: 'json',
                        success: function (response) {
                            const minDate = response.date;
                            const maxDate = getYesterday();
                            $('#dt-from').val(minDate);
                            $('#dt-to').attr('min', minDate);
                            $('#dt-to').attr('max', maxDate);

                            // Clear date not in range of min and max date
                            const currentVal = $('#dt-to').val();
                            if (currentVal && currentVal < minDate || currentVal > maxDate) {
                                $('#dt-to').val('');
                            }
                        },
                        error: function (data, textStatus, errorThrown) {
                            console.log(data.responseText);
                        }
                    });
                }
            }

            function loadIncentive() {
                let customerId = $('#hdn-customer-id').val();
                let fromDate   = $('#dt-from').val();
                let toDate     = $('#dt-to').val();

                if(customerId == 0) Swal.fire('Attention', 'Please Select Customer!', 'warning');
                else if(fromDate == "") Swal.fire('Attention', 'Please Give \'From Date\'', 'warning');
                else if(toDate == "") Swal.fire('Attention', 'Please Give \'To Date\'', 'warning');
                else {
                    $.ajax({
                        url: "{{ route('incentives.load') }}",
                        type: 'GET',
                        data: {
                            customer_id : customerId,
                            from_date   : fromDate,
                            to_date     : toDate,
                        },
                        dataType: 'json',
                        success: function (response) {
                            if(response.success) {
                                console.log(response);
                                $('#hdg-customer').text($('#act-customer-name').val());
                                $('#hdg-duration').text(response.date_title);
                                generateIncentiveTable(response);
                                $('#div-incentive').show();
                                data['id']   = customerId;
                                data['from'] = fromDate;
                                data['to']   = toDate;
                            }
                            else {
                                Swal.fire('Sorry!', response.message, 'error');
                            }
                        },
                        error: function (data, textStatus, errorThrown) {
                            Swal.fire('Sorry!', 'Unable to Load Incentive!', 'error');
                            console.log(data.responseText);
                        }
                    });
                }
            }

            function saveIncentive() {
                let hasIncentive = $('#div-incentive').is(':visible');
                if(!hasIncentive) {
                    Swal.fire('Sorry!', 'Missing incentive data.<br/>Generate the table to submit.', 'warning');
                }
                else {
                    $('#btn-submit').prop('disabled', true); // Disable submit button
                    $.ajax({
                        url: "{{ route('incentives.store') }}",
                        type: 'POST',
                        data: {
                            customer_id : data['id'],
                            from_date   : data['from'],
                            to_date     : data['to'],
                        },
                        dataType: 'json',
                        success: function (response) {
                            if(response.success) {
                                Swal.fire('Success!', response.message, 'success');
                                clearIncentiveDiv();
                                $('#div-incentive').hide();
                                $('#hdn-customer-id').val(0);
                                $('#act-customer-name').val("");
                                $('#btn-submit').prop('disabled', false); // Enable submit button
                            }
                            else {
                                Swal.fire('Sorry!', response.message, 'error');
                                $('#btn-submit').prop('disabled', false);
                            }
                        },
                        error: function(xhr, status, error) {
                            let response = JSON.parse(xhr.responseText);
                            Swal.fire('Sorry!', response.message, response.icon || 'error');
                            $('#btn-submit').prop('disabled', false);
                        }
                    });
                }
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
                            <td class="text-right pr-2">${record.qty.toFixed(2)}</td>
                            <td class="text-right pr-2">${record.inc_rate.toFixed(2)}</td>
                            <td class="text-right pr-2">${record.inc_amt.toFixed(2)}</td>
                            <td class="text-right pr-2">${record.lkg_qty.toFixed(2)}</td>
                            <td class="text-right pr-2">${record.lkg_amt.toFixed(2)}</td>
                        </tr>`;
                    $tbody.append(row);
                });

                // Update totals in footer
                let totals = response.totals;
                $('#th-tot-qty').text(formatToIndianNumberFormat(totals.qty,true));
                $('#th-tot-inc-amt').text(formatToIndianNumberFormat(totals.inc_amt,true));
                $('#th-tot-lkg-qty').text(formatToIndianNumberFormat(totals.lkg_qty,true));
                $('#th-tot-lkg-amt').text(formatToIndianNumberFormat(totals.lkg_amt,true));

                // Update summary
                let summary = response.summary;
                $('#td-inc-amt').text(formatToIndianNumberFormat(totals.inc_amt,true));
                $('#td-lkg-amt').text(formatToIndianNumberFormat(totals.lkg_amt,true));
                $('#td-tot-amt').text(formatToIndianNumberFormat(summary.total,true));
                $('#td-tds-amt').text(formatToIndianNumberFormat(summary.tds_amount,true));
                $('#td-round-off').text(getRoundOffString(summary.round_off));
                $('#th-net-amt').text(formatToIndianNumberFormat(summary.net_total,true));
            }

            function clearIncentiveDiv() {
                $('#hdg-customer').text('');
                $('#hdg-duration').text('');
                $('.calc-val').text('');
                $('#tbl-incentive tbody').empty();
            }
        });
    </script>
@endpush

@section('footerScript')
    <script src="{{ asset('plugins/sweet-alert2/sweetalert2.min.js') }}"></script>
    <script src="{{ asset('assets/pages/jquery.sweet-alert.init.js') }}"></script>
@stop