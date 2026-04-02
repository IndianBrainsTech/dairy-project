@extends('app-layouts.admin-master')

@section('title', 'Price Variant')

@section('headerStyle')
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
                    @slot('title') Price Variant @endslot
                    @slot('item1') Explorer @endslot
                    @slot('item2') Price Master @endslot
                @endcomponent
            </div><!--end col-->
        </div>
        <!-- end page title end breadcrumb -->

        <div class="row">
            <div class="col-lg-10">
                <div class="card">
                    <div class="card-body">
                        <div class="row"> 
                            <div class="col-4">                   
                                <h4 class="header-title mt-0">Price Variant &nbsp;
                                    <button type="button" class="btn btn-pink btn-round" style="font-weight:500">
                                        {{ count($productGroup) }}
                                    </button>
                                </h4>  
                            </div>  
                            <div class="col-8 d-flex justify-content-end">                                                            
                                <form action="{{ route('price.variant') }}" method="POST" class="float-right">
                                    @csrf
                                    <div class="input-group mr-3 d-flex">
                                        <span class="input-group-prepend">
                                            <button type="button" class="btn btn-info">
                                                <i class="fas fa-search"></i>
                                            </button>
                                        </span>
                                        <input type="text" id="txtProduct" name="productName" class="form-control" placeholder="Product" value="{{ old('productName', $productName ?? '') }}" />
                                        <!-- Hidden input for the product ID -->
                                        <input type="hidden" id="productId" name="productId" value="{{ old('productId', $productId ?? '') }}" />
                                        <input type="submit" value="Submit" class="btn btn-primary btn-sm ml-3 px-3" />
                                    </div>                                        
                                </form>
                            </div>
                        </div>                        
                        <div class="table-responsive dash-social">
                            <table id="datatable" class="table table-sm table-bordered dt-responsive nowrap" style="border-collapse: collapse; border-spacing: 0; width: 100%">
                                <thead class="thead-light">
                                    <tr>
                                        <th data-priority="5" class="text-center">S.No</th>
                                        <th style="max-width: 110px" data-priority="4" class="text-center">Price</th>
                                        <th data-priority="1" class="text-center">Price Master</th>
                                        <th data-priority="2" class="text-center">Action</th>                                     
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($productGroup as $pro)                                           
                                            <tr>
                                                <!-- Display S.No (Use a counter) -->
                                                <td class="text-center">{{ $loop->index + 1 }}</td>
                                                                                                                                                        
                                                <td class="text-right pr-3">{{"Rs. ". $pro['price'] }}</td>                                                    
                                                <td> 
                                                    @if(is_array($pro['txn_id']))
                                                    @foreach($pro['txn_id'] as $txn)
                                                        {{ $txn }}@if(!$loop->last), @endif
                                                    @endforeach
                                                    @else
                                                        {{$pro['txn_id']}}
                                                    @endif
                                                </td>                                                                                  
                                                <!-- Action column with customers and modal trigger -->
                                                <td class="text-center">
                                                    <a href="#" class="show" data-toggle="modal" data-target="#customerModal" data-customers="{{ json_encode($pro['customers']) }}">
                                                        <i class="dripicons-preview text-primary font-20"></i>
                                                    </a>
                                                </td>
                                            </tr>                                          
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div><!--end card-body-->
                </div><!--end card-->
            </div> <!--end col-->
        </div><!--end row-->
    </div>
    <!-- Modal -->
    <div class="modal fade" id="customerModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Customer Details</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" style="overflow-y :auto;max-height:450px ">
                    <!-- Table for customer details -->
                    <table class="table table-bordered table-sm">
                        <thead>
                            <tr>
                                <th scope="col">S.No</th>
                                <th scope="col">Customer Name</th>
                            </tr>
                        </thead>
                        <tbody id="customerList">
                            <!-- Customer rows will be inserted here -->
                        </tbody>
                    </table>
                </div>
                {{-- <div class="modal-body">
                    <div id="customerList" class="list-group" style="max-height: 300px; overflow-y: auto;">
                        <!-- Customers will be shown here in a scrollable list -->
                    </div>
                </div> --}}
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
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
        $('#datatable').dataTable( {
                "lengthMenu": [[10, 25, 50, 100], [10, 25, 50, 100]],
                "pageLength": 50,
            } );
        var cus     = new Map();
        @foreach ($customerName as $id => $name )
            cus.set({{$id}}, '{{$name}}');
        @endforeach

        var products = new Map();              
        // Initialize the products map with the product data passed from the server
        @foreach($products as $product)
            products.set({{ $product->id }}, '{{ $product->short_name }}');
        @endforeach
        // Initialize the product input field
        $("#txtProduct").autocomplete({
            source: autocompleteSource(products),
            autoFocus: true,
            minLength: 0,  // Autocomplete will start immediately
            select: function(event, ui) {
                var name = ui.item.value;
                var id = getKeyByValue(products, name);  // Get productId from name
                console.log("Product => Selected ID: " + id + ", Name: " + name);
                $("#productId").val(id);  // Set the productId in the hidden input
            }
        });
        // Function to define autocomplete source
        function autocompleteSource(sourceMap) {
            return function(request, response) {
                let results = Array.from(sourceMap.entries()).map(function([key, value]) {
                    return {
                        label: value,   // Product name to display in autocomplete
                        value: value    // Value sent when selected (same as label in this case)
                    };
                }).filter(function(item) {
                    return item.label.toLowerCase().startsWith(request.term.toLowerCase()); // Filter based on input
                });
                response(results);
            };
        }
        // Helper function to get the key (productId) by value (product name)
        function getKeyByValue(map, value) {
            for (var [key, val] of map.entries()) {
                if (val === value) {
                    return key;
                }
            }
            return null;  // Return null if not found
        }

        // Preselect product if productId is already set in the hidden input
        let productId = $("#productId").val();
        if (productId) {
            const productName = products.get(parseInt(productId));
            if (productName) {
                $("#txtProduct").val(productName);
            }
        }
        
        $('#customerModal').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget);
            var customerIds = button.data('customers');
            var customerNames = [];
            customerIds.forEach(function(customerId) {
                var intCustomerId = parseInt(customerId);
                var customerName = cus.get(intCustomerId);
                
                if (customerName) {
                    customerNames.push(customerName);
                }
            });
            $('#customerList').empty();
            customerNames.forEach(function(customerName, index) {
                var row = `<tr>
                                <td class="text-center">${index + 1}</td>
                                <td>${customerName}</td>
                            </tr>`;
                $('#customerList').append(row);
            });
            // customerNames.forEach(function(customerName) {
            // var listItem = `<a href="#" class="list-group-item list-group-item-action">${customerName}</a>`;
            // $('#customerList').append(listItem);
            // });
        });

    });
</script>
@endpush

@section('footerScript')
    <script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables/dataTables.bootstrap4.min.js') }}"></script>
@stop
