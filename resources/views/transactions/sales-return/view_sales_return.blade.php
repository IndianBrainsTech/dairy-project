@extends('app-layouts.admin-master')

@section('title', 'Sales Return')

@section('headerStyle')
    <link href="{{ asset('plugins/sweet-alert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css">
    <style type="text/css">
        .color-blue {
            color: blue;
        }        
    </style>
@stop

@section('content')
    <div class="container-fluid">
        <!-- Page-Title -->
        <div class="row">
            <div class="col-sm-12">
                @component('app-components.breadcrumb-3')
                    @slot('title') View Sales Return @endslot
                    @slot('item1') Transactions @endslot
                    @slot('item2') Sales Return @endslot                   
                @endcomponent
            </div><!--end col-->
        </div>
        <!-- end page title end breadcrumb -->
 
        <div class="row"> 
            <div class="col-lg-12 mx-auto">
                <div class="card">
                    <div class="card-body">

                        <div class="row">
                            <div class="col-lg-12">
                                <div class="float-right">                                    
                                    <a id="prev" href="#" class="btn btn-info" style="padding-top:3px; padding-bottom:3px; margin-right:4px">Prev</a>
                                    <a id="next" href="#" class="btn btn-secondary pd3" style="padding-top:3px; padding-bottom:3px;">Next</a>                                    
                                </div>

                                <h6 class="font-14 mb-3" style="color:#fd3c97">Sales Return Data :</h6>
                                <div class="row">
                                    <div class="col-lg-5">
                                        <div class="row ml-2 mb-2">
                                            <div class="col-md-3">Txn ID</div>
                                            <div class="col-md-9 color-blue" id="txnId">{{ $txn_id }}</div>
                                        </div>
                                        <div class="row ml-2 mb-2">
                                            <div class="col-md-3">Txn Date</div>
                                            <div class="col-md-9 color-blue" id="txnDate">{{ displayDate($returnItems->txn_date) }}</div>
                                        </div>
                                        <div class="row ml-2 mb-2">
                                            <div class="col-md-3">Route</div>
                                            <div class="col-md-9 color-blue" id="route">{{ $returnItems->route->name }}</div>
                                        </div>
                                        <div class="row ml-2 mb-2">
                                            <div class="col-md-3">Customer</div>
                                            <div class="col-md-9 color-blue" id="customer">{{ $returnItems->customer->customer_name }}</div>
                                        </div>
                                    </div>
                                    <div class="col-lg-7">
                                        <div class="row mb-2">
                                            <div class="col-md-3">Invoice Number</div>
                                            <div class="col-md-9 color-blue" id="invNum">{{ $returnItems->invoice_num }}</div>
                                        </div>
                                        <div class="row mb-2">
                                            <div class="col-md-3">Invoice Type</div>
                                            <div class="col-md-9 color-blue" id="invType">{{ $returnItems->invoice_type }}</div>
                                        </div>
                                        <div class="row mb-2">
                                            <div class="col-md-3">Action</div>
                                            <div class="col-md-9 color-blue" id="action">{{ $returnItems->action }}</div>
                                        </div>
                                    </div>
                                </div>

                                <h6 class="font-14 my-3" style="color:#fd3c97">Data Table :</h6>
                                <div class="table-responsive dash-social">
                                    <table id="tableOrderedItems" class="table table-bordered table-sm" style="overflow-x:scroll">
                                        <thead class="thead-light">
                                            <tr>
                                                <th class="text-center">S.No</th>
                                                <th>Category</th>
                                                <th>Product</th>
                                                <th class="d-none">Data</th>
                                                <th class="text-center text-nowrap det-col">Order Qty</th>
                                                <th class="text-right det-col">Price</th>
                                                <th class="text-right det-col">Amount</th>
                                                <th class="text-right tax-col det-col">Tax</th>
                                                <th class="text-right tax-col det-col">Total</th>
                                                <th class="text-center text-nowrap">Return Qty</th>
                                                <th class="text-right">Amount</th>
                                                <th class="text-right tax-col">Tax</th>
                                                <th class="text-right tax-col">Total</th>
                                                <th class="text-center">Remarks</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        </tbody>
                                        <tfoot class="thead-light">
                                            <tr>                                                
                                                <th colspan="3" class="text-right">Total</th>
                                                <th class="det-col"></th>
                                                <th class="det-col"></th>
                                                <th id="totAmt" class='text-right det-col'></th>
                                                <th id="totTaxAmt" class='text-right tax-col det-col'></th>
                                                <th id="totNetAmt" class='text-right tax-col det-col'></th>
                                                <th></th>
                                                <th id="totReturn" class='text-right'></th>
                                                <th id="totTaxRet" class='text-right tax-col'></th>
                                                <th id="totNetRet" class='text-right tax-col'></th>
                                                <th id="finalReturn" class='text-left'></th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>

                                <div class="btn-group btn-group-toggle" data-toggle="buttons">
                                    <label class="btn btn-outline-purple" style="padding-top:3px;padding-bottom:3px">
                                        <input type="radio" id="rdoShort">Short
                                    </label>
                                    <label class="btn btn-outline-purple" style="padding-top:3px;padding-bottom:3px">
                                        <input type="radio" id="rdoDetail">Detail
                                    </label>
                                </div>
                            </div><!--end col-->
                        </div><!--end row-->                        
                    </div><!--end card-body-->
                </div><!--end card-->
            </div><!--end col-->
        </div><!--end row-->   
    </div>
@stop

@push('custom-scripts')
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.js"></script>
    <script>
        $(document).ready(function() {
            // Setup CSRF token for AJAX requests
            $.ajaxSetup({
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
            });

            let idList = "{{$id_list}}";
            let txnId;
            let invType;
            doInit();

            function doInit() {                
                let invoiceItems = @json($invoiceItems['invoiceItems']);
                let returnItems = @json($returnItems);
                txnId = "{{ $txn_id }}";
                invType = "{{ $returnItems->invoice_type }}";
                idList = idList.split(',');
                loadInvoiceItems(invoiceItems);
                loadReturnItems(returnItems);
                $('#rdoDetail').prop("checked", true).trigger('change');
            }

            function loadInvoiceItems(invoiceItems) {                
                let totAmt = 0;
                let totTaxAmt = 0;
                let totNetAmt = 0;                
                invoiceItems.forEach(function(item, index) {
                    let id = item.product_id;
                    let primUnit = item.support_units[0].unit_id;
                    let taxAmt = "";
                    let netAmt = "";
                    totAmt += item.amount;
                    if(invType == "Tax") {                        
                        taxAmt = item.tax_amt.toFixed(2);
                        netAmt = item.tot_amt.toFixed(2);
                        totTaxAmt += item.tax_amt;
                        totNetAmt += item.tot_amt;
                    }
                    const priceCellContent = item.category === "Regular" ? `${item.price} / ${getUnitName(primUnit)}` : "";                    
                    const newRow = $("<tr>")
                        .append(`<td class='text-center'>${index + 1}</td>`)
                        .append(`<td>${item.category}</td>`)
                        .append(`<td class='text-nowrap'>${item.product_name}</td>`)
                        .append(`<td id='data${id}' class='d-none' data-product-id='${id}' data-product='${item.product_name}' data-qty='${item.order_qty}' data-unit='${item.order_unit}' data-price='${item.price}' data-prim-unit='${primUnit}'></td>`)
                        .append(`<td class='text-center text-nowrap det-col'>${item.order_qty} ${getUnitName(item.order_unit)}</td>`)
                        .append(`<td class='text-right text-nowrap det-col' id='price${id}'>${priceCellContent}</td>`)
                        .append(`<td class='text-right det-col' id='amount${id}'>${item.amount.toFixed(2)}</td>`)
                        .append(`<td class='text-right tax-col det-col'>${taxAmt}</td>`)
                        .append(`<td class='text-right tax-col det-col'>${netAmt}</td>`)
                        .append(`<td class='text-center text-nowrap' id='retQty${id}'></td>`)
                        .append(`<td class='text-right' id='retAmt${id}'></td>`)
                        .append(`<td class='text-right tax-col' id='retTaxAmt${id}'></td>`)
                        .append(`<td class='text-right tax-col' id='retNetAmt${id}'></td>`)
                        .append(`<td id='remarks${id}' class='text-nowrap'></td>`);
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
                    let qtyStr = item.qty + " " + getUnitName(item.unit);
                    $(`#retQty${id}`).text(qtyStr);
                    $(`#retAmt${id}`).text(item.amount);
                    $(`#remarks${id}`).text(item.remarks);
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
            }

            function getUnitName(unitId) {
                var unitName = "";
                @foreach($units as $unit)
                    if(unitId == "{{$unit->id}}")
                        unitName = "{{$unit->display_name}}";
                @endforeach
                return unitName;
            }

            function getRoundOffString(roundOff) {
                return (roundOff > 0 ? '+' : '') + roundOff.toFixed(2);                
            }

            $('#rdoShort').change(function () {                
                if(this.checked) {
                    $("#tableOrderedItems .det-col").hide();                    
                    $("#tableOrderedItems tbody tr [id^=retQty]").each(function (index, element) {
                        var value = $(this).text();
                        if(!value) {
                            var $row = $(this).closest('tr');
                            $row.hide();
                        }
                    });
                }
            });

            $('#rdoDetail').change(function () {                
                if(this.checked) {
                    $("#tableOrderedItems .det-col").show();
                    if(invType == "Sales")
                        $("#tableOrderedItems .tax-col").hide();
                    $("#tableOrderedItems tbody").find("tr").show();
                }
            });

            $(document).on('keydown', function(event) {
                if (event.key === 'ArrowLeft') {
                    event.preventDefault();
                    $('#prev').click();
                }
                else if (event.key === 'ArrowRight') {
                    event.preventDefault();
                    $('#next').click();
                }
            });

            $('#prev').click(function () {                
                var index = idList.indexOf(txnId);
                if(index == 0) {
                    Swal.fire('Sorry!','No Previous Record!','warning');
                }
                else {
                    txnId = idList[index - 1];
                    loadSalesReturn();
                }
            });

            $('#next').click(function () {
                var index = idList.indexOf(txnId);
                if(index == idList.length-1) {
                    Swal.fire('Sorry!','No Next Record!','warning');
                }
                else {
                    txnId = idList[index + 1];
                    loadSalesReturn();
                }
            });

            function loadSalesReturn() {
                $.ajax({
                    url: "{{ route('sales-return.get') }}",
                    type: "GET",
                    data: { txn_id: txnId },
                    dataType: 'json',
                    success: function(data) {
                        txnId = data.txn_id;
                        invType = data.returnItems.invoice_type;
                        clearFields();
                        updateFields(data.returnItems);
                        loadInvoiceItems(data.invoiceItems.invoiceItems);
                        loadReturnItems(data.returnItems);

                        if ($('#rdoShort').prop('checked'))
                            $('#rdoShort').trigger('change');
                        else if ($('#rdoDetail').prop('checked'))
                            $('#rdoDetail').trigger('change');
                    },
                    error: function(data) {
                        Swal.fire('Sorry!', data.responseText, 'error');
                    }
                });
            }

            function clearFields() {
                $('#txnId').text('');
                $('#txnDate').text('');
                $('#route').text('');
                $('#customer').text('');
                $('#invNum').text('');
                $('#invType').text('');
                $('#action').text('');
                $('#tableOrderedItems tbody').empty();
                $('#totAmt').text('');
                $('#totTaxAmt').text('');
                $('#totNetAmt').text('');
                $('#totReturn').text('');
                $('#totTaxRet').text('');
                $('#totNetRet').text('');
                $('#finalReturn').text('');
            }
            
            function updateFields(data) {
                $('#txnId').text(txnId);
                $('#txnDate').text(data.txn_date);
                $('#route').text(data.route.name);
                $('#customer').text(data.customer.customer_name);
                $('#invNum').text(data.invoice_num);
                $('#invType').text(data.invoice_type);
                $('#action').text(data.action);
            }
        });
    </script>
@endpush

@section('footerScript')
    <script src="{{ asset('plugins/sweet-alert2/sweetalert2.min.js') }}"></script>
    <script src="{{ asset('assets/pages/jquery.sweet-alert.init.js') }}"></script>
@stop