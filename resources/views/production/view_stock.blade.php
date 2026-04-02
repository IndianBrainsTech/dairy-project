@extends('app-layouts.admin-master')

@section('title', 'View Stock')

@section('headerStyle')
    <link href="{{ URL::asset('plugins/sweet-alert2/sweetalert2.min.css')}}" rel="stylesheet" type="text/css">
    <link href="{{ URL::asset('assets/css/my-style.css')}}" rel="stylesheet" type="text/css">
@stop

@section('content')
    <div class="container-fluid">
        <!-- Page-Title -->
        <div class="row">
            <div class="col-sm-12">
                @component('app-components.breadcrumb-3')
                    @slot('title') View Stock @endslot
                    @slot('item1') Transactions @endslot
                    @slot('item2') Production @endslot
                @endcomponent
            </div><!--end col-->
        </div>
        <!-- end page title end breadcrumb -->
 
        <div class="row"> 
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-body">

                        <!-- Order Info -->
                        <div class="px-2">
                            <div class="row my-2">
                                <div class="col-md-2 col-sm-2">
                                    Txn ID <br/>
                                    <div class="mt-2">Date</div>
                                </div>
                                <div class="col-md-6 col-sm-5">
                                    <div class="my-bold blue-text">{{ $txn_id }}</div>
                                    <div class="mt-2">{{ getIndiaDate($entryDate) }}</div>
                                </div>
                                <div class="col-md-4 col-sm-5 text-right">
                                    <a href="#" id="btnPrev" class="btn btn-info py-1 mr-2" data-toggle="tooltip" data-placement="top" title="Left Arrow (<)">Prev</a>
                                    <a href="#" id="btnNext" class="btn btn-secondary py-1" data-toggle="tooltip" data-placement="top" title="Right Arrow (>)">Next</a>
                                </div>
                            </div>                           
                            {{-- <div class="row my-2">
                                <div class="col-md-3">Invoice Date</div>
                                <div class="col-md-9">{{ $order['invoice_date'] }}</div>
                            </div> --}}
                        </div>

                        <!-- Order Table -->
                        <h6 class="my-heading p-2 pt-3">Entry Stock Details:</h6>
                        <div class="table-responsive dash-social px-2">
                            <table id="tableOrderedItems" class="table table-bordered table-sm">
                                <thead class="thead-light">
                                    <tr>
                                        <th class='text-center'>S.No</th>                                        
                                        <th>Product</th>
                                        <th>Batch NO</th>
                                        <th class="text-center">Qty</th>                                        
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($stockEntries as $entry)
                                        <tr>
                                            <td class='text-center'>{{ $loop->index + 1 }}</td>
                                            <td>{{ $entry->product_name }}</td>
                                            <td>{{ $entry->batch_no }}</td>                                            
                                            <td class="text-center">{{ $entry->entry_qty." ".$entry->entry_unit }}</td>                                                                                   
                                        </tr>
                                    @endforeach
                                </tbody>
                                {{-- <tfoot>
                                    <tr class="thead-light">
                                        <th colspan="5" class="text-center">Total</th>
                                        <th id="orderTotalAmt" class='text-right'></th>
                                        <th id="orderTotalTax" class='text-right'></th>
                                        <th id="orderTotalTotal" class='text-right'></th>
                                        <th id="orderTotalDisc" class='text-right odc'></th>
                                    </tr>                                    
                                </tfoot> --}}
                            </table>
                        </div>                       
                            <hr/>                            
                            @if($entryDate == date('Y-m-d'))                           
                            <div class="text-center">                                
                                <a class="btn btn-dark px-3 py-1 mr-3" href="{{route('entry.edit',['code'=>$txn_id])}}">Edit Entry</a>
                            </div>
                            @endif
                    </div><!--end card-body-->
                </div><!--end card-->
            </div><!--end col-->
        </div><!--end row-->
    </div>   
   
@stop

@push('custom-scripts')
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.js"></script>
    <script src="{{ URL::asset('assets/js/helper.js')}}"></script>
    <script>
    var existingTxnIds = @json($existingTxnIds);      
    $(document).ready(function () {
        var txnId = "{{ $txn_id }}";        
        var entryDate = "{{ $entryDate }}";

        $(document).on('keydown', function(event) {
            if (event.key === 'ArrowLeft') {
                loadPreviousStock(txnId, entryDate); 
            } else if (event.key === 'ArrowRight') {
                loadNextStock(txnId, entryDate);  
            }
        });

        // Button click for previous entry
        $('#btnPrev').on("click", function () {
            loadPreviousStock(txnId, entryDate);
        });

        // Button click for next entry
        $('#btnNext').on("click", function () {
            loadNextStock(txnId, entryDate);
        });

        function loadPreviousStock(txnId, entryDate) {   
            var firstIndexId = existingTxnIds[0];        
            var firstTxnId = parseInt(firstIndexId.split('-')[1]);       
            var currentTxnNumber = parseInt(txnId.split('-')[1]);            
            if (currentTxnNumber > firstTxnId) {              
                var previousTxnNumber = currentTxnNumber - 1;  
                var previousTxnId = 'ST-' + String(previousTxnNumber).padStart(3, '0');                  
                showStock(previousTxnId, entryDate);
            } else {
                Swal.fire('No Previous Stock Entry', 'This is the first stock entry.', 'warning');
            }
        }

        function loadNextStock(txnId, entryDate) {            
            var currentTxnNumber = parseInt(txnId.split('-')[1]);            
            var nextTxnNumber = currentTxnNumber + 1;  
            var nextTxnId = 'ST-' + String(nextTxnNumber).padStart(3, '0');     
            showStock(nextTxnId, entryDate);
        }

        function showStock(txnId, entryDate) {            
            var form = $('<form>', {
                'method': 'POST',
                'action': "{{ route('stock.show') }}"  
            });           
            var csrfToken = $('meta[name="csrf-token"]').attr('content');
            form.append($('<input>', {
                'type': 'hidden',
                'name': '_token',
                'value': csrfToken
            }));           
            form.append($('<input>', {
                'type': 'hidden',
                'name': 'stock_num',
                'value': txnId
            }));
            form.append($('<input>', {
                'type': 'hidden',
                'name': 'entryDate',
                'value': entryDate
            }));    

            if (!existingTxnIds.includes(txnId)) {
                // Show alert if txn_id does not exist
                Swal.fire('Stock Not Found', 'This is the last stock entry.', 'warning');
            } else {
                // Append the form and submit it
                $('body').append(form);
                form.submit();
            }
        
        }
   });

</script>
@endpush

@section('footerScript')
    <!-- Sweet-Alert  -->
    <script src="{{ URL::asset('plugins/sweet-alert2/sweetalert2.min.js')}}"></script>
    <script src="{{ URL::asset('assets/pages/jquery.sweet-alert.init.js')}}"></script>
@stop