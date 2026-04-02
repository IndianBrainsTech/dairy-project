@extends('app-layouts.admin-master')

@section('title', 'Make Invoices')

@section('headerStyle')
    <link href="{{ asset('plugins/sweet-alert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('plugins/animate/animate.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('plugins/datatables/dataTables.bootstrap4.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/css/my-style.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('assets/css/my-actxt.css') }}" rel="stylesheet" type="text/css">
    <style type="text/css">
        .my-control {
            padding: 6px 10px;
            margin-right: 16px;
        }
    </style>
@stop

@section('content')
    <div class="container-fluid">
        <!-- Page-Title -->
        <div class="row">
            <div class="col-sm-12">
                @component('app-components.breadcrumb-3')
                    @slot('title') Make Invoices @endslot
                    @slot('item1') Transactions @endslot
                    @slot('item2') Invoices @endslot
                @endcomponent
            </div><!--end col-->
        </div>
        <!-- end page title end breadcrumb -->
 
        <div class="row"> 
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        
                        <div>
                            <label class="my-text">Date</label>
                            <input type="date" name="invDate" id="invDate" value="{{$date}}" class="my-control mr-2" tabindex="1" required>
                            <label for="route" class="my-text">Route</label>
                            <select name="route" id="route" class="my-control mr-2" tabindex="2" required>
                                <option value="">Select Route</option>
                                @foreach($routes as $route)
                                    <option value="{{$route->id}}">{{$route->name}}</option>
                                @endforeach
                            </select>
                            <button id="btnLoadOrders" class="btn btn-secondary btn-sm ml-2 px-3" tabindex="3">Load Orders</button>
                        </div>
                            
                        <div id="divGenerate">
                            <div class="row mt-3 mb-2">
                                <div class="col-md-3">
                                    <div class="input-group">
                                        <span class="input-group-prepend">
                                            <button type="button" class="btn btn-info" tabindex="-1"><i class="fas fa-search"></i></button>
                                        </span>
                                        <input type="text" id="vehicleNum" tabindex="4" class="form-control" placeholder="Vehicle Number">
                                        <input type="hidden" id="vehicleNumId">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="input-group">
                                        <span class="input-group-prepend">
                                            <button type="button" class="btn btn-info" tabindex="-1"><i class="fas fa-search"></i></button>
                                        </span>
                                        <input type="text" id="driver" tabindex="5" class="form-control" placeholder="Driver Name">
                                        <input type="hidden" id="driverId">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="dripicons-phone"></i></span>
                                        </div>
                                        <input type="text" id="mobileNum" tabindex="6" class="form-control" maxlength="10" tabindex="5" placeholder="Driver Mobile No.">                                            
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <input type="submit" id="btnGenerate" value="Generate Invoices" class="btn btn-primary btn-sm px-3" tabindex="7"/>
                                </div>
                            </div>
                        </div>
                        <hr/>

                        <div class="table-responsive dash-social">
                            <table id="datatable" class="table table-sm table-bordered dt-responsive nowrap" style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                                <thead class="thead-light">
                                    <tr>
                                        <th>S.No</th>
                                        <th>Route</th>
                                        <th>Invoice Date</th>
                                        <th>Order No</th>
                                        <th>Customer</th>
                                        <th>Invoice Status</th>
                                    </tr>
                                </thead>
                                <tbody>                                    
                                </tbody>
                            </table>
                        </div>
                    </div><!--end card-body-->
                </div><!--end card-->
            </div> <!--end col-->
        </div><!--end row-->
    </div>
@stop

@push('custom-scripts')
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.js"></script>
    <script src="{{ asset('assets/js/input-restriction.js') }}"></script>
    <script src="{{ asset('assets/js/helper.js') }}"></script>
    <script>
        $(document).ready(function () {
            $.ajaxSetup({
                headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
            });

            // Initialization
            let vehicles = @json($vehicles);
            let drivers = new Map();
            doInit();

            function doInit() { 
                // restrictToTodayAndTomorrow('#invDate');
                restrictDate('#invDate');
                $("#divGenerate").hide();
            
                @foreach($drivers as $driver)
                    var key = '{{$driver->name}}';
                    var value = { id: '{{$driver->id}}', mobile: '{{$driver->mobile_num}}' };
                    drivers.set(key, value);
                @endforeach

                $('#datatable').dataTable( {
                    "lengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
                    "pageLength": -1,
                    "columnDefs": [
                        { "targets": [0, 2, 3], "className": "text-center" }  // Target columns for center alignment
                    ]
                } );

                // Event Handlers
                $('#btnLoadOrders').click(loadOrders);
                $('#btnGenerate').click(generateInvoices);
            }

            $("#vehicleNum").autocomplete({
                source: vehicles,
                autoFocus: true,
                minLength: 0
            });

            $("#driver").autocomplete({
                source: autocompleteSource(drivers),
                autoFocus: true,
                minLength: 0,
                select: function(event, ui) {
                    var name = ui.item.value;
                    var driverData = drivers.get(name);
                    // Log selected driver ID and name
                    console.log("Driver => Selected ID: " + driverData.id + ", Name: " + name);
                    // Set the mobile number in the #mobileNum textbox
                    $("#mobileNum").val(driverData.mobile);
                }
            });

            function loadOrders(){                
                let date = $("#invDate").val();
                let route = $("#route").val();
                if(!date) {
                    Swal.fire('Attention','Please Select Date','error');
                }
                else if(!route) {
                    Swal.fire('Attention','Please Select Route','error');
                }
                else {                    
                    $.ajax({
                        url: "{{ route('invoices.orders.get') }}",
                        type: "GET",
                        data: {
                            inv_date : date,
                            route_id : route,
                        },
                        dataType: 'json',
                        success: function(data) {
                            if(data.orders.length === 0) {
                                $("#divGenerate").hide();
                                $('#datatable').DataTable().clear().draw();
                                Swal.fire('Info','No data found!','info');
                            }
                            else {
                                generateTable(data.route, data.orders);
                                loadDispatchData(data.dispatch);                                                                
                                toggleGenerateButton(data.orders);
                                $("#divGenerate").show();
                            }
                        },
                        error: function(data) {
                            Swal.fire('Sorry!', data.responseText, 'error');
                        }
                    });
                }
            }

            function loadDispatchData(dispatch) {
                if(dispatch) {
                    $("#vehicleNum").val(dispatch.vehicle_number);
                    $("#driver").val(dispatch.driver_name);
                    $("#mobileNum").val(dispatch.mobile_num);
                    // $('#divGenerate input[type="text"]').prop('disabled', true);
                }
                else {
                    $("#vehicleNum").val('');
                    $("#driver").val('');
                    $("#mobileNum").val('');
                    $('#divGenerate input[type="text"]').prop('disabled', false);
                }
            }

            function generateTable(route, orders) {
                let sno = 1;
                var table = $('#datatable').DataTable();

                // Clear existing rows
                table.clear();

                // Append new rows using DataTable's API
                orders.forEach(function(item) {
                    const newRow = [
                        sno++, 
                        route, 
                        item.invoice_date, 
                        item.order_num, 
                        item.customer.customer_name, 
                        applyBadge(item.invoice_status)
                    ];

                    // Add the new row to the DataTable
                    table.row.add(newRow);
                });

                // Redraw the table to reflect the changes
                table.draw();
            }

            function toggleGenerateButton(orders) {
                // Check if there is at least one order with the status "Not Generated"
                const hasNotGenerated = orders.some(item => item.invoice_status === "Not Generated");

                // shows or hides the button based on the boolean value of hasNotGenerated
                $("#btnGenerate").toggle(hasNotGenerated);
            }

            function applyBadge(status) {
                if(status == "Not Generated")
                    return `<span class='badge badge-md badge-soft-primary'>${status}</span>`;
                else if(status == "Generated")
                    return `<span class='badge badge-md badge-soft-success'>${status}</span>`;
                else if(status == "Cancelled")
                    return `<span class='badge badge-md badge-soft-danger'>${status}</span>`;
                else
                    return "";
            }

            function isValidated(invoiceDate, routeId, vehicle, driver, mobileNum) {
                let isValid = false;
                if(!invoiceDate)
                    Swal.fire("Attention!", "Please Select Date", "warning");
                else if(!routeId)
                    Swal.fire("Attention!", "Please Select Route", "warning");
                else if(!vehicle)
                    Swal.fire("Attention!", "Please Enter Vehicle Number", "warning");
                else if(!driver)
                    Swal.fire("Attention!", "Please Enter Driver Name", "warning");
                else if(!mobileNum)
                    Swal.fire("Attention!", "Please Enter Driver Mobile Number", "warning");
                else if(mobileNum.length != 10)
                    Swal.fire("Attention!", "Mobile Number Seems Incorrect", "warning");
                else
                    isValid = true;
                return isValid;
            }

            function generateInvoices() {
                const invoiceDate = $("#invDate").val();
                const routeId     = $("#route").val();
                const vehicle     = $("#vehicleNum").val();
                const driver      = $("#driver").val();
                const mobileNum   = $("#mobileNum").val();
                console.log(`Date: ${invoiceDate}, RouteId: ${routeId}, Vehicle: ${vehicle}, Driver: ${driver}, Mobile Number: ${mobileNum}`);

                if( isValidated(invoiceDate, routeId, vehicle, driver, mobileNum) ) {
                    $.ajax({
                        url: "{{ route('invoices.build') }}",
                        type: "POST",
                        data: {
                            invoice_date :  invoiceDate,
                            route_id     :  routeId,
                            vehicle_num  :  vehicle,
                            driver_name  :  driver,
                            mobile_num   :  mobileNum
                        },
                        dataType: 'json',
                        success: function(data) {
                            console.log(data);                            
                            Swal.fire({
                                title: 'Success!',
                                text: data.message,
                                type: 'success'
                            })
                            .then(
                                function() {
                                    window.location.reload(true);
                                }
                            );
                        },
                        error: function(data) {
                            console.log("Error : " + data.responseText);
                            Swal.fire('Sorry!', data.responseText, 'error');
                        }
                    });
                }
            }

            function restrictDate(dateControl) {
                // Get today's date (local time)
                let today = new Date();
                let day1 = new Date('2025-02-01');

                // Add one day to today's date to allow tomorrow
                let tomorrow = new Date();
                tomorrow.setDate(today.getDate() + 1);

                // Format date as 'YYYY-MM-DD' (ensuring two-digit month and day)
                function formatDate(date) {
                    let yyyy = date.getFullYear();
                    let mm = String(date.getMonth() + 1).padStart(2, '0'); // Months are 0-based
                    let dd = String(date.getDate()).padStart(2, '0');
                    return `${yyyy}-${mm}-${dd}`;
                }

                // Get formatted dates
                let tomorrowFormatted = formatDate(tomorrow);
                let day1Formatted = formatDate(day1);

                // Set the min and max attributes on the date input
                $(dateControl).attr('min', day1Formatted);
                $(dateControl).attr('max', tomorrowFormatted);
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