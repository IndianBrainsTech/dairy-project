@extends('app-layouts.admin-master')

@section('title', 'Create Diesel Bill Statement')

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
                @component('app-components.breadcrumb-4')
                    @slot('title') Create Diesel Bill Statement @endslot
                    @slot('item1') Transactions @endslot
                    @slot('item2') Diesel Bills @endslot
                    @slot('item3') Generation @endslot
                @endcomponent
            </div>
        </div>
        <!-- end page title end breadcrumb -->

        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">

                        <div class="row gx-2 gy-2 align-items-center">
                            <!-- Petrol Bunk -->
                            <div class="col-12 col-md-auto flex-grow-1">
                                <div class="input-group w-100">
                                    <div class="input-group-prepend">
                                        <button type="button" class="btn btn-info btn-match" aria-label="Select Petrol Bunk" tabindex="-1">
                                            <i class="fas fa-search"></i>
                                        </button>
                                    </div>
                                    <input type="text" id="act-bunk-name" class="form-control" placeholder="Petrol Bunk" tabindex="1">
                                    <input type="hidden" id="hdn-bunk-id">
                                </div>
                            </div>

                            <!-- From Date -->
                            <div class="col-6 col-md-auto">
                                <input type="date" id="dt-from" class="form-control" max="{{ date('Y-m-d') }}" tabindex="2" disabled>
                            </div>

                            <!-- To Date -->
                            <div class="col-6 col-md-auto">
                                <input type="date" id="dt-to" class="form-control" max="{{ date('Y-m-d') }}" tabindex="3">
                            </div>

                            <!-- Buttons -->
                            <div class="col-12 col-md-auto d-flex flex-wrap justify-content-md-end gap-2">
                                <button id="btn-load" type="button" class="btn btn-dark btn-sm px-3 mx-2" aria-label="View" title="View" tabindex="4">
                                    View
                                </button>
                                <button id="btn-submit" type="button" class="btn btn-primary btn-sm px-3 mx-2" aria-label="Submit" title="Submit" tabindex="5">
                                    Submit
                                </button>
                            </div>
                        </div>
                        <hr/>

                        <div id="div-statement" class="table-responsive dash-social px-2">
                            <h2 id="hdg-bunk" class="app-h2"></h2>
                            <h3 id="hdg-duration" class="app-h3 pb-2"></h3>
                            <table id="tbl-statement" class="table table-bordered table-sm">
                                <thead class="thead-light">
                                    <tr>
                                        <th class="text-center">S.No</th>
                                        <th class="text-center">Date</th>
                                        <th class="text-left pl-2">Vehicle</th>
                                        <th class="text-left pl-2">Driver</th>
                                        <th class="text-left pl-2">Route</th>
                                        <th class="text-right pr-2">Fuel</th>
                                        <th class="text-right pr-2">Pre KM</th>
                                        <th class="text-right pr-2">Cur KM</th>
                                        <th class="text-right pr-2">Run KM</th>
                                        <th class="text-right pr-2">KMPL</th>
                                        <th class="text-right pr-2">Rate</th>
                                        <th class="text-right pr-2">Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                                <tfoot class="text-right">
                                    <tr class="thead-light">
                                        <th colspan="5" class="text-center">Total / Average</th>
                                        <th id="th-total-fuel" class="calc-val pr-2"></th>
                                        <th></th>
                                        <th></th>
                                        <th id="th-total-kilometer" class="calc-val pr-2"></th>
                                        <th id="th-average-kmpl" class="calc-val pr-2"></th>
                                        <th id="th-average-rate" class="calc-val pr-2"></th>
                                        <th id="th-total-amount" class="calc-val pr-2"></th>
                                    </tr>
                                    <tr id="tr-tds">
                                        <th colspan="11" class="pr-2">TDS</th>
                                        <th id="th-tds-amount" class='calc-val pr-2'></th>
                                    </tr>
                                    <tr>
                                        <th colspan="11" class="pr-2">Round Off</th>
                                        <th id="th-round-off" class='calc-val pr-2'></th>
                                    </tr>
                                    <tr>
                                        <th colspan="11" class="pr-2">Net Amount</th>
                                        <th id="th-net-amount" class='calc-val pr-2'></th>
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
    <script src="{{ asset('assets/js/helper-v1.js')}}"></script>
    <script>
        $(document).ready(function () {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            }); 

            const bunks            = @json($bunks);
            const bunkMap          = new Map(bunks.map(bunk => [bunk.id, bunk.name]));
            const bunkNameMap      = new Map(bunks.map(bunk => [bunk.name, bunk.id]));
            const maxDate          = {{ date('Y-m-d') }};
            const showWarning      = msg => Swal.fire('Sorry!', msg, 'warning');

            const $dtFrom          = $('#dt-from');
            const $dtTo            = $('#dt-to');
            const $actBunkName     = $('#act-bunk-name');
            const $hdnBunkId       = $('#hdn-bunk-id');
            const $btnLoad         = $('#btn-load');
            const $btnSubmit       = $('#btn-submit');
            const $hdgBunk         = $('#hdg-bunk');
            const $hdgDuration     = $('#hdg-duration');
            const $divStatement    = $('#div-statement');
            const $tblStatment     = $('#tbl-statement');
            const $tblBody         = $('#tbl-statement tbody');
            const $thTotalFuel     = $('#th-total-fuel');
            const $thTotalKilomter = $('#th-total-kilometer');
            const $thAverageKmpl   = $('#th-average-kmpl');
            const $thAverageRate   = $('#th-average-rate');
            const $thTotalAmount   = $('#th-total-amount');
            const $thTdsAmount     = $('#th-tds-amount');
            const $thRoundOff      = $('#th-round-off');
            const $thNetAmount     = $('#th-net-amount');
            const $trTds           = $('#tr-tds');
            const $calcValues      = $('.calc-val');
            doInit();

            function doInit() {
                $("body").toggleClass("enlarge-menu");
                $divStatement.hide();
                $btnLoad.on('click', loadBillStatement);
                $btnSubmit.on('click', saveBillStatement);
            }

            $actBunkName.autocomplete({
                source: autocompleteSource(bunkMap),
                autoFocus: true,
                minLength: 0,
                select: function (event, ui) {
                    const id = ui.item.id;
                    const name = ui.item.value;
                    console.log(`Selected Bunk => ID: ${id}, Name: ${name}`);
                    $hdnBunkId.val(id);
                    $divStatement.hide();
                    handleBunkChange(id);
                }
            });

            $actBunkName.on('change', function () {
                let name = $(this).val().trim();
                if(name == "") {
                    $hdnBunkId.val('');
                }
                else if(!bunkNameMap.has(name)){
                    const id = $hdnBunkId.val();
                    name = bunkMap.get(parseInt(id));
                    $actBunkName.val(name);
                }
            });

            function handleBunkChange(bunkId) {
                $.ajax({
                    url: "{{ route('diesel-bills.statements.date') }}",
                    type: 'GET',
                    data: { bunk_id : bunkId },
                    dataType: 'json'
                })
                .done(response => {
                    console.log("AJAX Success:", response);
                    if(response.success) {
                        const minDate = response.date;
                        $dtFrom.val(minDate);
                        $dtTo.attr('min', minDate);

                        // Clear date not in range of min and max date
                        const currentVal = $dtTo.val();
                        if (currentVal && currentVal < minDate || currentVal > maxDate) {
                            $dtTo.val('');
                        }
                    }
                    else {
                        Swal.fire('Sorry!', response.message, 'error');
                    }
                })
                .fail((xhr, status, error) => {
                    handleAjaxError(xhr, status, error);
                });
            }

            function loadBillStatement() {
                const bunkId    = $hdnBunkId.val();
                const fromDate  = $dtFrom.val();
                const toDate    = $dtTo.val();

                if(!bunkId)     return showWarning('Please select petrol bunk name');
                if(!fromDate)   return showWarning('Please give \'from date\'');
                if(!toDate)     return showWarning('Please give \'to date\'');

                clearStatementDiv();
                $btnLoad.prop('disabled', true);
                $.ajax({
                    url: "{{ route('diesel-bills.statements.load') }}",
                    type: 'GET',
                    data: {
                        bunk_id   : bunkId,
                        from_date : fromDate,
                        to_date   : toDate,
                    },
                    dataType: 'json'
                })
                .done(response => {
                    console.log("AJAX Success:", response);
                    if(response.success) {
                        buildStatement(response);
                        $divStatement.show();
                    } 
                    else {
                        Swal.fire('Sorry!', response.message, 'error');
                    }
                })
                .fail((xhr, status, error) => {
                    handleAjaxError(xhr, status, error);
                })
                .always(() => {
                    $btnLoad.prop('disabled', false);
                });
            }

            function buildStatement(response) {
                // Update titles
                $hdgBunk.text($actBunkName.val());
                $hdgDuration.text(response.date_title);

                // Populate rows
                response.records.forEach((record, index) => {
                    const row = `
                        <tr>
                            <td class="text-center">${index + 1}</td>
                            <td class="text-center">${record.document_date}</td>
                            <td class="text-left pl-2">${record.vehicle_number}</td>
                            <td class="text-left pl-2">${record.driver_name}</td>
                            <td class="text-left pl-2">${record.route_name}</td>
                            <td class="text-right pr-2">${record.fuel}</td>
                            <td class="text-right pr-2">${record.opening_km}</td>
                            <td class="text-right pr-2">${record.closing_km}</td>
                            <td class="text-right pr-2">${record.running_km}</td>
                            <td class="text-right pr-2">${record.kmpl}</td>
                            <td class="text-right pr-2">${record.rate}</td>
                            <td class="text-right pr-2">${record.amount}</td>
                        </tr>`;
                    $tblBody.append(row);
                });

                // Update summary
                const summary = response.summary;
                $thTotalFuel.text(summary.total_fuel);
                $thTotalKilomter.text(summary.total_km);
                $thAverageKmpl.text(summary.average_kmpl);
                $thAverageRate.text(summary.average_rate);
                $thTotalAmount.text(summary.total_amount);
                $thTdsAmount.text(summary.tds_amount);
                $thRoundOff.text(summary.round_off);
                $thNetAmount.text(summary.net_amount);

                // TDS row visibility
                $trTds.toggle(Number(summary.tds_amount) !== 0);
            }

            function saveBillStatement() {
                const bunkId    = $hdnBunkId.val();
                const bunkName  = $actBunkName.val().trim();
                const fromDate  = $dtFrom.val();
                const toDate    = $dtTo.val();
                const hasStatement = $divStatement.is(':visible');

                if(!bunkId)     return showWarning('Please select petrol bunk name');
                if(!fromDate)   return showWarning('Please give \'from date\'');
                if(!toDate)     return showWarning('Please give \'to date\'');
                if(!hasStatement) return showWarning('Please generate the table before submit.');

                $btnSubmit.prop('disabled', true);
                $.ajax({
                    url: "{{ route('diesel-bills.statements.store') }}",
                    type: 'POST',
                    data: {
                        bunk_id   : bunkId,
                        bunk_name : bunkName,
                        from_date : fromDate,
                        to_date   : toDate,
                    },
                    dataType: 'json'
                })
                .done(response => {
                    console.log("AJAX Success:", response);
                    if(response.success) {
                        Swal.fire('Success!', response.message, 'success');
                        clearStatementDiv();
                        $divStatement.hide();
                        $hdnBunkId.val('');
                        $actBunkName.val('');
                    } 
                    else {
                        Swal.fire('Sorry!', response.message, 'error');
                    }
                })
                .fail((xhr, status, error) => {
                    handleAjaxError(xhr, status, error);
                })
                .always(() => {
                    $btnSubmit.prop('disabled', false);
                });
            }

            function clearStatementDiv() {
                $hdgBunk.text('');
                $hdgDuration.text('');
                $tblBody.empty();
                $calcValues.text('');
            }
        });
    </script>
@endpush

@section('footerScript')
    <script src="{{ asset('plugins/sweet-alert2/sweetalert2.min.js') }}"></script>
    <script src="{{ asset('assets/pages/jquery.sweet-alert.init.js') }}"></script>
@stop