@extends('app-layouts.admin-master')

@section('title', 'Invoice Report')

@section('headerStyle')
    <link href="{{ asset('plugins/sweet-alert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('plugins/animate/animate.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('assets/css/report-style.css') }}" rel="stylesheet" type="text/css">
    <style type="text/css">
        #reportTable tr:nth-child(even) {
            background-color: #fff;
        }
        #reportTable tr.head-row {
            background-color: #e5f2ff;
            height: 32px;
            font-weight: 600;
        }
        #reportTable tr.sub-head-row {
            background-color: #f1f1f1;
            font-weight: 600;
        }
        #reportTable tr.summary-head-row {
            background-color: #e5f2ff;
            height: 40px;
            font-weight: 700;
        }
        #reportTable tr.sub-total-row {
            background-color: #edeef3;
            font-weight: 600;
        }
        #reportTable tr.total-row {
            background-color: #f1edff;
            font-weight: 600;
        }
        #reportTable tr.empty-row {
            height: 24px;
        }
        #reportTable tr.empty-row2 {
            height: 80px;
        }
        #reportTable tr.grand-total-row {
            background-color: #e7fdff;
            font-weight: 700;
            height: 36px;
        }
        
        /* Hide on screen, show only in print */
        @media screen {
            .print-only {
                display: none !important;
            }
        }

        @media print {
            .print-only {
                display: block !important;
            }
        }

    </style>
@stop

@section('content')
    <div class="container-fluid">
        <!-- Page-Title -->
        <div class="row">
            <div class="col-sm-12">
                @component('app-components.breadcrumb-2')
                    @slot('title') Invoice Report @endslot
                    @slot('item1') Reports @endslot
                @endcomponent
            </div>
        </div>
        <!-- end page title end breadcrumb -->

        @php
            $isSingleRoute = ($routeId <> 0);
            $isAllRoutes = ($routeId == 0);
            $noRecord = false;
            if($isSingleRoute) {
                if(!$reportData[0]['routeRecords']) {
                    $noRecord = true;
                }
            }
        @endphp

        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
 
                        <div>
                            <form method="post" action="{{ route('report.invoice') }}">
                                @csrf
                                <label class="my-text">From</label>
                                <input type="date" name="fromDate" id="fromDate" value="{{$fromDate}}" class="my-control mr-2">
                                <label class="my-text">To</label>
                                <input type="date" name="toDate" id="toDate" value="{{$toDate}}" class="my-control mr-3">                                
                                <label for="route" class="my-text">Route</label>
                                <select name="route" id="route" class="my-control mr-2" required>
                                    <option value="0" @selected($routeId=="0")>All</option>
                                    <option value="-1" @selected($routeId=="-1")>Company Orders</option>
                                    <option value="-2" @selected($routeId=="-2")>Function Orders</option>
                                    @foreach($routes as $route)
                                        <option value="{{$route->id}}" @selected($routeId==$route->id)>{{$route->name}}</option>
                                    @endforeach
                                </select>
                                <input type="submit" value="Submit" class="btn btn-gradient-primary btn-sm px-3 mx-3"/>
                                <a id="btnPrint" href="#" class="btn btn-pink py-1 mr-2"><i class="fa fa-print"></i></a>
                                <button id="btnExport" class="btn btn-pink py-0 px-2"><i class="mdi mdi-file-excel font-18"></i></button>
                            </form>
                        </div>
                        <hr/>

                        @if($noRecord)
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
                                        <div class="print-header print-only" style="display: flex; align-items: center; justify-content: center;">
                                            <img src="{{ asset('assets/images/logo.jpg') }}" alt="Logo" class="logo" style="height: 60px; margin-right: 20px;">
                                            <h3 class="text-center pb-2" style="color:maroon">Aasaii Food Productt</h3>
                                            <h3 class="text-center pb-2" style="color:blue">Invoice Report</h3>
                                            <h4 class="text-center pb-2">{{ formatDateRange($fromDate, $toDate) }}</h4>
                                            @if($isSingleRoute)
                                                @if($routeId == -1)
                                                    <h4 class="text-center pb-2">Company Orders</h4>
                                                @elseif($routeId == -2)
                                                    <h4 class="text-center pb-2">Function Orders</h4>
                                                @else
                                                    <h4 class="text-center pb-2">Route : {{ $reportData[0]['route'] }}</h4>
                                                @endif
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                
                                <table id="reportTable">
                                    <thead class="thead-light">
                                        <tr>
                                            <th class="text-center">S.No</th>
                                            <th class="text-center">Date</th>
                                            <th class="text-center">Customer</th>
                                            <th class="text-center">Invoice Number</th>
                                            <th class="text-right pr-2">Qty</th>
                                            <th class="text-right pr-2">Amount</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        @foreach($reportData as $routeData)
                                            @if($routeData['routeRecords'])
                                                {{-- Display route row --}}
                                                @if($isAllRoutes)
                                                    <tr class="head-row">
                                                        <td colspan="8" class="pl-4">{{ $routeData['route'] }}</td>
                                                    </tr>
                                                @endif

                                                @php($isPayMode = !(($routeData['route'] == "Company") || ($routeData['route'] == "Function")) )

                                                {{-- Iterate through the routeRecords (grouped by payment mode) --}}
                                                @foreach($routeData['routeRecords'] as $payMode => $data)
                                                    {{-- Display payment mode row --}}
                                                    @if($isPayMode)
                                                        <tr class="sub-head-row">
                                                            <td></td>
                                                            <td colspan="7">{{ $payMode }}</td>
                                                        </tr>
                                                    @endif

                                                    {{-- Display customer records under the current payment mode --}}
                                                    @php($sno = 1)

                                                    @foreach($data['records'] as $record)
                                                        <tr>
                                                            <td class="text-center pr-0">{{ $sno++ }}</td>
                                                            <td class="text-center">{{ displayDate($record['date']) }}</td>
                                                            <td class="text-left pl-2">{{ $record['customer'] }}</td>
                                                            <td class="text-center">{{ $record['inv_num'] }}</td>
                                                            <td class="text-right pr-2">{{ $record['qty'] }}</td>
                                                            <td class="text-right pr-2">{{ $record['amount'] }}</td>
                                                        </tr>
                                                    @endforeach

                                                    {{-- Display totals for the current payment mode if there are records --}}
                                                    @if(count($data['records']) && $isPayMode)
                                                        <tr class="text-right sub-total-row">                                                            
                                                            <td colspan="4" class="pr-3">Total ({{ $payMode }})</td>
                                                            <td class="pr-2">{{ $data['totals']['qty'] }}</td>
                                                            <td class="pr-2">{{ $data['totals']['amount'] }}</td>
                                                        </tr>
                                                    @endif
                                                @endforeach

                                                {{-- Display route totals --}}
                                                <tr class="total-row text-right">
                                                    <td colspan="4" class="pr-3">{{ $isPayMode ? 'Route Total' : 'Total' }}</td>
                                                    <td class="pr-2">{{ $routeData['routeTotals']['qty'] }}</td>
                                                    <td class="pr-2">{{ $routeData['routeTotals']['amount'] }}</td>
                                                </tr>

                                                {{-- Add an empty row separator for next route, if any --}}
                                                @if($isAllRoutes)
                                                    <tr class="empty-row">
                                                        <td colspan="8"></td>
                                                    </tr>
                                                @endif
                                            @endif
                                        @endforeach

                                        @if($isAllRoutes)
                                        {{-- Display grand totals --}}
                                            <tr class="grand-total-row text-right">                                                
                                                <td colspan="4" class="pr-3">Grand Total</td>
                                                <td class="pr-2">{{ $grandTotals['qty'] }}</td>
                                                <td class="pr-2">{{ $grandTotals['amount'] }}</td>
                                            </tr>

                                            <tr class="empty-row2">
                                                <td colspan="8"></td>
                                            </tr>
                                            <tr class="summary-head-row">
                                                <td colspan="8" class="pl-4">Route wise Summary</td>
                                            </tr>
                                            <tr class="head-row">
                                                <th class="text-center">S.No</th>                                                
                                                <th></th>
                                                <th class="text-center">Route</th>
                                                <th class="text-center">No of Invoices</th>
                                                <th class="text-right">Qty</th>
                                                <th class="text-right">Amount</th>
                                            </tr>

                                            @php($sno = 1)
                                            @php($count = 0)
                                            @foreach($routeRecords as $record)
                                                @php($count += $record['count'])                                                                                               
                                                <tr>
                                                    <td class="text-center">{{ $sno++ }}</td>
                                                    <td></td>
                                                    <td class="text-left pl-3">{{ $record['route'] }}</td>
                                                    <td class="text-center">{{ getEmptyForZero($record['count']) }}</td>
                                                    <td class="text-right pr-2">{{ getEmptyForZero($record['qty']) }}</td>
                                                    <td class="text-right pr-2">{{ getEmptyForZero($record['amount']) }}</td>
                                                </tr>
                                            @endforeach

                                            <tr class="grand-total-row">                                                
                                                <td colspan="3" class=" text-right pr-3">Grand Total</td>
                                                <td class="text-center">{{ getEmptyForZero($count) }}</td>
                                                <td class="text-right pr-2">{{ $grandTotals['qty'] }}</td>
                                                <td class="text-right pr-2">{{ $grandTotals['amount'] }}</td>
                                            </tr>

                                            <tr class="empty-row2">
                                                <td colspan="8"></td>
                                            </tr>
                                            <tr class="summary-head-row">
                                                <td colspan="8" class="pl-4">Payment Mode wise Summary</td>
                                            </tr>
                                            <tr class="head-row">
                                                <th class="text-center">S.No</th>
                                                <th></th>
                                                <th class="text-center">Payment Mode</th>
                                                <th class="text-center">No of Invoices</th>
                                                <th class="text-right">Qty</th>
                                                <th class="text-right">Amount</th>
                                            </tr>

                                            @php($sno = 1)
                                            @php($count = 0)
                                            @foreach($payModeRecords as $record)
                                                @php($count += $record['count'])
                                                <tr>
                                                    <td class="text-center">{{ $sno++ }}</td>
                                                    <td></td>
                                                    <td class="text-left pl-3">{{ $record['pay_mode'] }}</td>
                                                    <td class="text-center">{{ getEmptyForZero($record['count']) }}</td>
                                                    <td class="text-right pr-2">{{ getEmptyForZero($record['qty']) }}</td>
                                                    <td class="text-right pr-2">{{ getEmptyForZero($record['amount']) }}</td>
                                                </tr>
                                            @endforeach

                                            <tr class="grand-total-row text-right">                                                
                                                <td colspan="3" class="pr-3">Grand Total</td>
                                                <td class="text-center">{{ getEmptyForZero($count) }}</td>
                                                <td class="text-right pr-2">{{ $grandTotals['qty'] }}</td>
                                                <td class="text-right pr-2">{{ $grandTotals['amount'] }}</td>
                                            </tr>
                                        @endif
                                    </tbody>
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

            $("#fromDate").trigger('change');

            $('#btnPrint').on("click", function () {
                event.preventDefault();
                const noRecord = "{{ $noRecord }}";
                if(noRecord) {
                    Swal.fire('Sorry','No data found to print','warning');
                }
                else {
                    var originalContents = $('body').html();
                    var printContents = $('#report-div').html();
                    $('body').html(printContents);
                    window.print();
                    $('body').html(originalContents);
                }
            });

            $('#btnExport').click(function(event) {
                event.preventDefault();
                const noRecord = "{{ $noRecord }}";
                if(noRecord) {
                    Swal.fire('Sorry','No data found to download','warning');
                }
                else {
                    var query = {
                        fromDate: $("#fromDate").val(),
                        toDate: $("#toDate").val(),     
                        route: $("#route").val()
                    };
                    var url = "{{ route('export.invoice') }}?" + $.param(query);
                    window.location = url;
                }
            });
        });
    </script>
@endpush 

@section('footerScript')
    <script src="{{ asset('plugins/sweet-alert2/sweetalert2.min.js') }}"></script>
    <script src="{{ asset('assets/pages/jquery.sweet-alert.init.js') }}"></script>
@stop