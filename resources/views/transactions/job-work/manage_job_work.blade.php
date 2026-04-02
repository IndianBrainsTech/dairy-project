@extends('app-layouts.admin-master')

@section('title', 'Job Work')

@section('headerStyle')
    <link href="{{ asset('plugins/sweet-alert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('plugins/animate/animate.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('assets/css/my-style.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('assets/css/my-actxt.css') }}" rel="stylesheet" type="text/css">
    <style type="text/css">
        .my-control {
            padding: 6px 10px;
            margin-right: 16px;
        }
    </style>
@stop

@php
    if(!isset($job_work)) {
        $mode              = "New";
        $title             = "Create Job Work";
        $lbl_submit        = "Submit";
        $job_work_num      = $job_work_num;
        $job_work_date     = date('Y-m-d');
        $customer_id       = "";
        $customer_name     = "";
        $billing_address   = "";
        $delivery_address  = "";
        $vehicle_num       = "";
        $driver_name       = "";
        $driver_mobile_num = "";
        $action            = route('job-work.store');
    } 
    else {
        $mode              = "Edit";
        $title             = "Edit Job Work";
        $lbl_submit        = "Update";
        $job_work_num      = $job_work->job_work_num;
        $job_work_date     = $job_work->job_work_date;
        $customer_id       = $job_work->customer_id;
        $customer_name     = $job_work->customer_name;
        $billing_address   = $job_work->customer_data->billAddr;
        $delivery_address  = $job_work->customer_data->deliAddr;
        $vehicle_num       = $job_work->vehicle_num;
        $driver_name       = $job_work->driver_name;
        $driver_mobile_num = $job_work->driver_mobile_num;
        $action            = route('job-work.update');
    }
@endphp

@section('content')
    <div class="container-fluid">
        <!-- Page-Title -->
        <div class="row">
            <div class="col-sm-12">
                @component('app-components.breadcrumb-3')
                    @slot('title') {{ $title }} @endslot
                    @slot('item1') Transactions @endslot
                    @slot('item2') Job Work @endslot
                @endcomponent
            </div>
        </div>
        <!-- end page title end breadcrumb -->

        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">

                        <div class="row mb-3">
                            <div class="col-md-8">
                                <div class="row mb-2">
                                    <input type="text" class="my-control text-center" style="width:140px" value="{{ $job_work_num }}" tabindex="-1" readonly>
                                    <input type="date" id="job-work-date" class="my-control text-center" style="width:140px" value="{{ $job_work_date }}" tabindex="1">
                                    <select name="route" id="route" class="d-none"><option value="0">Route</option></select>
                                    <div class="input-group" style="width:456px">
                                        <span class="input-group-prepend">
                                            <button type="button" class="btn btn-info py-1" tabindex="-1"><i class="fas fa-search"></i></button>
                                        </span>
                                        <input type="text" id="customer" value="{{ $customer_name }}" placeholder="Customer" class="my-control" style="width:396px" tabindex="2">
                                        <input type="hidden" id="customerId" value="{{ $customer_id }}">
                                    </div>
                                </div>
                                <label for="billing-address" class="my-text mr-3 mt-1 mb-0">Billing Address &ensp;&nbsp;</label>
                                <select id="billing-address" tabindex="3" class="my-control mb-1" style="width:600px;color:#656d9a"></select>
                                <label for="delivery-address" class="my-text mr-3 mt-1 mb-0">Delivery Address</label>
                                <select id="delivery-address" tabindex="4" class="my-control" style="width:600px;color:#656d9a"></select>
                            </div>
                            <div class="col-md-4">
                                <div class="row mb-2">
                                    <div class="col-md-4">
                                        <label for="vehicle-num" class="my-text mt-1">Vehicle No. <small class="text-danger font-13">*</small></label>
                                    </div>
                                    <div class="col-md-8">
                                        <div class="input-group" style="width:240px">
                                            <span class="input-group-prepend">
                                                <button type="button" class="btn btn-info py-0" tabindex="-1"><i class="fas fa-search"></i></button>
                                            </span>
                                            <input type="text" id="vehicle-num" value="{{ $vehicle_num }}" class="my-control" style="width:180px" tabindex="5">
                                        </div>
                                    </div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-md-4">
                                        <label for="driver-name" class="my-text mt-1">Driver Name <small class="text-danger font-13">*</small></label>
                                    </div>
                                    <div class="col-md-8">
                                        <div class="input-group" style="width:240px">
                                            <span class="input-group-prepend">
                                                <button type="button" class="btn btn-info py-0" tabindex="-1"><i class="fas fa-search"></i></button>
                                            </span>
                                            <input type="text" id="driver-name" value="{{ $driver_name }}" class="my-control" style="width:180px" tabindex="6">
                                        </div>
                                    </div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-md-4">
                                        <label for="driver-mobile-num" class="my-text mt-1">Mobile No. <small class="text-danger font-13">*</small></label>
                                    </div>
                                    <div class="col-md-8">
                                        <div class="input-group" style="width:240px">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text"><i class="dripicons-phone"></i></span>
                                            </div>
                                            <input type="text" id="driver-mobile-num" value="{{ $driver_mobile_num }}" class="my-control" style="width:180px" maxlength="10" tabindex="7">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <hr/>

                        <div class="row mb-2">
                            <label for="product" class="my-text ml-2 mr-2" style="width:125px">Product <small class="text-danger font-13">*</small></label>
                            <select id="product" class="my-control" style="width:160px" tabindex="8">
                                <option value="">Select Product</option>
                                @foreach($products as $product)
                                    <option value="{{$product->id}}" data-hsn="{{$product->hsn_code}}">{{$product->name}}</option>
                                @endforeach
                            </select>

                            <label for="qty-kg" class="my-text ml-4 mr-2">Qty (in Kgs) <small class="text-danger font-13">*</small></label>
                            <input type="text" id="qty-kg" class="my-control" style="width:100px" tabindex="9">

                            <label for="clr" class="my-text mx-2">CLR <small class="text-danger font-13">*</small></label>
                            <input type="text" id="clr" class="my-control" style="width:80px" tabindex="10">

                            <label for="fat" class="my-text mx-2">FAT </label>
                            <input type="text" id="fat" class="my-control" style="width:80px" tabindex="11">

                            <label for="ts-rate" class="my-text mx-2">TS Rate</label>
                            <input type="text" id="ts-rate" class="my-control" style="width:80px" tabindex="12">

                            <button id="btn-clear" class="btn btn-warning mx-2 py-0" tabindex="14"><i class="fas fa-trash-alt"></i></button>
                        </div>

                        <div class="row mb-3">
                            <label class="my-text mx-2" style="width:250px"> </label>
                            <label class="my-label mx-2">Qty (in Ltr) : <span id="qty-ltr" class="my-data"></span></label>
                            <label class="my-label mx-2">SNF : <span id="snf" class="my-data"></span></label>
                            <label class="my-label mx-2">TS : <span id="ts" class="my-data"></span></label>
                            <label class="my-label mx-2">Rate/Ltr : <span id="rate" class="my-data"></span></label>
                            <label class="my-label mx-2">Amount : <span id="amount" class="my-data"></span></label>
                            <button id="btn-add" type="button" class="btn btn-info mx-2 pt-1" style="max-height:32px" tabindex="13"><i class="fas fa-plus"></i></button>
                        </div>

                        <div class="table-responsive table-container">
                            <table id="table-items" class="table table-bordered table-sm text-center">
                                <thead>
                                    <tr class="thead-light" style='height:36px'>
                                        <th>S.No</th>
                                        <th>Product</th>
                                        <th>HSN Code</th>
                                        <th>Qty (Kg)</th>
                                        <th>CLR</th>
                                        <th>FAT</th>
                                        <th>SNF</th>
                                        <th>Qty (Ltr)</th>
                                        <th>TS</th>
                                        <th>TS Rate</th>
                                        <th>Rate / Ltr</th>
                                        <th class="text-right pr-2">Amount</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                                <tfoot>
                                    <tr id="totalRow">
                                        <td colspan="3">Total</td>
                                        <th id="tot_qty_kg"></th>
                                        <td colspan="3"></td>
                                        <th id="tot_qty_ltr"></th>
                                        <td colspan="3"></td>
                                        <th id="tot_amt" class="text-right pr-2"></th>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <td colspan="11" class="text-right pr-2">Round Off</td>
                                        <td id="round-off" class="text-right pr-2"></td>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <td colspan="11" class="text-right pr-2">Net Amount</td>
                                        <th id="net-amt" class="text-right pr-2"></th>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        <div class="row">
                            <div class="col-sm-12">
                                <button id="btn-submit" class="btn btn-primary float-right mr-3 px-3" tabindex="15">{{ $lbl_submit }}</button>
                            </div>
                        </div>

                    </div><!--end card-body-->
                </div><!--end card-->
            </div> <!--end col-->
        </div><!--end row-->
    </div><!--container-->
@stop

@push('custom-scripts')
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.js"></script>
    <script src="{{ asset('assets/js/customer-selection2.js') }}"></script>
    <script src="{{ asset('assets/js/input-restriction.js') }}"></script>
    <script src="{{ asset('assets/js/helper.js') }}"></script>
    <script>
        $(document).ready(function () {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            let vehicles = new Map();
            let drivers = new Map();
            let sno = -1;            
            doInit();

            async function doInit() {
                restrictDates('#job-work-date','2025-04-01','tomorrow');
                $("#table-items").hide();

                @foreach($vehicles as $vehicle)
                    var key = '{{$vehicle->vehicle_number}}';
                    var value = '{{$vehicle->id}}';
                    vehicles.set(key,value);
                @endforeach

                @foreach($drivers as $driver)
                    var key = '{{$driver->name}}';
                    var value = { id: '{{$driver->id}}', mobile: '{{$driver->mobile_num}}' };
                    drivers.set(key, value);
                @endforeach

                let customersUrl = "{{ route('customers.get.route', ':id') }}";
                await initializeCustomerSelection("#customer", '#route', "#vehicle-num", customersUrl);

                $('#customerId').on('change', updateCustomerData);
                $('#driver-name').on('blur', loadMobileNumber);
                $('#driver-mobile-num').on('keypress', restrictToInteger);

                $('#qty-kg, #clr, #fat, #ts-rate')
                    .on('keypress', restrictToFloat)
                    .on('keydown', handleEnterKey)
                    .on('change', doCalc);

                $('#btn-add').on('click', addOrUpdateRow);
                $('#btn-clear').on('click', clearFields);
                $('#btn-submit').on('click', doSubmit);

                $('body').on('click', '.edit-item', function (event) {
                    let row = $(this).closest('tr');
                    sno = $(row).find('td:nth-child(1)').text();
                    loadData(row);
                });

                $('body').on('click', '.delete-item', deleteItem);

                @if($mode === 'Edit')
                    let cname = @json($customer_name);
                    $('#customerId').val('{{ $customer_id }}');
                    $('#customer').val(cname);                    
                    updateCustomerData();
                    let jobWorkItems = @json($job_work_items);
                    addRows(jobWorkItems);
                @endif
            }

            $("#vehicle-num").autocomplete({
                source: Array.from(vehicles.keys()),
                autoFocus: true,
                minLength: 0,
                select: function(event, ui) {
                    let number = ui.item.value;
                    let id = vehicles.get(number);
                    console.log("Vehicle => Selected ID: " + id + ", Number: " + number);
                    $("#vehicleNumId").val(id).trigger("change");
                }
            });

            $("#driver-name").autocomplete({
                source: autocompleteSource(drivers),
                autoFocus: true,
                minLength: 0,
                select: function(event, ui) {
                    let name = ui.item.value;
                    let driverData = drivers.get(name);
                    console.log("Driver => Selected ID: " + driverData.id + ", Name: " + name);
                    // $("#driverId").val(driverData.id);
                    $("#driver-mobile-num").val(driverData.mobile);
                }
            });

            function updateCustomerData() {
                let id = $('#customerId').val();                
                if(id) {
                    let url = "{{ route('customers.data.address', ':cust_id') }}".replace(':cust_id', id);
                    $.get(url, function (data) {
                        let addresses = data.addresses;
                        $('#billing-address').empty();
                        $('#delivery-address').empty();
                        addresses.forEach(item => {
                            const addressText = `${item.address_lines}, ${item.district}, ${item.state}${item.pincode ? ' - ' + item.pincode : ''}`;
                            const option = new Option(addressText, item.id);
                            $('#billing-address, #delivery-address').append(option);
                        });
                        
                        if($('#table-items tbody tr').length > 0) {
                            updateTotals();
                        }
                    });
                }
            }

            function loadMobileNumber() {
                let driver = $("#driver-name").val();
                let driverData = drivers.get(driver);
                let mobileNum = driverData ? driverData.mobile : '';
                $("#driver-mobile-num").val(mobileNum);
            }

            function deleteItem() {
                $(this).closest('tr').remove();
                let n = $('#table-items tbody tr').length;
                if(n==0) {
                    $("#tot_qty_kg").text("");
                    $("#tot_qty_ltr").text("");
                    $("#tot_amt").text("");                    
                    $("#round-off").text("");
                    $("#net-amt").text("");
                    $("#table-items").hide();
                }
                else {
                    sno = -1;
                    updateSerialNumbers();
                    updateTotals();
                    if(n==1)
                        $("#totalRow").hide();
                }
            }

            function doCalc() {
                let qtyKg  = parseFloat($("#qty-kg").val()) || 0;
                let clr    = parseFloat($("#clr").val()) || 0;
                let fat    = parseFloat($("#fat").val()) || 0;
                let tsRate = parseFloat($("#ts-rate").val()) || 0;

                if (clr) {
                    let snf = (fat * 0.2 + 0.36) + (clr / 4);
                    snf = Math.round(snf*100) / 100;

                    let ts = snf + fat;
                    ts = Math.round(ts*100) / 100;

                    let qtyLtr = qtyKg / (1 + clr / 1000);
                    qtyLtr = Math.round(qtyLtr*100) / 100;

                    let rate = ts * tsRate / 100;
                    rate = Math.round(rate*100) / 100;

                    let amount = qtyLtr * rate;
                    amount = Math.round(amount*100) / 100;

                    $("#snf").text(snf.toFixed(2));
                    $("#ts").text(ts.toFixed(2));
                    $("#qty-ltr").text(qtyLtr.toFixed(2));
                    $("#rate").text(rate.toFixed(2));
                    $("#amount").text(amount.toFixed(2));
                }
            }

            function addOrUpdateRow() {
                let customerId = $("#customerId").val();
                let productId  = $("#product").val();
                let qtyKg      = $("#qty-kg").val();
                let clr        = $("#clr").val();
                let tsRate     = $("#ts-rate").val() || 0;

                if(!customerId) {
                    Swal.fire("Attention!", "Please Select Customer", "warning");
                }
                else if(!productId) {
                    Swal.fire("Attention!", "Please Select Product", "warning");
                }
                else if(!qtyKg) {
                    Swal.fire("Attention!", "Please Enter Qty (in Kgs)", "warning");
                }
                else if(!clr) {
                    Swal.fire("Attention!", "Please Enter CLR", "warning");
                }
                else {
                    let product = $("#product option:selected").text();
                    let hsnCode = $("#product option:selected").data('hsn');
                    let qtyLtr  = $("#qty-ltr").text();
                    let fat     = $("#fat").val();
                    let snf     = $("#snf").text();
                    let ts      = $("#ts").text();
                    let rate    = $("#rate").text();
                    let amount  = $("#amount").text();

                    if(sno == -1) { // Add Record
                        const record = `
                            <tr style='height:32px'>
                                <td></td>
                                <td data-id="${productId}">${product}</td>
                                <td>${hsnCode}</td>
                                <td>${qtyKg}</td>
                                <td>${clr}</td>
                                <td>${fat}</td>
                                <td>${snf}</td>
                                <td>${qtyLtr}</td>
                                <td>${ts}</td>
                                <td>${tsRate}</td>
                                <td>${rate}</td>
                                <td class="text-right pr-2">${amount}</td>
                                <td>
                                    <a href="#" class="edit-item" class="mr-2"><i class="fas fa-edit text-info font-16"></i></a>
                                    <a href="#" class="delete-item"><i class="fas fa-trash-alt text-warning font-16"></i></a>
                                </td>
                            </tr>`;
                        $("#table-items tbody").append(record);

                        updateSerialNumbers("#table-items");

                        if($('#table-items tbody tr').length == 1)
                            $("#totalRow").hide();
                        else
                            $("#totalRow").show();
                    }
                    else { // Update Record
                        let row = findRow(sno, "#table-items");
                        if(row) {
                            $(row).find('td:nth-child(4)').text(qtyKg);
                            $(row).find('td:nth-child(5)').text(clr);
                            $(row).find('td:nth-child(6)').text(fat);
                            $(row).find('td:nth-child(7)').text(snf);
                            $(row).find('td:nth-child(8)').text(qtyLtr);
                            $(row).find('td:nth-child(9)').text(ts);
                            $(row).find('td:nth-child(10)').text(tsRate);
                            $(row).find('td:nth-child(11)').text(rate);
                            $(row).find('td:nth-child(12)').text(amount);
                        }
                    }

                    updateTotals();
                    clearFields();
                    $("#table-items").show();
                }
            }

            function addRows(orderItems) {
                orderItems.forEach(item => {
                    const record = `
                        <tr style='height:32px'>
                            <td></td>
                            <td data-id="${item.product_id}">${item.product_name}</td>
                            <td>${item.hsn_code}</td>
                            <td>${item.qty_kg}</td>
                            <td>${item.clr}</td>
                            <td>${item.fat ?? ''}</td>
                            <td>${item.snf}</td>
                            <td>${item.qty_ltr}</td>
                            <td>${item.ts}</td>
                            <td>${item.ts_rate}</td>
                            <td>${item.rate}</td>
                            <td class="text-right pr-2">${item.amount}</td>
                            <td>
                                <a href="#" class="edit-item" class="mr-2"><i class="fas fa-edit text-info font-16"></i></a>
                                <a href="#" class="delete-item"><i class="fas fa-trash-alt text-warning font-16"></i></a>
                            </td>
                        </tr>`;
                    $("#table-items tbody").append(record);
                });

                if($('#table-items tbody tr').length == 1)
                    $("#totalRow").hide();
                else
                    $("#totalRow").show();

                updateSerialNumbers("#table-items");
                updateTotals();
                $("#table-items").show();
            }

            function clearFields() {
                $("#product").val('');
                $("#qty-kg").val('');
                $("#clr").val('');
                $("#fat").val('');
                $("#ts-rate").val('');
                $("#qty-ltr").text('');
                $("#snf").text('');
                $("#ts").text('');
                $("#rate").text('');
                $("#amount").text('');
                sno = -1;
            }

            function loadData(row) {
                let prodId = $(row).find('td:nth-child(2)').data('id');
                let qtyKg  = $(row).find('td:nth-child(4)').text();
                let clr    = $(row).find('td:nth-child(5)').text();
                let fat    = $(row).find('td:nth-child(6)').text();
                let snf    = $(row).find('td:nth-child(7)').text();
                let qtyLtr = $(row).find('td:nth-child(8)').text();
                let ts     = $(row).find('td:nth-child(9)').text();
                let tsRate = $(row).find('td:nth-child(10)').text();
                let rate   = $(row).find('td:nth-child(11)').text();
                let amount = $(row).find('td:nth-child(12)').text();

                $("#product").val(prodId);
                $("#qty-kg").val(qtyKg);
                $("#clr").val(clr);
                $("#fat").val(fat);
                $("#ts-rate").val(tsRate);
                $("#qty-ltr").text(qtyLtr);
                $("#snf").text(snf);
                $("#ts").text(ts);
                $("#rate").text(rate);
                $("#amount").text(amount);
            }

            function updateTotals() {
                let totKg = 0;
                let totLtr = 0;
                let totAmt = 0;

                $('#table-items tbody tr').each(function() {
                    let kg  = $(this).find('td:nth-child(4)').text();
                    let ltr = $(this).find('td:nth-child(8)').text();
                    let amt = $(this).find('td:nth-child(12)').text();
                    totKg  += Number(kg);
                    totLtr += Number(ltr);
                    totAmt += Number(amt);
                });
                $("#tot_qty_kg").text(totKg || "");
                $("#tot_qty_ltr").text(totLtr.toFixed(2) || "");
                $("#tot_amt").text(totAmt.toFixed(2) || "");

                let roundOff = Math.round(totAmt) - totAmt;
                let netAmt = Math.round(totAmt);
                $("#round-off").text(getRoundOffString(roundOff));
                $("#net-amt").text(netAmt.toFixed(2));
            }

            function getJobWorkData() {
                let records = [];

                $('#table-items tbody tr').each(function() {
                    let record = {};
                    record['product_id']   = $(this).find('td:nth-child(2)').data('id');
                    record['product_name'] = $(this).find('td:nth-child(2)').text();
                    record['hsn_code']     = $(this).find('td:nth-child(3)').text();
                    record['qty_kg']       = $(this).find('td:nth-child(4)').text();
                    record['clr']          = $(this).find('td:nth-child(5)').text();
                    record['fat']          = $(this).find('td:nth-child(6)').text();
                    record['snf']          = $(this).find('td:nth-child(7)').text();
                    record['qty_ltr']      = $(this).find('td:nth-child(8)').text();
                    record['ts']           = $(this).find('td:nth-child(9)').text();
                    record['ts_rate']      = $(this).find('td:nth-child(10)').text();
                    record['rate']         = $(this).find('td:nth-child(11)').text();
                    record['amount']       = $(this).find('td:nth-child(12)').text();
                    records.push(record);
                });
                
                return records;
            }

            function isValidated(jobWorkDate, customerId, vehicleNum, driverName, mobileNum, count) {
                let isValid = false;
                if(!jobWorkDate)
                    Swal.fire("Attention!", "Please Select Job Work Date", "warning");
                else if(!customerId)
                    Swal.fire("Attention!", "Please Select Customer", "warning");
                else if(!vehicleNum)
                    Swal.fire("Attention!", "Please Select Vehicle Number", "warning");
                else if(!driverName)
                    Swal.fire("Attention!", "Please Select Driver", "warning");
                else if(!mobileNum)
                    Swal.fire("Attention!", "Please Enter Mobile Number", "warning");
                else if(mobileNum.length != 10)
                    Swal.fire("Attention!", "Mobile Number Seems Incorrect", "warning");
                else if(count == 0)
                    Swal.fire("Attention!", "Please Add Item Details", "warning");
                else
                    isValid = true;
                return isValid;
            }

            function doSubmit() {
                const jobWorkNum      = "{{ $job_work_num }}";
                const jobWorkDate     = $("#job-work-date").val();
                const customerId      = $("#customerId").val();
                const vehicleNum      = $("#vehicle-num").val();
                const driverName      = $("#driver-name").val();
                const driverMobileNum = $("#driver-mobile-num").val();
                const count           = $('#table-items tbody tr').length;

                if(isValidated(jobWorkDate, customerId, vehicleNum, driverName, driverMobileNum, count)) {
                    const jobWorkData  = getJobWorkData();
                    const customerName = $("#customer").val();
                    const billingAddr  = $("#billing-address").val();
                    const deliveryAddr = $("#delivery-address").val();
                    const totAmt       = $("#tot_amt").text();                    
                    const roundOff     = $("#round-off").text();
                    const netAmt       = $("#net-amt").text();

                    $.ajax({
                        url: '{{ $action }}',
                        type: "POST",
                        data: {
                            job_work_num      :  jobWorkNum,
                            job_work_date     :  jobWorkDate,
                            customer_id       :  customerId,
                            customer_name     :  customerName,
                            billing_addr      :  billingAddr,
                            delivery_addr     :  deliveryAddr,
                            vehicle_num       :  vehicleNum,
                            driver_name       :  driverName,
                            driver_mobile_num :  driverMobileNum,
                            job_work_data     :  jobWorkData,
                            item_count        :  count,
                            tot_amt           :  totAmt,                            
                            round_off         :  roundOff,
                            net_amt           :  netAmt
                        },
                        dataType: 'json',
                        success: function(data) {
                            console.log(data);
                            Swal.fire({
                                title: 'Success!',
                                text: data.message,
                                icon: 'success'
                            })
                            .then(
                                function() {
                                    @if ($mode === 'New')
                                        askPrint(data.job_work_num);
                                    @else
                                        window.location.replace("{{ route('job-work.index') }}");
                                    @endif
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

            function askPrint(jobWorkNum) {
                Swal.fire({
                        title: 'Print?',
                        text: 'Do you want to print?',
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonText: 'Yes, print!',
                        cancelButtonText: 'No, close',
                    })
                    .then((result) => {
                        if (result.value)
                            doPrint(jobWorkNum);
                        else
                            window.location.replace("{{ route('job-work.index') }}");
                    });
            }

            function doPrint(jobWorkNum) {
                $.ajax({
                    url: "{{ route('delivery-challan.print') }}",
                    type: "POST",
                    data: { job_work_num: jobWorkNum },
                    dataType: 'html',
                    success: function (data) {
                        let printWindow = window.open('', '_blank');
                        printWindow.document.write(data);
                        printWindow.document.close();
                        printWindow.onload = function() {
                            printWindow.doPrint();
                            printWindow.close();
                            window.location.replace("{{ route('job-work.index') }}");
                        };
                    },
                    error: function (data, textStatus, errorThrown) {
                        Swal.fire("Sorry!", data.responseText, 'warning');
                    }
                });
            }
        });
    </script>
@endpush

@section('footerScript')
    <script src="{{ asset('plugins/sweet-alert2/sweetalert2.min.js') }}"></script>
    <script src="{{ asset('assets/pages/jquery.sweet-alert.init.js') }}"></script>
    <script src="{{ asset('assets/js/hide-menu.js') }}"></script>
@stop