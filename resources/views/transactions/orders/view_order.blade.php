@extends('app-layouts.admin-master')

@section('title', 'View Order')

@section('headerStyle')
    <link href="{{ asset('plugins/sweet-alert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('assets/css/my-style.css') }}" rel="stylesheet" type="text/css">
@stop

@section('content')
    <div class="container-fluid">
        <!-- Page-Title -->
        <div class="row">
            <div class="col-sm-12">
                @component('app-components.breadcrumb-3')
                    @slot('title') View Order @endslot
                    @slot('item1') Transactions @endslot
                    @slot('item2') Orders @endslot
                @endcomponent
            </div><!--end col-->
        </div>
        <!-- end page title end breadcrumb -->
 
        <div class="row"> 
            <div class="col-lg-10 mx-auto">
                <div class="card">
                    <div class="card-body">

                        <!-- Order Info -->
                        <div class="px-2">
                            <div class="row my-2">
                                <div class="col-md-3">
                                    Order Number <br/>
                                    <div class="mt-2">Ordered on</div>
                                </div>
                                <div class="col-md-6">
                                    <div class="my-bold blue-text">{{ $order['order_num'] }}</div>
                                    <div class="mt-2">{{ $order['order_dt'] }}</div>
                                </div>
                                <div class="col-md-3 text-right">
                                    <a href="#" id="btnPrev" class="btn btn-info py-1 mr-2" data-toggle="tooltip" data-placement="top" title="Left Arrow (<)">Prev</a>
                                    <a href="#" id="btnNext" class="btn btn-secondary py-1" data-toggle="tooltip" data-placement="top" title="Right Arrow (>)">Next</a>
                                </div>
                            </div>
                           
                            <div class="row my-2">
                                <div class="col-md-3">Order Status</div>
                                <div class="col-md-9">
                                    {{ getOrderStatus($order['invoice_status']) }}
                                    @if($order['cancel_remarks'])
                                        &nbsp; [{{ $order['cancel_remarks'] }}]
                                    @endif
                                </div>
                            </div>
                            <div class="row my-2">
                                <div class="col-md-3">Invoice Date</div>
                                <div class="col-md-9">{{ $order['invoice_date'] }}</div>
                            </div>
                            <div class="row my-2">
                                <div class="col-md-3">Route</div>
                                <div class="col-md-9 my-bold">{{ $order['route'] }}</div>
                            </div>
                            <div class="row my-2">
                                <div class="col-md-3">Customer</div>
                                <div class="col-md-9 my-bold">{{ $order['customer'] }}</div>
                            </div>
                            <div class="row my-2">
                                <div class="col-md-3">Billing Address</div>
                                <div class="col-md-9">{{ $order['billing_address'] }}</div>
                            </div>
                            <div class="row my-2">
                                <div class="col-md-3">Delivery Address</div>
                                <div class="col-md-9">{{ $order['delivery_address'] }}</div>
                            </div>                            
                            <div class="row my-2">
                                <div class="col-md-3">Created by</div>
                                <div class="col-md-9">{{ $order['created_by'] }}</div>
                            </div>
                            @if($order['edited_by'])
                                <div class="row my-2">
                                    <div class="col-md-3">Edited by</div>
                                    <div class="col-md-9">{{ $order['edited_by'] }}</div>
                                </div>
                            @endif
                            @if($order['actioned_by'])
                                <div class="row my-2">
                                    <div class="col-md-3">{{ $order['invoice_status'] }} by</div>
                                    <div class="col-md-9">{{ $order['actioned_by'] }}</div>
                                </div>
                            @endif
                        </div>

                        <!-- Order Table -->
                        <h6 class="my-heading p-2 pt-3">Order Data :</h6>
                        <div class="table-responsive dash-social px-2">
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
                                        <th class='text-right odc'>Discount</th> <!-- Order Discount Column -->
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($orderItems as $orderItem)
                                        <tr>
                                            <td class='text-center'>{{ $loop->index + 1 }}</td>
                                            <td>{{ $orderItem->item_category }}</td>
                                            <td>{{ $orderItem->product_name }}</td>
                                            <td>{{ $orderItem->qty_str }}</td>
                                            <td class='text-right'>{{ $orderItem->price_str }}</td>
                                            <td class='text-right'>{{ getTwoDigitPrecision($orderItem->amount) }}</td>
                                            <td class='text-right'>{{ getTwoDigitPrecision($orderItem->tax,"") }}</td>
                                            <td class='text-right'>{{ getTwoDigitPrecision($orderItem->total) }}</td>
                                            <td class='text-right odc'>{{ getTwoDigitPrecision($orderItem->discount,"") }}</td>                                            
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr class="thead-light">
                                        <th colspan="5" class="text-center">Total</th>
                                        <th id="orderTotalAmt" class='text-right'></th>
                                        <th id="orderTotalTax" class='text-right'></th>
                                        <th id="orderTotalTotal" class='text-right'></th>
                                        <th id="orderTotalDisc" class='text-right odc'></th>
                                    </tr>
                                    <tr>
                                        <th colspan="7" class="text-right pr-2">TCS</th>
                                        <th id="orderTcs" class='text-right'></th>
                                        <th class='odc'></th>
                                    </tr>
                                    <tr>
                                        <th colspan="7" class="text-right pr-2">Discount</th>
                                        <th id="orderDisc" class='text-right'></th>
                                        <th class='odc'></th>
                                    </tr>
                                    <tr>
                                        <th colspan="7" class="text-right pr-2">Round Off</th>
                                        <th id="orderRoundOff" class='text-right'></th>
                                        <th class='odc'></th>
                                    </tr>
                                    <tr>
                                        <th colspan="7" class="text-right pr-2">Net Amount</th>
                                        <th id="orderNetAmt" class='text-right'></th>
                                        <th class='odc'></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>                        

                        <!-- Exempted Data Table -->
                        @if($order['has_exempted'])
                            <h6 class="my-heading p-2 pt-4">Exempted Sales Data :</h6>
                            <div class="table-responsive dash-social px-2">
                                <table id="tableExemptedItems" class="table table-bordered table-sm">
                                    <thead class="thead-light">
                                        <tr>
                                            <th class='text-center'>S.No</th>
                                            <th>Category</th>
                                            <th>Product</th>
                                            <th>Qty</th>
                                            <th class='text-right'>Price</th>
                                            <th class='text-right'>Amount</th>
                                            <th class='text-right d-none'>Tax</th>
                                            <th class='text-right d-none'>Total</th>
                                            <th class='text-right edc'>Discount</th> <!-- Exempted Discount Column -->
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php($sno = 1)
                                        @foreach($orderItems as $orderItem)                                        
                                            @if($orderItem->taxable == 0)
                                                <tr>
                                                    <td class='text-center'>{{ $sno++ }}</td>
                                                    <td>{{ $orderItem->item_category }}</td>
                                                    <td>{{ $orderItem->product_name }}</td>
                                                    <td>{{ $orderItem->qty_str }}</td>
                                                    <td class='text-right'>{{ $orderItem->price_str }}</td>
                                                    <td class='text-right'>{{ getTwoDigitPrecision($orderItem->amount) }}</td>
                                                    <td class='text-right d-none'></td>
                                                    <td class='text-right d-none'>{{ getTwoDigitPrecision($orderItem->total) }}</td>
                                                    <td class='text-right edc'>{{ getTwoDigitPrecision($orderItem->discount,"") }}</td>
                                                </tr>
                                            @endif
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                        <tr class="thead-light">
                                            <th colspan="5" class="text-center">Total</th>
                                            <th id="exemptedTotalAmt"   class='text-right'></th>
                                            <th id="exemptedTotalTax"   class='text-right d-none'></th>
                                            <th id="exemptedTotalTotal" class='text-right d-none'></th>
                                            <th id="exemptedTotalDisc"  class='text-right edc'></th>
                                        </tr>
                                        <tr>
                                            <th colspan="5" class="text-right pr-2">TCS</th>
                                            <th id="exemptedTcs" class='text-right'></th>
                                            <th class='d-none'></th>
                                            <th class='d-none'></th>
                                            <th class='edc'></th>
                                        </tr>
                                        <tr>
                                            <th colspan="5" class="text-right pr-2">Discount</th>
                                            <th id="exemptedDisc" class='text-right'></th>
                                            <th class='d-none'></th>
                                            <th class='d-none'></th>
                                            <th class='edc'></th>
                                        </tr>
                                        <tr>
                                            <th colspan="5" class="text-right pr-2">Round Off</th>
                                            <th id="exemptedRoundOff" class='text-right'></th>
                                            <th class='d-none'></th>
                                            <th class='d-none'></th>
                                            <th class='edc'></th>
                                        </tr>
                                        <tr>
                                            <th colspan="5" class="text-right pr-2">Net Amount</th>
                                            <th id="exemptedNetAmt" class='text-right'></th>
                                            <th class='d-none'></th>
                                            <th class='d-none'></th>
                                            <th class='edc'></th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        @endif

                        <!-- Taxable Table -->
                        @if($order['has_taxable'])
                            <h6 class="my-heading p-2 pt-3">Tax Sales Data :</h6>
                            <div class="table-responsive dash-social px-2">
                                <table id="tableTaxableItems" class="table table-bordered table-sm">
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
                                            <th class='text-right tdc'>Discount</th> <!-- Taxable Discount Column -->
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php($sno = 1)
                                        @foreach($orderItems as $orderItem)
                                            @if($orderItem->taxable == 1)
                                                <tr>
                                                    <td class='text-center'>{{ $sno++ }}</td>
                                                    <td>{{ $orderItem->item_category }}</td>
                                                    <td>{{ $orderItem->product_name }}</td>
                                                    <td>{{ $orderItem->qty_str }}</td>
                                                    <td class='text-right'>{{ $orderItem->price_str }}</td>
                                                    <td class='text-right'>{{ getTwoDigitPrecision($orderItem->amount) }}</td>
                                                    <td class='text-right'>{{ getTwoDigitPrecision($orderItem->tax,"") }}</td>
                                                    <td class='text-right'>{{ getTwoDigitPrecision($orderItem->total) }}</td>
                                                    <td class='text-right tdc'>{{ getTwoDigitPrecision($orderItem->discount,"") }}</td>
                                                </tr>
                                            @endif
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                        <tr class="thead-light">
                                            <th colspan="5" class="text-center">Total</th>
                                            <th id="taxableTotalAmt"   class='text-right'></th>
                                            <th id="taxableTotalTax"   class='text-right'></th>
                                            <th id="taxableTotalTotal" class='text-right'></th>
                                            <th id="taxableTotalDisc"  class='text-right tdc'></th>
                                        </tr>
                                        <tr>
                                            <th colspan="7" class="text-right pr-2">TCS</th>
                                            <th id="taxableTcs" class='text-right'></th>
                                            <th class='tdc'></th>
                                        </tr>
                                        <tr>
                                            <th colspan="7" class="text-right pr-2">Discount</th>
                                            <th id="taxableDisc" class='text-right'></th>
                                            <th class='tdc'></th>
                                        </tr>
                                        <tr>
                                            <th colspan="7" class="text-right pr-2">Round Off</th>
                                            <th id="taxableRoundOff" class='text-right'></th>
                                            <th class='tdc'></th>
                                        </tr>
                                        <tr>
                                            <th colspan="7" class="text-right pr-2">Net Amount</th>
                                            <th id="taxableNetAmt" class='text-right'></th>
                                            <th class='tdc'></th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        @endif

                        @if($order['invoice_status'] == "Not Generated")
                            <hr/>
                            <div class="text-center">
                                <button type="button" class="btn btn-dark px-3 py-1 mr-3" id="btnEdit">Edit Order</button>                                
                                <button type="button" class="btn btn-danger px-3 py-1 ml-3" data-toggle="modal" data-animation="bounce" data-target="#modal_form">Cancel Order</button>
                            </div>
                        @endif
                    </div><!--end card-body-->
                </div><!--end card-->
            </div><!--end col-->
        </div><!--end row-->
    </div>

    <!-- Start of Order Cancel Modal -->
    <div class="modal fade" id="modal_form" tabindex="-1" role="dialog" aria-labelledby="modalOrderCancelLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm" role="document">
            <div class="modal-content" style="min-width:400px">
                <div class="modal-header">
                    <h5 class="modal-title mt-0" id="modal_title">Order Cancel</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group row mb-0">
                                    <textarea id="remarks" rows="3" class="form-control mx-2" placeholder="Reason / Remarks for Cancellation"></textarea>
                                </div>
                            </div>
                        </div>   
                    </div>
                    <div class="modal-footer">
                        <input type="button" class="btn btn-secondary mr-2" data-dismiss="modal" value="Close" />
                        <input type="button" class="btn btn-primary ml-3" id="btnOrderCancel" value="Submit"/>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- End of Order Cancel Modal -->
@stop

@push('custom-scripts')
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.js"></script>
    <script src="{{ asset('assets/js/helper.js') }}"></script>
    <script>
    $(document).ready(function () {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        
        doInit();

        function doInit() {
            let salesTcs  = {{ $order['sales_tcs'] ?? 0 }};
            let salesDisc = {{ $order['sales_disc'] ?? 0 }};
            let taxTcs    = {{ $order['tax_tcs'] ?? 0 }};
            let taxDisc   = {{ $order['tax_disc'] ?? 0 }};
            calculateTotals("#tableOrderedItems", "#order", salesTcs, taxTcs, salesDisc, taxDisc, "odc");
            @if($order['has_exempted'])
                calculateTotals("#tableExemptedItems", "#exempted", salesTcs, 0, salesDisc, 0 , "edc");
            @endif
            @if($order['has_taxable'])
                calculateTotals("#tableTaxableItems", "#taxable", 0, taxTcs, 0, taxDisc, "tdc");
            @endif
        }

        function calculateTotals(tableSelector, outputPrefix, salesTcs, taxTcs, salesDisc, taxDisc, discCol) {
            let totalAmt = 0, totalTax = 0, totalTotal = 0, totalDisc = 0;

            $(`${tableSelector} tbody tr`).each(function() {
                const amount = parseFloat($(this).find('td:nth-child(6)').text()) || 0;
                const tax    = parseFloat($(this).find('td:nth-child(7)').text()) || 0;
                const total  = parseFloat($(this).find('td:nth-child(8)').text()) || 0;
                const disc   = parseFloat($(this).find('td:nth-child(9)').text()) || 0;

                totalAmt += amount;
                totalTax += tax;
                totalTotal += total;
                totalDisc += disc;
            });

            $(`${outputPrefix}TotalAmt`).text(totalAmt.toFixed(2));
            $(`${outputPrefix}TotalTax`).text(totalTax.toFixed(2));
            $(`${outputPrefix}TotalTotal`).text(totalTotal.toFixed(2));
            $(`${outputPrefix}TotalDisc`).text(totalDisc.toFixed(2));

            const tcs      = salesTcs + taxTcs;
            const discount = salesDisc + taxDisc;
            const amount   = totalTotal + tcs - discount;
            const roundOff = Math.round(amount) - amount;
            const netAmt   = Math.round(amount);

            $(`${outputPrefix}Tcs`).text(tcs.toFixed(2));
            $(`${outputPrefix}Disc`).text(discount.toFixed(2));
            $(`${outputPrefix}RoundOff`).text(getRoundOffString(roundOff));
            $(`${outputPrefix}NetAmt`).text(netAmt.toFixed(2));
            
            if (!totalDisc) {
                $(`.${discCol}`).hide();
            }
        }

        let orders = "{{$orders}}";
        let ordersArray = orders.split(',');

        $(document).on('keydown', function(event) {
            if (event.key === 'ArrowLeft') {
                $('#btnPrev').click();
            }
            else if (event.key === 'ArrowRight') {
                $('#btnNext').click();
            }
        });

        $('#btnPrev').on("click", function () {
            var index = ordersArray.indexOf("{{$order['order_num']}}");
            if(index == 0) {
                Swal.fire('Sorry!','No Previous Order!','warning');
            }
            else {
                var orderNum = ordersArray[index - 1];
                showOrder(orderNum);
            }
        });

        $('#btnNext').on("click", function () {
            var index = ordersArray.indexOf("{{$order['order_num']}}");
            if(index == ordersArray.length-1) {
                Swal.fire('Sorry!','No Next Order!','warning');
            }
            else {
                var orderNum = ordersArray[index + 1];
                showOrder(orderNum);
            }
        });

        function showOrder(orderNum) {
            // Create a form element
            var form = $('<form>', {
                'method': 'POST',
                'action': "{{ route('orders.show') }}"
            });

            // Add CSRF token
            var csrfToken = $('meta[name="csrf-token"]').attr('content');
            form.append($('<input>', {
                'type': 'hidden',
                'name': '_token',
                'value': csrfToken
            }));

            // Add the data as hidden inputs
            form.append($('<input>', {
                'type': 'hidden',
                'name': 'order_num',
                'value': orderNum
            }));

            form.append($('<input>', {
                'type': 'hidden',
                'name': 'orders',
                'value': orders
            }));

            // Append the form to the body and submit it
            $('body').append(form);
            form.submit();
        }

        $('#btnOrderCancel').on("click", function () {
            let remarks = $("#remarks").val();
            if(!remarks) {
                Swal.fire('Attention','Please Enter Reason for Order Cancel','warning');
                return;
            }
            else {
                $.ajax({
                    url: "{{ route('orders.cancel') }}",
                    type: "GET",
                    data: { 
                        order_num : "{{$order['order_num']}}",
                        remarks   : remarks
                    },
                    dataType: 'json',
                    success: function (data) {
                        Swal.fire('Success!', data.message, 'success')
                            .then(() => window.location.replace("{{ route('orders.index') }}"));
                    },
                    error: function (data) {
                        showAlert(data.responseText);
                        console.log(data.responseText);
                    }
                });
            }
        });

        $('#btnEdit').on("click", function () {
            let orderNum = "{{ $order['order_num'] }}";

            // Create a form element
            var form = $('<form>', {
                'method': 'POST',
                'action': "{{ route('orders.edit') }}"
            });

            // Add CSRF token
            var csrfToken = $('meta[name="csrf-token"]').attr('content');
            form.append($('<input>', { 'type': 'hidden', 'name': '_token', 'value': csrfToken }));

            // Add the data as hidden inputs
            form.append($('<input>', { 'type': 'hidden', 'name': 'order_num', 'value': orderNum }));
            
            // Append the form to the body and submit it
            $('body').append(form);
            form.submit();
        });
    });
</script>
@endpush

@section('footerScript')
    <!-- Sweet-Alert  -->
    <script src="{{ asset('plugins/sweet-alert2/sweetalert2.min.js') }}"></script>
    <script src="{{ asset('assets/pages/jquery.sweet-alert.init.js') }}"></script>
@stop