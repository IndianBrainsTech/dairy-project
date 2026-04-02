@extends('app-layouts.admin-master')

@section('title', 'Discount Master')

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

        .table-container input.discount-cell {
            color: black;
            max-width: 80px;
            font-weight: 500;
            padding-top: 1px;
            padding-bottom: 1px;
            height: 30px;
            text-align: center;
        }

        .my-control {
            border: 1px solid #e8ebf3; 
            padding:6px;
            border-radius: 0.25rem;
            border-bottom: 1px solid #e8ebf3;
            transition: border-color 0s ease-out;
            background-color: #fff;
            margin-right:20px;
            width:100%;
        }
    </style>
@stop

@php
    if(!isset($discount_list)) {
        $isEdit = false;
        $title = "Add Discount Master";
        $action = route('discount-master.store');
        $effect_date = "";
        $narration = "";
        $applicable_customers = "";
        $discount_list = "";
    }
    else {
        $isEdit = true;
        $title = "Edit Discount Master";
        $action = route('discount-master.update', ['id' => $id]);
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
                    @slot('item3') Discount Masters @endslot
                @endcomponent
            </div>
        </div>
        <!-- end page title end breadcrumb -->

        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                        
                            <div class="col-lg-6">                                
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
                                                    <input type="date" class="form-control" id="effect_date" name="effect_date" value="{{$effect_date}}" min="{{ $txn_date }}" >
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
                                            <button type="button" id="addCustomers" class="btn btn-primary ml-3 pr-3" data-toggle="modal" data-animation="bounce" data-target="#modal_customers" style="height:32px; padding-top:1px; padding-bottom:1px"><i class="mdi mdi-plus-circle-outline mr-2"></i>Add</button>
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
                        
                            <div class="col-lg-5 mx-auto">                            
                                <div class="row">
                                    <div class="col-sm-6">
                                        <h4 class="header-title mt-0">Discount List</h4>
                                    </div>
                                    <div class="col-sm-6" style="margin-top:-5px; margin-bottom:10px; text-align:right">
                                        <div class="btn-group btn-group-toggle" data-toggle="buttons">
                                            <label class="btn btn-outline-purple" style="padding-top:3px;padding-bottom:3px">
                                                <input type="radio" id="rdoCollapse">Collapse
                                            </label>
                                            <label class="btn btn-outline-purple" style="padding-top:3px;padding-bottom:3px">
                                                <input type="radio" id="rdoExpand">Expand
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="table-responsive table-container" style="max-height:400px">
                                    <table id="tableProducts" class="table table-bordered table-sm">
                                        <thead class="thead-light" style="height:36px">
                                            <tr>
                                                <th class="text-center">S.No</th>
                                                <th class="d-none">ID</th>
                                                <th>Product</th>
                                                <th>Discount</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($products as $product)
                                                <tr>
                                                    <td class="text-center">{{ $loop->index + 1 }}</td>                                            
                                                    <td class="d-none">{{ $product->id }}</td>
                                                    <td>{{ $product->name }}</td>
                                                    <td><input type="text" name="prod[{{$product->id}}]" id="prod{{$product->id}}" class="form-control discount-cell" data-id="{{$product->id}}" tabindex="{{$loop->index + 1}}" maxlength="8"></td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>

                                <div class="row">
                                    <div class="col-sm-12">
                                        <button type="button" id="submit" class="btn btn-primary float-right mt-4 px-4">Submit</button>
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
                        <button id="btnAdd" class="btn btn-primary px-3" data-dismiss="modal">Add</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- End of Customer Modal -->
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

            if(@json($isEdit)) {
                // Load Applicable Customers
                var applCustomers = @json($applicable_customers);
                applCustomers.forEach(function(customer) {
                    addTableRow(customer.id,customer.customer_name);
                });
                
                // Update Discount List
                var discountList = @json($discount_list);
                for (var productId in discountList) {
                    if (discountList.hasOwnProperty(productId)) {
                        var discount = discountList[productId];
                        $('input[id="prod' + productId + '"]').val(discount);
                    }
                }
                $('#rdoCollapse').prop("checked", true);
                collapseDiscountList();
            }
            else {
                $('#tableCustomers').hide();
                $('#rdoExpand').prop("checked", true);
            }

            $(".discount-cell").keypress(function(e) {
                var key = String.fromCharCode(e.keyCode);
                
                // Check if the entered character matches the regular expression
                if (key.match(/[^0-9.]/g))
                    return false;

                // Ensure only one decimal point
                if (key === '.' && e.target.value.indexOf('.') !== -1)
                    return false;
                
                return true;
            });
  
            $('#rdoCollapse').change(function () {
                if(this.checked) {
                    collapseDiscountList();
                }
            });

            $('#rdoExpand').change(function () {
                if(this.checked) {
                    $("#tableProducts").find("tr").show();
                }
            });

            function collapseDiscountList() {
                $(".discount-cell").each(function (index, element) {
                    var value = $(this).val();
                    if(!value) {
                        var $row = $(this).closest('tr');
                        $row.hide();
                    }
                });
            }

            // Setup - add a text input to each header cell
            $('#datatable thead tr:nth-child(1) th:nth-child(n+2)').each( function (i) {
                var title = $('#datatable thead th').eq( $(this).index() ).text();
                $(this).html( '<input type="text" class="my-control" placeholder="'+title+'" data-index="'+i+'" />' );
            } );

            // var table = $('#datatable').DataTable();
            var table = $('#datatable').DataTable( {
                dom: 't',
                paging: false,
            } );

            // Filter event handler
            $( table.table().container() ).on('keyup', 'thead tr:nth-child(1) input', function () {
                var columnIndex = $(this).data('index'); // Current column index
                table
                    .column(columnIndex + 1) // Target the next column
                    .search(this.value) // Perform the search in the next column
                    .draw();
            } );

            // Prevent Sorting on Click in TextBox
            $('#datatable thead tr th input[type="text"]').on('click', function(event) {
                // Stop the event propagation to prevent sorting
                event.stopPropagation();
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

            $('#btnAdd').on('click', function (event) {
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

            function getDiscountList() {
                var discountData = {};
                $('[id^=prod]').each(function() {
                    var id = $(this).attr('data-id'); // Get the id
                    var value = $(this).val(); // Get the value
                    if (value.trim() !== '') { // Check if the value is not empty
                        discountData[id] = value; // Add to the collection
                    }                    
                });
                return discountData;
            }

            $('#submit').on('click', function (event) {
                event.preventDefault();
                var effect_date = $("#effect_date").val();
                var narration = $("#narration").val();
                var custIds = getCustomerIds();
                var discountList = getDiscountList();
                
                if(!effect_date) {
                    Swal.fire('Attention','Please Select Effect Date','warning');
                }
                else if(!narration) {
                    Swal.fire('Attention','Please Give Narration','warning');
                }
                else if(Object.keys(discountList).length == 0) {
                    Swal.fire('Attention','Please Update Discount List','warning');
                }
                else if(custIds.length == 0) {
                    Swal.fire('Attention','Please Add Applicable Customers','warning');
                }
                else {
                    $.ajax({
                        url: "{{ $action }}",
                        type: "POST",
                        data: {                            
                            effect_date:   effect_date,
                            narration:     narration,
                            cust_ids:      custIds,
                            discount_list: discountList
                        },
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
                                    window.location.replace("{{ route('discount-master.index') }}");
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