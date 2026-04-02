@extends('app-layouts.admin-master')

@section('title', 'Edit Order')

@section('headerStyle')
    <link href="{{ asset('plugins/sweet-alert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('plugins/animate/animate.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('plugins/datatables/dataTables.bootstrap4.min.css') }}" rel="stylesheet" type="text/css" />
    <style type="text/css">
        .my-control {
            border: 1px solid #e8ebf3; 
            padding:6px;
            border-radius: 0.25rem;
            border-bottom: 1px solid #e8ebf3;
            transition: border-color 0s ease-out;
            background-color: #fff;
            margin-right: 16px;
        }
        .my-unit {
            border: 1px solid #e8ebf3;
            border-radius: 0.25rem;
            min-width: 65px;
            appearance: none;
            padding-left: 0.75rem;
        }        
        .color-darkblue {
            color: darkblue;
        }
        
        .ui-menu-item .ui-menu-item-wrapper.ui-state-active {
            background: #506ee4 !important;
            font-weight: bold !important;
            color: #ffffff !important;
        }
                
        .nav-tabs .nav-item .nav-link.active {
            background-color: #9ba7ca;
            border-bottom-color: #9ba7ca;
        }

        .empty-row {
            display: table-row !important;
        }

        .inv-table {
            font-size: 0.95em; 
            font-weight: 500;
        }
        .inv-table tr {
            height: 25px;
        }
        .inv-title {
            font-size: 14px;
        }
        .inv-label {
            padding: 4px 8px;            
            text-align: right;
            width: 90px;
        } 
        .inv-input {
            border: 1px solid #e8ebf3; 
            padding: 4px 8px;
            border-radius: 0.25rem;
            border-bottom: 1px solid #e8ebf3;
            transition: border-color 0s ease-out;
            background-color: #fff;
            text-align: right;
            width: 90px;
        }
        .inv-amt {
            color: darkBlue; 
            font-size: 1em;
        }

        .my-control:disabled, .my-control[readonly] {
            background-color: #f1f5fa;
        }
    </style>
@stop

@section('content')
    <div class="container-fluid">
        <!-- Page-Title -->
        <div class="row">
            <div class="col-sm-12">
                @component('app-components.breadcrumb-3')
                    @slot('title') Edit Order @endslot
                    @slot('item1') Transactions @endslot
                    @slot('item2') Sales @endslot
                @endcomponent
            </div><!--end col-->
        </div>
        <!-- end page title end breadcrumb -->
 
        <div class="row"> 
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body pb-0" style="min-height: 470px; padding-left:30px">
                        @if(Session::has('error'))
                            <div class="alert alert-danger" style="margin-top:20px">
                                {{ Session::get('error') }}
                            </div>
                        @endif

                        <form id="frmOrder">
                        @csrf
                            <div class="row">
                                <div class="col-md-9">
                                    <div class="row mb-2">
                                        <input type="text" value="{{$orderNum}}" class="my-control text-center" readonly>
                                        <input type="date" name="invoice_date" id="invoiceDate" value="{{$order->invoice_date}}" class="my-control">
                                        <div class="input-group mr-3" style="width:375px">
                                            <span class="input-group-prepend">
                                                <button type="button" class="btn btn-info"><i class="fas fa-search"></i></button>
                                            </span>
                                            <input type="text" id="txtCustomer" value="{{$order->customer->customer_name}}" class="form-control" placeholder="Customer">
                                            <input type="hidden" id="customer" value="{{$order->customer->id}}">
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <label for="billingAddress" class="my-text mr-3 mt-1 mb-0">Billing Address &ensp;&nbsp;</label>
                                        <select name="billingAddress" id="billingAddress" class="my-control mb-1" style="width:700px;color:#656d9a">
                                        </select>
                                        <label for="deliveryAddress" class="my-text mr-3 mt-1 mb-0">Delivery Address</label>
                                        <select name="deliveryAddress" id="deliveryAddress" class="my-control" style="width:700px;color:#656d9a">
                                        </select>
                                    </div>
                                    
                                    <div class="row mb-2">
                                        <select id="selectCategory" class="form-control mr-3" style="width:100px">
                                            <option value="Regular">Regular</option>
                                            <option value="Damage">Damage</option>
                                            <option value="Spoilage">Spoilage</option>
                                            <option value="Sample">Sample</option>
                                            <option value="Free">Free</option>
                                        </select>
                                        <div class="input-group mr-3" style="width:200px">
                                            <span class="input-group-prepend">
                                                <button type="button" class="btn btn-info"><i class="fas fa-search"></i></button>
                                            </span>
                                            <input type="text" id="txtProduct" class="form-control" placeholder="Product">
                                        </div>
                                        <input type="text" id="txtQty" class="form-control" style="width:80px" placeholder="Qty">
                                        <select id="selectUnit" class="form-control mr-3" style="width:80px">
                                            <option value="">Unit</option>
                                        </select>
                                        <input type="text" id="txtPrice" class="form-control mr-3" style="width:90px;text-align:center" placeholder="Price" readonly>
                                        <input type="text" id="txtStock" class="form-control mr-3" style="width:90px;text-align:center" placeholder="Stock" readonly>
                                        <button id="btnAdd" type="button" class="btn btn-info mr-2"><i class="fas fa-plus"></i></button>
                                        <button id="btnClear" type="button" class="btn btn-warning"><i class="fas fa-trash-alt"></i></button>
                                    </div>
    
                                    <div class="table-responsive dash-social mt-4 mb-2" style="margin-left:-12px;" >
                                        <table id="tableOrderedItems" class="table table-bordered table-sm">
                                            <thead class="thead-light">
                                                <tr>
                                                    <th class='text-center'>S.No</th>
                                                    <th>Category</th>
                                                    <th>Product</th>
                                                    <th>Qty</th>
                                                    <th class='text-right'>Price</th>
                                                    <th class='text-right'>Amount</th>
                                                    <th class='text-right'>Tax</th>
                                                    <th class='text-right'>Total</th>
                                                    <th class='text-right'>Discount</th>
                                                    <th class="d-none">Product ID</th>
                                                    <th class="d-none">Qty</th>
                                                    <th class="d-none">Unit</th>
                                                    <th class="d-none">Tax Type</th>
                                                    <th class='text-center'>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody style="height: 136px">
                                            </tbody>
                                            <tfoot class="thead-light">
                                                <tr>
                                                    <th colspan="5" class="text-center">Total</th>
                                                    <th id="totalAmt" class='text-right'></th>
                                                    <th id="totalTax" class='text-right'></th>
                                                    <th id="orderTotal" class='text-right'></th>
                                                    <th id="totalDisc" class='text-right'></th>
                                                    <th colspan="4" class="d-none"></th>
                                                    <th></th>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>

                                    <div class="row mb-3">
                                        <button type="button" id="btnReset" class="btn btn-secondary waves-effect waves-light btn-sm">
                                            <i class="mdi mdi-refresh mr-2"></i>Reset
                                        </button>                                        
                                        <div class="ml-auto">
                                            <button type="button" id="btnSubmit" class="btn btn-primary btn-sm px-3 mr-4" data-toggle="tooltip" data-placement="top" title="Alt+U">
                                                <i class="mdi mdi-shopping mr-2"></i>Update Order
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="card card-body p-0">
                                        <!-- Nav tabs -->
                                        <ul class="nav nav-tabs" role="tablist">
                                            <li class="nav-item">
                                                <a class="nav-link active" data-toggle="tab" href="#invoice" role="tab">Invoice</a>
                                            </li>
                                            <li class="nav-item">
                                                <a class="nav-link" data-toggle="tab" href="#pricelist" role="tab">Price List</a>
                                            </li>
                                        </ul>
        
                                        <!-- Tab panes -->
                                        <div class="tab-content">
                                            <div class="tab-pane active py-1" id="invoice" role="tabpanel" style="height:432px">
                                                <div class="card mb-2" id="card-sales">
                                                    <h5 class="card-header bg-secondary text-white inv-title mt-0 py-2">Sales Invoice</h5>
                                                    <div class="card-body p-2">
                                                        <table class="inv-table">
                                                            <tr>
                                                                <td width="75%">Invoice Amount</td>
                                                                <td id="salesInvAmt" class="inv-label"> </td>
                                                            </tr>
                                                            <tr>
                                                                <td id="salesTcsLabel">TCS</td>
                                                                <td id="salesTcs" class="inv-label"> </td>
                                                            </tr>
                                                            <tr>
                                                                <td>Discount</td>
                                                                <td><input type="text" id="salesDisc" class="inv-input" disabled></td>
                                                            </tr>
                                                            <tr>
                                                                <td>Round Off</td>
                                                                <td id="salesRoundOff" class="inv-label"> </td>
                                                            </tr>
                                                            <tr>
                                                                <td>Net Amount</td>
                                                                <td id="salesNetAmt" class='inv-label inv-amt'> </td>
                                                            </tr>
                                                        </table>
                                                    </div><!--end card-body-->
                                                </div>

                                                <div class="card mb-2" id="card-tax">
                                                    <h5 class="card-header bg-secondary text-white inv-title mt-0 py-2">Tax Invoice</h5>
                                                    <div class="card-body p-2">
                                                        <table class="inv-table">
                                                            <tr>
                                                                <td width="75%">Invoice Amount</td>
                                                                <td id="taxInvAmt" class="inv-label"> </td>
                                                            </tr>
                                                            <tr>
                                                                <td id="taxTcsLabel">TCS</td>
                                                                <td id="taxTcs" class="inv-label"> </td>
                                                            </tr>
                                                            <tr>
                                                                <td>Discount</td>
                                                                <td><input type="text" id="taxDisc" class="inv-input" disabled></td>
                                                            </tr>
                                                            <tr>
                                                                <td>Round Off</td>
                                                                <td id="taxRoundOff" class="inv-label"> </td>
                                                            </tr>
                                                            <tr>
                                                                <td>Net Amount</td>
                                                                <td id="taxNetAmt" class='inv-label inv-amt'> </td>
                                                            </tr>
                                                        </table>
                                                    </div><!--end card-body-->
                                                </div>

                                                <div class="card mb-3" id="card-total">
                                                    <div class="card-header mt-0 px-2 py-1">
                                                        <table class="inv-table">
                                                            <tr>
                                                                <td width="75%">Grand Total</td>
                                                                <td id="grandTotalAmt" class='inv-label inv-amt'> </td>
                                                            </tr>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="tab-pane" id="pricelist" role="tabpanel" style="max-height:432px; overflow-y:scroll">
                                                <table id="tablePriceList" class="table table-bordered table-sm mb-0">
                                                    <tr>
                                                        <th>Product</th>
                                                        <th class='text-right'>Price</th>
                                                        <th class='d-none'>Data</th>
                                                    </tr>
                                                    @foreach($products as $product)
                                                        <tr>
                                                            <td>{{$product->short_name}}</td>
                                                            <td class='text-right' id="prc{{$product->id}}"></td>
                                                            <th class='d-none' id="prod{{$product->id}}" data-tax="{{$product->tax_type}}" data-gst="{{$product->gst}}"></th>
                                                        </tr>
                                                    @endforeach
                                                </table>
                                            </div>
                                        </div>
                                    </div><!--end card-->
                                </div>
                            </div>

                            @if(Session::has('success'))
                                <div class="alert alert-success" style="width:100%;text-align:center;margin-top:20px">
                                    {{ Session::get('success') }}
                                </div>
                            @endif
                        </form>
                    </div><!--end card-body--> 
                </div><!--end card--> 
            </div> <!--end col-->
        </div><!--end row-->   
    </div>
@stop

@push('custom-scripts')
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.js"></script>
    <script>
        $(document).ready(function () {
            $.ajaxSetup({
                headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
            });           
            var creditlimit = 0;
            var oustd = 0;
            let CurrentTotal = 0;
            let units = new Map();
            let keys = "";
            @foreach($units as $unit)
                var key = '{{$unit->hot_key}}';
                var value = '{{$unit->id}}';
                units.set(key,value);
                keys = keys + key;
            @endforeach
            
            let products = new Map();
            @foreach($products as $product)
                var key = '{{$product->name}}';
                var value = '{{$product->id}}';
                products.set(key,value);
            @endforeach

            let customers = new Map();
            @foreach($customers as $customer)
                var key = '{{$customer->customer_name}}';
                var value = '{{$customer->id}}';
                customers.set(key,value);
            @endforeach

            let stock = @json($currentStock);   
            let productUnits = @json($productUnits);

            var priceList = [];
            var discountList = [];
            var tcsInfo;
            var resetFlag;
            doInit();
            
            $("#txtCustomer").autocomplete({
                source: autocompleteSource(customers),
                autoFocus: true,
                minLength: 0,
                select: function(event, ui) {
                    var name = ui.item.value;
                    var id = customers.get(name);
                    console.log("Selected ID: " + id + ", Name: " + name);
                    $("#customer").val(id).trigger('change');                    
                }
            });           

            let enterCount = 0;

            $("#txtCustomer").on('keydown', function(e) {
                if (e.key === "Enter") {
                    enterCount++;
                    
                    if (enterCount === 2) {
                        // Shift focus to the next input field on the second Enter press
                        $("#txtProduct").focus();  // Shift focus to the product field
                        enterCount = 0; // Reset counter after focus is shifted
                    }
                }
            });

            $("#txtCustomer").on('blur', function() {
                // Reset counter when focus leaves the input field
                enterCount = 0;
            });

            $('#customer').change(function () {                
                handleCustomerChange();
            });

            function handleCustomerChange() {
                // Clear all the price cells
                $('[id^="prc"]').text('');
                $("#salesTcsLabel").text("TCS");
                $("#taxTcsLabel").text("TCS");
                $("#btnClear").click();

                var customerId = $("#customer").val();
                if(customerId) {
                    let url = "{{ route('customers.data.billing', ':cust_id') }}".replace(':cust_id', customerId);
                    $.get(url, function (data) {
                        tcsInfo = data.tcs_info;
                        priceList = data.price_list;
                        discountList = data.discount_list;
                        creditlimit = data.credit_limit;     
                        
                        let addresses = data.addresses;
                        $('#billingAddress').empty();
                        $('#deliveryAddress').empty();
                        addresses.forEach(item => {
                            const addressText = `${item.address_lines}, ${item.district}, ${item.state}${item.pincode ? ' - ' + item.pincode : ''}`;
                            const option = new Option(addressText, item.id);
                            $('#billingAddress, #deliveryAddress').append(option);
                        });

                        // Loop through the priceList array
                        priceList.forEach(function(item) {
                            // Construct the id for the price cell
                            var priceCell = "#prc" + item.product_id;
                            // Update the price cell with the price value
                            var price = item.price + " / " + item.unit;
                            $(priceCell).text(price);
                        });

                        if(resetFlag) {
                            fetchAndLoadOrderItems();
                        }
                        else if($('#tableOrderedItems tbody tr:not(.empty-row)').length > 0) {
                            $('#salesDisc').val('');
                            $('#taxDisc').val('');
                            let orderItems = getOrderItems();
                            loadOrderItems(orderItems);
                        }

                        if(discountList != "")
                            showDiscountColumn();
                        else
                            hideDiscountColumn();                        
                    });

                    if($('#txtProduct').prop('disabled')) {
                        $('#txtProduct').prop('disabled', false);
                        $('#txtQty').prop('disabled', false);
                    }
                }
                else {                    
                    $('#salesDisc').val('');
                    $('#taxDisc').val('');
                    $('#txtProduct').prop('disabled', true);
                    $('#txtQty').prop('disabled', true);
                }
            }

            $("#txtProduct").autocomplete({
                source: autocompleteSource(products),
                autoFocus: true,
                minLength: 0,
                select: function(event, ui) {
                    var key = ui.item.value;
                    var id = products.get(key);
                    var price = getPriceString(id);
                    let stock = getCurrentStock(id);
                    updateUnits(id);
                    $("#txtPrice").val(price);
                    $("#txtStock").val(stock.stock_text);
                    $("#txtQty").focus();
                }
            });

            function autocompleteSource(sourceMap) {
                return function(request, response) {
                    let results = Array.from(sourceMap.entries()).map(function([key, value]) {
                        return {
                            label: key,
                            value: key
                        };
                    }).filter(function(item) {
                        return item.label.toLowerCase().startsWith(request.term.toLowerCase());
                    });
                    response(results);
                };
            }

            $("#txtQty").keypress(function(e){
                var key = String.fromCharCode(e.keyCode).toUpperCase();
                if(keys.includes(key)) {
                    var value = units.get(key);
                    $("#selectUnit").val(value);
                }
                if (e.keyCode == 13) {   // Enter
                    $("#btnAdd").trigger('click');
                }
                if (key.match(/[^0-9.]/g))
                    return false;
            });

            $("#btnAdd").click(function(e){
                var category = $('#selectCategory').val();
                var product = $('#txtProduct').val().trim();                
                var qty = $('#txtQty').val();
                var unitId = $('#selectUnit').val();
                // var normalizedProduct = product.replace(/ ML$/, "").replace(/ PKT$/, "").trim();
                // var productId = products.get(normalizedProduct);  
                var productId = products.get(product);              
                var convertQty = convertToPrimary(productId, qty, unitId);
                var cStock = getCurrentStock(productId);                
                if(product=="") alert('Please Select Product');
                else if(qty=="") alert('Please Enter Quantity');
                else if(unitId==null) alert('Please Select Unit');
                else if(convertQty > cStock.current_stock ) Swal.fire('Sorry!', product + ' is out of stock.', 'warning');
                else {                    
                    var productId = products.get(product);                    
                    if(productId) {
                        addOrUpdateRow(category, productId, product, qty, unitId);
                        $("#btnClear").trigger('click');
                        $("#txtProduct").focus();
                    }
                    else {
                        alert('Product Not Accepted! Please Select it from List');
                    }
                }
            });

            $("#btnClear").click(function(e){
                $('#selectCategory').val('Regular');
                $('#txtProduct').val('');
                $('#txtQty').val('');
                $('#txtPrice').val('');
                $('#txtStock').val('');
                $('#selectUnit').children().remove();
                $('#selectUnit').append(`<option value="">Unit</option>`);
            });

            $('body').on('click', '.edit_item', function (event) {
                var row = $(this).closest('tr');
                var category = $(row).find('td:nth-child(2)').text();
                var product = $(row).find('td:nth-child(3)').text();
                var productId = $(row).find('td:nth-child(10)').text();
                var qty = $(row).find('td:nth-child(11)').text();
                var unitId = $(row).find('td:nth-child(12)').text();
                var price = $(row).find('td:nth-child(5)').text();
                var convertQty = convertToPrimary(productId, qty, unitId);
                $("#selectCategory").val(category);
                var cStock = getCurrentStock(productId);
                updateUnits(productId);
                $("#txtProduct").val(product);
                $("#txtQty").val(qty);
                $("#txtPrice").val(price);                
                $("#selectUnit").val(unitId);
                $("#txtStock").val(cStock.stock_text);
                $("#txtQty").select();
            });

            $('body').on('click', '.delete_item', function (event) {
                $(this).closest('tr').remove();
                addEmptyRow();
                updateSerialNumbers();
                updateOrderTable();
            });

            $("#salesDisc").keypress(function(e){
                var key = String.fromCharCode(e.keyCode);
                if (e.keyCode == 13) {   // Enter
                    $("#salesDisc").blur();
                }
                if (key.match(/[^0-9.]/g))
                    return false;
            });

            $("#taxDisc").keypress(function(e){
                var key = String.fromCharCode(e.keyCode);
                if (e.keyCode == 13) {   // Enter
                    $("#taxDisc").blur();
                }
                if (key.match(/[^0-9.]/g))
                    return false;
            });

            $("#salesDisc").blur(function() {
                // Get and parse the invoice amount and TCS amount
                let invAmt = parseFloat($("#salesInvAmt").text());
                let tcsAmt = parseFloat($("#salesTcs").text());

                // Calculate the total amount
                let amount = invAmt + tcsAmt;

                // Get and parse the discount value
                let disc = parseFloat($(this).val());

                // If the discount is valid and less than the total amount
                if(!isNaN(disc)) {
                    if(disc < amount) {                
                        amount = amount - disc;
                        $("#salesDisc").val(`${disc.toFixed(2)}`);
                    }
                    else {                        
                        Swal.fire('Sorry!','Discount should not exceeds invoice amount','warning');
                        $("#salesDisc").val('');
                    }
                }

                // Calculate round-off and net amount
                let roundOff = Math.round(amount) - amount; 
                let netAmt = Math.round(amount);

                // Update the UI with the calculated values
                $("#salesRoundOff").text(getRoundOffString(roundOff));
                $("#salesNetAmt").text(`${netAmt.toFixed(2)}`);

                // Update grand total
                updateGrandTotal();
            });

            $("#taxDisc").blur(function() {
                // Get and parse the invoice amount and TCS amount
                let invAmt = parseFloat($("#taxInvAmt").text());
                let tcsAmt = parseFloat($("#taxTcs").text());

                // Calculate the total amount
                let amount = invAmt + tcsAmt;

                // Get and parse the discount value
                let disc = parseFloat($(this).val());
                
                // If the discount is valid and less than the total amount
                if(!isNaN(disc)) {
                    if(disc < amount) {
                        amount = amount - disc;
                        $("#taxDisc").val(`${disc.toFixed(2)}`);
                    }
                    else {
                        Swal.fire('Sorry!','Discount should not exceeds invoice amount','warning');
                        $("#taxDisc").val('');
                    }
                }

                // Calculate round-off and net amount
                let roundOff = Math.round(amount) - amount; 
                let netAmt = Math.round(amount);

                // Update the UI with the calculated values
                $("#taxRoundOff").text(getRoundOffString(roundOff));
                $("#taxNetAmt").text(`${netAmt.toFixed(2)}`);

                // Update grand total
                updateGrandTotal();
            });

            $("#btnReset").click(function(e){
                $("#invoiceDate").val('{{$order->invoice_date}}');
                $("#txtCustomer").val('{{$order->customer->customer_name}}');
                $("#customer").val('{{$order->customer->id}}');
                resetFlag = true;
                handleCustomerChange();
            });

            function doInit() {                
                $('#txtProduct').prop('disabled', true);
                $('#txtQty').prop('disabled', true);
                $("#card-sales").css({"display":"none"});
                $("#card-tax").css({"display":"none"});
                $("#card-total").css({"display":"none"});
                @can('update_order_discount')
                    $('#salesDisc').prop('disabled', false);
                    $('#taxDisc').prop('disabled', false);
                @endcan
                // restrictToTodayAndTomorrow();
                restrictDate('#invoiceDate');
                hideDiscountColumn();
                resetFlag = true;
                handleCustomerChange();
                getOutStandingAmount();
            }

            function getOutStandingAmount() {
                var id = $("#customer").val();
                console.log("Id : " + id); 
                let url = "{{ route('receipts.receivables', ':id') }}".replace(':id', id);
                
                $.get(url, function(data) {
                    let i = 1;
                    let invoices = data.invoices;
                    
                    invoices.forEach(function(item) {                        
                        oustd += item.outstanding;
                    });        
                    
                    console.log("Total Outstanding: " + oustd);
                }).fail(function() {
                    console.error("Failed to fetch receivables");
                });
            }

            $("#btnSubmit").click(function(e){
                var availableLimit = creditlimit - oustd;
                var custId = $("#customer").val();
                var invDate = $("#invoiceDate").val();                
                if(!custId) {
                    Swal.fire('Attention','Please Select Customer','error');
                }
                else if(!invDate) {
                    Swal.fire('Attention','Please Choose Date','error');
                }                
                else if($('#tableOrderedItems tbody tr:not(.empty-row)').length == 0){
                    Swal.fire('Sorry!','Please Enter Data for Order','warning');
                }
                else if(creditlimit != null && creditlimit != 0 && (creditlimit<oustd || creditlimit<CurrentTotal || availableLimit < CurrentTotal)){
                    Swal.fire('Sorry!', 
                            'Credit limit has been reached!' +
                            '<br>Credit Limit: ' + creditlimit.toLocaleString() +
                            '<br>Total Outstanding: ' + oustd.toLocaleString() +
                            '<br>Available Limit: ' + availableLimit.toLocaleString(),
                            'warning');
                     
                }
                else if(creditlimit == 0 && creditlimit != null && creditlimit<oustd)
                {
                    Swal.fire('Sorry!', 
                            'No Credit Limit Available!' +
                            '<br>Credit Limit: ' + creditlimit.toLocaleString() +
                            '<br>Total Outstanding: ' + oustd.toLocaleString() +
                            '<br>Available Limit: ' + availableLimit.toLocaleString(),
                            'warning');
                }
                else {
                    var billAddr = $("#billingAddress").val();
                    var deliAddr = $("#deliveryAddress").val();
                    let orderData = getOrderData();                    
                    let addressData = getAddressData();                    
                    $.ajax({
                        url: "{{ route('orders.update') }}",
                        type: "POST",
                        data: {
                            order_num    : '{{ $orderNum }}',
                            customer_id  : custId,
                            invoice_date : invDate,
                            address_data : addressData,
                            order_data   : orderData
                        },
                        dataType: 'json',
                        success: function (data) {
                            Swal.fire('Success!', data.message, 'success')
                                .then(() => window.location.replace("{{ route('orders.index') }}"));               
                        },
                        error: function (data, textStatus, errorThrown) {
                            Swal.fire('Sorry!', 'Unable to Update Order!', 'error');
                            console.log(data.responseText);
                        }
                    });
                }
            });

            function getAddressData() {
                return JSON.stringify([{
                    billing_id: $('#billingAddress').val(),
                    billing_address: $('#billingAddress option:selected').text(),
                    delivery_id: $('#deliveryAddress').val(),
                    delivery_address: $('#deliveryAddress option:selected').text()
                }]);
            }

            $(document).on('keydown', function(event) {
                if (event.altKey && event.key.toUpperCase() === 'U')
                    $('#btnSubmit').click();                
            });            

            function restrictToTodayAndTomorrow() {
                // Get today's date
                let today = new Date();
                let tomorrow = new Date();
                
                // Set tomorrow's date
                tomorrow.setDate(today.getDate() + 1);
                
                // Format the dates to 'YYYY-MM-DD' which is the required format for date input fields
                let todayFormatted = today.toISOString().split('T')[0];
                let tomorrowFormatted = tomorrow.toISOString().split('T')[0];
                
                // Set the min and max attributes to allow only today and tomorrow
                $('#invoiceDate').attr('min', todayFormatted);
                $('#invoiceDate').attr('max', tomorrowFormatted);
            }


            function updateUnits(productId) {
                $('#selectUnit').children().remove();
                var item = priceList.find(function(item) { return item.product_id == productId; });
                $("#selectUnit").append(new Option(item.unit, item.unit_id));
                var n = item.other_units.length;
                for(var i=0; i<n; i++) {
                    var unit = item.other_units[i];
                    $("#selectUnit").append(new Option(getUnitName(unit.unit_id), unit.unit_id));
                }
            }

            function getUnitName(unitId) {
                var unitName = "";
                @foreach($units as $unit)
                    if(unitId == "{{$unit->id}}")
                        unitName = "{{$unit->display_name}}";
                @endforeach
                return unitName;
            }

            function getPriceString(productId) {
                // Find the item in the priceList array that matches the productId                
                var item = priceList.find(function(item) { return item.product_id == productId; });                
                var price = item.price + " / " + item.unit;
                return price;
            }

            function getRoundOffString(roundOff) {
                let formattedRoundOff = (roundOff > 0 ? '+' : '') + roundOff.toFixed(2);
                return formattedRoundOff;
            }

            function getAmount(productId, qty, unitId) {
                var item = priceList.find(function(item) { return item.product_id == productId; });
                var amount = 0;
                if(item.unit_id != unitId) {
                    var n = item.other_units.length;
                    for(var i=0; i<n; i++) {
                        var unit = item.other_units[i];
                        if(unit.unit_id == unitId) {
                            qty = qty * unit.conversion;
                            break;
                        }
                    }
                }
                amount = qty * item.price;
                return { amount: amount, qty: qty };
            }

            function getDiscount(productId, qty) {
                // Check if the discountList is not empty and the productId exists in the list
                if (discountList && discountList[productId] !== undefined) {
                    let disc = discountList[productId];
                    disc = qty * disc;
                    return disc.toFixed(2);
                } 
                else {
                    return ""; // or return zero if the product ID is not found
                }                
            }

            function createOrUpdateRow(category, productId, product, qty, unitId, row = null) {
                var qtyStr = qty + " " + getUnitName(unitId);
                var priceStr = getPriceString(productId);
                var taxType = $("#prod"+productId).attr('data-tax');
                var amount = 0;
                var tax = "";
                var total = 0;
                var disc = "";

                if (category == "Regular") {
                    var result = getAmount(productId, qty, unitId);
                    amount = result.amount;
                    total = amount;
                    if (taxType == "Taxable") {
                        tax = $("#prod" + productId).attr('data-gst');
                        tax = amount * tax / 100;
                        total = amount + tax;
                        tax = tax.toFixed(2);
                    }
                    if (discountList != "") {
                        disc = getDiscount(productId, result.qty);
                    }
                }

                amount = amount.toFixed(2);
                total = total.toFixed(2);
                
                if (row == null) {
                    const record = `
                        <tr style='height:32px'>
                            <td class='text-center'></td>
                            <td>${category}</td>
                            <td>${product}</td>
                            <td>${qtyStr}</td>
                            <td class='text-right'>${priceStr}</td>
                            <td class='text-right'>${amount}</td>
                            <td class='text-right'>${tax}</td>
                            <td class='text-right'>${total}</td>
                            <td class='text-right'>${disc}</td>
                            <td class="d-none">${productId}</td>
                            <td class="d-none">${qty}</td>
                            <td class="d-none">${unitId}</td>
                            <td class='d-none'>${taxType}</td>
                            <td class='text-center'>
                                <a href="#" class="edit_item" class="mr-2"><i class="fas fa-edit text-info font-16"></i></a>
                                <a href="#" class="delete_item"><i class="fas fa-trash-alt text-warning font-16"></i></a>
                            </td>
                        </tr>`;
                    $("#tableOrderedItems tbody").append(record);
                } 
                else {
                    $(row).find('td:nth-child(4)').text(qtyStr);
                    $(row).find('td:nth-child(6)').text(amount);
                    $(row).find('td:nth-child(7)').text(tax);
                    $(row).find('td:nth-child(8)').text(total);
                    $(row).find('td:nth-child(11)').text(qty);
                    $(row).find('td:nth-child(12)').text(unitId);
                    if (discountList != "") {
                        $(row).find('td:nth-child(9)').text(disc);
                    }
                }
            }

            function addOrUpdateRow(category, productId, product, qty, unitId) {
                var row = findRow(category, productId);
                createOrUpdateRow(category, productId, product, qty, unitId, row);
                if (row == null) {
                    addEmptyRow();
                    updateSerialNumbers();
                }
                updateOrderTable();
            }

            function addEmptyRow() {
                // Delete empty row if exists
                $("#tableOrderedItems tbody tr.empty-row").last().remove();
                // Add new empty row, if space available
                if($('#tableOrderedItems tbody tr').length <= 6) {               
                    const emptyRow = `<tr class='empty-row'><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>`;
                    $("#tableOrderedItems tbody").append(emptyRow);
                }
            }

            function findRow(category, productId) {
                let foundRow = null;
                
                $("#tableOrderedItems tbody tr:not(.empty-row)").each(function() {
                    const rowCategory = $(this).find('td:nth-child(2)').text();
                    const rowProductId = $(this).find('td:nth-child(10)').text();
                    
                    if (rowCategory === category && rowProductId === productId) {
                        foundRow = $(this);
                        return false; // Exit the loop
                    }
                });
                
                return foundRow;
            }

            // Function to show the discount column
            function showDiscountColumn() {
                // Remove the d-none class from the header cell
                $('#tableOrderedItems th').eq(8).removeClass('d-none');
                
                // Remove the d-none class from each cell in the body
                $('#tableOrderedItems tbody tr').each(function() {
                    $(this).find('td').eq(8).removeClass('d-none');
                });
                
                // Remove the d-none class from the footer cell
                $('#totalDisc').removeClass('d-none');
            }

            // Function to hide the discount column
            function hideDiscountColumn() {
                // Add the d-none class from the header cell
                $('#tableOrderedItems th').eq(8).addClass('d-none');
                
                // Add the d-none class from each cell in the body
                $('#tableOrderedItems tbody tr').each(function() {
                    $(this).find('td').eq(8).addClass('d-none');
                });
                
                // Add the d-none class from the footer cell
                $('#totalDisc').addClass('d-none');
            }

            // Function to update the serial numbers (S.No) in the table
            function updateSerialNumbers() {
                // Update the serial number cell for each row
                $("#tableOrderedItems tbody tr:not(.empty-row)").each(function(index) {
                    $(this).find("td:first").text(index + 1);
                });
            }

            function updateOrderTable() {
                let totalAmount = 0;
                let totalTax = 0;
                let totalOrder = 0;
                let totalDisc = 0;

                $("#tableOrderedItems tbody tr:not(.empty-row)").each(function() {
                    const amount = parseFloat($(this).find('td:nth-child(6)').text()) || 0;
                    const tax = parseFloat($(this).find('td:nth-child(7)').text()) || 0;
                    const total = parseFloat($(this).find('td:nth-child(8)').text()) || 0;
                    const disc = parseFloat($(this).find('td:nth-child(9)').text()) || 0;

                    totalAmount += amount;
                    totalTax += tax;
                    totalOrder += total;
                    totalDisc += disc;
                });

                $("#totalAmt").text(`${totalAmount.toFixed(2)}`);
                CurrentTotal = totalAmount.toFixed(2);
                $("#totalTax").text(`${totalTax.toFixed(2)}`);
                $("#orderTotal").text(`${totalOrder.toFixed(2)}`);

                if(discountList != "") {
                    $("#totalDisc").text(`${totalDisc.toFixed(2)}`);
                    showDiscountColumn();
                }
                else {
                    hideDiscountColumn();
                }

                updateInvoicesTable();
            }

            function updateInvoicesTable() {
                let hasSalesInvoice = false;
                let hasTaxInvoice = false;
                let salesInvAmt = 0;
                let taxInvAmt = 0;
                let salesDisc = 0;
                let taxDisc = 0;

                $("#tableOrderedItems tbody tr:not(.empty-row)").each(function() {
                    const total = parseFloat($(this).find('td:nth-child(8)').text()) || 0;
                    const disc = parseFloat($(this).find('td:nth-child(9)').text()) || 0;
                    const taxType = $(this).find('td:nth-child(13)').text();
                    if(taxType == "Taxable") {
                        taxInvAmt += total;
                        taxDisc += disc;
                        hasTaxInvoice = true;
                    }
                    else {
                        salesInvAmt += total;
                        salesDisc += disc;
                        hasSalesInvoice = true;
                    }
                });
 
                if(hasSalesInvoice) {
                    updateSalesInvoiceTable(salesInvAmt, salesDisc);
                    $("#card-sales").css({"display":"block"});
                }
                else {
                    $("#card-sales").css({"display":"none"});
                }

                if(hasTaxInvoice) {
                    updateTaxInvoiceTable(taxInvAmt, salesInvAmt, taxDisc);
                    $("#card-tax").css({"display":"block"});
                }
                else {
                    $("#card-tax").css({"display":"none"});
                }

                updateGrandTotal();
            }

            function updateSalesInvoiceTable(invAmt, discount) {
                // Update the displayed invoice amount with two decimal places
                $("#salesInvAmt").text(invAmt.toFixed(2));
                
                let tcsAmt = 0;

                // Check the TCS status and calculate TCS amount accordingly
                if (tcsInfo.tcs_status === "TCS Applied") {
                    // Calculate TCS based on the invoice amount
                    tcsAmt = Math.floor(invAmt) * parseFloat(tcsInfo.tcs_percent) / 100;
                    $("#salesTcsLabel").text(`TCS (${tcsInfo.tcs_percent}%)`);
                } 
                else if (tcsInfo.tcs_status === "TCS Applicable") {
                    // Calculate excess amount over TCS limit and apply TCS if necessary
                    let newTurnover = tcsInfo.turnover + invAmt;
                    if (newTurnover > tcsInfo.tcs_limit) {
                        let excessAmt = newTurnover - tcsInfo.tcs_limit;
                        tcsAmt = Math.floor(excessAmt) * parseFloat(tcsInfo.tcs_percent) / 100;
                        $("#salesTcsLabel").text(`TCS (${tcsInfo.tcs_percent}%)`);
                    }
                }

                // Add TCS amount to the invoice amount
                invAmt += tcsAmt;

                // Add Discount to the invoice amount, if any
                if(discountList != "") {
                    $("#salesDisc").val(discount.toFixed(2));
                    invAmt -= discount;
                }                

                // Calculate round-off and net amount
                let roundOff = Math.round(invAmt) - invAmt;
                let netAmt = Math.round(invAmt);

                // Update the displayed TCS amount, round-off, and net amount with two decimal places
                $("#salesTcs").text(tcsAmt.toFixed(2));
                $("#salesRoundOff").text(getRoundOffString(roundOff));
                $("#salesNetAmt").text(netAmt.toFixed(2));
            }

            function updateTaxInvoiceTable(invAmt, salesInvAmt, discount) {
                // Update the displayed invoice amount with two decimal places
                $("#taxInvAmt").text(invAmt.toFixed(2));
                
                let tcsAmt = 0;

                // Check the TCS status and calculate TCS amount accordingly
                if (tcsInfo.tcs_status === "TCS Applied") {
                    // Calculate TCS based on the invoice amount
                    tcsAmt = Math.floor(invAmt) * parseFloat(tcsInfo.tcs_percent) / 100;
                    $("#taxTcsLabel").text(`TCS (${tcsInfo.tcs_percent}%)`);
                } 
                else if (tcsInfo.tcs_status === "TCS Applicable") {
                    // Calculate excess amount over TCS limit and apply TCS if necessary
                    let newTurnover = tcsInfo.turnover + salesInvAmt + invAmt;
                    if (newTurnover > tcsInfo.tcs_limit) {
                        let excessAmt = newTurnover - tcsInfo.tcs_limit;
                        tcsAmt = Math.floor(excessAmt) * parseFloat(tcsInfo.tcs_percent) / 100;
                        $("#taxTcsLabel").text(`TCS (${tcsInfo.tcs_percent}%)`);
                    }
                }

                // Add TCS amount to the invoice amount
                invAmt += tcsAmt;

                // Add Discount to the invoice amount, if any
                if(discountList != "") {
                    $("#taxDisc").val(discount.toFixed(2));
                    invAmt -= discount;
                }

                // Calculate round-off and net amount
                let roundOff = Math.round(invAmt) - invAmt;
                let netAmt = Math.round(invAmt);

                // Update the displayed TCS amount, round-off, and net amount with two decimal places
                $("#taxTcs").text(tcsAmt.toFixed(2));
                $("#taxRoundOff").text(getRoundOffString(roundOff));
                $("#taxNetAmt").text(netAmt.toFixed(2));
            }

            function updateGrandTotal() {
                if(($("#card-sales").is(":visible")) && ($("#card-tax").is(":visible"))) {
                    var grandTotal = parseFloat($("#salesNetAmt").text()) + parseFloat($("#taxNetAmt").text());
                    $("#grandTotalAmt").text(grandTotal.toFixed(2));
                    $("#card-total").css({"display":"block"});
                }
                else {
                    $("#card-total").css({"display":"none"});
                }
            }

            function getOrderData() {
                let orderItems = [];

                // Iterate over each row in the tbody
                $('#tableOrderedItems tbody tr:not(.empty-row)').each(function() {
                    // Collect each row's data into an object and push it into the orderItems array
                    orderItems.push({
                        category  : $(this).find('td:nth-child(2)').text(),
                        productId : $(this).find('td:nth-child(10)').text(),
                        qty       : $(this).find('td:nth-child(11)').text(),
                        unit      : $(this).find('td:nth-child(12)').text(),
                        qtyStr    : $(this).find('td:nth-child(4)').text(),
                        priceStr  : $(this).find('td:nth-child(5)').text(),
                        amount    : $(this).find('td:nth-child(6)').text(),
                        tax       : $(this).find('td:nth-child(7)').text(),
                        total     : $(this).find('td:nth-child(8)').text(),
                        discount  : $(this).find('td:nth-child(9)').text(),
                        taxType   : $(this).find('td:nth-child(13)').text()
                    });
                });

                // Collect discounts and tcs                
                let discounts = [$("#salesDisc").val(), $("#taxDisc").val()];
                let tcs = [$("#salesTcs").text(), $("#taxTcs").text()];

                // Log or process the data as needed
                console.log('Order Items:', orderItems);
                console.log('Discounts:', discounts);
                console.log('Tcs:', tcs);

                return {
                    orderItems: orderItems,
                    discounts: discounts,
                    tcs: tcs
                };
            }

            function fetchAndLoadOrderItems() {
                clearOrderInfo();
                let url = "{{ route('orders.get', ':orderNum') }}".replace(':orderNum', '{{ $orderNum }}');
                $.get(url, function (data) {
                    loadOrderItems(data.orderItems);
                    let order = data.order;
                    $("#billingAddress").val(order.address_data.billing_id);
                    $("#deliveryAddress").val(order.address_data.delivery_id);
                    $("#salesDisc").val(order.sales_disc);
                    $("#taxDisc").val(order.tax_disc);
                    $("#salesDisc").blur();
                    $("#taxDisc").blur();
                    resetFlag = false;
                    updateStock(data.orderItems);
                });
            }

            function getOrderItems() {
                let orderItems = [];                
                $('#tableOrderedItems tbody tr:not(.empty-row)').each(function() {
                    let row = {};
                    row.item_category = $(this).find('td:nth-child(2)').text().trim();
                    row.product_id = $(this).find('td:nth-child(10)').text().trim();
                    row.product_name = $(this).find('td:nth-child(3)').text().trim();
                    row.qty = $(this).find('td:nth-child(11)').text().trim();
                    row.unit_id = $(this).find('td:nth-child(12)').text().trim();
                    orderItems.push(row);
                });
                return orderItems;
            }

            function loadOrderItems(orderItems) {
                $('#tableOrderedItems tbody').empty();
                for (var i = 0; i < orderItems.length; i++) {
                    var category = orderItems[i].item_category;
                    var productId = orderItems[i].product_id;
                    var product = orderItems[i].product_name;
                    var qty = orderItems[i].qty;
                    var unitId = orderItems[i].unit_id;
                    createOrUpdateRow(category, productId, product, qty, unitId);
                }
                addEmptyRow();
                updateSerialNumbers();
                updateOrderTable();
            }

            function clearOrderInfo() {
                $("#btnClear").click();
                $('#tableOrderedItems tbody').empty();
                $("#totalAmt").text('');
                $("#totalTax").text('');
                $("#orderTotal").text('');
                $("#totalDisc").text('');
                $("#salesDisc").val('');
                $("#taxDisc").val('');
                $("#card-sales").css({"display":"none"});
                $("#card-tax").css({"display":"none"});
                $("#card-total").css({"display":"none"});
            }
            
            function convertToPrimary(productId, qty, unitId) {
                var primQty = 0;
                var productUnit = productUnits.filter(function(item) {
                     return item.product_id == productId; 
                });
                productUnit.forEach(function(unit) {
                    if(unit.prim_unit==1)
                        primQty = qty;
                    else if(unitId== unit.unit_id)
                        primQty = qty * unit.conversion;
                });
                return primQty;
            }

            function getStockByProductId(productId) {
                let stockArray = Object.values(stock);
                let stockItem = stockArray.find(item => item.item_id == productId);                
                return { ...stockItem };
            }

            function getCurrentStock(productId) {
                let stockItem = getStockByProductId(productId);
                let category = $('#selectCategory').val();
                let stockQty = 0;

                $("#tableOrderedItems tbody tr:not(.empty-row)").each(function() {
                    const rowCategory = $(this).find('td:nth-child(2)').text();
                    const rowProductId = parseInt($(this).find('td:nth-child(10)').text());
                    
                    if (rowCategory != category && rowProductId === productId) {
                        const qty = $(this).find('td:nth-child(11)').text();
                        const unitId = $(this).find('td:nth-child(12)').text();
                        stockQty += parseFloat(convertToPrimary(productId, qty, unitId));
                        console.log(qty + ", " + unitId + ", " + stockQty);
                    }
                });
                
                stockItem.current_stock -= stockQty;
                stockItem.stock_text = stockItem.current_stock + " " + getUnitName(stockItem.unit_id);                
                return stockItem;
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

            function updateStock(orderItems) {
                let stockArray = Object.values(stock);
                orderItems.forEach(item => {
                    let qty = convertToPrimary(item.product_id, item.qty, item.unit_id);                    
                    let stockItem = stockArray.find(s => s.item_id == item.product_id);                    
                    if (stockItem) {
                        stockItem.current_stock = (parseFloat(stockItem.current_stock) + qty).toFixed(2);
                    }
                });                
            }

        });                
    </script>
@endpush

@section('footerScript')
    <!-- Sweet-Alert  -->
    <script src="{{ asset('plugins/sweet-alert2/sweetalert2.min.js') }}"></script>
    <script src="{{ asset('assets/pages/jquery.sweet-alert.init.js') }}"></script>
    <!-- Required datatable js -->
    <script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables/dataTables.bootstrap4.min.js') }}"></script>    
    <!-- Responsive examples -->
    <script src="{{ asset('plugins/datatables/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables/responsive.bootstrap4.min.js') }}"></script>
    <script type="text/javascript">
        $(window).on('load', function() {
            $("body").toggleClass("enlarge-menu");
        });
    </script>
@stop