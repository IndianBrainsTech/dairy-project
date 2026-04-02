@extends('app-layouts.admin-master')

@section('title', 'Stock Entry')

@section('headerStyle')
    <link href="{{ URL::asset('plugins/sweet-alert2/sweetalert2.min.css')}}" rel="stylesheet" type="text/css">
    <link href="{{ URL::asset('plugins/animate/animate.css')}}" rel="stylesheet" type="text/css">
    <link href="{{ URL::asset('plugins/datatables/dataTables.bootstrap4.min.css')}}" rel="stylesheet" type="text/css" />
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
    </style>
@stop
@section('content')
    <div class="container-fluid">
        <!-- Page-Title -->
        <div class="row">
            <div class="col-sm-12">
                @component('app-components.breadcrumb-3')
                    @slot('title') {{isset($oldEntry) ? "Edit Stock" : " Add Stock" }}@endslot
                    @slot('item1') Transactions @endslot
                    @slot('item2') Production @endslot
                @endcomponent
            </div><!-- end col -->
        </div><!-- end row -->
        <!-- end page title end breadcrumb -->

        <div class="row">
            <div class="col-lg-10 col-md-11 col-sm-12">
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
                                <div class="col-md-12">
                                    <div class="row mb-2">
                                        <div class="input-group mr-3" style="width:200px">
                                            <span class="input-group-prepend">
                                                <button type="button" class="btn btn-info"><i class="fas fa-search"></i></button>
                                            </span>
                                            <input type="text" id="txtProduct" class="form-control" placeholder="Product">
                                        </div>
                                        <input type="text" id="txtBatch" class="form-control" style="width:100px;margin-right:15px" placeholder="Batch NO">
                                        <input type="text" id="txtQty" class="form-control" style="width:80px" placeholder="Qty">
                                        <select id="selectUnit" class="form-control mr-3" style="width:80px">
                                            <option value="">Unit</option>
                                        </select>                                        
                                        <button id="btnAdd" type="button" class="btn btn-info mr-2"><i class="fas fa-plus"></i></button>
                                        <button id="btnClear" type="button" class="btn btn-warning"><i class="fas fa-trash-alt"></i></button>
                                    </div>

                                    <div class="table-responsive dash-social mt-4 mb-2" style="margin-left:-12px;">
                                        <table id="tableOrderedItems" class="table table-bordered table-sm">
                                            <thead class="thead-light">
                                                <tr>
                                                    <th class="text-center">S.No</th>
                                                    <th>Product</th>
                                                    <th>Batch NO</th>
                                                    <th>Qty</th>                                                   
                                                    <th class="d-none">Product ID</th>
                                                    <th class="text-center">Action</th>
                                                    <th class="d-none">Qty</th>
                                                    <th class="d-none">Unit</th>
                                                </tr>
                                            </thead>
                                            <tbody style="height: 266px">
                                            </tbody>
                                            <tfoot class="thead-light">
                                                <tr>
                                                    <th colspan="5" class="text-center"></th>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>                                   

                                    <div class="row mb-3">
                                        <button type="button" id="btnReset" class="btn btn-warning waves-effect waves-light btn-sm mr-2">
                                            <i class="fas fa-trash-alt mr-2"></i>Clear
                                        </button>
                                        <div class="ml-auto">                                            
                                            <button type="button" id="btnSubmit" class="btn btn-primary btn-sm px-3 mr-4" data-toggle="tooltip" data-placement="top" title="Alt+O">
                                                <i class="mdi mdi-shopping mr-2"></i>{{(isset($oldEntry)) ? "Update Stock" : " Add Stock"}}
                                            </button>                                            
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div><!-- end card-body -->
                </div><!-- end card -->
            </div><!-- end col -->
        </div><!-- end row -->
    </div><!-- end container-fluid -->
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
                var key = '{{$product->short_name}}';
                var value = '{{$product->id}}';
                products.set(key,value);
            @endforeach                      
            
            $("#txtProduct").autocomplete({                
                source: autocompleteSource(products),
                autoFocus: true,
                minLength: 0,
                select: function(event, ui) {                    
                    var key = ui.item.value;
                    var id = products.get(key);                                      
                    updateUnits(id,null);                   
                    $("#txtBatch").focus();
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
            $("#txtBatch").keypress(function(e)
            {
                if (e.keyCode == 13) {   // Enter                    
                    $("#txtQty").focus();
                }                
            })

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
                
                var product = $('#txtProduct').val();
                var batch = $('#txtBatch').val();
                var qty = $('#txtQty').val();
                var unitId = $('#selectUnit').val();
                if(product=="") alert('Please Select Product');
                else if(batch=="") alert('Please Enter Batch No');
                else if(qty=="") alert('Please Enter Quantity');
                else if(unitId==null) alert('Please Select Unit');
                else {
                    var productId = products.get(product);                                    
                    if(productId) {
                        addOrUpdateRow(productId, product, qty, unitId, batch);
                        $("#btnClear").trigger('click');
                        $("#txtProduct").focus();
                    }
                    else {
                        alert('Product Not Accepted! Please Select it from List');
                    }
                }
            });

            $("#btnClear").click(function(e){                
                $('#txtProduct').val('');
                $('#txtBatch').val('');                
                $('#txtQty').val('');                
                $('#selectUnit').children().remove();
                $('#selectUnit').append(`<option value="">Unit</option>`);
            });

            $('body').on('click', '.edit_item', function (event) {
                var row = $(this).closest('tr');
                var productId = $(row).find('td:nth-child(5)').text();
                updateUnits(productId, function () {
                    var product = $(row).find('td:nth-child(2)').text();
                    var batch = $(row).find('td:nth-child(3)').text();
                    var qty = $(row).find('td:nth-child(7)').text(); 
                    var unitId = $(row).find('td:nth-child(8)').text();
                    $("#txtProduct").val(product);
                    $("#txtBatch").val(batch);                
                    $("#txtQty").val(qty);                
                    $("#selectUnit").val(unitId);                    
                    $("#txtQty").select();
                });
            });

            $('body').on('click', '.delete_item', function (event) {
                $(this).closest('tr').remove();
                addEmptyRow();
                updateSerialNumbers();                
            });            

            $("#btnReset").click(function(e){
                clearOrderInfo();
            });           

            $("#btnSubmit").click(function(e){
                if($('#tableOrderedItems tbody tr:not(.empty-row)').length == 0){
                    Swal.fire('Sorry!','Please Enter Data for Stock Entry','warning');
                }
                else {                   
                    let stockData = getOrderData(); 
                    var oldData = @json($txn_id ?? []);                                                                        
                    $.ajax({
                        url: action,
                        type: "POST",
                        data: {
                           stockDatas   : stockData,
                           txn_id :oldData
                        },
                        dataType: 'json',
                        success: function (data) {     
                            console.log(data);                                                                   
                            Swal.fire({
                                    title: 'Success!',
                                    html: data.message,
                                    icon: 'success'
                                }
                            )
                            .then(
                                function() { 
                                    window.location.reload(true);
                                }
                            );  
                        },
                        error: function (data, textStatus, errorThrown) {
                            var errorText = data.message;
                            console.log(data);  
                            Swal.fire({
                                    title:'Sorry!',
                                    text: errorText,
                                    icon:'warning',
                                    confirmButtonColor: '#FF0000'
                                }
                            );
                        }
                    });                    
                }
            });            

            $(document).on('keydown', function(event) {
                if (event.altKey && event.key.toUpperCase() === 'O')
                    $('#btnSubmit').click();                
            });   

            function updateUnits(productId, callback) {  // Add callback as a parameter                
                $.ajax({
                    url: "{{ route('price.explorer') }}",
                    type: "GET",
                    data: { productId: productId },
                    success: function(data) {                                              
                        $('#selectUnit').empty();                        
                        var item = data.products[0]; 
                        
                        // Append primary unit
                        item.conversion.forEach(function(unit) {
                            if (unit.unit_id == 1) {                                  
                                $("#selectUnit").append(new Option(unit.unit_name, unit.unit_id));
                            }
                        });
                        
                        // Append other units
                        item.conversion.forEach(function(unit) {
                            if (unit.unit_id !== 1) {                               
                                $("#selectUnit").append(new Option(unit.unit_name, unit.unit_id));
                            }
                        });
                        
                        // Execute callback if it was provided
                        if (callback) {
                            callback();
                        }
                    },
                    error: function(data, textStatus, errorThrown) {
                        console.log("Error:", textStatus, errorThrown);
                    }
                });          
            }

            function getUnitName(unitId) {
                var unitName = "";
                @foreach($units as $unit)
                    if(unitId == "{{$unit->id}}")
                        unitName = "{{$unit->display_name}}";
                @endforeach
                return unitName;
            }

            function createOrUpdateRow(productId, product, qty, unitId, batch, row = null) {                
                var qtyStr = qty + " " + getUnitName(unitId); 
                if (row == null) {
                    const record = `
                        <tr style='height:32px'>
                            <td class='text-center'></td>                           
                            <td>${product}</td>
                            <td>${batch}</td> 
                            <td>${qtyStr}</td>                               
                            <td class="d-none">${productId}</td>                            
                            <td class='text-center'>
                                <a href="#" class="edit_item" class="mr-2"><i class="fas fa-edit text-info font-16"></i></a>
                                <a href="#" class="delete_item"><i class="fas fa-trash-alt text-warning font-16"></i></a>
                            </td>
                            <td class="d-none">${qty}</td>  
                            <td class="d-none">${unitId}</td> 
                        </tr>`;
                    $("#tableOrderedItems tbody").append(record);
                } 
                else {
                    $(row).find('td:nth-child(3)').text(batch);
                    $(row).find('td:nth-child(4)').text(qtyStr);
                    $(row).find('td:nth-child(7)').text(qty);
                    $(row).find('td:nth-child(8)').text(unitId);                    
                }
            }

            function addOrUpdateRow(productId, product, qty, unitId, batch) {
                var row = findRow(productId);              
                createOrUpdateRow(productId, product, qty, unitId, batch, row);
                if (row == null) {
                    addEmptyRow();
                    updateSerialNumbers();
                }              
            }
            function updateSerialNumbers() {
                // Update the serial number cell for each row
                $("#tableOrderedItems tbody tr:not(.empty-row)").each(function(index) {
                    $(this).find("td:first").text(index + 1);
                });
            }

            function addEmptyRow() {
                // Delete empty row if exists
                $("#tableOrderedItems tbody tr.empty-row").last().remove();
                // Add new empty row, if space available
                if($('#tableOrderedItems tbody tr').length <= 6) {               
                    const emptyRow = `<tr class='empty-row'><td></td><td></td><td></td><td></td><td></td></tr>`;
                    $("#tableOrderedItems tbody").append(emptyRow);
                }
            }

            function findRow(productId) {
                let foundRow = null;
                
                $("#tableOrderedItems tbody tr:not(.empty-row)").each(function() {                    
                    const rowProductId = $(this).find('td:nth-child(5)').text();
                    
                    if (rowProductId === productId) {
                        foundRow = $(this);
                        return false; // Exit the loop
                    }
                });
                
                return foundRow;
            }           

            function getOrderData() {
                let stockData = [];

                // Iterate over each row in the tbody
                $('#tableOrderedItems tbody tr:not(.empty-row)').each(function() {
                    // Collect each row's data into an object and push it into the orderItems array
                    stockData.push({                       
                        productId : $(this).find('td:nth-child(5)').text(),
                        qty       : $(this).find('td:nth-child(7)').text(),
                        unit      : $(this).find('td:nth-child(8)').text(),
                        qtyStr    : $(this).find('td:nth-child(4)').text(),                        
                        batch    : $(this).find('td:nth-child(3)').text(),                        
                        productName   : $(this).find('td:nth-child(2)').text()
                    });
                });             
                return {
                    stockData: stockData                   
                };
            }           

            function clearOrderInfo() {
                $("#btnClear").click();
                $('#tableOrderedItems tbody').empty();                
            }    
            
            function getUnitId(unitName) {
                var unitId = "";
                @foreach($units as $unit)
                    if(unitName == "{{$unit->display_name}}")
                        unitId = "{{$unit->id}}";
                @endforeach
                return unitId;
            }
                        
            var isEdit = @json(isset($oldEntry) ? true : false);
            var title = @json(isset($oldEntry) ? "Edit Stock" : "Stock Entry");
            var action = @json(isset($oldEntry) ? route('stock.update', ['id' => $txn_id]) : route('stock.store'));
            var oldEntries = @json(isset($oldEntry) ? $oldEntry : []);
            if(isEdit) {
                oldEntries.forEach(function(entry) {
                    var txn_id = entry.txn_id || "";
                    var product = entry.product_name || "";
                    var batch = entry.batch_no || "";
                    var unitName = entry.entry_unit || "";                    
                    var qty = entry.entry_qty || "";
                    var productId = entry.product_id || "";
                    var unitId = getUnitId(unitName);                    
                    createOrUpdateRow(productId, product, qty, unitId, batch);                    
                });
                addEmptyRow();
                updateSerialNumbers();    
                     
            }            

 });  
    </script>
@endpush

@section('footerScript')
    <!-- Sweet-Alert  -->
    <script src="{{ URL::asset('plugins/sweet-alert2/sweetalert2.min.js')}}"></script>
    <script src="{{ URL::asset('assets/pages/jquery.sweet-alert.init.js')}}"></script>
    <!-- Required datatable js -->
    <script src="{{ URL::asset('plugins/datatables/jquery.dataTables.min.js')}}"></script>
    <script src="{{ URL::asset('plugins/datatables/dataTables.bootstrap4.min.js')}}"></script>    
    <!-- Responsive examples -->
    <script src="{{ URL::asset('plugins/datatables/dataTables.responsive.min.js')}}"></script>
    <script src="{{ URL::asset('plugins/datatables/responsive.bootstrap4.min.js')}}"></script>
@stop