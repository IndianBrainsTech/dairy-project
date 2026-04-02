@extends('app-layouts.admin-master')

@section('title', 'Diesel Bill Entry')

@section('headerStyle')
    <link href="{{ asset('plugins/datatables/dataTables.bootstrap4.min.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('plugins/sweet-alert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('plugins/animate/animate.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('assets/css/app-style-v1.css') }}" rel="stylesheet" type="text/css">
    <style type="text/css">
        .modal-field {
            font-weight: 600;
        }
        hr {
            margin-top: 8px;
            margin-bottom: 8px;
        }
    </style>
@stop

@section('content')
    <div class="container-fluid">
        <!-- Page-Title -->
        <div class="row">
            <div class="col-12">
                @component('app-components.breadcrumb-4')
                    @slot('title') Diesel Bill Entry @endslot
                    @slot('item1') Transactions @endslot
                    @slot('item2') Diesel Bills @endslot
                    @slot('item3') Entry @endslot
                @endcomponent
            </div><!--end col-->
        </div>
        <!-- end page title end breadcrumb -->

        <div class="row">
            <div class="col-12">
                <div class="card">

                    <div class="container-fluid">
                        <div class="card shadow-sm my-3">
                            <div class="card-body p-0">
                                <form id="frm-diesel-bill">
                                    <div class="table-responsive">
                                        <table class="table table-bordered align-middle mb-0">
                                            <thead class="table-light text-center">
                                                <tr class="text-nowrap">
                                                    <th style="width: 10%">Document Date <small class="text-danger font-13">*</small></th>
                                                    <th style="width: 40%">Petrol Bunk Name <small class="text-danger font-13">*</small></th>
                                                    <th style="width: 15%">Bill Number</th>
                                                    <th style="width: 10%">Bill Date <small class="text-danger font-13">*</small></th>
                                                    <th style="width: 25%">Route <small class="text-danger font-13">*</small></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td>
                                                        <input type="date" id="dt-document" class="form-control" value="{{ date('Y-m-d') }}" 
                                                            max="{{ date('Y-m-d') }}" tabindex="1" style="width:125px">
                                                        <input type="hidden" id="hdn-bill-id">
                                                    </td>
                                                    <td>
                                                        <div class="input-group w-100 h-100">
                                                            <div class="input-group-prepend">
                                                                <button type="button" class="btn btn-info btn-match" aria-label="Select Petrol Bunk" tabindex="-1">
                                                                    <i class="fas fa-search"></i>
                                                                </button>
                                                            </div>
                                                            <input type="text" id="act-bunk-name" class="form-control" placeholder="Petrol Bunk" tabindex="1">
                                                            <input type="hidden" id="hdn-bunk-id">
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <input type="text" id="txt-bill-number" class="form-control text-center" tabindex="3">
                                                    </td>
                                                    <td>
                                                        <input type="date" id="dt-bill" class="form-control" value="{{ date('Y-m-d') }}" 
                                                            max="{{ date('Y-m-d') }}" tabindex="4" style="width:125px">
                                                    </td>
                                                    <td>
                                                        <div class="input-group w-100 h-100">
                                                            <div class="input-group-prepend">
                                                                <button type="button" class="btn btn-info btn-match" aria-label="Select Route" tabindex="-1">
                                                                    <i class="fas fa-search"></i>
                                                                </button>
                                                            </div>
                                                            <input type="text" id="act-route-name" class="form-control" placeholder="Route" tabindex="5">
                                                            <input type="hidden" id="hdn-route-id">
                                                        </div>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>

                                    <div class="table-responsive">
                                        <table class="table table-bordered align-middle mb-0">
                                            <thead class="table-light text-center">
                                                <tr class="text-nowrap">
                                                    <th style="width: 26%;">Vehicle Number <small class="text-danger font-13">*</small></th>
                                                    <th style="width: 26%;">Driver Name <small class="text-danger font-13">*</small></th>
                                                    <th style="width: 11%;">Fuel (Ltrs) <small class="text-danger font-13">*</small></th>
                                                    <th style="width: 11%;">Rate (₹) <small class="text-danger font-13">*</small></th>
                                                    <th style="width: 13%;">Opening KM <small class="text-danger font-13">*</small></th>
                                                    <th style="width: 13%;">Closing KM <small class="text-danger font-13">*</small></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td>
                                                        <div class="input-group w-100 h-100">
                                                            <div class="input-group-prepend">
                                                                <button type="button" class="btn btn-info btn-match" aria-label="Select Vehicle Number" tabindex="-1">
                                                                    <i class="fas fa-search"></i>
                                                                </button>
                                                            </div>
                                                            <input type="text" id="act-vehicle-number" class="form-control" placeholder="Vehicle Number" tabindex="6">
                                                            <input type="hidden" id="hdn-vehicle-id">
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div class="input-group w-100 h-100">
                                                            <div class="input-group-prepend">
                                                                <button type="button" class="btn btn-info btn-match" aria-label="Select Driver" tabindex="-1">
                                                                    <i class="fas fa-search"></i>
                                                                </button>
                                                            </div>
                                                            <input type="text" id="act-driver-name" class="form-control" placeholder="Driver" tabindex="6">
                                                            <input type="hidden" id="hdn-driver-id">
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <input type="text" id="txt-fuel" class="form-control text-center" tabindex="7">
                                                    </td>
                                                    <td>
                                                        <input type="text" id="txt-rate" class="form-control text-center" tabindex="8">
                                                    </td>
                                                    <td>
                                                        <input type="text" id="txt-opening-km" class="form-control text-center" tabindex="9">
                                                    </td>
                                                    <td>
                                                        <input type="text" id="txt-closing-km" class="form-control text-center" tabindex="10">
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>

                                    <div class="row my-3">
                                        <div class="col-12 d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center">
                                            <!-- Left side labels -->
                                            <div class="mb-2 mb-md-0">
                                                <span class="bg-soft-primary rounded mx-2 p-2">Amount : <b><span id="spn-amount"></span></b></span>
                                                <span class="bg-soft-primary rounded mx-2 p-2">Running KM : <b><span id="spn-running-km"></span></b></span>
                                                <span class="bg-soft-primary rounded mx-2 p-2">KM per Liter : <b><span id="spn-kmpl"></span></b></span>
                                            </div>

                                            <!-- Right side buttons -->
                                            <div class="text-md-right text-center">
                                                <button type="button" id="btn-reset" class="btn btn-sm btn-secondary px-3 mx-2 mb-2 mb-md-0">Clear</button>
                                                <button type="button" id="btn-submit" class="btn btn-sm btn-primary px-3 mx-2">Save</button>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                                <hr/>

                                <div class="table-responsive dash-social p-2">
                                    <table id="datatable" class="table table-bordered table-sm dt-responsive nowrap">
                                        <thead class="thead-light">
                                            <tr>
                                                <th class="text-center">S.No</th>
                                                <th class="text-center">Date</th>
                                                <th class="text-left pl-2">Petrol Bunk</th>
                                                <th class="text-left pl-2">Vehicle Number</th>
                                                <th class="text-left pl-2">Route</th>
                                                <th class="text-center">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                </div><!--end card-->
            </div><!--end col-->
        </div><!--end row-->
    </div><!-- container -->

    @include('transactions.diesel-bills.entries.show-modal')
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

            const bunks    = @json($bunks);
            const routes   = @json($routes);
            const vehicles = @json($vehicles);
            const drivers  = @json($drivers);

            // Create maps for quick lookup by ID
            const bunkMap    = new Map(bunks.map(bunk => [bunk.id, bunk.name]));
            const routeMap   = new Map(routes.map(route => [route.id, route.name]));
            const vehicleMap = new Map(vehicles.map(vehicle => [vehicle.id, vehicle.vehicle_number]));
            const driverMap  = new Map(drivers.map(driver => [driver.id, driver.name]));

            // Create maps for quick lookup by Name
            const bunkNameMap   = new Map(bunks.map(bunk => [bunk.name, bunk.id]));
            const routeNameMap  = new Map(routes.map(route => [route.name, route.id]));
            const vehicleNumberMap = new Map(vehicles.map(vehicle => [vehicle.vehicle_number, vehicle.id]));
            const driverNameMap = new Map(drivers.map(driver => [driver.name, driver.id]));

            const showWarning = msg => Swal.fire('Sorry!', msg, 'warning');
            const $datatable = $('#datatable');

            const table = $datatable.DataTable({
                lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
                pageLength: 25,
            });

            const $dtDocument       = $('#dt-document');
            const $dtBill           = $('#dt-bill');
            const $hdnBillId        = $('#hdn-bill-id');
            const $hdnBunkId        = $('#hdn-bunk-id');
            const $hdnRouteId       = $('#hdn-route-id');
            const $hdnVehicleId     = $('#hdn-vehicle-id');
            const $hdnDriverId      = $('#hdn-driver-id');
            const $actBunkName      = $('#act-bunk-name');
            const $actRouteName     = $('#act-route-name');
            const $actVehicleNumber = $('#act-vehicle-number');
            const $actDriverName    = $('#act-driver-name');
            const $txtBillNumber    = $('#txt-bill-number');
            const $txtFuel          = $('#txt-fuel');
            const $txtRate          = $('#txt-rate');
            const $txtOpeningKm     = $('#txt-opening-km');
            const $txtClosingKm     = $('#txt-closing-km');
            const $spnAmount        = $('#spn-amount');
            const $spnRunningKm     = $('#spn-running-km');
            const $spnKmpl          = $('#spn-kmpl');
            const $btnSubmit        = $('#btn-submit');
            const $btnReset         = $('#btn-reset');

            const $divDocumentDate  = $('#div-document-date');
            const $divBunkName      = $('#div-bunk-name');
            const $divBillNumber    = $('#div-bill-number');
            const $divBillDate      = $('#div-bill-date');
            const $divRouteName     = $('#div-route-name');
            const $divVehicleNumber = $('#div-vehicle-number');
            const $divDriverName    = $('#div-driver-name');
            const $divFuel          = $('#div-fuel');
            const $divRate          = $('#div-rate');
            const $divAmount        = $('#div-amount');
            const $divOpeningKm     = $('#div-opening-km');
            const $divClosingKm     = $('#div-closing-km');
            const $divRunningKm     = $('#div-running-km');
            const $divKmpl          = $('#div-kmpl');
            const $mdlForm          = $('#modal-form');
            const $mdlFields        = $('.modal-field');

            doInit();

            function doInit() {
                $('a[href="#MenuTransactions"]').click();

                restrictToFloatNumbers('#txt-fuel');
                restrictToFloatNumbers('#txt-rate');
                restrictToNumbers('#txt-opening-km');
                restrictToNumbers('#txt-closing-km');

                moveToNextOnEnter();
                refreshTable();

                $txtRate.on('change', calculateAmount);
                $txtOpeningKm.on('change', calculateKilometer);
                $txtClosingKm.on('change', calculateKilometer);
                $btnReset.on('click', clearFields);
                $btnSubmit.on('click', doSubmit);

                $txtFuel.on('change', function() {
                    calculateAmount();
                    calculateKilometer();
                });

                $txtClosingKm.on('keypress', function(e) {
                    if (e.which === 13) {
                        e.preventDefault();
                        $btnSubmit.focus();
                    }
                });

                $datatable.on('click', '.btn-view', function () {
                    let id = $(this).data('id');
                    viewRecord(id);
                });

                $datatable.on('click', '.btn-edit', function () {
                    let id = $(this).data('id');
                    clearFields();
                    editRecord(id);
                });

                $datatable.on('click', '.btn-delete', function () {
                    let id = $(this).data('id');
                    clearFields();
                    deleteRecord(id);
                });
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
                    $txtBillNumber.focus();
                }
            });

            $actRouteName.autocomplete({
                source: autocompleteSource(routeMap),
                autoFocus: true,
                minLength: 0,
                select: function (event, ui) {
                    const id = ui.item.id;
                    const name = ui.item.value;
                    console.log(`Selected Route => ID: ${id}, Name: ${name}`);
                    $hdnRouteId.val(id);
                    $actVehicleNumber.focus();
                }
            });

            $actVehicleNumber.autocomplete({
                source: autocompleteSourceIncludes(vehicleMap),
                autoFocus: true,
                minLength: 0,
                select: function (event, ui) {
                    const id = ui.item.id;
                    const number = ui.item.value;
                    console.log(`Selected Vehicle => ID: ${id}, Number: ${number}`);
                    $hdnVehicleId.val(id);
                    $actDriverName.focus();
                    loadKilometer(id);
                }
            });

            $actDriverName.autocomplete({
                source: autocompleteSource(driverMap),
                autoFocus: true,
                minLength: 0,
                select: function (event, ui) {
                    const id = ui.item.id;
                    const name = ui.item.value;
                    console.log(`Selected Driver => ID: ${id}, Name: ${name}`);
                    $hdnDriverId.val(id);
                    $txtFuel.focus();
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

            $actRouteName.on('change', function () {
                let name = $(this).val().trim();
                if(name == "") {
                    $hdnRouteId.val('');
                }
                else if(!routeNameMap.has(name)){
                    const id = $hdnRouteId.val();
                    name = routeMap.get(parseInt(id));
                    $actRouteName.val(name);
                }
            });

            $actVehicleNumber.on('change', function () {
                let number = $(this).val().trim();
                if(number == "") {
                    $hdnVehicleId.val('');
                }
                else if(!vehicleNumberMap.has(number)){
                    const id = $hdnVehicleId.val();
                    number = vehicleMap.get(parseInt(id));
                    $actVehicleNumber.val(number);
                }
            });

            $actDriverName.on('change', function () {
                let name = $(this).val().trim();
                if(!driverNameMap.has(name)) {
                    $hdnDriverId.val('');
                }
            });

            function loadKilometer(vehicleId) {
                $.ajax({
                    url: "{{ route('diesel-bills.entries.opening') }}",
                    type: 'GET',
                    data: { vehicle_id : vehicleId },
                    dataType: 'json'
                })
                .done(response => {
                    console.log("AJAX Success:", response);
                    $txtOpeningKm.val(response.kilometer);
                })
                .fail((xhr, status, error) => {
                    handleAjaxError(xhr, status, error);
                });
            }

            function calculateAmount() {
                const fuel = parseFloat($txtFuel.val()) || 0;
                const rate = parseFloat($txtRate.val()) || 0;
                let amount = '';

                if (fuel > 0 && rate > 0) {
                    amount = fuel * rate;
                    amount = formatToIndianNumberFormat(amount, true);
                }

                $spnAmount.text(amount);
            }

            function calculateKilometer() {
                const opening = parseFloat($txtOpeningKm.val());
                const closing = parseFloat($txtClosingKm.val());
                const fuel    = parseFloat($txtFuel.val()) || 0;

                let km = '';
                let kmpl = '';

                // Run calculation only if both fields have values (including zero)
                const hasOpening = $txtOpeningKm.val().trim() !== '';
                const hasClosing = $txtClosingKm.val().trim() !== '';

                if (hasOpening && hasClosing) {
                    if (closing < opening) {
                        Swal.fire('Sorry!', 'Closing kilometer should be greater than Opening kilometer', 'warning')
                            .then(() => {
                                $txtClosingKm.val('');
                                $spnRunningKm.text('');
                                $spnKmpl.text('');
                            });
                        return;
                    }

                    // Valid input: calculate km and kmpl
                    km = closing - opening;
                    kmpl = fuel > 0 ? (km / fuel).toFixed(2) : 0;
                }

                $spnRunningKm.text(km);
                $spnKmpl.text(kmpl);
            }

            function refreshTable() {
                $.ajax({
                    url: "{{ route('diesel-bills.entries.pending') }}",
                    type: 'GET',
                    dataType: 'json'
                })
                .done(response => {
                    console.log("AJAX Success:", response);

                    table.clear();
                    let sno = 1;

                    response.records.forEach(record => {
                        table.row.add([
                            `<div class="text-center">${sno++}</div>`,
                            `<div class="text-center">${record.document_date}</div>`,
                            `<div class="text-left pl-2">${record.bunk_name}</div>`,
                            `<div class="text-left pl-2">${record.vehicle_number}</div>`,
                            `<div class="text-left pl-2">${record.route_name}</div>`,
                            `<div class="text-center">
                                <button type="button" class="btn btn-link btn-icon btn-view p-0 mx-1" data-id="${record.id}" title="View">
                                    <i class="dripicons-preview text-primary font-18"></i>
                                </button>
                                <button type="button" class="btn btn-link btn-icon btn-edit p-0 mx-1" data-id="${record.id}" title="Edit">
                                    <i class="fas fa-edit text-info font-16"></i>
                                </button>
                                <button type="button" class="btn btn-link btn-icon btn-delete p-0 mx-1" data-id="${record.id}" title="Delete">
                                    <i class="fas fa-trash-alt text-warning font-16"></i>
                                </button>
                            </div>`
                        ]);
                    });

                    table.draw();
                })
                .fail((xhr, status, error) => {
                    handleAjaxError(xhr, status, error);
                });
            }

            function viewRecord(id) {
                $.ajax({
                    url: "{{ route('diesel-bills.entries.fetch', ['bill' => '__ID__']) }}".replace('__ID__', id),
                    type: 'GET',
                    dataType: 'json'
                })
                .done(response => {
                    console.log("AJAX Success:", response);
                    const record = response.record;
                    $mdlFields.text('');
                    $divDocumentDate.text(record.document_date);
                    $divBunkName.text(record.bunk_name);
                    $divBillNumber.text(record.bill_number);
                    $divBillDate.text(record.bill_date);
                    $divRouteName.text(record.route_name);
                    $divVehicleNumber.text(record.vehicle_number);
                    $divDriverName.text(record.driver_name);
                    $divFuel.text(record.fuel);
                    $divRate.text(record.rate);
                    $divAmount.text(record.amount);
                    $divOpeningKm.text(record.opening_km);
                    $divClosingKm.text(record.closing_km);
                    $divRunningKm.text(record.running_km);
                    $divKmpl.text(record.kmpl);
                    $mdlForm.modal('show');
                })
                .fail((xhr, status, error) => {
                    handleAjaxError(xhr, status, error);
                });
            }

            function editRecord(id) {
                $.ajax({
                    url: "{{ route('diesel-bills.entries.fetch', ['bill' => '__ID__']) }}".replace('__ID__', id),
                    type: 'GET',
                    dataType: 'json'
                })
                .done(response => {
                    console.log("AJAX Success:", response);
                    let record = response.record;
                    $hdnBillId.val(record.id);
                    $dtDocument.val(convertToYMD(record.document_date));
                    $hdnBunkId.val(record.bunk_id);
                    $actBunkName.val(record.bunk_name);
                    $txtBillNumber.val(record.bill_number);
                    $dtBill.val(convertToYMD(record.bill_date));
                    $hdnRouteId.val(record.route_id);
                    $actRouteName.val(record.route_name);
                    $hdnVehicleId.val(record.vehicle_id);
                    $actVehicleNumber.val(record.vehicle_number);
                    $hdnDriverId.val(record.driver_id);
                    $actDriverName.val(record.driver_name);
                    $txtFuel.val(record.fuel);
                    $txtRate.val(record.rate);
                    $txtOpeningKm.val(record.opening_km);
                    $txtClosingKm.val(record.closing_km);
                    $spnAmount.text(record.amount);
                    $spnRunningKm.text(record.running_km);
                    $spnKmpl.text(record.kmpl);
                    $('html, body').animate({ scrollTop: 0 }, 'fast');
                })
                .fail((xhr, status, error) => {
                    handleAjaxError(xhr, status, error);
                });
            }

            function deleteRecord(id) {
                Swal.fire({
                    title: 'Are you sure?',
                    text: `Do you want to delete the diesel bill?`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, delete it!',
                    cancelButtonText: 'No, close',
                })
                .then((result) => {
                    if (result.value) {
                        $.ajax({
                            url: "{{ route('diesel-bills.entries.destroy', ['bill' => '__ID__']) }}".replace('__ID__', id),
                            type: 'DELETE',
                            dataType: 'json'
                        })
                        .done(response => {
                            console.log("AJAX Success:", response);
                            if(response.success)
                                Swal.fire('Success!', response.message, 'success')
                                    .then(() => refreshTable());
                            else
                                Swal.fire('Sorry!', response.message, 'error');
                        })
                        .fail((xhr, status, error) => {
                            handleAjaxError(xhr, status, error);
                        });
                    }
                });
            }

            function doSubmit() {
                const documentDate  = $dtDocument.val();
                const bunkId        = $hdnBunkId.val();
                const billDate      = $dtBill.val();
                const routeId       = $hdnRouteId.val();
                const vehicleNumber = $actVehicleNumber.val().trim();
                const driverName    = $actDriverName.val().trim();
                const fuel          = parseFloat($txtFuel.val()) || 0;
                const rate          = parseFloat($txtRate.val()) || 0;
                const openingKm     = parseInt($txtOpeningKm.val()) || 0;
                const closingKm     = parseInt($txtClosingKm.val()) || 0;

                if(!documentDate)  return showWarning('Please enter document date');
                if(!bunkId)        return showWarning('Please select petrol bunk name');
                if(!billDate)      return showWarning('Please enter bill date');
                if(!routeId)       return showWarning('Please select route');
                if(!vehicleNumber) return showWarning('Please enter vehicle number');
                if(!driverName)    return showWarning('Please enter driver name');
                if(!fuel)          return showWarning('Please enter fuel');
                if(!rate)          return showWarning('Please enter rate');
                if($txtOpeningKm.val().trim() === '') return showWarning('Please enter opening kilometer');
                if($txtClosingKm.val().trim() === '') return showWarning('Please enter closing kilometer');

                // Prepare remaining fields
                const billNumber = $txtBillNumber.val().trim();
                const bunkName   = $actBunkName.val().trim();
                const routeName  = $actRouteName.val().trim();
                const vehicleId  = $hdnVehicleId.val();
                const driverId   = $hdnDriverId.val();

                // Prepare JSON data
                const requestData = {
                    document_date   : documentDate,
                    bunk_id         : bunkId,
                    bunk_name       : bunkName,
                    bill_number     : billNumber,
                    bill_date       : billDate,
                    route_id        : routeId,
                    route_name      : routeName,
                    vehicle_id      : vehicleId,
                    vehicle_number  : vehicleNumber,
                    driver_id       : driverId,
                    driver_name     : driverName,
                    fuel            : fuel,
                    rate            : rate,
                    opening_km      : openingKm,
                    closing_km      : closingKm,
                };

                const billId = $hdnBillId.val();
                const url = billId 
                    ? "{{ route('diesel-bills.entries.update', ['bill' => '__ID__']) }}".replace('__ID__', billId)
                    : "{{ route('diesel-bills.entries.store') }}";

                const method = billId ? 'PUT' : 'POST';

                $btnSubmit.prop('disabled', true);
                $.ajax({
                    url: url,
                    type: method,
                    data: requestData,
                    dataType: 'json'
                })
                .done(response => {
                    console.log("AJAX Success:", response);
                    if(response.success) {
                        Swal.fire('Success!', response.message, 'success')
                            .then(() => {
                                clearFields();
                                refreshTable();
                                $actBunkName.focus();
                            });
                    } else {
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

            function clearFields() {
                [$hdnBillId, $hdnBunkId, $hdnRouteId, $hdnVehicleId, $hdnDriverId].forEach(el => el.val(''));
                [$actBunkName, $actRouteName, $actVehicleNumber, $actDriverName].forEach(el => el.val(''));
                [$txtBillNumber, $txtFuel, $txtRate, $txtOpeningKm, $txtClosingKm].forEach(el => el.val(''));
                [$spnAmount, $spnRunningKm, $spnKmpl].forEach(el => el.text(''));
            }
        });
    </script>
@endpush

@section('footerScript')
    <script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('plugins/sweet-alert2/sweetalert2.min.js') }}"></script>
    <script src="{{ asset('assets/pages/jquery.sweet-alert.init.js') }}"></script>
@stop