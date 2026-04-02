@extends('app-layouts.admin-master')

@section('title', 'Receipt')

@section('headerStyle')
    <link href="{{ URL::asset('plugins/datatables/dataTables.bootstrap4.min.css')}}" rel="stylesheet" type="text/css" />
    <link href="{{ URL::asset('assets/css/my-style.css')}}" rel="stylesheet" type="text/css">
    <link href="{{ URL::asset('assets/css/my-actxt.css')}}" rel="stylesheet" type="text/css">
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
                    @slot('title') Receipt (Denomination) @endslot
                    @slot('item1') Transactions @endslot
                    @slot('item2') Denomination @endslot
                @endcomponent
            </div><!--end col-->
        </div>
        <!-- end page title end breadcrumb -->
 
        <div class="row"> 
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">

                        <form method="post" action="{{route('receipt.denomination')}}">
                            @csrf
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="row mb-2">
                                        <input type="date" name="date" id="date" value="{{$date}}" class="my-control ml-2">
                                        <select name="routeId" id="route" class="my-control">
                                            <option value="0" @selected($routeId==0)>Select Route</option>
                                            @foreach($routes as $route)
                                                <option value="{{$route->id}}" @selected($routeId==$route->id)>{{$route->name}}</option>
                                            @endforeach
                                        </select>                                        
                                        <input type="submit" value="Submit" class="btn btn-primary btn-sm ml-3 px-3"/>
                                    </div>
                                </div>
                            </div>
                        </form><!--end form-->
                        <hr/>

                        <div class="table-responsive dash-social">
                            <table id="datatable" class="table table-sm table-bordered dt-responsive nowrap" style="border-collapse: collapse; border-spacing: 0; width: 100%">
                                <thead class="thead-light">
                                    <tr> 
                                        <th data-priority="5" class="text-center">S.No</th>
                                        <th data-priority="2">Customers</th>                                        
                                        <th data-priority="1" class="text-center">Receipt No</th>
                                        <th data-priority="3" class="text-center">Amount</th>                                        
                                        <th data-priority="4" class="text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody>             
                                    @if(empty($receiptDenomination) && $groupDenomination->isEmpty())
                                        <tr>
                                            <td class="text-center" colspan="5">No data available in table</td>
                                        </tr>
                                        @php
                                            $serialNumber = 1;  // Initialize serial number                                                                                         
                                        @endphp
                                    @else
                                        @php
                                            $serialNumber = 1;  // Initialize serial number                                             
                                            $rNumbers = '';  
                                        @endphp
                                
                                        @foreach($receiptDenomination as $receipt)                                       
                                            <tr>
                                                <td class="text-center">{{ $serialNumber++ }}</td> 
                                                <td>{{$receipt->customer_name}}</td>
                                                <td class="text-center">{{ $receipt->receipt_num }}</td>
                                                <td class="text-center">{{ $receipt->amount }}</td>
                                                <td class="text-center">
                                                    <a href="#" class="view-receipt" data-order="{{$receipt->receipt_num}}">
                                                        <i class="dripicons-preview text-primary font-20"></i>
                                                    </a>
                                                </td>
                                            </tr>                                        
                                        @endforeach
                                
                                        @foreach($groupDenomination as $recei)
                                            @php
                                                $rowCount = count($recei);   
                                                $rNumbers = [];                                              
                                            @endphp
                                            @foreach($recei as $i => $receipt)
                                            @php 
                                                if ($i == 0) {
                                                    foreach($recei as $r)   {                                         
                                                    $rNumbers[] = $r->receipt_num;
                                                    }
                                                }
                                            @endphp
                                                <tr>
                                                    @if($i == 0) 
                                                        <td rowspan="{{ $rowCount }}" class="text-center">{{ $serialNumber++ }}</td> <!-- Increment serial number -->
                                                    @endif
                                                    <td>{{$receipt->customer_name}}</td>
                                                    <td class="text-center">{{ $receipt->receipt_num }}</td>
                                                    <td class="text-center">{{ $receipt->amount }}</td>
                                                    @if($i == 0)
                                                        <td rowspan="{{ $rowCount }}" class="text-center">
                                                            <a href="#" class="view-receipt2" data-order="{{ implode(',', $rNumbers) }}">
                                                                <i class="dripicons-preview text-primary font-20"></i>
                                                            </a>
                                                        </td>                                                    
                                                    @endif
                                                </tr>
                                            @endforeach    
                                        @endforeach                                        
                                    @endif
                                    @foreach($noDenomination as $k => $noDeno)
                                        <tr class="text-warning">
                                            @php
                                                $dcount = count($noDenomination)
                                            @endphp
                                            @if($k == 0) 
                                                <td rowspan="{{ $dcount }}" class="text-center">{{ $serialNumber++ }}</td> <!-- Increment serial number -->
                                            @endif
                                            <td>{{$noDeno->customer_name}}</td>
                                            <td class="text-center">{{ $noDeno->receipt_num }}</td>
                                            <td class="text-center">{{ $noDeno->amount }}</td>
                                            @if($k == 0)
                                                <td rowspan="{{ $dcount }}" class="text-center">
                                                    No Denomination
                                                </td>                                                    
                                            @endif                                        
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
@stop

@push('custom-scripts')
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.js"></script>
    <script src="{{ URL::asset('assets/js/input-restriction.js')}}"></script>
    <script src="{{ URL::asset('assets/js/helper.js')}}"></script>
    <script> 
        $(document).ready(function () {
            $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
            });          

            // Function for single receipt view
            $('.view-receipt').on('click', function () {
                let receiptId = $(this).data('order');
                submitForm(receiptId);
            });

            // Function for grouped receipt view
            $('.view-receipt2').on('click', function () {
                let receiptId = $(this).data('order'); 
                submitForm(receiptId);
            });

            // Reusable function to submit the form dynamically
            function submitForm(receiptId) {
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
        });

    </script>
@endpush

@section('footerScript')
    <script src="{{ URL::asset('plugins/datatables/jquery.dataTables.min.js')}}"></script>
    <script src="{{ URL::asset('plugins/datatables/dataTables.bootstrap4.min.js')}}"></script>
@stop