@extends('app-layouts.admin-master')

@section('title', 'Day Route Denomination')

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
                    @slot('title') Day Route Denomination @endslot
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
                            <div class="row my-2">                                
                                <div class="col-md-3">
                                    Route <br/>
                                    <div class="mt-2">Date </div>
                                </div>
                                <div class="col-md-5">
                                    <div class="my-bold blue-text">{{ $allDenomination[0]['route'] }}</div>
                                    <div class="mt-2">
                                        {{getIndiaDate($date)}}                           
                                    </div>
                                </div>
                                <div class="col-md-4 text-right">
                                    <a href="#" class="btn btn-pink" style="padding-top:3px; padding-bottom:3px; margin-right:6px" id="btnPrint"><i class="fa fa-print"></i></a> 
                                    <a href="#" id="btnPrev" class="btn btn-info py-1 mr-2" data-toggle="tooltip" data-placement="top" title="Left Arrow (<)">Prev</a>
                                    <a href="#" id="btnNext" class="btn btn-secondary py-1" data-toggle="tooltip" data-placement="top" title="Right Arrow (>)">Next</a>
                                </div>
                            </div>
                           
                            <div class="row my-2">
                                <div class="col-md-3">Total Receipts</div>
                                <div class="col-md-9">
                                    {{ $allDenomination[0]['total_receipt'] }}                          
                                </div>
                            </div>
                            <div class="row my-2">
                                <div class="col-md-3">Amount </div>
                                <div class="col-md-9">                                    
                                            {{ "Rs. ".$allDenomination[0]['total_amount']}}
                                </div>
                            </div>
                        <!-- receipt Table Table -->
                        <div id="routeDenomination">
                        <h6 class="my-heading p-2 pt-1">Denomination :</h6>
                        <div class="row">
                            <div class="col-sm-5">
                                <div class="table-responsive">
                                    <table id="routeDenomTable" class="table table-sm table-bordered nowrap text-right" >
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
                                                    @foreach ($allDenomination[0]['denom_total'] as $amount => $count)
                                                        @if($amount == $note->note_value)
                                                            <td width="90px" style="border-right-width:0px; border-left-width:0px">
                                                                {{ $count }} &ensp; = 
                                                            </td>
                                                            @php 
                                                                $found = true; 
                                                                $total = $count * $note->note_value;
                                                            @endphp
                                                        @endif
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
                                               @foreach ($allDenomination[0]['denom_total'] as $amount => $count)
                                                    @if("1" == $amount) <!-- Check if the amount matches -->
                                                        <td width="90px" style="border-right-width:0px; border-left-width:0px">
                                                            {{$count}} &ensp; = 
                                                        </td>
                                                    
                                                        @php
                                                            $found = true; 
                                                            $total = $count * $amount;  
                                                        @endphp
                                                    @endif
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
                                                <th id="denomTotal" style="padding-right:20px">{{$allDenomination[0]['total_amount']}}</th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>                            
                         </div>
                       </div><!--end print-->
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
    var listId = @json($routeIds);
    var currentNo = @json($routeId);
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
        function getCurrentIndex(routeNum) {
            return listId.findIndex(item => item.route_id == routeNum);
        }

        $('#btnPrev').on("click", function () {                   
            var currentIndex = getCurrentIndex(currentNo);                               
            if (currentIndex > 0) {
                var prevRoute = listId[currentIndex -1].route_id;                                
                showOrder(prevRoute); 
            } else {
                Swal.fire('Sorry!', 'No Previous Denomination!', 'warning'); 
            }
        });
        $('#btnNext').on("click", function () {   
            var currentIndex = getCurrentIndex(currentNo);                          
            if (currentIndex < listId.length-1) {                
                var nextRoute = listId[currentIndex + 1].route_id;
                showOrder(nextRoute); // Show the next receipt
            } else {
                Swal.fire('Sorry!', 'No Next Denomination!', 'warning'); 
            }
        });

        // Function to show a specific receipt based on its ID
        function showOrder(Route) {  
            console.log(Route);           
            let form = $('<form>', {
                    'method': 'POST',
                    'action': '{{ route("route.denomination.view") }}'
                });

                // Add CSRF token
                var csrfToken = $('meta[name="csrf-token"]').attr('content');
                form.append($('<input>', {
                    'type': 'hidden',
                    'name': '_token',
                    'value': csrfToken
                }));                
                form.append($('<input>', {
                    'type': 'hidden',
                    'name': 'date',
                    'value': '{{ \Carbon\Carbon::parse($date)->format('Y-m-d') }}'  // Convert date to YYYY-MM-DD format using Carbon
                }));
                form.append($('<input>', {
                    'type': 'hidden',
                    'name': 'routeId',
                    'value': Route
                }));
                // Append form to body and submit
                form.appendTo('body').submit();
        }
        $('#btnPrint').click(function () {
            if (!$('#routeDenomination').length) {
                Swal.fire('Sorry!', 'No Data Found to Print', 'warning');
            } else {
                $('#btnPrint, #btnPrev, #btnNext').hide(); 
                var originalContents = $('body').html();
                
                // Dynamically update print content with actual data
                var printContents = `
                    <h2 class="mb-3">Day Route Denomination</h2>
                    <p>Route: <strong>{{ $allDenomination[0]['route'] }}</strong></p>
                    <p>Date: {{ getIndiaDate($date) }}</p>
                    <p>Total Receipts: {{ $allDenomination[0]['total_receipt'] }}</p>
                    <p>Amount: Rs. {{ $allDenomination[0]['total_amount'] }}</p>
                    `;
                printContents += $('#routeDenomination').html();
                // Print the content
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