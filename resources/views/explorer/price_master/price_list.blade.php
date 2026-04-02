@extends('app-layouts.admin-master')

@section('title', 'Price List')

@section('headerStyle')
    <link href="{{ asset('plugins/datatables/dataTables.bootstrap4.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/css/my-style.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('assets/css/my-actxt.css') }}" rel="stylesheet" type="text/css">
    <style type="text/css">
        .my-control {
            padding: 6px 10px;
            margin-right: 16px;
        }
        #priceTable tr.head-row {
            background-color: #b8c9bf;
            height: 38px;
            font-weight: bold;
            font-size: 15px;
            vertical-align: middle;            
        }
        #priceTable tr.head-row th {
            vertical-align: middle; /* Vertically center text */
        }
        #priceTable tr.sub-head-row {
            background-color: #f1f1f1;
            font-weight: 600;
        }
    </style>
@stop

@section('content')
    <div class="container-fluid">
        <!-- Page-Title -->
        <div class="row">
            <div class="col-sm-12">
                @component('app-components.breadcrumb-3')
                    @slot('title') Price List @endslot
                    @slot('item1') Data Explorer @endslot
                    @slot('item2') Price Master @endslot
                @endcomponent
            </div><!--end col-->
        </div>
        <!-- end page title end breadcrumb -->
 
        <div class="row"> 
            <div class="col-lg-8 col-md-12 col-sm-12">
                <div class="card">
                    <div class="card-body">
                        <form method="post" action="{{ route('price.list') }}">
                            @csrf
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="row mb-2">
                                        <div class="col-md-9 col-sm-7 d-flex align-items-center">
                                            <div class="input-group mr-3" style="width:375px">
                                                <span class="input-group-prepend">
                                                    <button type="button" class="btn btn-info"><i class="fas fa-search"></i></button>
                                                </span>
                                                <input type="text" id="customer" class="form-control" placeholder="Customer">
                                                <input type="hidden" name="customerId" id="customerId" value="{{$customerId}}">
                                            </div>
                                            <input type="submit" value="Submit" class="btn btn-primary btn-sm ml-3 px-3"/>
                                        
                                        </div>                                                                  
                                    </div>
                                </div>
                            </div>                            
                        </form><!--end form-->
                        <hr/>
                          <div class="table-responsive dash-social">
                            <table id="priceTable" class="table table-sm table-bordered dt-responsive nowrap" style="border-collapse: collapse; border-spacing: 0; width: 100%">
                                <thead>
                                    <tr class="head-row"> 
                                        <th class="pl-2" colspan="5">Associated Price Master</th>                                        
                                    </tr>
                                    <tr> 
                                        <th data-priority="4" class="text-center">S.No</th>
                                        <th data-priority="1" class="text-center">Txn ID</th>
                                        <th data-priority="1" class="text-center">Entry Date</th>
                                        <th data-priority="2" class="text-center">Effect Date</th>
                                        <th data-priority="3" class="text-center">Narration</th>
                                    </tr>
                                </thead>
                                <tbody class="priceMasters">
                                    @if($priceMasters->isEmpty())
                                        <tr>
                                            <td class="text-center" colspan="5">No data available in table</td>       
                                        </tr>      
                                    @else
                                        @foreach ($priceMasters as $index => $master)
                                            <tr>
                                                <td class="text-center">{{ $index + 1 }}</td>                                            
                                                <td class="text-center">{{ $master->txn_id }}</td>
                                                <td class="text-center">{{ $master->txn_date }}</td>
                                                <td class="text-center">{{ $master->effect_date }}</td>
                                                <td class="text-center">{{ $master->narration }}</td>
                                            </tr>
                                        @endforeach
                                    @endif            
                                </tbody>                                
                            </table>
                            </div>
                            {{-- 2nd Table Start--}}
                            <div class="table-responsive dash-social">
                            <table id="priceTable" class="mt-4 table table-sm table-bordered dt-responsive nowrap" style="border-collapse: collapse; border-spacing: 0; width: 100%">
                                
                                <thead>
                                    <tr class="head-row"> 
                                        <th class="pl-2" colspan="4">Price List</th>                                        
                                    </tr>
                                    <tr> 
                                        <th data-priority="4" class="text-center">S.No</th>
                                        <th data-priority="1" class="text-center">Product</th>
                                        <th data-priority="2" class="text-center">Price</th>
                                        <th data-priority="3" class="text-center">Base</th>
                                    </tr>
                                </thead>   
                                <tbody>
                                    @if($customerId != 0)
                                    @foreach ($priceListAll as $index => $list)
                                        <tr>
                                            <td class="text-center">{{ $index + 1 }}</td>                                            
                                            <td class="text-left">{{ $list['product']['name']}}</td>
                                            <td class="text-right">{{ $list ['price']." / ".$list ['unit'] }}</td>
                                            <td class="text-center"> {{ array_key_exists('txn_id', $list) ? $list['txn_id'] : "Standard" }}</td>
                                        </tr>
                                    @endforeach     
                                    @else   
                                        <tr>
                                            <td colspan = "4" class="text-center">No data available in table</td>       
                                        </tr>      
                                    @endif                                        
                                </tbody>                              
                            </table>
                            {{-- 2nd table end --}}
                        </div> 
                    </div><!--end card-body-->
                </div><!--end card-->
            </div> <!--end col-->
        </div><!--end row-->
    </div>
@stop

@push('custom-scripts')
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.js"></script>
    <script src="{{ asset('assets/js/input-restriction.js') }}"></script>
    <script src="{{ asset('assets/js/helper.js') }}"></script>
    <script>
        $(document).ready(function () {
            $.ajaxSetup({
                headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
            });
            var row = "{{ $row }}";            
            if (row) {
                var rowIndex = parseInt(row) - 1;                
                $(".priceMasters tr:eq(" + rowIndex + ")").addClass("table-primary");
            }
            let customers = new Map();
            doInit();

            function doInit() {
                loadCustomers();
                $('#datatable').dataTable( {
                    "lengthMenu": [[10, 25, 50, 100], [10, 25, 50, 100]],
                    "pageLength": 50,
                } );
            }            

            function loadCustomers() {
            let routeId = 0;            
            customers = new Map();
            $("#customer").val('');
            let url = "{{ route('customers.get.route', ':id') }}".replace(':id', routeId);
            $.get(url, function (data) {
                var customerList = data.customers;
                customerList.forEach(function(customer) {
                    customers.set(customer.id, customer.customer_name); // key, value
                });

                // Update the autocomplete source after updating customers
                $("#customer").autocomplete('option', 'source', autocompleteSource1(customers));

                // Show customer name
                let custId = parseInt($("#customerId").val());
                if(custId) {
                    const customer = customers.get(custId);
                    $("#customer").val(customer);
                }
            });
            }

            $("#customer").autocomplete({
                source: autocompleteSource1(customers),
                autoFocus: true,
                minLength: 0,
                select: function(event, ui) {
                    var name = ui.item.value;
                    var id = getKeyByValue(customers, name);
                    console.log("Customer => Selected ID: " + id + ", Name: " + name);
                    $("#customerId").val(id);
                }
            });

            $('#customer').blur(function () {
                if(!$("#customer").val())
                    $("#customerId").val(0);
            });            
        });
    </script>
@endpush

@section('footerScript')
    <script src="{{ asset('plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('plugins/datatables/dataTables.bootstrap4.min.js') }}"></script>
@stop