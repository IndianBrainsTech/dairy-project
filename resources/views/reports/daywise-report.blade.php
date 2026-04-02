@extends('app-layouts.admin-master')

@section('title', 'Day wise Report')

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
            @page {
                size: A4;
                margin: 30pt;
            }

            #reportTable th {                
                font-size: 13pt !important;                
            }

            #reportTable td {
                font-size: 12pt !important;
            }

            .print-only {
                display: block !important;
            }

            #reportTable tr.empty-row {            
                display: none;
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
                    @slot('title') Day wise Report @endslot
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
                            <form method="post" action="{{ route('report.day-wise') }}">
                                @csrf
                                <label class="my-text">From</label>
                                <input type="date" name="fromDate" id="fromDate" value="{{$fromDate}}" class="my-control mr-2">
                                <label class="my-text">To</label>
                                <input type="date" name="toDate" id="toDate" value="{{$toDate}}" class="my-control mr-3">
                                <label class="my-text">Report Type</label>
                                <select name="reportType" id="reportType" class="my-control mr-3">
                                    <option value="Format1" @selected($reportType=="Format1")>Format 1</option>
                                    <option value="Format2" @selected($reportType=="Format2")>Format 2</option>
                                </select>
                                <label for="route" class="my-text">Route</label>
                                <select name="route" id="route" class="my-control mr-2" required>
                                    <option value="0" @selected($routeId=="0")>All</option>
                                    <option value="-1" @selected($routeId=="-1")>Company Orders</option>
                                    <option value="-2" @selected($routeId=="-2")>Function Orders</option>
                                    @foreach($routes as $route)
                                        <option value="{{$route->id}}" @selected($routeId==$route->id)>{{$route->name}}</option>
                                    @endforeach
                                </select>
                                <div class="checkbox checkbox-primary mr-2" style="display:inline">
                                    <input name="yest_bal" id="yest-bal" type="checkbox" {{ $yest_bal ? 'checked' : '' }}>
                                    <label for="yest-bal">Yesterday Balance</label>
                                </div>
                                <input type="submit" value="Submit" class="btn btn-gradient-primary btn-sm px-3 mx-3"/>
                                <a id="btnPrint" href="#" class="btn btn-pink py-1 mr-2"><i class="fa fa-print"></i></a>
                                <!-- <button id="btnExport" class="btn btn-pink py-0 px-2"><i class="mdi mdi-file-excel font-18"></i></button> -->
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
                                        <div class="print-header print-only" style="position: relative">
                                            <img src="{{ asset('assets/images/logo.jpg') }}" alt="Logo" class="title-logo">
                                            <div>
                                                <p class="title-company-name">Aasaii Food Productt</p>
                                                <p class="title-report-name">{{ $reportType == 'Format1' ? 'Day wise Outstanding Summary' : 'Day wise Summary' }}</p>
                                                <p class="title-report-date">{{ formatDateRange($fromDate, $toDate) }}</p>
                                            </div>
                                            @if($isSingleRoute)
                                                @if($routeId == -1)
                                                    <p class="title-report-route">Company Orders</p>
                                                @elseif($routeId == -2)
                                                    <p class="title-report-route">Function Orders</p>
                                                @else
                                                    <p class="title-report-route">Route : {{ $reportData[0]['route'] }}</p>
                                                @endif
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                @if($reportType == "Format1")
                                    <table id="reportTable">
                                        <thead class="thead-light text-center">
                                            <tr>
                                                <th>S.No</th>
                                                <th>Customer</th> 
                                                <th>Opening</th>
                                                <th class="text-nowrap">Yst Inv</th>
                                                <th class="text-nowrap">Inv Amt</th>                                                
                                                <th>Cash</th>
                                                <th>Bank</th>
                                                <th>Inctv</th>
                                                <th>Dpst</th>
                                                <th>Others</th>
                                                <th>Disc</th>
                                                <th class="text-nowrap">Day Bal</th>
                                                <th class="text-nowrap ybal">Yst Bal</th>
                                                <th>Closing</th>
                                            </tr>
                                        </thead>

                                        <tbody>
                                            @foreach($reportData as $routeData)
                                                @if($routeData['routeRecords'])
                                                    {{-- Display route row --}}
                                                    @if($isAllRoutes)
                                                        <tr class="head-row">
                                                            <td colspan="14" class="pl-4">{{ $routeData['route'] }}</td>
                                                        </tr>
                                                    @endif

                                                    @php($isPayMode = ! (($routeData['route'] == "Company") || ($routeData['route'] == "Function")) )

                                                    {{-- Iterate through the routeRecords (grouped by payment mode) --}}
                                                    @foreach($routeData['routeRecords'] as $payMode => $data)
                                                        {{-- Display payment mode row --}}
                                                        @if($isPayMode)
                                                            <tr class="sub-head-row">
                                                                <td></td>
                                                                <td colspan="13">{{ $payMode }}</td>
                                                            </tr>
                                                        @endif

                                                        {{-- Display customer records under the current payment mode --}}
                                                        @php($sno = 1)

                                                        @foreach($data['records'] as $record)
                                                            <tr class="text-right pr-2">
                                                                <td class="text-center pr-0">{{ $sno++ }}</td>
                                                                <td class="text-left pr-0">{{ $record['customer'] }}</td>
                                                                <td>{{ $record['open_bal'] }}</td>
                                                                <td>{{ $record['prev_inv'] }}</td>
                                                                <td>{{ $record['inv_amt'] }}</td>                                                                
                                                                <td>{{ $record['cash'] }}</td>
                                                                <td>{{ $record['bank'] }}</td>
                                                                <td>{{ $record['incentive'] }}</td>
                                                                <td>{{ $record['deposit'] }}</td>
                                                                <td>{{ $record['others'] }}</td>                                                                
                                                                <td>{{ $record['discount'] }}</td>
                                                                <td>{{ $record['day_bal'] }}</td>
                                                                <td class="ybal">{{ $record['yest_bal'] }}</td>
                                                                <td>{{ $record['close_bal'] }}</td>
                                                            </tr>
                                                        @endforeach

                                                        {{-- Display totals for the current payment mode if there are records --}}
                                                        @if(count($data['records']) && $isPayMode)
                                                            <tr class="text-right pr-2 sub-total-row">
                                                                <td></td>
                                                                <td class="text-left pr-0">Total ({{ $payMode }})</td>
                                                                <td>{{ $data['totals']['open_bal'] }}</td>
                                                                <td>{{ $data['totals']['prev_inv'] }}</td>
                                                                <td>{{ $data['totals']['inv_amt'] }}</td>                                                                
                                                                <td>{{ $data['totals']['cash'] }}</td>
                                                                <td>{{ $data['totals']['bank'] }}</td>
                                                                <td>{{ $data['totals']['incentive'] }}</td>
                                                                <td>{{ $data['totals']['deposit'] }}</td>
                                                                <td>{{ $data['totals']['others'] }}</td>
                                                                <td>{{ $data['totals']['discount'] }}</td>
                                                                <td>{{ $data['totals']['day_bal'] }}</td>
                                                                <td class="ybal">{{ $data['totals']['yest_bal'] }}</td>
                                                                <td>{{ $data['totals']['close_bal'] }}</td>
                                                            </tr>
                                                        @endif
                                                    @endforeach

                                                    {{-- Display route totals --}}
                                                    <tr class="text-right pr-2 total-row">
                                                        <td colspan="2" class="text-left pl-4">{{ $isPayMode ? 'Route Total' : 'Total' }}</td>
                                                        <td>{{ $routeData['routeTotals']['open_bal'] }}</td>
                                                        <td>{{ $routeData['routeTotals']['prev_inv'] }}</td>
                                                        <td>{{ $routeData['routeTotals']['inv_amt'] }}</td>                                                        
                                                        <td>{{ $routeData['routeTotals']['cash'] }}</td>
                                                        <td>{{ $routeData['routeTotals']['bank'] }}</td>
                                                        <td>{{ $routeData['routeTotals']['incentive'] }}</td>
                                                        <td>{{ $routeData['routeTotals']['deposit'] }}</td>
                                                        <td>{{ $routeData['routeTotals']['others'] }}</td>
                                                        <td>{{ $routeData['routeTotals']['discount'] }}</td>
                                                        <td>{{ $routeData['routeTotals']['day_bal'] }}</td>
                                                        <td class="ybal">{{ $routeData['routeTotals']['yest_bal'] }}</td>
                                                        <td>{{ $routeData['routeTotals']['close_bal'] }}</td>
                                                    </tr>

                                                    {{-- Add an empty row separator for next route, if any --}}
                                                    @if($isAllRoutes)
                                                        <tr class="empty-row">
                                                            <td colspan="14"></td>
                                                        </tr>
                                                    @endif
                                                @endif
                                            @endforeach

                                            @if($isAllRoutes)
                                            {{-- Display grand totals --}}
                                                <tr class="grand-total-row text-right pr-2">
                                                    <td></td>
                                                    <td class="text-left pr-0">Grand Total</td>
                                                    <td>{{ $grandTotals['open_bal'] }}</td>
                                                    <td>{{ $grandTotals['prev_inv'] }}</td>
                                                    <td>{{ $grandTotals['inv_amt'] }}</td>                                                    
                                                    <td>{{ $grandTotals['cash'] }}</td>
                                                    <td>{{ $grandTotals['bank'] }}</td>
                                                    <td>{{ $grandTotals['incentive'] }}</td>
                                                    <td>{{ $grandTotals['deposit'] }}</td>
                                                    <td>{{ $grandTotals['others'] }}</td>
                                                    <td>{{ $grandTotals['discount'] }}</td>
                                                    <td>{{ $grandTotals['day_bal'] }}</td>
                                                    <td class="ybal">{{ $grandTotals['yest_bal'] }}</td>
                                                    <td>{{ $grandTotals['close_bal'] }}</td>
                                                </tr>

                                                <tr class="empty-row2">
                                                    <td colspan="14"></td>
                                                </tr>
                                                <tr class="summary-head-row">
                                                    <td colspan="14" class="pl-4">Route wise Summary</td>
                                                </tr>
                                                <tr class="head-row">
                                                    <th class="text-center">S.No</th>
                                                    <th class="text-left">Route</th>
                                                    <th class="text-right">Opening</th>
                                                    <th class="text-right">Yst Inv</th>
                                                    <th class="text-right">Inv Amt</th>                                                    
                                                    <th class="text-right">Cash</th>
                                                    <th class="text-right">Bank</th>
                                                    <th>Inctv</th>
                                                    <th>Dpst</th>
                                                    <th class="text-right">Others</th>
                                                    <th class="text-right">Disc</th>
                                                    <th class="text-right">Day Bal</th>
                                                    <th class="text-right ybal">Yst Bal</th>
                                                    <th class="text-right">Closing</th>
                                                </tr>

                                                @php($sno = 1)
                                                @foreach($reportData as $record)
                                                    @php($rdata = $record['routeTotals'])
                                                    @if(!empty($rdata['open_bal']) || !empty($rdata['inv_amt']) || !empty($rdata['cash']) || !empty($rdata['bank']) || !empty($rdata['others']) || !empty($rdata['close_bal']))
                                                    <tr class="text-right pr-2">
                                                        <td class="text-center pr-0">{{ $sno++ }}</td>
                                                        <td class="text-left pr-0">{{ $record['route'] }}</td>
                                                        <td>{{ getEmptyForZero($rdata['open_bal']) }}</td>
                                                        <td>{{ getEmptyForZero($rdata['prev_inv']) }}</td>
                                                        <td>{{ getEmptyForZero($rdata['inv_amt']) }}</td>                                                        
                                                        <td>{{ getEmptyForZero($rdata['cash']) }}</td>
                                                        <td>{{ getEmptyForZero($rdata['bank']) }}</td>
                                                        <td>{{ getEmptyForZero($rdata['incentive']) }}</td>
                                                        <td>{{ getEmptyForZero($rdata['deposit']) }}</td>
                                                        <td>{{ getEmptyForZero($rdata['others']) }}</td>
                                                        <td>{{ getEmptyForZero($rdata['discount']) }}</td>
                                                        <td>{{ getEmptyForZero($rdata['day_bal']) }}</td>
                                                        <td class="ybal">{{ getEmptyForZero($rdata['yest_bal']) }}</td>
                                                        <td>{{ getEmptyForZero($rdata['close_bal']) }}</td>
                                                    </tr>
                                                    @endif
                                                @endforeach

                                                <tr class="grand-total-row text-right pr-2">
                                                    <td></td>
                                                    <td class="text-left pr-0">Grand Total</td>
                                                    <td>{{ $grandTotals['open_bal'] }}</td>
                                                    <td>{{ $grandTotals['prev_inv'] }}</td>
                                                    <td>{{ $grandTotals['inv_amt'] }}</td>                                                    
                                                    <td>{{ $grandTotals['cash'] }}</td>
                                                    <td>{{ $grandTotals['bank'] }}</td>
                                                    <td>{{ $grandTotals['incentive'] }}</td>
                                                    <td>{{ $grandTotals['deposit'] }}</td>
                                                    <td>{{ $grandTotals['others'] }}</td>
                                                    <td>{{ $grandTotals['discount'] }}</td>
                                                    <td>{{ $grandTotals['day_bal'] }}</td>
                                                    <td class="ybal">{{ $grandTotals['yest_bal'] }}</td>
                                                    <td>{{ $grandTotals['close_bal'] }}</td>
                                                </tr>

                                                <tr class="empty-row2">
                                                    <td colspan="14"></td>
                                                </tr>
                                                <tr class="summary-head-row">
                                                    <td colspan="14" class="pl-4">Payment Mode wise Summary</td>
                                                </tr>
                                                <tr class="head-row">
                                                    <th class="text-center">S.No</th>
                                                    <th class="text-left">Payment Mode</th>
                                                    <th class="text-right">Opening</th>
                                                    <th class="text-right">Yst Inv</th>
                                                    <th class="text-right">Inv Amt</th>                                                    
                                                    <th class="text-right">Cash</th>
                                                    <th class="text-right">Bank</th>
                                                    <th>Inctv</th>
                                                    <th>Dpst</th>
                                                    <th class="text-right">Others</th>
                                                    <th class="text-right">Disc</th>
                                                    <th class="text-right">Day Bal</th>
                                                    <th class="text-right ybal">Yst Bal</th>
                                                    <th class="text-right">Closing</th>
                                                </tr>

                                                @php($sno = 1)
                                                @foreach($payModeRecords as $record)
                                                    <tr class="text-right pr-2">
                                                        <td class="text-center pr-0">{{ $sno++ }}</td>
                                                        <td class="text-left pr-0">{{ $record['pay_mode'] }}</td>
                                                        <td>{{ getEmptyForZero($record['open_bal']) }}</td>
                                                        <td>{{ getEmptyForZero($record['prev_inv']) }}</td>
                                                        <td>{{ getEmptyForZero($record['inv_amt']) }}</td>                                                        
                                                        <td>{{ getEmptyForZero($record['cash']) }}</td>
                                                        <td>{{ getEmptyForZero($record['bank']) }}</td>
                                                        <td>{{ getEmptyForZero($record['incentive']) }}</td>
                                                        <td>{{ getEmptyForZero($record['deposit']) }}</td>
                                                        <td>{{ getEmptyForZero($record['others']) }}</td>
                                                        <td>{{ getEmptyForZero($record['discount']) }}</td>
                                                        <td>{{ getEmptyForZero($record['day_bal']) }}</td>
                                                        <td class="ybal">{{ getEmptyForZero($record['yest_bal']) }}</td>
                                                        <td>{{ getEmptyForZero($record['close_bal']) }}</td>
                                                    </tr>
                                                @endforeach

                                                <tr class="grand-total-row text-right pr-2">
                                                    <td></td>
                                                    <td class="text-left pr-0">Grand Total</td>
                                                    <td>{{ $grandTotals['open_bal'] }}</td>
                                                    <td>{{ $grandTotals['prev_inv'] }}</td>
                                                    <td>{{ $grandTotals['inv_amt'] }}</td>                                                    
                                                    <td>{{ $grandTotals['cash'] }}</td>
                                                    <td>{{ $grandTotals['bank'] }}</td>
                                                    <td>{{ $grandTotals['incentive'] }}</td>
                                                    <td>{{ $grandTotals['deposit'] }}</td>
                                                    <td>{{ $grandTotals['others'] }}</td>
                                                    <td>{{ $grandTotals['discount'] }}</td>
                                                    <td>{{ $grandTotals['day_bal'] }}</td>
                                                    <td class="ybal">{{ $grandTotals['yest_bal'] }}</td>
                                                    <td>{{ $grandTotals['close_bal'] }}</td>
                                                </tr>
                                            @endif
                                        </tbody>
                                    </table>
                                @elseif($reportType == "Format2")
                                    <table id="reportTable">
                                        <thead class="thead-light">
                                            <tr class="text-center">
                                                <th>S.No</th>
                                                <th>Customer</th>
                                                <th class="text-nowrap">Yst Inv</th>
                                                <th class="text-nowrap">Inv Amt</th>                                                
                                                <th>Cash</th>                                                
                                                @foreach($banks as $bank)
                                                    <th>{{$bank->display_name}}</th>
                                                @endforeach
                                                <th>Incentive</th>
                                                <th>Deposit</th>
                                                <th>Others</th>
                                                <th>Disc</th>
                                                <th class="text-nowrap">Day Bal</th>
                                                <th class="text-nowrap ybal">Yst Bal</th>
                                            </tr>
                                        </thead>

                                        <tbody>
                                            @foreach($reportData as $routeData)
                                                @if($routeData['routeRecords'])
                                                    {{-- Display route row --}}
                                                    @if($isAllRoutes)
                                                        <tr class="head-row">
                                                            <td colspan="14" class="pl-4">{{ $routeData['route'] }}</td>
                                                        </tr>
                                                    @endif

                                                    @php($isPayMode = ! (($routeData['route'] == "Company") || ($routeData['route'] == "Function")) )

                                                    {{-- Iterate through the routeRecords (grouped by payment mode) --}}
                                                    @foreach($routeData['routeRecords'] as $payMode => $data)
                                                        {{-- Display payment mode row --}}
                                                        @if($isPayMode)
                                                            <tr class="sub-head-row">
                                                                <td></td>
                                                                <td colspan="13">{{ $payMode }}</td>
                                                            </tr>
                                                        @endif

                                                        {{-- Display customer records under the current payment mode --}}
                                                        @php($sno = 1)

                                                        @foreach($data['records'] as $record)
                                                            <tr class="text-right pr-2">
                                                                <td class="text-center pr-0">{{ $sno++ }}</td>
                                                                <td class="text-left pr-0">{{ $record['customer'] }}</td>
                                                                <td>{{ $record['prev_inv'] }}</td>
                                                                <td>{{ $record['inv_amt'] }}</td>                                                                
                                                                <td>{{ $record['cash'] }}</td>
                                                                @foreach($banks as $bank)
                                                                    <td class="text-right">{{$record[$bank->display_name]}}</td>
                                                                @endforeach
                                                                <td>{{ $record['incentive'] }}</td>
                                                                <td>{{ $record['deposit'] }}</td>
                                                                <td>{{ $record['others'] }}</td>
                                                                <td>{{ $record['discount'] }}</td>
                                                                <td>{{ $record['day_bal'] }}</td>
                                                                <td class="ybal">{{ $record['yest_bal'] }}</td>
                                                            </tr>
                                                        @endforeach

                                                        {{-- Display totals for the current payment mode if there are records --}}
                                                        @if(count($data['records']) && $isPayMode)
                                                            <tr class="text-right pr-2 sub-total-row">
                                                                <td></td>
                                                                <td class="text-left pr-0">Total ({{ $payMode }})</td>
                                                                <td>{{ $data['totals']['prev_inv'] }}</td>
                                                                <td>{{ $data['totals']['inv_amt'] }}</td>                                                                
                                                                <td>{{ $data['totals']['cash'] }}</td>
                                                                @foreach($banks as $bank)
                                                                    <td class="text-right">{{$data['totals'][$bank->display_name]}}</td>
                                                                @endforeach
                                                                <td>{{ $data['totals']['incentive'] }}</td>
                                                                <td>{{ $data['totals']['deposit'] }}</td>
                                                                <td>{{ $data['totals']['others'] }}</td>
                                                                <td>{{ $data['totals']['discount'] }}</td>
                                                                <td>{{ $data['totals']['day_bal'] }}</td>
                                                                <td class="ybal">{{ $data['totals']['yest_bal'] }}</td>
                                                            </tr>
                                                        @endif
                                                    @endforeach

                                                    {{-- Display route totals --}}
                                                    <tr class="text-right pr-2 total-row">
                                                        <td colspan="2" class="text-left pl-4">{{ $isPayMode ? 'Route Total' : 'Total' }}</td>
                                                        <td>{{ $routeData['routeTotals']['prev_inv'] }}</td>
                                                        <td>{{ $routeData['routeTotals']['inv_amt'] }}</td>                                                        
                                                        <td>{{ $routeData['routeTotals']['cash'] }}</td>
                                                        @foreach($banks as $bank)
                                                            <td class="text-right">{{$routeData['routeTotals'][$bank->display_name]}}</td>
                                                        @endforeach
                                                        <td>{{ $routeData['routeTotals']['incentive'] }}</td>
                                                        <td>{{ $routeData['routeTotals']['deposit'] }}</td>
                                                        <td>{{ $routeData['routeTotals']['others'] }}</td>
                                                        <td>{{ $routeData['routeTotals']['discount'] }}</td>
                                                        <td>{{ $routeData['routeTotals']['day_bal'] }}</td>
                                                        <td class="ybal">{{ $routeData['routeTotals']['yest_bal'] }}</td>
                                                    </tr>

                                                    {{-- Add an empty row separator for next route, if any --}}
                                                    @if($isAllRoutes)
                                                        <tr class="empty-row">
                                                            <td colspan="14"></td>
                                                        </tr>
                                                    @endif
                                                @endif
                                            @endforeach

                                            @if($isAllRoutes)
                                            {{-- Display grand totals --}}
                                                <tr class="grand-total-row text-right pr-2">
                                                    <td></td>
                                                    <td class="text-left pr-0">Grand Total</td>
                                                    <td>{{ $grandTotals['prev_inv'] }}</td>
                                                    <td>{{ $grandTotals['inv_amt'] }}</td>                                                    
                                                    <td>{{ $grandTotals['cash'] }}</td>
                                                    @foreach($banks as $bank)
                                                        <td class="text-right">{{$grandTotals[$bank->display_name]}}</td>
                                                    @endforeach
                                                    <td>{{ $grandTotals['incentive'] }}</td>
                                                    <td>{{ $grandTotals['deposit'] }}</td>
                                                    <td>{{ $grandTotals['others'] }}</td>
                                                    <td>{{ $grandTotals['discount'] }}</td>
                                                    <td>{{ $grandTotals['day_bal'] }}</td>
                                                    <td class="ybal">{{ $grandTotals['yest_bal'] }}</td>
                                                </tr>

                                                <tr class="empty-row2">
                                                    <td colspan="14"></td>
                                                </tr>
                                                <tr class="summary-head-row">
                                                    <td colspan="14" class="pl-4">Route wise Summary</td>
                                                </tr>
                                                <tr class="head-row">
                                                    <th class="text-center">S.No</th>
                                                    <th class="text-left">Route</th>
                                                    <th class="text-right">Yst Inv</th>
                                                    <th class="text-right">Inv Amt</th>                                                    
                                                    <th class="text-right">Cash</th>
                                                    @foreach($banks as $bank)
                                                        <th class="text-right">{{$bank->display_name}}</th>
                                                    @endforeach
                                                    <th class="text-right">Incentive</th>
                                                    <th class="text-right">Deposit</th>
                                                    <th class="text-right">Others</th>
                                                    <th class="text-right">Disc</th>
                                                    <th class="text-right">Day Bal</th>
                                                    <th class="text-right ybal">Yst Bal</th>
                                                </tr>

                                                @php($sno = 1)
                                                @foreach($reportData as $record)
                                                    @if(!isEmptyRecord($record['routeTotals'], $banks))
                                                        <tr class="text-right pr-2">
                                                            <td class="text-center pr-0">{{ $sno++ }}</td>
                                                            <td class="text-left pr-0">{{ $record['route'] }}</td>
                                                            <td>{{ getEmptyForZero($record['routeTotals']['prev_inv']) }}</td>
                                                            <td>{{ getEmptyForZero($record['routeTotals']['inv_amt']) }}</td>                                                            
                                                            <td>{{ getEmptyForZero($record['routeTotals']['cash']) }}</td>
                                                            @foreach($banks as $bank)
                                                                <td class="text-right">{{ getEmptyForZero($record['routeTotals'][$bank->display_name]) }}</td>
                                                            @endforeach
                                                            <td>{{ getEmptyForZero($record['routeTotals']['incentive']) }}</td>
                                                            <td>{{ getEmptyForZero($record['routeTotals']['deposit']) }}</td>
                                                            <td>{{ getEmptyForZero($record['routeTotals']['others']) }}</td>
                                                            <td>{{ getEmptyForZero($record['routeTotals']['discount']) }}</td>
                                                            <td>{{ getEmptyForZero($record['routeTotals']['day_bal']) }}</td>
                                                            <td class="ybal">{{ getEmptyForZero($record['routeTotals']['yest_bal']) }}</td>
                                                        </tr>
                                                    @endif
                                                @endforeach

                                                <tr class="grand-total-row text-right pr-2">
                                                    <td></td>
                                                    <td class="text-left pr-0">Grand Total</td>
                                                    <td>{{ $grandTotals['prev_inv'] }}</td>
                                                    <td>{{ $grandTotals['inv_amt'] }}</td>                                                    
                                                    <td>{{ $grandTotals['cash'] }}</td>
                                                    @foreach($banks as $bank)
                                                        <td class="text-right">{{$grandTotals[$bank->display_name]}}</td>
                                                    @endforeach
                                                    <td>{{ $grandTotals['incentive'] }}</td>
                                                    <td>{{ $grandTotals['deposit'] }}</td>
                                                    <td>{{ $grandTotals['others'] }}</td>
                                                    <td>{{ $grandTotals['discount'] }}</td>
                                                    <td>{{ $grandTotals['day_bal'] }}</td>
                                                    <td class="ybal">{{ $grandTotals['yest_bal'] }}</td>
                                                </tr>

                                                <tr class="empty-row2">
                                                    <td colspan="14"></td>
                                                </tr>
                                                <tr class="summary-head-row">
                                                    <td colspan="14" class="pl-4">Payment Mode wise Summary</td>
                                                </tr>
                                                <tr class="head-row">
                                                    <th class="text-center">S.No</th>
                                                    <th class="text-left">Payment Mode</th>
                                                    <th class="text-right">Yst Inv</th>
                                                    <th class="text-right">Inv Amt</th>
                                                    <th class="text-right">Cash</th>
                                                    @foreach($banks as $bank)
                                                        <th class="text-right">{{$bank->display_name}}</th>
                                                    @endforeach
                                                    <th class="text-right">Incentive</th>
                                                    <th class="text-right">Deposit</th>
                                                    <th class="text-right">Others</th>
                                                    <th class="text-right">Disc</th>
                                                    <th class="text-right">Day Bal</th>
                                                    <th class="text-right ybal">Yst Bal</th>
                                                </tr>

                                                @php($sno = 1)
                                                @foreach($payModeRecords as $record)
                                                    <tr class="text-right pr-2">
                                                        <td class="text-center pr-0">{{ $sno++ }}</td>
                                                        <td class="text-left pr-0">{{ $record['pay_mode'] }}</td>
                                                        <td>{{ getEmptyForZero($record['prev_inv']) }}</td>
                                                        <td>{{ getEmptyForZero($record['inv_amt']) }}</td>                                                        
                                                        <td>{{ getEmptyForZero($record['cash']) }}</td>
                                                        @foreach($banks as $bank)
                                                            <td class="text-right">{{ getEmptyForZero($record[$bank->display_name]) }}</td>
                                                        @endforeach
                                                        <td>{{ getEmptyForZero($record['incentive']) }}</td>
                                                        <td>{{ getEmptyForZero($record['deposit']) }}</td>
                                                        <td>{{ getEmptyForZero($record['others']) }}</td>
                                                        <td>{{ getEmptyForZero($record['discount']) }}</td>
                                                        <td>{{ getEmptyForZero($record['day_bal']) }}</td>
                                                        <td class="ybal">{{ getEmptyForZero($record['yest_bal']) }}</td>
                                                    </tr>
                                                @endforeach

                                                <tr class="grand-total-row text-right pr-2">
                                                    <td></td>
                                                    <td class="text-left pr-0">Grand Total</td>
                                                    <td>{{ $grandTotals['prev_inv'] }}</td>
                                                    <td>{{ $grandTotals['inv_amt'] }}</td>                                                    
                                                    <td>{{ $grandTotals['cash'] }}</td>
                                                    @foreach($banks as $bank)
                                                        <td class="text-right">{{$grandTotals[$bank->display_name]}}</td>
                                                    @endforeach
                                                    <td>{{ $grandTotals['incentive'] }}</td>
                                                    <td>{{ $grandTotals['deposit'] }}</td>
                                                    <td>{{ $grandTotals['others'] }}</td>
                                                    <td>{{ $grandTotals['discount'] }}</td>
                                                    <td>{{ $grandTotals['day_bal'] }}</td>
                                                    <td class="ybal">{{ $grandTotals['yest_bal'] }}</td>
                                                </tr>
                                            @endif
                                        </tbody>
                                    </table>
                                @endif
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

            $("body").toggleClass("enlarge-menu");

            @if(!$yest_bal)
                $('.ybal').hide();
            @endif

            $('#yest-bal').on("change", function () {
                $('.ybal').toggle($(this).is(':checked'));
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
                        rdate: $("#rdate").val(),
                        route: $("#route").val()
                    };
                    var url = "{{ route('export.day-wise') }}?" + $.param(query);
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