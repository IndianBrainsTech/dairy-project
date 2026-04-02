@extends('app-layouts.admin-master')

@section('title', 'Closing Balance')

@section('headerStyle')
    <link href="{{ asset('plugins/sweet-alert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('assets/css/my-style.css') }}" rel="stylesheet" type="text/css">
@stop

@section('content')
    <div class="container-fluid">
        <!-- Page-Title -->
        <div class="row">
            <div class="col-sm-12">
                @component('app-components.breadcrumb-3')
                    @slot('title') Closing Balance @endslot
                    @slot('item1') Transactions @endslot
                    @slot('item2') Expenses @endslot
                @endcomponent
            </div><!--end col-->
        </div>
        <!-- end page title end breadcrumb -->
 
        <div class="row"> 
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <form action="{{ route('closing.balance') }}">
                                @csrf
                                <input type="date" name="date" id="date" value="{{$date}}" class="my-control ml-2">
                                <input type="submit" value="Submit" class="btn btn-primary btn-sm ml-3 px-3"/>  
                            </form>
                        </div> 
                        <hr/>     
                        <div class="row">        
                            <div class="col-6" style="margin-top:26px">                                                                                                     
                                    <div class="table-responsive">
                                        <table class="table border-dashed mb-0">
                                            <thead class="thead-light">
                                                <tr>
                                                    <th class="border-bottom-0">Particular</th>
                                                    <th class="border-bottom-0 text-right">Amount</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <th class="border-top-0 text-dark" scope="row">
                                                        <i class="fas fa-coins text-success font-24 mr-2 align-middle"></i>Opening Amount
                                                    </th>
                                                    <td class="border-top-0 text-right">{{$opening_amount}}</td>
                                                </tr>
                                                <tr>
                                                    <th class="text-dark" scope="row">
                                                        <i class="fas fa-hand-holding-usd text-primary font-24 mr-2 align-middle"></i>Receipt Amount
                                                    </th>
                                                    <td class="text-right">{{$receipt['total_amount']}}</td>                                                 
                                                </tr>
                                                <tr>
                                                    <th class="text-dark" scope="row">
                                                        <i class="fas fa-money-bill-wave text-warning font-24 mr-2 align-middle"></i>Expense Amount
                                                    </th>
                                                    <td class="text-right">{{$expense['total_amount']}}</td>
                                                </tr>
                                                <tr>
                                                    <th class="text-dark" scope="row">
                                                        <i class="fas fa-piggy-bank text-pink font-24 mr-2 align-middle"></i>Closing Amount
                                                    </th>
                                                    <td class="text-right">{{$closing_balance['total_amount']}}</td>
                                                </tr>
                                            </tbody>
                                        </table><!--end /table-->
                                        
                                    </div>                                
                            </div>
                            <div class="col-6">
                                    <h6 class="my-heading mt-0">Denomination :</h6>                                    
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
                                                                @foreach ($closing_balance['denom_total'] as $amount => $count)
                                                                    @if($amount == $note->note_value)
                                                                        <td width="90px" style="border-right-width:0px; border-left-width:0px">
                                                                            {{ $count }} &ensp; = 
                                                                        </td>
                                                                        @php 
                                                                            $found = true; 
                                                                            $total = $count * $note->note_value;
                                                                            if ($total === 0) {
                                                                                $total = null;
                                                                            }
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
                                                           @foreach ($closing_balance['denom_total'] as $amount => $count)
                                                                @if("1" == $amount) <!-- Check if the amount matches -->
                                                                    <td width="90px" style="border-right-width:0px; border-left-width:0px">
                                                                        {{$count}} &ensp; = 
                                                                    </td>
                                                                
                                                                    @php
                                                                        $found = true; 
                                                                        $total = $count * $amount;  
                                                                        if ($total === 0) {
                                                                            $total = null;
                                                                        }
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
                                                            <th id="denomTotal" style="padding-right:20px">{{$closing_balance['total_amount']}}</th>
                                                        </tr>
                                                    </tfoot>
                                                </table>
                                            </div>      
                            </div>
                        </div>
                       
                        
                    </div><!--end card-body-->
                </div><!--end card-->
            </div><!--end col-->
        </div><!--end row-->
    </div>
   
@stop

@push('custom-scripts')
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.js"></script>
    <script src="{{ asset('assets/js/helper.js') }}"></script>
    <script>       

</script>
@endpush

@section('footerScript')
    <!-- Sweet-Alert  -->
    <script src="{{ asset('plugins/sweet-alert2/sweetalert2.min.js') }}"></script>
    <script src="{{ asset('assets/pages/jquery.sweet-alert.init.js') }}"></script>
@stop