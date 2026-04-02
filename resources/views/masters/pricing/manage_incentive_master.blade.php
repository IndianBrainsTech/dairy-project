@extends('app-layouts.admin-master')

@section('title', 'Incentive Master')

@section('headerStyle')
    <link href="{{ asset('plugins/sweet-alert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('plugins/animate/animate.css') }}" rel="stylesheet" type="text/css">    
    <link href="{{ asset('plugins/datatables/dataTables.bootstrap4.min.css') }}" rel="stylesheet" type="text/css" />        
    <style type="text/css">
        .table-container {            
            overflow-y: scroll; /* Enable vertical scrolling */
        }

        thead th {
            position: sticky; /* Stick the header row while scrolling */
            top: 0;
        }

        .table-container input.num-input {
            color: black;
            max-width: 80px;
            font-weight: 500;
            padding-top: 1px;
            padding-bottom: 1px;
            height: 30px;
            text-align: center;
        }

        .my-text {
            font-size: 13px;            
        }

        .my-control {
            border: 1px solid #e8ebf3; 
            padding: 6px;
            border-radius: 0.25rem;
            border-bottom: 1px solid #e8ebf3;
            transition: border-color 0s ease-out;
            background-color: #fff;
            margin-right:10px;
            width:100%;
        }

        th.sorting_disabled:before,
        th.sorting_disabled:after {
            display: none !important; /* Removes the sort icon */
        }
    </style>
@stop

@php
    if(!isset($incentive_data)) {
        $isEdit = false;
        $title = "Add Incentive Master";
        $action = route('incentive-master.store');
        $effect_date = "";
        $narration = "";
        $applicable_customers = "";
        $incentive_type = "";
        $incentive_rate = "";
        $incentive_data = [];
        $slab_data = [];
    }
    else {
        $isEdit = true;
        $title = "Edit Incentive Master";
        $action = route('incentive-master.edit', ['id' => $id]);
        if($slab_data == null) $slab_data = [];
    }
@endphp

@section('content')
    <div class="container-fluid">
        <!-- Page-Title -->
        <div class="row">
            <div class="col-sm-12">
                @component('app-components.breadcrumb-4')
                    @slot('title') {{$title}} @endslot
                    @slot('item1') Masters @endslot
                    @slot('item2') Deals & Pricing @endslot
                    @slot('item3') Incentive Masters @endslot                    
                @endcomponent
            </div>
        </div>
        <!-- end page title end breadcrumb -->

        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                        
                            <div class="col-lg-6 pr-4">                                
                                <h4 class="header-title mt-0 mb-3">Master Data</h4>
                                <div class="row">
                                    <div class="col-sm-12">

                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="txn_id">Txn ID</label>
                                                    <input type="text" class="form-control" name="txn_id" value="{{$txn_id}}" readonly>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="txn_date">Txn Date</label>
                                                    <input type="date" class="form-control" name="txn_date" value="{{$txn_date}}" readonly>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="effect_date">Effect Date <small class="text-danger font-13">*</small></label>
                                                    <input type="date" class="form-control" id="effect_date" name="effect_date" value="{{$effect_date}}" min="2025-04-01" >
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="form-group row">
                                                    <label for="narration" class="col-sm-3 col-form-label text-right">Narration <small class="text-danger font-13">*</small></label>
                                                    <div class="col-sm-9">
                                                        <input class="form-control" type="text" id="narration" name="narration"  value="{{$narration}}" >
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                                <br/>

                                <div class="row">
                                    <div class="col-sm-12">
                                        <h4 class="header-title mt-0">
                                            Applicable Customer
                                            <button type="button" id="addCustomers" class="btn btn-outline-primary waves-effect waves-light ml-3 pr-3" data-toggle="modal" data-animation="bounce" data-target="#modal_customers" style="height:32px; padding-top:1px; padding-bottom:1px"><i class="mdi mdi-plus-circle-outline mr-1"></i>Add</button>
                                        </h4>
                                        <div class="table-responsive table-container" style="max-height:250px">
                                            <table id="tableCustomers" class="table table-bordered table-sm">
                                                <thead class="thead-light" style="height:36px">
                                                    <tr>
                                                        <th class="text-center">S.No</th>
                                                        <th class="d-none">ID</th>
                                                        <th>Customer</th>
                                                        <th>Action</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div> <!-- End of Left Half -->

                            <div class="col-lg-6">
                                <div class="row">
                                    <div class="col-sm-12">
                                        <h4 class="header-title mt-0">Incentive Type
                                            <div class="btn-group btn-group-toggle pl-4" data-toggle="buttons">
                                                <label class="btn btn-outline-purple" style="padding-top:3px;padding-bottom:3px">
                                                    <input type="radio" name="rdoIncentive" id="rdoIncFixed">Fixed
                                                </label>
                                                <label class="btn btn-outline-purple" style="padding-top:3px;padding-bottom:3px">
                                                    <input type="radio" name="rdoIncentive" id="rdoIncSlab">Slab-wise
                                                </label>
                                            </div>
                                        </h4>
                                    </div>
                                </div>

                                <div id="divIncFixed" class="mt-2 mb-4">
                                    <label class="my-text mr-3">Incentive Rate <small class="text-danger font-13">*</small></label>
                                    <input type="text" id="txtIncRate" class="my-control text-center ml-2 num-input" style="width:80px">
                                </div>

                                <div id="divIncSlab" class="my-2">
                                    <div>
                                        <label class="my-text mr-1">From</label>
                                        <input type="text" id="slabFrom" class="my-control num-input text-center" style="width:70px" value="1" readonly>
                                        <label class="my-text mr-1">To</label>
                                        <input type="text" id="slabTo" class="my-control num-input text-center" style="width:70px">
                                        <label class="my-text mr-1">Rate <small class="text-danger font-13">*</small></label>
                                        <input type="text" id="slabRate" class="my-control num-input text-center" style="width:70px">
                                        <button id="addSlab" class="btn btn-outline-primary waves-effect waves-light btn-sm"><span class="fas fa-plus px-1"></span></button>
                                    </div>
                                    <div class="row">
                                        <div class="col-sm-8">
                                            <div class="table-responsive mt-3">
                                                <table id="tableSlab" class="table table-bordered table-sm text-center">
                                                    <thead class="thead-light">
                                                        <tr>
                                                            <th>From</th>
                                                            <th>To</th>
                                                            <th>Rate</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                    </tbody>
                                                </table>                                        
                                            </div>
                                        </div>
                                        <div class="col-sm-4 mb-3 pl-0 d-flex align-items-end justify-content-start full-height">
                                            <button id="deleteSlab" class="btn btn-outline-danger waves-effect waves-light btn-sm px-1 py-0"><span class="fas fa-trash-alt px-1"></span></button>
                                        </div>
                                    </div>
                                    
                                </div><!-- End of divIncSlab -->

                                <div class="row">
                                    <div class="col-sm-12">
                                        <h4 class="header-title mt-0">
                                            Incentive Data
                                            <button type="button" id="setupIncentive" class="btn btn-outline-primary waves-effect waves-light ml-4 pr-3" data-toggle="modal" data-animation="bounce" data-target="#modal_incentive" style="height:32px; padding-top:1px; padding-bottom:1px"><i class="mdi mdi-settings-outline mr-1"></i>Setup</button>
                                        </h4>
                                        <div class="table-responsive table-container" style="max-height:300px">
                                            <table id="tableIncentive" class="table table-bordered table-sm text-center">
                                                <thead class="thead-light">
                                                    <tr>
                                                        <th>S.No</th>
                                                        <th class="d-none">ID</th>
                                                        <th class='text-left'>Product</th>
                                                        <th>Inc Rate</th>
                                                        <th>Lk Qty</th>
                                                        <th>Lk Amt</th>                                                        
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row mt-3">
                                    <div class="col-sm-12">
                                        <button type="button" id="submit" class="btn btn-primary float-right">Submit</button>
                                    </div>
                                </div>
                            </div><!-- End of Right Half -->

                        </div><!--end row--> 
                    </div><!--end card-body--> 
                </div><!--end card--> 
            </div> <!--end col-->
        </div><!--end row--> 
    </div><!-- container -->
        
    <!-- Start of Customer Modal -->
    <div class="modal fade" id="modal_customers" tabindex="-1" role="dialog" aria-labelledby="modalCustomerLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title mt-0" id="modal_title">Choose and Add Customer(s)</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="form_customers">                    
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="table-responsive">
                                    <table id="datatable" class="table table-sm table-bordered nowrap" style="overflow-y:auto; width:100%">
                                        <thead class="thead-light">
                                            <tr>
                                                <th></th>
                                                <th class='d-none'>ID</th>
                                                <th>Customer</th>
                                                <th>Group</th>
                                                <th>Route</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($customers as $customer)
                                                <tr>
                                                    <td class="text-right">
                                                        <div class="checkbox checkbox-primary checkbox-single">
                                                            <input type="checkbox" name="cust[{{$customer->id}}]" id="cust{{$customer->id}}" value="{{$customer->customer_name}}">
                                                            <label style="margin-bottom:0px"></label>
                                                        </div>
                                                    </td>
                                                    <td class='d-none'>{{ $customer->id }}</td>
                                                    <td>{{ $customer->customer_name }}</td>
                                                    <td>{{ $customer->group }}</td>
                                                    <td>{{ $customer->route->name }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>   
                    </div>
                    <div class="modal-footer"> 
                        <button class="btn btn-secondary mr-3" data-dismiss="modal">Close</button>
                        <button id="btnAddCustomers" class="btn btn-primary px-3" data-dismiss="modal">Add</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- End of Customer Modal -->

    <!-- Start of Incentive Modal -->
    <div class="modal fade" id="modal_incentive" tabindex="-1" role="dialog" aria-labelledby="modalIncentiveLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title mt-0" id="modal_title">Incentive Data</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="form_incentive">                    
                    <div class="modal-body">
                        <div class="row" style="display: flex; justify-content: flex-end;">
                            <input type="text" id="txtLkQty" class="my-control num-input text-center mr-4" placeholder="Lk Qty" style="width:75px;">
                            <input type="text" id="txtLkAmt" class="my-control num-input text-center mr-4" placeholder="Lk Amt" style="width:75px">
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="table-responsive">
                                    <table id="incentiveTable" class="table table-sm table-bordered nowrap" style="overflow-y:auto; width:100%">
                                        <thead class="thead-light">
                                            <tr>
                                                <th class="text-right pr-1">
                                                    <div class="checkbox checkbox-primary checkbox-single">
                                                        <input type="checkbox" id="chkSelectAll">
                                                        <label class="mb-0"></label>
                                                    </div>
                                                </th>
                                                <th class='d-none'>ID</th>
                                                <th>Product</th>
                                                <th class="text-center">Inc Rate</th>
                                                <th class="text-center">Lk Qty</th>
                                                <th class="text-center">Lk Amt</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($products as $product)
                                                <tr>
                                                    <td class="text-right">
                                                        <div class="checkbox checkbox-primary checkbox-single">
                                                            <input type="checkbox" name="prod[{{$product->id}}]" id="prod{{$product->id}}" value="{{$product->name}}">
                                                            <label class="mb-0"></label>
                                                        </div>
                                                    </td>
                                                    <td class='d-none'>{{ $product->id }}</td>
                                                    <td>{{ $product->name }}</td>
                                                    <td class="text-center"><input type="text" id="incRate{{$product->id}}" class="my-control num-input text-center mr-0" style="width:70px"></td>
                                                    <td class="text-center"><input type="text" id="lkQty{{$product->id}}" class="my-control num-input text-center mr-0" style="width:70px"></td>
                                                    <td class="text-center"><input type="text" id="lkAmt{{$product->id}}" class="my-control num-input text-center mr-0" style="width:70px"></td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>   
                    </div>
                    <div class="modal-footer"> 
                        <button class="btn btn-secondary mr-3" data-dismiss="modal">Close</button>
                        <button id="btnSaveIncentive" class="btn btn-primary px-3">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- End of Incentive Modal -->
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

            var incentiveType = "{{$incentive_type}}";
            var $slabTableHead = $("#tableSlab thead");
            $slabTableHead.addClass("d-none");
            $("#deleteSlab").hide();

            if(@json($isEdit)) {
                // Load Applicable Customers
                var applCustomers = @json($applicable_customers);
                applCustomers.forEach(function(customer) {
                    addTableRow(customer.id,customer.customer_name);
                });

                // Set incentive type                
                if(incentiveType == "Fixed") {
                    var rate = "{{$incentive_rate}}";
                    $("#txtIncRate").val(rate);
                    $('#rdoIncFixed').prop("checked", true);
                    $("#divIncSlab").hide();
                }
                else if(incentiveType == "Slab") {
                    loadSlabTable();
                    $('#rdoIncSlab').prop("checked", true);
                    $("#divIncFixed").hide();
                }

                loadIncentiveTable();
            }
            else {
                $('#tableCustomers').hide();
                $('#tableIncentive').hide();
                $("#divIncSlab").hide();
                $('#rdoIncFixed').prop("checked", true);
            }

            function loadSlabTable() {
                let from,to,rate,record;
                @foreach($slab_data as $data)
                    from = "{{$data['from']}}";
                    to = "{{$data['to']}}";
                    rate = "{{$data['rate']}}";
                    record = `<tr> <td>${from}</td> <td>${to}</td> <td>${rate}</td> </tr>`;
                    $("#tableSlab tbody").append(record);
                @endforeach
                $("#deleteSlab").show();
                from = (to=="") ? "" : parseInt(to)+1;
                $('#slabFrom').val(from);
            }

            function loadIncentiveTable() {
                let sno=0, id, product, rate, qty, amount;
                @foreach($incentive_data as $data)
                    id = "{{$data['id']}}";
                    product = "{{$data['product']}}";
                    @if (isset($data['inc_rate']))
                        rate = "{{$data['inc_rate']}}";
                    @else
                        rate = "";
                    @endif
                    qty = "{{$data['lk_qty']}}";
                    amount = "{{$data['lk_amt']}}";
                    addIncentiveTableRow(++sno, id, product, rate, qty, amount)
                @endforeach
                if(incentiveType == "Slab")
                    $('#tableIncentive thead tr th:nth-child(4), #tableIncentive tbody tr td:nth-child(4)').hide();
            }
            
            $(".num-input").keypress(function(e) {
                var key = String.fromCharCode(e.keyCode);
                
                // Check if the entered character matches the regular expression
                if (key.match(/[^0-9.]/g))
                    return false;

                // Ensure only one decimal point
                if (key === '.' && e.target.value.indexOf('.') !== -1)
                    return false;
                
                return true;
            });
  
            $('#rdoIncFixed').change(function () {
                if(this.checked) {
                    $("#divIncSlab").hide();
                    $("#divIncFixed").show();
                    $('#tableIncentive thead tr th:nth-child(4), #tableIncentive tbody tr td:nth-child(4)').show();
                }
            });

            $('#rdoIncSlab').change(function () {
                if(this.checked) {
                    $("#divIncFixed").hide();
                    $("#divIncSlab").show();
                    $('#tableIncentive thead tr th:nth-child(4), #tableIncentive tbody tr td:nth-child(4)').hide();
                }
            });
            
            $("#slabTo").keypress(function(e) { 
                if (e.keyCode == 13) {   // Enter
                    $('#slabRate').focus();
                }
            });

            $("#slabRate").keypress(function(e) { 
                if (e.keyCode == 13) {   // Enter
                    $("#addSlab").trigger('click');
                }
            });

            $("#txtLkQty").keypress(function(e) {
                if (e.keyCode == 13) {   // Enter
                    $('#txtLkAmt').focus();
                }
            });

            $("#txtLkAmt").keypress(function(e) { 
                if (e.keyCode == 13) {   // Enter
                    $('#txtLkAmt').blur();
                }
            });

            $("#txtLkQty").blur(function() {
                var chkProducts = $('[id^=prod]:checked');
                var qty = $('#txtLkQty').val();
                if(qty == "") qty = 0;
                $(chkProducts).each(function (index, element) {
                    var id = $(this).attr('id').replace("prod","");
                    $('#lkQty'+id).val(qty);
                });
            });

            $("#txtLkAmt").blur(function() {
                var chkProducts = $('[id^=prod]:checked');
                var amt = $('#txtLkAmt').val();
                if(amt == "") amt = 0;
                $(chkProducts).each(function (index, element) {
                    var id = $(this).attr('id').replace("prod","");
                    $('#lkAmt'+id).val(amt);
                });
            });

            $("#addSlab").click(function(e) { 
                let from = $('#slabFrom').val();
                let to = $('#slabTo').val();
                let rate = $('#slabRate').val();
                if(from === "" || from === undefined) {
                    Swal.fire('Sorry!','No Data to Add','warning');
                }
                else if(rate === "" || rate === undefined) {
                    Swal.fire('Sorry!','Please Enter Rate','warning');
                }
                else if(parseInt(to) < parseInt(from)) {
                    Swal.fire('Sorry!','\'To\' Value should greater than \'From\' Value','warning');
                }
                else if((to === "" || to === undefined) && ($('#tableSlab tbody tr').length === 0)) {
                    Swal.fire('Sorry!','Please Enter \'To\' Value','warning');
                }
                else {
                    addSlabRow(from,to,rate);
                }
            });

            function addSlabRow(from,to,rate) {
                let record = `<tr> <td>${from}</td> <td>${to}</td> <td>${rate}</td> </tr>`;
                $("#tableSlab tbody").append(record);
                $slabTableHead.removeClass("d-none");
                $("#deleteSlab").show();
                if(to === "" || to === undefined) {
                    $('#slabFrom').val('');
                    $('#slabTo').val('');
                    $('#slabRate').val('');
                    $("#slabTo").prop("disabled", true);
                    $("#slabRate").prop("disabled", true);
                    $('#txtLkQty').focus();
                }
                else {
                    $('#slabFrom').val(parseInt(to)+1);
                    $('#slabTo').val('');
                    $('#slabRate').val('');
                    $('#slabTo').focus();
                }
            }

            $("#deleteSlab").click(function(e) { 
                var tableBody = $('#tableSlab tbody');
                var rowCount = tableBody.find('tr').length;
                if (rowCount > 0) {
                    tableBody.find('tr:last').remove();
                    $("#slabTo").prop("disabled", false);
                    $("#slabRate").prop("disabled", false);                    
                    if(rowCount == 1) {
                        $slabTableHead.addClass("d-none");
                        $("#deleteSlab").hide();
                        $('#slabFrom').val('1');
                    }
                    else {
                        var $lastRow = $("#tableSlab tbody tr").last();
                        var to = $lastRow.find("td").eq(1).text();
                        $('#slabFrom').val(parseInt(to)+1);
                    }
                }
            });
 
            // Setup - add a text input to each header cell
            $('#datatable thead tr:nth-child(1) th:nth-child(n+2)').each( function (i) {
                var title = $('#datatable thead th').eq( $(this).index() ).text();
                $(this).html( '<input type="text" class="my-control" placeholder="'+title+'" data-index="'+(i+1)+'" />' );
            } );
            
            var table = $('#datatable').DataTable( {
                dom: 't',
                paging: false,
            } );

            // Filter event handler
            $( table.table().container() ).on('keyup', 'thead tr:nth-child(1) input', function () {
                var columnIndex = $(this).data('index'); // Current column index
                table
                    .column(columnIndex) // Target the column
                    .search(this.value) // Perform the search in the column
                    .draw();
            } );

            // Prevent Sorting on Click in TextBox
            $('#datatable thead tr th input[type="text"]').on('click', function(event) {
                // Stop the event propagation to prevent sorting
                event.stopPropagation();
            });

            // Setup - add a text input to product column
            var headerCell = $('#incentiveTable thead th').eq(2);
            headerCell.html( '<input type="text" class="my-control" placeholder="'+headerCell.text()+'" data-index="2" />' );

            var table2 = $('#incentiveTable').DataTable( {
                dom: 't',
                paging: false,
                "order": [], // Disables initial sorting
                "columnDefs": [ 
                    { "orderable": false, "targets": [0,3,4,5] } // Disables sorting 
                ]
            } );

            // Filter event handler
            $(table2.table().container()).on('keyup', 'thead th:nth-child(3) input', function () {
                var columnIndex = $(this).data('index'); // Current column index                
                table2
                    .column(columnIndex) // Target the column
                    .search(this.value) // Perform the search in the column
                    .draw();
            });

            // Prevent Sorting on Click in TextBox
            $('#incentiveTable thead tr th input[type="text"]').on('click', function (event) {
                // Stop the event propagation to prevent sorting
                event.stopPropagation();
            });

            $('#incentiveTable tbody').on('change', 'input[type="checkbox"]', function() {
                // Get the row of the checkbox that was changed
                var row = $(this).closest('tr');                
                if ($(this).is(':checked')) {
                    let incRate = $('#txtIncRate').val();
                    let lkQty = $('#txtLkQty').val();
                    let lkAmt = $('#txtLkAmt').val();
                    if(lkQty == "") lkQty = 0;
                    if(lkAmt == "") lkAmt = 0;
                    row.find('input[id^="incRate"]').val(incRate);
                    row.find('input[id^="lkQty"]').val(lkQty);
                    row.find('input[id^="lkAmt"]').val(lkAmt);
                } 
                else {
                    row.find('input[id^="incRate"]').val('');
                    row.find('input[id^="lkQty"]').val('');
                    row.find('input[id^="lkAmt"]').val('');
                }
            });

            $('#chkSelectAll').change(function () {
                // Get the checked state of the "Select All" checkbox
                var isChecked = $(this).is(':checked');
                // Set the checked state of all checkboxes in the table body
                $('#incentiveTable tbody input[type="checkbox"]').prop('checked', isChecked).trigger('change');
            });

            $('#addCustomers').on('click', function (event) {
                event.preventDefault();

                // Close button previously clicked
                // Collect checkboxes whose state is checked if any
                var chkCustomers = $('[id^=cust]:checked');
                // Uncheck all checkboxes selected by the chkCustomers selector
                chkCustomers.prop('checked', false);

                // Unhide previously hidden rows if any
                $('#datatable tbody tr').show();

                // Collect all IDs from 'tableCustomers'
                var ids = getCustomerIds();

                // Hide rows in 'datatable' based on collected IDs
                $('#datatable tbody tr').filter(function() {
                    return ids.includes($(this).find('td:eq(1)').text()); // Assuming the ID is in the second column
                }).hide();
            } );

            $('#btnAddCustomers').on('click', function (event) {
                event.preventDefault();

                // Clear fields in the first row
                $('#datatable thead tr:nth-child(1) input').val('');
                // Clear filter
                table.column(2).search('').draw();

                var chkCustomers = $('[id^=cust]:checked');
                $(chkCustomers).each(function (index, element) {
                    var id = $(this).attr('id').replace("cust","");
                    var name = $(this).val();
                    addTableRow(id,name);
                });
            });

            // Function to add a row to the table
            function addTableRow(id, customer) {
                // Get the current row count
                var rowCount = $("#tableCustomers tbody tr").length;

                // Show table, if already hidden
                if(rowCount == 0)
                    $('#tableCustomers').show();

                // Calculate the new S.No
                var sno = rowCount + 1;
 
                // Create new row
                var newRow = $("<tr>");

                // Add cells to the new row
                newRow.append("<td class='text-center'>" + sno + "</td>");
                newRow.append("<td class='d-none'>" + id + "</td>");
                newRow.append("<td>" + customer + "</td>");                
                newRow.append("<td class='text-center'><a href='#'><i class='fas fa-trash-alt text-danger font-16 delete-row'></i></a></td>");
                newRow.append("</tr>");
                
                // Append the new row to the table body
                $("#tableCustomers tbody").append(newRow);
            }

            // Event delegation to handle delete button clicks
            $("#tableCustomers").on("click", ".delete-row", function() {
                // Get the row containing the delete button
                var row = $(this).closest("tr");
                // Remove the row from the table
                row.remove();

                var rowCount = $("#tableCustomers tbody tr").length;
                if(rowCount == 0)
                    // Hide table if has no rows
                    $('#tableCustomers').hide();
                else
                    // Update the serial numbers
                    updateSerialNumbers();
            });

            // Function to update the serial numbers (S.No) in the table
            function updateSerialNumbers() {
                $("#tableCustomers tbody tr").each(function(index) {
                    // Update the serial number cell for each row
                    $(this).find("td:first").text(index + 1);
                });
            }

            // Function to collect all IDs from 'tableCustomers'
            function getCustomerIds() {
                var ids = [];
                $('#tableCustomers tbody tr').each(function() {
                    ids.push($(this).find('td:eq(1)').text()); // Assuming the ID is in the second column
                });
                return ids;
            }

            $('#setupIncentive').on('click', function (event) {
                event.preventDefault();
                // Uncheck all checkboxes
                $('#incentiveTable tbody input[type="checkbox"]').prop('checked', false);                
                // Clear all textboxes
                $('#incentiveTable tbody input[type="text"]').val('');

                // If incentive type is fixed, then incentive rate is required
                if ($('#rdoIncFixed').is(':checked')) {
                    let rate = $('#txtIncRate').val();
                    if(rate === "" || rate === undefined) {
                        Swal.fire('Attention','Enter Incentive Rate','warning');
                        return false;
                    }
                }

                // Update UI based on #tableIncentive
                var $tableIncentiveRows = $('#tableIncentive tbody tr');
                $tableIncentiveRows.each(function() {
                    var $row = $(this);
                    var id = $row.find('td').eq(1).text();
                    var product = $row.find('td').eq(2).text();
                    var incRate = $row.find('td').eq(3).text();
                    var lkQty = $row.find('td').eq(4).text();
                    var lkAmt = $row.find('td').eq(5).text();
                    if(incRate == "") incRate = $('#txtIncRate').val();
                    $('#incentiveTable tbody #prod' + id).prop('checked',true);
                    $('#incentiveTable tbody #incRate' + id).val(incRate);
                    $('#incentiveTable tbody #lkQty' + id).val(lkQty);
                    $('#incentiveTable tbody #lkAmt' + id).val(lkAmt);
                });

                // If incentive type is slab, then hide incentive rate column
                if ($('#rdoIncSlab').is(':checked')) {
                    $('#incentiveTable thead tr th:nth-child(4), #incentiveTable tbody tr td:nth-child(4)').hide();
                }
                else {
                    $('#incentiveTable thead tr th:nth-child(4), #incentiveTable tbody tr td:nth-child(4)').show();
                }
            });

            $('#btnSaveIncentive').on('click', function (event) {
                event.preventDefault();

                var chkProducts = $('[id^=prod]:checked');
                var dataArray = [];
                var hasEmptyFields = false;
                var isFixed = $('#rdoIncFixed').is(':checked');
                $(chkProducts).each(function (index, element) {
                    var id = $(this).attr('id').replace("prod","");
                    var name = $(this).val();
                    var rate = $('#incRate'+id).val();
                    var qty = $('#lkQty'+id).val();
                    var amt = $('#lkAmt'+id).val();

                    if ((isFixed && rate === "") || qty === "" || amt === "") {
                        hasEmptyFields = true;
                        if(isFixed && rate == "") Swal.fire('Attention','Incentive Rate Missing! Please Check!','warning');
                        else if(qty == "") Swal.fire('Attention','Leakage Quantity Missing! Please Check!','warning');
                        else if(amt == "") Swal.fire('Attention','Leakage Amount Missing! Please Check!','warning');
                        return false; // Break the loop
                    }

                    dataArray.push({
                        id: id,
                        name: name,
                        rate: rate,
                        qty: qty,
                        amt: amt
                    });
                });

                if(!hasEmptyFields) {
                    // Clear fields in the first row
                    $('#incentiveTable thead tr:nth-child(1) input').val('');
                    // Clear filter
                    table2.column(2).search('').draw();
                    // Clear Existing Rows
                    $("#tableIncentive tbody").empty();

                    if(chkProducts.length == 0) {
                        $('#tableIncentive').hide();
                    }
                    else {
                        $('#tableIncentive').show();
                        let sno = 0;
                        dataArray.forEach(function(item) {
                            addIncentiveTableRow(++sno, item.id, item.name, item.rate, item.qty, item.amt);
                        });
                    }

                    if ($('#rdoIncSlab').is(':checked')) {
                        $('#tableIncentive thead tr th:nth-child(4), #tableIncentive tbody tr td:nth-child(4)').hide();
                    }

                    $('#modal_incentive').modal('hide');
                }
            });

            // Function to add a row to the table
            function addIncentiveTableRow(sno, id, product, rate, qty, amount) {
                // Create new row
                var newRow = $("<tr>");

                // Add cells to the new row
                newRow.append("<td>" + sno + "</td>");
                newRow.append("<td class='d-none'>" + id + "</td>");
                newRow.append("<td class='text-left'>" + product + "</td>");
                newRow.append("<td>" + rate + "</td>");
                newRow.append("<td>" + qty + "</td>");
                newRow.append("<td>" + amount + "</td>");
                newRow.append("</tr>");
                
                // Append the new row to the table body
                $("#tableIncentive tbody").append(newRow);
            }

            function getSlabData() {
                var slabData = [];
                
                $('#tableSlab tbody tr').each(function() {
                    var row = $(this);
                    var from = row.find('td:nth-child(1)').text();
                    var to = row.find('td:nth-child(2)').text();
                    var rate = row.find('td:nth-child(3)').text();
                    
                    // Construct a data object for the current row
                    var rowData = {
                        from: from,
                        to: to,
                        rate: rate
                    };
                    
                    // Add the row data to the slabData array
                    slabData.push(rowData);
                });
                
                return JSON.stringify(slabData);
            }

            function getIncentiveData(incentiveType) {
                var incentiveData = [];

                $('#tableIncentive tbody tr').each(function() {
                    var row = $(this);
                    var sno = row.find('td:nth-child(1)').text();
                    var id = row.find('td:nth-child(2)').text();
                    var product = row.find('td:nth-child(3)').text();
                    var qty = row.find('td:nth-child(5)').text();
                    var amt = row.find('td:nth-child(6)').text();
                    
                    var rowData = {                        
                        id: id,
                        product: product,
                        lk_qty: qty,
                        lk_amt: amt
                    };

                    // Include 'rate' if incentiveType is 'Fixed'
                    if (incentiveType === 'Fixed') {
                        var rate = row.find('td:nth-child(4)').text();
                        rowData.inc_rate = rate;
                    }

                    incentiveData.push(rowData);
                });

                return JSON.stringify(incentiveData);
            }
 
            $('#submit').on('click', function (event) {
                event.preventDefault();
                var effectDate = $("#effect_date").val();
                var narration = $("#narration").val();
                var custIds = getCustomerIds();
                var incType = ($('#rdoIncSlab').is(':checked')) ? "Slab" : "Fixed";                
                
                if(!effectDate) {
                    Swal.fire('Attention','Please Select Effect Date','warning');
                }
                else if(!narration) {
                    Swal.fire('Attention','Please Give Narration','warning');
                }
                else if(custIds.length == 0) {
                    Swal.fire('Attention','Please Add Applicable Customers','warning');
                }
                else if(incType == "Fixed" && ($('#txtIncRate').val()) == "") {
                    Swal.fire('Attention','Enter Incentive Rate','warning');
                }
                else if(incType == "Slab" && ($('#tableSlab tbody tr').length) < 2) {
                    Swal.fire('Attention','Please Enter More than one Slabs!','warning');
                }
                else if (($('#tableIncentive tbody tr').length) == 0) {
                    Swal.fire('Attention','Please Give Incentive Data!','warning');
                }
                else {
                    $.ajax({
                        url: "{{ $action }}",
                        type: "POST",
                        data: function() {
                            // Common parameters
                            var ajaxData = {
                                effect_date: effectDate,
                                narration: narration,
                                cust_ids: custIds,
                                incentive_type: incType,
                            };

                            // Add specific parameters based on incentive_type
                            if (incType === "Fixed")
                                ajaxData.incentive_rate = $('#txtIncRate').val();                            
                            else if (incType === "Slab")
                                ajaxData.slab_data = getSlabData();
                            
                            ajaxData.incentive_data = getIncentiveData(incType);

                            return ajaxData;
                        }(),
                        dataType: 'json',
                        success: function (data) {
                            Swal.fire({
                                    title:'Success!',
                                    text: data.message,
                                    // text: JSON.stringify(data), // Convert data to string for display
                                    type:'success'
                                }
                            )
                            .then(
                                function() { 
                                    window.location.replace("{{ route('incentive-master.index') }}");
                                }
                            );  
                        },
                        error: function (data, textStatus, errorThrown) {
                            var errorText = data.responseText;
                            Swal.fire({
                                    title:'Sorry!',
                                    text:errorText,
                                    type:'warning',
                                    confirmButtonColor: '#FF0000'
                                }
                            );
                        }
                    });
                }
            });

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
@stop