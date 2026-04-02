@extends('app-layouts.admin-master')

@section('title', 'Receipt Denomination')

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
                    @slot('title') Receipt Denomination @endslot
                    @slot('item1') Transactions @endslot
                    @slot('item2') Denomination @endslot
                @endcomponent
            </div><!--end col-->
        </div>
        <!-- end page title end breadcrumb -->
 
        <div class="row"> 
            <div class="col-lg-9 mx-auto">
                <div class="card">
                    <div class="card-body">

                        <!-- Order Info -->
                        <div class="px-2">
                            @if(count($denomination[0]['customer']) == 1)
                            <div class="row my-2">                                
                                <div class="col-md-3">
                                    Date <br/>
                                    <div class="mt-2">Customer </div>
                                </div>
                                <div class="col-md-5">
                                    <div class="my-bold blue-text">{{ getIndiaDate($denomination[0]['date']) }}</div>
                                    <div class="mt-2">
                                        @foreach ($denomination[0]['customer'] as $customer)
                                            {{ $customer }} <!-- Display customer name -->
                                            @if(!$loop->last), @endif <!-- Add comma only if not the last item -->
                                        @endforeach
                                    </div>
                                </div>
                                <div class="col-md-4 text-right">
                                    <a href="#" class="btn btn-pink" style="padding-top:3px; padding-bottom:3px; margin-right:6px" id="btnPrint"><i class="fa fa-print"></i></a> 
                                    <a href="#" id="btnPrev" class="btn btn-info py-1 mr-2" data-toggle="tooltip" data-placement="top" title="Left Arrow (<)">Prev</a>
                                    <a href="#" id="btnNext" class="btn btn-secondary py-1" data-toggle="tooltip" data-placement="top" title="Right Arrow (>)">Next</a>
                                </div>
                            </div>
                           
                            <div class="row my-2">
                                <div class="col-md-3">Receipt No </div>
                                <div class="col-md-9">
                                    @foreach ($denomination[0]['receipt_num'] as $receipt)
                                            {{ $receipt }} <!-- Display customer name -->
                                            @if(!$loop->last), @endif <!-- Add comma only if not the last item -->
                                    @endforeach
                                </div>
                            </div>
                            <div class="row my-2">
                                <div class="col-md-3">Amount </div>
                                <div class="col-md-9">                                    
                                            {{ "Rs. ".$denomination[0]['amount']}}
                                </div>
                            </div>                                                    
                            @else                                
                                <div class="row my-2">                                
                                    <div class="col-md-3 col-sm-4">
                                        Date <br/>
                                        <div class="mt-2">Amount </div>
                                    </div>
                                    <div class="col-md-5 col-sm-4">
                                        <div class="my-bold blue-text">{{ getIndiaDate($denomination[0]['date']) }}</div>
                                        <div class="mt-1">{{ "Rs. ".$denomination[0]['amount']}}</div>                                        
                                    </div>
                                    <div class="col-md-4 col-sm-4 text-right">
                                        <a href="#" class="btn btn-pink" style="padding-top:3px; padding-bottom:3px; margin-right:6px" id="btnPrint"><i class="fa fa-print"></i></a> 
                                        <a href="#" id="btnPrev" class="btn btn-info py-1 mr-2" data-toggle="tooltip" data-placement="top" title="Left Arrow (<)">Prev</a>
                                        <a href="#" id="btnNext" class="btn btn-secondary py-1" data-toggle="tooltip" data-placement="top" title="Right Arrow (>)">Next</a>
                                    </div>
                                </div>
                                <div class="row mt-4">
                                    <div class="col-md-8">
                                        <div class="table-responsive">
                                            <table id="deTable" class="table table-sm table-bordered nowrap text-right" >
                                                <thead class="thead-light">
                                                    <tr>
                                                        <th class="text-center">S.No</th>
                                                        <th class="text-center">Customer</th>
                                                        <th class="text-center">Receipt</th>
                                                        <th class="text-center">Amount</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @for ($i = 0; $i < count($denomination[0]['customer']); $i++)
                                                        <tr>
                                                            <td class="text-center">{{ $i + 1 }}</td>
                                                            <td class="text-left">{{ $denomination[0]['customer'][$i] }}</td>
                                                            <td class="text-center">{{ $denomination[0]['receipt_num'][$i] }}</td>
                                                            <td class="text-center">{{ $denomination[0]['cus_amount'][$i] }}</td>
                                                        </tr>
                                                    @endfor
                                                </tbody>
                                                                       
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            @endif

                        <!-- receipt Table Table -->
                        <div id="receipt_denomination">
                        <h6 class="my-heading p-2 pt-1">Denomination :</h6>
                        <div class="row">
                            <div class="col-sm-5">
                                <div class="table-responsive">
                                    <table id="denomTable" class="table table-sm table-bordered nowrap text-right" >
                                        <thead class="thead-light">
                                            <tr>
                                                <th class="text-center">Note</th>
                                                <th class="text-center">Count</th>
                                                <th class="text-center">Amount</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($notes as $note)                                            
                                                <tr>                                                                                                 
                                                    <td width="70px" style="border-right-width:0px"> {{ $note->note_value }} &ensp; X </td>
                                                    @php $found = false; 
                                                    $total = null;
                                                    @endphp
                                                    @foreach ($denomination[0]['denomination'] as $denom)
                                                        @foreach ($denom as $amount => $count)
                                                            @if($amount == $note->note_value)                                                                
                                                                <td width="90px" style="border-right-width:0px; border-left-width:0px">
                                                                    {{ $count }} &ensp; = 
                                                                </td>
                                                                @php $found = true; 
                                                                 $total=$count * $note->note_value
                                                                 @endphp
                                                            @endif
                                                        @endforeach
                                                    @endforeach

                                                    <!-- If no match found for the note_value, show 0 -->
                                                    @if(!$found)
                                                    <td width="90px" style="border-right-width:0px; border-left-width:0px">
                                                         &ensp; = 
                                                    </td>
                                                    @endif
                                                    <td width="70px" style="border-left-width:0px; padding-right:20px" id="noteAmt{{$note->note_value}}">{{$total}}</td>                                                    
                                                </tr>
                                            @endforeach
                                            <tr>
                                                <td width="70px" style="border-right-width:0px"> Coins </td>
                                                @php $found = false; 
                                                    $total = null;
                                                @endphp                                                
                                                @foreach ($denomination[0]['denomination'] as $denom)
                                                        @foreach ($denom as $amount => $count)
                                                            @if( "1" == $amount)       
                                                                <td width="90px" style="border-right-width:0px; border-left-width:0px"> {{$count}} &ensp; = </td>                                                                
                                                            @php $found = true; 
                                                                $total=$count * $amount
                                                            @endphp
                                                            @endif
                                                        @endforeach
                                                @endforeach
                                                @if(!$found)
                                                    <td width="90px" style="border-right-width:0px; border-left-width:0px">
                                                         &ensp; = 
                                                    </td>
                                                @endif
                                                <td width="70px" style="border-left-width:0px; padding-right:20px" id="noteAmt1">{{$total}}</td>
                                            </tr>
                                        </tbody>
                                        <tfoot class="thead-light">
                                            <tr>
                                                <th colspan="2">Total</th>
                                                <th id="denomTotal" style="padding-right:20px">{{$denomination[0]['amount']}}</th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div><!--end print-->
                        </div>                    
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
    var listId = @json($listId);
    var currentNo = @json($receiptNo);
    console.log(listId);
    console.log(currentNo);
    $(document).ready(function () {
        $(document).on('keydown', function(event) {
            if (event.key === 'ArrowLeft') {
                $('#btnPrev').click();
            }
            else if (event.key === 'ArrowRight') {
                $('#btnNext').click();
            }
        });        
        function extractNumber(receiptNum) {
            return parseInt(receiptNum.replace('RC-', ''));
        }        
        function formatReceiptNumber(number) {
            return 'RC-' + number.toString().padStart(3, '0');
        }        
        function getCurrentIndex(receiptNum) {
            return listId.findIndex(item => item.receipt_num === receiptNum);
        }

        $('#btnPrev').on("click", function () {
            var currentReceipt = currentNo[0];             
            var currentIndex = getCurrentIndex(currentReceipt);
            console.log(currentIndex);
            if (currentIndex < listId.length-1) {
                var prevReceipt = listId[currentIndex +1].receipt_num;                
                showOrder([prevReceipt]); 
            } else {
                Swal.fire('Sorry!', 'No Previous Denomination!', 'warning'); 
            }
        });
        $('#btnNext').on("click", function () {
            var currentReceipt = currentNo[currentNo.length - 1];             
            var currentIndex = getCurrentIndex(currentReceipt); 
            console.log(currentIndex);

            if (currentIndex > 0) {                
                var nextReceipt = listId[currentIndex - 1].receipt_num;
                showOrder([nextReceipt]); // Show the next receipt
            } else {
                Swal.fire('Sorry!', 'No Next Denomination!', 'warning'); 
            }
        });

        // Function to show a specific receipt based on its ID
        function showOrder(receiptId) {
            console.log(receiptId);
            let form = $('<form>', {
                    'method': 'POST',
                    'action': '{{ route("receipt.denomination.view") }}'
                });

                // Add CSRF token
                var csrfToken = $('meta[name="csrf-token"]').attr('content');
                form.append($('<input>', {
                    'type': 'hidden',
                    'name': '_token',
                    'value': csrfToken
                }));

                // Add receipt ID
                form.append($('<input>', {
                    'type': 'hidden',
                    'name': 'id',
                    'value': receiptId
                }));
                form.append($('<input>', {
                    'type': 'hidden',
                    'name': 'date',
                    'value': '{{ \Carbon\Carbon::parse($date)->format('Y-m-d') }}'  // Convert date to YYYY-MM-DD format using Carbon
                }));
                form.append($('<input>', {
                    'type': 'hidden',
                    'name': 'routeId',
                    'value': {{$routeId}}
                }));
                // Append form to body and submit
                form.appendTo('body').submit();
        }
        $('#btnPrint').click(function () {
            if (!$('#receipt_denomination').length) {
                Swal.fire('Sorry!', 'No Data Found to Print', 'warning');
            } else {
                $('#btnPrint, #btnPrev, #btnNext').hide(); 
                var originalContents = $('body').html(); 
                var printContents = '<h2 class="mb-4">Receipt Denomination</h2>';
                @if(count($denomination[0]['customer']) == 1)
                    printContents += `
                        <p>Date: <strong>{{ getIndiaDate($denomination[0]['date']) }}</strong></p>
                        <p>Amount: Rs. {{ $denomination[0]['amount'] }}</p>
                        <p>Customer: 
                            @foreach ($denomination[0]['customer'] as $customer)
                                {{ $customer }}@if(!$loop->last), @endif
                            @endforeach
                        </p>
                        <p>Receipt No: 
                            @foreach ($denomination[0]['receipt_num'] as $receipt)
                                {{ $receipt }}@if(!$loop->last), @endif
                            @endforeach
                        </p>
                    `;
                @else
                    // Multiple customers case
                    printContents += `
                        <p>Date: <strong>{{ getIndiaDate($denomination[0]['date']) }}</strong></p>
                        <p>Total Amount: Rs. {{ $denomination[0]['amount'] }}</p>
                        <table class="table table-sm table-bordered nowrap text-right">
                            <thead>
                                <tr>
                                    <th class="text-center">S.No</th>
                                    <th class="text-center">Customer</th>
                                    <th class="text-center">Receipt No</th>
                                    <th class="text-center">Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                @for ($i = 0; $i < count($denomination[0]['customer']); $i++)
                                    <tr>
                                        <td class="text-center">{{ $i + 1 }}</td>
                                        <td class="text-left">{{ $denomination[0]['customer'][$i] }}</td>
                                        <td class="text-center">{{ $denomination[0]['receipt_num'][$i] }}</td>
                                        <td class="text-center">{{ $denomination[0]['cus_amount'][$i] }}</td>
                                    </tr>
                                @endfor
                            </tbody>
                        </table>
                    `;
                @endif
                printContents += $('#receipt_denomination').html();
                $('body').html(printContents);
                window.print();
                $('body').html(originalContents);
                $('#btnPrint, #btnPrev, #btnNext').show();
            }
        });
    });

</script>
@endpush

@section('footerScript')
    <!-- Sweet-Alert  -->
    <script src="{{ URL::asset('plugins/sweet-alert2/sweetalert2.min.js')}}"></script>
    <script src="{{ URL::asset('assets/pages/jquery.sweet-alert.init.js')}}"></script>
@stop