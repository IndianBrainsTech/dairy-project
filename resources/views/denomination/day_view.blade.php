@extends('app-layouts.admin-master')

@section('title', 'Day Denomination')

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
                    @slot('title') Day Denomination @endslot
                    @slot('item1') Transactions @endslot
                    @slot('item2') Denomination @endslot
                @endcomponent
            </div><!--end col-->
        </div>
        <!-- end page title end breadcrumb -->
 
        <div class="row"> 
            <div class="col-lg-9 mx-auto">
                <div class="card">
                    <div class="card-body" id="routeDenomination">
                        <!-- Order Info -->
                        <div class="px-2">                            
                            <div class="row my-2">                                
                                <div class="col-md-3">                                   
                                    <div class="mt-2">Date </div>
                                </div>
                                <div class="col-md-5">                                    
                                    <div class="my-bold blue-text mt-2">
                                        {{getIndiaDate($date)}}                           
                                    </div>
                                </div>
                                <div class="col-md-4 text-right">
                                    <a href="#" class="btn btn-pink" style="padding-top:3px; padding-bottom:3px; margin-right:6px" id="btnPrint"><i class="fa fa-print"></i></a>                                     
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
                        {{-- start note table --}}
                        <div id="dayDenomination">
                            <h6 class="my-heading p-2 pt-1">Note Count :</h6>
                            <div class="row">
                                <div class="col-sm-10">
                                    <div class="table-responsive">
                                        <table id="routeDenomTable" class="table table-sm table-bordered nowrap text-right">
                                            <thead class="thead-light">
                                                <tr>
                                                    <th class="text-center">S. No</th>
                                                    <th class="text-center">Route</th>
                                                    @foreach($notes as $note)
                                                        <th class="text-center">{{ $note->note_value }}</th>
                                                    @endforeach
                                                    <th class="text-center">Coins</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                {{-- Loop to fill the table --}}
                                                @foreach($routeNote as $index => $route)
                                                    <tr>
                                                        <td class="text-center">{{ $index + 1 }}</td>
                                                        <td class="text-left">{{ $route['route'] }}</td>
                                                        @foreach($notes as $note)
                                                            <td class="text-center">{{ $route['denom'][$note->note_value] ?? "" }}</td>
                                                        @endforeach
                                                        <td class="text-center">{{ $route['denom'][1] ?? "" }}</td> <!-- Coins column -->
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                            <tfoot class="thead-light">
                                                <tr>
                                                    <th colspan="2">Total</th>
                                                    @foreach($notes as $note)
                                                        @php
                                                            $totalForNote = 0;
                                                            foreach($routeNote as $route) {
                                                                $totalForNote += $route['denom'][$note->note_value] ?? 0;
                                                            }
                                                        @endphp
                                                        <th class="text-center">{{ $totalForNote }}</th>
                                                    @endforeach
                                                    @php
                                                        $totalForCoins = 0;
                                                        foreach($routeNote as $route) {
                                                            $totalForCoins += $route['denom'][1] ?? 0;
                                                        }
                                                    @endphp
                                                    <th class="text-center">{{ $totalForCoins }}</th>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
                            </div>                                            
                        {{-- end note count --}}
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
                                                    @php 
                                                        $found = false; 
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
                                                @php 
                                                    $found = false; 
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
                                                <th id="denomTotal" style="padding-right:20px">{{ $allDenomination[0]['total_amount'] }}</th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>                            
                        </div>
                    </div><!--end printing-->
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
    $(document).ready(function () {        
        $('#btnPrint').click(function () {
            if (!$('#dayDenomination').length) {
                Swal.fire('Sorry!', 'No Data Found to Print', 'warning');
            } else {
                $('#btnPrint, #btnPrev, #btnNext').hide(); 
                var originalContents = $('body').html();
                
                // Dynamically update print content with actual data
                var printContents = `
                    <h2 class="mb-3">Day Denomination</h2>                    
                    <p>Date: <strong>{{ getIndiaDate($date) }}</strong></p>
                    <p>Total Receipts: {{ $allDenomination[0]['total_receipt'] }}</p>
                    <p>Amount: Rs. {{ $allDenomination[0]['total_amount'] }}</p>
                    `;               
                
                printContents += $('#dayDenomination').html();
                
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
