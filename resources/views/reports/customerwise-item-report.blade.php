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
                    @slot('title') Customer wise Item Report @endslot
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
                            <form method="post" action="{{route('report.item.customer-wise') }}" class="d-flex">
                                @csrf
                                <label class="my-text">From</label>
                                <input type="date" name="fromDate" id="fromDate" value="{{$fromDate}}" class="my-control">
                                <label class="my-text">To</label>
                                <input type="date" name="toDate" id="toDate" value="{{$toDate}}" class="my-control">
                                <div class="input-group mx-2" style="width:350px">
                                    <span class="input-group-prepend">
                                        <button type="button" class="btn btn-info"><i class="fas fa-search"></i></button>
                                    </span>
                                    <input type="text" name='customer' id="customer" class="form-control" placeholder="Customer">
                                    <input type="hidden" name="customerId" id="customerId">
                                </div>
                                <input type="submit" value="Submit" class="btn btn-gradient-primary btn-sm px-3 mx-3"/>
                                <a id="btnPrint" href="#" class="btn btn-pink py-1 mr-2"><i class="fa fa-print"></i></a>
                                <button id="btnExport" class="btn btn-pink py-0 px-2"><i class="mdi mdi-file-excel font-18"></i></button>
                            </form>
                        </div>
                        <hr/>

                        @if(!$records)
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
                                            <h4 class="text-center pb-2" style="color:rgb(26, 75, 22)">{{$customer->customer_name}}</h4>
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
                                            <th class="text-left">Product</th>                                           
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
                                                    <td class="text-left">{{ $record['product'] }}</td>                                                    
                                                    <td class="text-center">{{ $record['category'] }}</td>
                                                    <td class="text-right">{{ getTwoDigitPrecision($record['qty']) }}</td>
                                                    <td class="text-right">{{ getTwoDigitPrecision($record['amount']) }}</td>
                                                </tr>
                                            @endif
                                        @endforeach
                                    </tbody>
                                    <tfoot class="thead-light">
                                        <tr>
                                            <th colspan="5" class="text-right">Grand Total</th>
                                            <th class="text-right">{{ getTwoDigitPrecision($totals['qty']) }}</th>
                                            <th class="text-right">{{ getTwoDigitPrecision($totals['amount']) }}</th>
                                        </tr>
                                    </tfoot>
                                </table>                                
                           
                        @endif
                        @if($summary)
                            <table id="reportTable" class="mt-4" style="width:360px; margin:auto">
                                <thead class="thead-light">
                                    <tr>
                                        <th colspan="2" class="text-center">Product Summary</th>                                  
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $totQty = 0;
                                    @endphp
                                    @foreach($summary as $product => $count)
                                        @php
                                            $totQty += $count['qty'];
                                        @endphp
                                        <tr>
                                            <td class="text-left">{{ $product }}</td>
                                            <td class="text-center">{{ getTwoDigitPrecision($count['qty']) }}</td>                                            
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="thead-light">
                                    <tr>
                                        <th colspan="1" class="text-right">Grand Total</th>                                    
                                        <th class="text-center">{{ getTwoDigitPrecision($totQty) }}</th>
                                    </tr>
                                </tfoot>
                            </table>    
                        @endif
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

            @if (!empty($customer))
                $("#customerId").val('{{ $customer->id }}');
                $("#customer").val('{{ $customer->customer_name }}');
            @endif

            let customers = new Map();
            @foreach($customers as $customer)
                var key = '{{$customer->customer_name}}';
                var value = '{{$customer->id}}';
                customers.set(key,value);
            @endforeach

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

            $("#customer").autocomplete({
                source: autocompleteSource(customers),                
                minLength: 0,
                select: function(event, ui) {
                    var name = ui.item.value;
                    var id = customers.get(name);
                    console.log("Selected ID: " + id + ", Name: " + name);
                    $("#customerId").val(id);
                }
            });

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
                        customerId: $("#customerId").val(),                        
                    };
                    var url = "{{ route('export.item.customer-wise') }}?" + $.param(query);
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