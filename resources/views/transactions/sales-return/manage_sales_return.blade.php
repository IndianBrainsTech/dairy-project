@extends('app-layouts.admin-master')

@section('title', 'Sales Return')

@section('headerStyle')
    <link href="{{ asset('plugins/sweet-alert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('plugins/animate/animate.css') }}" rel="stylesheet" type="text/css">
    <style type="text/css">
        .my-text {
            font-size: 14px;
            padding: 4px;
        }
        .my-control {
            border: 1px solid #e8ebf3;
            padding:6px;
            border-radius: 0.25rem;
            border-bottom: 1px solid #e8ebf3;
            transition: border-color 0s ease-out;
            background-color: #fff;
            margin-right:20px;
        }

        .ui-menu-item .ui-menu-item-wrapper.ui-state-active {
            background: #506ee4 !important;
            font-weight: bold !important;
            color: #ffffff !important;
        }
    </style>
@stop

@section('content')
    <div class="container-fluid">
        <!-- Page-Title -->
        <div class="row">
            <div class="col-sm-12">
                @component('app-components.breadcrumb-2')
                    @slot('title') Sales Return @endslot
                    @slot('item1') Transactions @endslot                    
                @endcomponent
            </div><!--end col-->
        </div>
        <!-- end page title end breadcrumb -->
  
        <div class="row"> 
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                                    
                        <div class="row mb-3">
                            <div class="ml-2">
                                <label class="my-text mb-0">Route</label><br/>
                                <select id="route" class="my-control">
                                    <option value="0">Select Route</option>
                                    @foreach($routes as $route)
                                        <option value="{{$route->id}}">{{$route->name}}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="ml-2">
                                <label class="my-text mb-0">Customer <small class="text-danger font-13">*</small></label><br/>
                                <select id="customer" class="my-control">
                                    <option value="">Select Customer</option>
                                    @foreach($customers as $customer)
                                        <option value="{{$customer->id}}">{{$customer->customer_name}}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="ml-2">
                                <label class="my-text mb-0">Invoice Number <small class="text-danger font-13">*</small></label><br/>
                                <div class="input-group mr-3" style="width:200px">
                                    <span class="input-group-prepend">
                                        <button type="button" class="btn btn-info"><i class="fas fa-search"></i></button>
                                    </span>
                                    <input type="text" id="invoice" class="form-control" placeholder="Invoice Number">
                                </div>                                
                            </div>

                            <div class="ml-2">
                                <label class="my-text mb-0">&nbsp;</label><br/>
                                <input type="button" id="btnReturnAll" class="btn btn-info btn-sm" value="Return All" />
                            </div>
                        </div>

                        <div id="salesReturnForm">
                            <div class="row mb-1 ml-1">
                                <label class="my-text">Invoice Date: </label>
                                <label class="my-text mr-3" style="color:blue" id="invDate">Date</label>
                                <label class="my-text ml-2">Invoice Type: </label>
                                <label class="my-text" style="color:blue" id="invType">Type</label>
                            </div>

                            <div class="table-responsive dash-social">
                                <table id="tableOrderedItems" class="table table-bordered table-sm" style="overflow-x:scroll">
                                    <thead class="thead-light">
                                        <tr>
                                            <th class="text-center">S.No</th>
                                            <th>Category</th>
                                            <th>Product</th>
                                            <th class="d-none">Data</th>
                                            <th class="text-center">Order Qty</th>
                                            <th class="text-right">Price</th>
                                            <th class="text-right">Amount</th>
                                            <th class="text-right tax-col">Tax</th>
                                            <th class="text-right tax-col">Total</th>
                                            <th class="text-center" style="width:160px">Return Qty <small class="text-danger font-13">*</small></th>
                                            <th class="text-right">Amount</th>
                                            <th class="text-right tax-col">Tax</th>
                                            <th class="text-right tax-col">Total</th>
                                            <th class="text-center" style="width:200px">Remarks</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                    <tfoot class="thead-light">
                                        <tr>
                                            <th colspan="5" class="text-right pr-4">Total</th>
                                            <th id="totAmt" class='text-right'></th>
                                            <th id="totTaxAmt" class='text-right tax-col'></th>
                                            <th id="totNetAmt" class='text-right tax-col'></th>
                                            <th></th>
                                            <th id="totReturn" class='text-right'></th>
                                            <th id="totTaxRet" class='text-right tax-col'></th>
                                            <th id="totNetRet" class='text-right tax-col'></th>
                                            <th id="finalReturn" class='text-left'></th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>

                            <div class="row">
                                <div class="col-sm-12">
                                    <h5 class="mt-0 header-title">Action</h5>
                                    <div class="radio radio-primary">
                                        <input type="radio" name="radio" id="rdoReplacement" value="Replacement">
                                        <label for="rdoReplacement"> Replacement (Give Replacement on Next Order) </label>
                                    </div>
                                    <div class="radio radio-primary">
                                        <input type="radio" name="radio" id="rdoRefund" value="Refund">
                                        <label for="rdoRefund"> Refund (Amount Refund to Customer) </label>
                                    </div>
                                    <div class="radio radio-primary">
                                        <input type="radio" name="radio" id="rdoDeduction" value="Deduction">
                                        <label for="rdoDeduction"> Deduction (Amount Deducted by Customer on Delivery) </label>
                                    </div>
                                    <div class="radio radio-primary">
                                        <input type="radio" name="radio" id="rdoReturnOrder" value="ReturnOrder">
                                        <label for="rdoReturnOrder"> Return against Order (Order Not Dispatched) </label>
                                    </div>
                                </div>
                            </div>

                            <div class="row mt-3">
                                <div class="col-sm-12">
                                    <div class="form-group row float-right mr-2">
                                        <input type="button" id="btnClear" class="btn btn-secondary mr-4" value="Clear" />
                                        <input type="button" id="btnSubmit" class="btn btn-primary mr-3" value="Submit"/>
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
    <script>
        $(document).ready(function() {
            // Setup CSRF token for AJAX requests
            $.ajaxSetup({
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
            });

            let units = new Map();
            let keys = "";
            @foreach($units as $unit)
                var key = '{{$unit->hot_key}}';
                var value = '{{$unit->id}}';
                units.set(key,value);
                keys = keys + key;
            @endforeach
            
            $('#salesReturnForm').hide();
            $('#btnReturnAll').hide();
            
            $('#route').change(function () {
                $('#customer').children(`option:not(:first)`).remove();
                $('#invoice').val('');
                $('#btnReturnAll').hide();
                $('#salesReturnForm').hide();
                clearFields();

                var id = $(this).val();
                if(id) {
                    let url = "{{ route('customers.get.route', ':id') }}".replace(':id', id);
                    $.get(url, function (data) {
                        var customers = data.customers;
                        for (var i=0; i<customers.length; i++) {
                            $('#customer').append(new Option(customers[i].customer_name, customers[i].id));
                        }
                    });
                }
            });            

            let invoices = [];
            let recentInvoices = [];

            $('#customer').change(function () {
                $('#invoice').val('');
                $('#btnReturnAll').hide();
                clearFields();
                var customerId = $(this).val();
                if(customerId) {
                    let url = "{{ route('sales-return.invoices', ':cust_id') }}".replace(':cust_id', customerId);
                    $.get(url, function (data) {
                        invoices = data.invoices;
                        recentInvoices = data.recentInvoices;
                        // console.log("Invoices : " + invoices);
                        // console.log("RecentInvoices : " + recentInvoices);
                        // $("#invoice").autocomplete('option', 'source', function(request, response) {
                        //     const source = $('#invoice').val() ? invoices : recentInvoices;
                        //     response($.ui.autocomplete.filter(source, request.term));
                        // });
                    });
                }
            });

            $("#invoice").autocomplete({
                source: function(request, response) {
                    const source = $('#invoice').val() ? invoices : recentInvoices;
                    response($.ui.autocomplete.filter(source, request.term));
                    if($('#invoice').val() === '') {
                        $('#btnReturnAll').hide();
                        clearFields();
                    }
                },
                autoFocus: true,
                minLength: 0,
                select: function(event, ui) {
                    var invoiceNum = ui.item.value;
                    loadData(invoiceNum);
                }
            });

            // Trigger autocomplete when the input field gains focus
            $('#invoice').focus(function() {
                $(this).autocomplete("search", $(this).val());
            });

            let invType;
            function loadData(invoiceNum) {
                $('#salesReturnForm').find('input, select, button').prop('disabled', false);
                clearFields();
                if(invoiceNum) {
                    let url = "{{ route('sales-return.invoices.items', ':inv_num') }}".replace(':inv_num', invoiceNum);
                    $.get(url, function (data) {
                        $('#invDate').text(data.invoiceDate);
                        $('#invType').text(data.invoiceType + " Invoice");
                        invType = data.invoiceType;
                        loadInvoiceItems(data.invoiceItems);
                        let returnItems = data.returnItems;
                        if(returnItems)
                            loadReturnItems(returnItems);
                        else
                            $('#btnReturnAll').show();
                    });
                    $('#salesReturnForm').show();
                }
                else {
                    $('#btnReturnAll').hide();
                }
            }

            function loadInvoiceItems(invoiceItems) {
                let totAmt = 0;
                let totTaxAmt = 0;
                let totNetAmt = 0;
                let tax = "";
                invoiceItems.forEach(function(item, index) {
                    let id = item.product_id;
                    let units = constructUnits(id, item.support_units);
                    let primUnit = item.support_units[0].unit_id;
                    let taxAmt = "";
                    let netAmt = "";
                    totAmt += item.amount;
                    if(invType == "Tax") {
                        tax = getTaxPercentage(item.tax_amt, item.amount);
                        totTaxAmt += item.tax_amt;
                        totNetAmt += item.tot_amt;
                        taxAmt = item.tax_amt.toFixed(2);
                        netAmt = item.tot_amt.toFixed(2);
                    }
                    const priceCellContent = item.category === "Regular" ? `${item.price} / ${getUnitName(primUnit)}` : "";                    
                    const newRow = $("<tr>")
                        .append(`<td class='text-center'>${index + 1}</td>`)
                        .append(`<td>${item.category}</td>`)
                        .append(`<td>${item.product_name}</td>`)
                        .append(`<td id='data${id}' class='d-none' data-product-id='${id}' data-product='${item.product_name}' data-qty='${item.order_qty}' data-unit='${item.order_unit}' data-price='${item.price}' data-prim-unit='${primUnit}' data-tax='${tax}'></td>`)
                        .append(`<td class='text-center'>${item.order_qty} ${getUnitName(item.order_unit)}</td>`)
                        .append(`<td class='text-right' id='price${id}'>${priceCellContent}</td>`)
                        .append(`<td class='text-right' id='amount${id}'>${item.amount.toFixed(2)}</td>`)
                        .append(`<td class='text-right tax-col'>${taxAmt}</td>`)
                        .append(`<td class='text-right tax-col'>${netAmt}</td>`)
                        .append(`<td class='text-center'><input type='text' id='qty${id}' class='my-control qty-input text-center mr-0' style='width:60px'>${units}</td>`)                                
                        .append(`<td class='text-right' id='retAmt${id}'></td>`)
                        .append(`<td class='text-right tax-col' id='retTaxAmt${id}'></td>`)
                        .append(`<td class='text-right tax-col' id='retNetAmt${id}'></td>`)
                        .append(`<td><input type='text' id='remarks${id}' class='my-control mr-0' style='width:200px'></td>`);
                    $("#tableOrderedItems tbody").append(newRow);
                });
                $('#totAmt').text(totAmt.toFixed(2));
                if(invType == "Tax") {
                    $('#totTaxAmt').text(totTaxAmt.toFixed(2));
                    $('#totNetAmt').text(totNetAmt.toFixed(2));
                    $("#tableOrderedItems .tax-col").show();
                }
                else {
                    $("#tableOrderedItems .tax-col").hide();
                }
            }

            function loadReturnItems(returnItems) {
                let returnData = returnItems.return_data;                
                returnData.forEach(function(item) {
                    let id = item.product_id;                    
                    $(`#qty${id}`).val(item.qty);
                    $(`#unit${id}`).val(item.unit);
                    $(`#retAmt${id}`).text(item.amount);
                    $(`#remarks${id}`).val(item.remarks);
                    if(invType == "Tax") {
                        $(`#retTaxAmt${id}`).text(item.tax_amt);
                        $(`#retNetAmt${id}`).text(item.net_amt);
                    }
                });

                let total = returnItems.amount;
                let taxAmt = returnItems.tax_amt;
                let totalAmt = returnItems.total_amt;
                let roundOff = returnItems.round_off;
                let finalAmt = returnItems.net_amt;
                $("#totReturn").text(total.toFixed(2));
                if(invType == "Tax") {
                    $("#totTaxRet").text(taxAmt.toFixed(2));
                    $("#totNetRet").text(totalAmt.toFixed(2));
                }
                if(roundOff) {
                    let finalValue = getRoundOffString(roundOff) + " = " + finalAmt.toFixed(2);
                    $("#finalReturn").text(finalValue);
                }
                else {
                    $("#finalReturn").text('');
                }

                let action = returnItems.action;
                $(`input[name="radio"][value="${action}"]`).prop('checked', true);

                $('#salesReturnForm').find('input, select, button').prop('disabled', true);
                $('#btnReturnAll').hide();
            }

            function constructUnits(id, units) {
                let select = `<select id="unit${id}" class="my-control mr-0" style="width:70px">`;

                units.forEach(unit => {
                    select += `<option value="${unit.unit_id}" data-conversion="${unit.conversion}">${getUnitName(unit.unit_id)}</option>`;
                });

                select += `</select>`;
                return select;
            }

            function getUnitName(unitId) {
                var unitName = "";
                @foreach($units as $unit)
                    if(unitId == "{{$unit->id}}")
                        unitName = "{{$unit->display_name}}";
                @endforeach
                return unitName;
            }

            function getTaxPercentage(taxAmt, amount) {
                let taxPercentage = "";
                if(taxAmt) {
                    taxPercentage = (taxAmt / amount) * 100;
                    taxPercentage = taxPercentage.toFixed(2);
                }
                return taxPercentage;
            }

            $('#tableOrderedItems tbody').on('keypress', '.qty-input', function(e){
                var key = String.fromCharCode(e.keyCode).toUpperCase();
                if(keys.includes(key)) {
                    var value = units.get(key);
                    var id = $(this).attr('id').replace('qty','');
                    $(`#unit${id}`).val(value);
                    calcReturnQtyValue(id);
                }
                if (e.keyCode == 13) {   // Enter
                    e.preventDefault();
                    var id = $(this).attr('id').replace('qty','');
                    $(`#remarks${id}`).focus();                    
                }
                if (key.match(/[^0-9.]/g)) {
                    return false;
                }
            });

            $('#tableOrderedItems tbody').on('keypress', '[id^=remarks]', function(e){
                if (e.keyCode == 13) {   // Enter
                    e.preventDefault();

                    // Find the current row
                    var $currentRow = $(this).closest('tr');
                    var $nextRow = $currentRow.next('tr'); // Get the next row

                    if ($nextRow.length) { // Check if next row exists
                        // Find the `qty` input in the next row
                        var nextQtyInput = $nextRow.find('input[id^=qty]').first();
                        if (nextQtyInput.length) {
                            nextQtyInput.focus(); // Set focus to the next `qty` input
                        }                        
                    }
                    else {
                        $('#rdoReplacement').click();
                        $('#rdoReplacement').focus();
                    }
                }
            });

            $("#tableOrderedItems tbody").on('change', '[id^=qty]', function() {
                let id = $(this).attr('id').replace('qty','');
                calcReturnQtyValue(id);
            });

            $("#tableOrderedItems tbody").on('change', '[id^=unit]', function() {
                let id = $(this).attr('id').replace('unit','');
                calcReturnQtyValue(id);
            });

            function calcReturnQtyValue(id) {
                let qty = $(`#qty${id}`).val();
                if(!isNaN(qty) && qty!=0) {
                    let price = $(`#data${id}`).data('price');
                    let primUnit = $(`#data${id}`).data('prim-unit');
                    let selUnit = $(`#unit${id}`).val();
                    if(selUnit != primUnit) {
                        var selectedOption = $(`#unit${id}`).find('option:selected');
                        var conversion = selectedOption.data('conversion');
                        qty *= conversion;
                    }

                    let amount = qty * price;
                    $(`#retAmt${id}`).text(amount.toFixed(2));

                    if(invType == "Tax") {
                        let tax = $(`#data${id}`).data('tax');
                        let taxAmt = amount * tax / 100;
                        let netAmt = amount + taxAmt;
                        $(`#retTaxAmt${id}`).text(taxAmt.toFixed(2));
                        $(`#retNetAmt${id}`).text(netAmt.toFixed(2));
                    }
                    else {
                        $(`#retTaxAmt${id}`).text('');
                        $(`#retNetAmt${id}`).text('');    
                    }
                }
                else {
                    $(`#retAmt${id}`).text('');
                    $(`#retTaxAmt${id}`).text('');
                    $(`#retNetAmt${id}`).text('');
                }
                calcReturnAmountTotal();
            }

            let roundOff;
            let finalAmt;

            function calcReturnAmountTotal() {
                let totReturn = 0;
                let totTaxRet = 0;
                let totNetRet = 0;

                $('#tableOrderedItems tbody [id^=retAmt]').each(function() {
                    var amount = $(this).text();
                    if (amount) {
                        totReturn += Number(amount);
                        if(invType == "Tax") {
                            var id = $(this).attr('id').replace('retAmt','');
                            let taxAmt = $(`#retTaxAmt${id}`).text();
                            let netAmt = $(`#retNetAmt${id}`).text();
                            totTaxRet += Number(taxAmt);
                            totNetRet += Number(netAmt);
                        }
                    }
                });

                $("#totReturn").text(totReturn.toFixed(2) || "");

                let totalAmt = totReturn;
                if(invType == "Tax") {
                    totalAmt = totNetRet;
                    $("#totTaxRet").text(totTaxRet.toFixed(2) || "");
                    $("#totNetRet").text(totNetRet.toFixed(2) || "");
                }

                roundOff = Math.round(totalAmt) - totalAmt;
                finalAmt = Math.round(totalAmt);

                if(roundOff) {
                    let finalValue = getRoundOffString(roundOff) + " = " + finalAmt.toFixed(2);
                    $("#finalReturn").text(finalValue);
                }
                else {
                    $("#finalReturn").text('');
                }
            }

            function getRoundOffString(roundOff) {
                let formattedRoundOff = (roundOff > 0 ? '+' : '') + roundOff.toFixed(2);
                return formattedRoundOff;
            }

            $('#btnReturnAll').click(function () { 
                $('#tableOrderedItems tbody tr').each(function() {
                    let id = $(this).find('[id^=data]').attr('id').replace('data','');

                    // Set the value of the qty textbox with data-qty
                    let qty = $(`#data${id}`).data('qty');
                    $(`#qty${id}`).val(qty);

                    // Set the selected value of the unit select control with data-unit
                    let unit = $(`#data${id}`).data('unit');
                    $(`#unit${id}`).val(unit);

                    calcReturnQtyValue(id);
                });
                $('#rdoReturnOrder').click();
            });

            $('#btnClear').click(function () {
                $('#tableOrderedItems tbody [id^=qty]').val('');
                $('#tableOrderedItems tbody [id^=retAmt]').text('');
                $('#tableOrderedItems tbody [id^=retTaxAmt]').text('');
                $('#tableOrderedItems tbody [id^=retNetAmt]').text('');
                $('#tableOrderedItems tbody [id^=remarks]').val('');
                $('#tableOrderedItems tbody [id^=unit]').each(function() {
                    $(this).prop('selectedIndex', 0);
                });
                $("#totReturn").text('');
                $("#totTaxRet").text('');
                $("#totNetRet").text('');
                $("#finalReturn").text('');
                $('input[name="radio"]').prop('checked', false);
                roundOff = finalAmt = 0;
            });

            function clearFields() {                
                $('#invDate').text('');
                $('#invType').text('');
                $('#tableOrderedItems tbody').empty();
                $("#totAmt").text('');
                $("#totTaxAmt").text('');
                $("#totNetAmt").text('');
                $("#totReturn").text('');
                $("#totTaxRet").text('');
                $("#totNetRet").text('');
                $("#finalReturn").text('');
                $('input[name="radio"]').prop('checked', false);                
                roundOff = finalAmt = 0;
            }

            function isValidated() {
                let invoice = $('#invoice').val();
                if (!invoice) {
                    Swal.fire('Sorry!', 'Invoice Data Not Found', 'warning');
                    return false;
                }

                var hasValue = $('#tableOrderedItems tbody [id^=qty]').filter(function() {
                    var val = $(this).val().trim();
                    return val !== "" && parseFloat(val) !== 0;
                }).length > 0;

                if (!hasValue) {
                    Swal.fire('Sorry!', 'No Data Entered', 'warning');
                    return false;
                }

                let isValid = true;
                $('#tableOrderedItems tbody tr').each(function() {
                    let id = $(this).find('[id^=data]').attr('id').replace('data', '');
                    let retQty = $(`#qty${id}`).val();
                    let retUnit = $(`#unit${id}`).val();
                    if (retQty) {
                        let ordQty = $(`#data${id}`).data('qty');
                        let ordUnit = $(`#data${id}`).data('unit');
                        let oqty = getPrimaryQty(id, ordQty, ordUnit);
                        let rqty = getPrimaryQty(id, retQty, retUnit);
                        if (rqty > oqty) {
                            let product = $(`#data${id}`).data('product');
                            Swal.fire('Sorry!', `Return Qty Exceeds Order Qty for '${product}'`, 'warning');
                            isValid = false;
                            return false;
                        }
                    }
                });

                if (!isValid) {
                    return false;
                }

                if ($('input[name="radio"]:checked').length === 0) {
                    Swal.fire('Sorry!', 'Please Select Action for Returned Items!', 'warning');
                    return false;
                }

                return true;
            }

            function getPrimaryQty(id, qty, unit) {                
                let primaryQty = qty; // Initialize with the original qty                
                $(`#unit${id}`).find('option').each(function() {
                    var unitId = $(this).val();
                    var conversion = $(this).data('conversion');                    
                    if(unit == unitId) {                
                        primaryQty = conversion ? (qty * conversion) : qty;                        
                        return false; // Exit the loop
                    }
                });
                return primaryQty; // Return the calculated qty
            }

            function getReturnData() {
                let returnData = [];

                $('#tableOrderedItems tbody tr').each(function() {
                    const row = $(this);
                    const qty = row.find('td:nth-child(10) [id^=qty]').val().trim();
                    if(qty !== "" && parseFloat(qty) !== 0)
                    {
                        const productId = row.find('td:nth-child(4)').data('product-id');
                        const unit = row.find('td:nth-child(10) [id^=unit]').val();
                        const amount = row.find('td:nth-child(11)').text();
                        const remarks = row.find('td:nth-child(14) [id^=remarks]').val();

                        if(invType == "Sales") {
                            returnData.push({
                                product_id: productId,
                                qty: qty,
                                unit: unit,
                                amount: amount,
                                remarks: remarks
                            });
                        }
                        else { // invType = "Tax"
                            const taxAmt = row.find('td:nth-child(12)').text();
                            const netAmt = row.find('td:nth-child(13)').text();
                            returnData.push({
                                product_id: productId,
                                qty: qty,
                                unit: unit,
                                amount: amount,
                                tax_amt: taxAmt,
                                net_amt: netAmt,
                                remarks: remarks
                            });
                        }                        
                    }
                });

                return returnData;
            }

            $('#btnSubmit').click(function () {
                if(isValidated()) {                    
                    const custId = $("#customer").val();
                    const invoiceNum = $("#invoice").val();
                    const data = getReturnData();
                    const returnData = JSON.stringify(data);
                    const amount = $("#totReturn").text();
                    const taxAmt = $("#totTaxRet").text();
                    const totalAmt = $("#totNetRet").text();
                    const action = $('input[name="radio"]:checked').val();
                   
                    $.ajax({
                        url: "{{ route('sales-return.store') }}",
                        type: "POST",
                        data: {
                            cust_id:      custId,
                            invoice_num:  invoiceNum,
                            invoice_type: invType,
                            return_data:  returnData,
                            amount:       amount,
                            tax_amt:      taxAmt,
                            total_amt:    totalAmt,
                            round_off:    roundOff.toFixed(2),
                            net_amt:      finalAmt.toFixed(2),
                            action:       action,
                            stockData:    data
                        },
                        dataType: 'json',
                        success: function(data) {
                            Swal.fire({
                                title: 'Success!',
                                text: data.message,
                                type: 'success'
                            })
                            .then(
                                function() {
                                    clearFields();
                                    $('#invoice').val('');
                                }
                            ); 
                        },
                        error: function(data) {
                            Swal.fire('Sorry!', data.responseText, 'error');
                        }
                    });
                }                                
            });
        });
    </script>
@endpush

@section('footerScript')
    <script src="{{ asset('plugins/sweet-alert2/sweetalert2.min.js') }}"></script>
    <script src="{{ asset('assets/pages/jquery.sweet-alert.init.js') }}"></script>
    <script type="text/javascript">
        $(window).on('load', function() {
            $("body").toggleClass("enlarge-menu");
        });
    </script>
@stop