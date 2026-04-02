@extends('app-layouts.admin-master')

@section('title', 'Item wise Customer Report')

@section('headerStyle')
    <link href="{{ asset('plugins/sweet-alert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('plugins/animate/animate.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('assets/css/report-style.css') }}" rel="stylesheet" type="text/css">
@stop
@section('content')
    <div class="container-fluid">
        <!-- Page-Title -->
        <div class="row">
            <div class="col-sm-12">
                @component('app-components.breadcrumb-3')
                    @slot('title') Item wise Customer Report @endslot
                    @slot('item1') Reports @endslot
                    @slot('item2') Sales Reports @endslot
                @endcomponent
            </div>
        </div>
        <!-- end page title end breadcrumb -->

        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
 
                        <div>
                            <form method="post" action="{{ route('report.customer.item-wise') }}" class="d-flex">
                                @csrf
                                <label class="my-text">From</label>
                                <input type="date" name="fromDate" id="fromDate" value="{{$fromDate}}" class="my-control">
                                <label class="my-text">To</label>
                                <input type="date" name="toDate" id="toDate" value="{{$toDate}}" class="my-control">
                                <input type="text" id="txtProduct" name="productName" class="form-control ml-3" style ="width: 250px" placeholder="Product" value="{{ old('productName', $productName ?? '') }}" />
                                <!-- Hidden input for the product ID -->
                                <input type="hidden" id="productId" name="productId" value="{{ old('productId', $productId ?? '') }}" />
                                <input type="submit" value="Submit" class="btn btn-gradient-primary btn-sm px-3 mx-3"/>
                                <a id="btnPrint" href="#" class="btn btn-pink py-1 mr-2"><i class="fa fa-print"></i></a>
                                <button id="btnExport" class="btn btn-pink py-0 px-2"><i class="mdi mdi-file-excel font-18"></i></button>
                            </form>
                        </div>
                        <hr/>

                        @if(!$records || count($records) == 1)
                            <div class="alert alert-outline-warning alert-warning-shadow mb-0 alert-dismissible fade show" role="alert" style="width:50%; text-align:center; margin:auto">
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true"><i class="mdi mdi-close"></i></span>
                                </button>
                                <strong>Sorry!</strong> No Data Found!
                            </div>
                        @else
                            <div id="report-div">
                                <div class="row">
                                    <div class="col-lg-12">
                                        <div class="print-header">
                                            <h3 class="text-center pb-2" style="color:maroon">Aasaii Food Product</h3>
                                            <h3 class="text-center pb-2" style="color:blue">Item-wise Customer Report</h3>
                                            <h4 class="text-center pb-2" style="color:rgb(26, 75, 22)">{{$productName}}</h4>
                                            <h4 class="text-center pb-3">{{ formatDateRange($fromDate, $toDate) }}</h4>
                                        </div>
                                    </div>
                                </div>

                                <table id="reportTable">
                                    <thead class="thead-light">
                                        <tr>
                                            <th class="text-center">S.No</th>
                                            <th class="text-center">Invoice Date</th>
                                            <th class="text-center">Invoice No</th>
                                            <th class="text-left">Customer</th>
                                            <th class="text-left">Route</th>
                                            <th class="text-center">Category</th>
                                            <th class="text-right">Qty</th>
                                            <th class="text-right">Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($records as $index => $record)
                                            @if($record['date'] !== 'Total') 
                                                <tr>
                                                    <td class="text-center">{{ $index + 1 }}</td>
                                                    <td class="text-center">{{ $record['date'] }}</td>
                                                    <td class="text-center">{{ $record['invoice_num'] }}</td>
                                                    <td class="text-left">{{ $record['customer'] }}</td>
                                                    <td class="text-left">{{ $record['route'] }}</td>
                                                    <td class="text-center">{{ $record['category'] }}</td>
                                                    <td class="text-right">{{ getTwoDigitPrecision($record['qty']) }}</td>
                                                    <td class="text-right">{{ getTwoDigitPrecision($record['amount']) }}</td>
                                                </tr>
                                            @endif
                                        @endforeach
                                    </tbody>
                                    <tfoot class="thead-light">
                                        <tr>
                                            <th colspan="6" class="text-right">Grand Total</th>
                                            <th class="text-right">{{ getTwoDigitPrecision($records[count($records) - 1]['qty']) }}</th>
                                            <th class="text-right">{{ getTwoDigitPrecision($records[count($records) - 1]['amount']) }}</th>
                                        </tr>
                                    </tfoot>
                                </table>                                
                            </div>
                        @endif
                    </div><!--end card-body--> 
                </div><!--end card--> 
            </div> <!--end col-->                               
        </div><!--end row--> 
    </div><!-- container -->    
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

            $('#fromDate').change(function() {
                var date = $(this).val();
                $('#toDate').attr('min',date);
            });

            var products = new Map();              
            // Initialize the products map with the product data passed from the server
            @foreach($products as $product)
                products.set({{ $product->id }}, '{{ $product->short_name }}');
            @endforeach
            // Initialize the product input field
            $("#txtProduct").autocomplete({
                source: autocompleteSource(products),
                // autoFocus: true,
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

            $('#btnPrint').on("click", function () {                
                var originalContents = $('body').html();
                var printContents = $('#report-div').html();
                $('body').html(printContents);
                window.print();
                $('body').html(originalContents);
            });

            $('#btnExport').click(function(event) {
                event.preventDefault();
                const count = "{{ count($records) }}";
                if(count == 0) {
                    Swal.fire('Sorry','No data found to download','warning');
                }
                else {
                    var query = {
                        fromDate: $("#fromDate").val(),
                        toDate: $("#toDate").val(),
                        productId: $("#productId").val(),
                        productName: $("#txtProduct").val()
                    };
                    var url = "{{ route('export.customer.item-wise') }}?" + $.param(query);
                    window.location = url;
                }
            });

            $("#fromDate").trigger('change');
        });
    </script>
@endpush 

@section('footerScript')
    <script src="{{ asset('plugins/sweet-alert2/sweetalert2.min.js') }}"></script>
    <script src="{{ asset('assets/pages/jquery.sweet-alert.init.js') }}"></script>
@stop