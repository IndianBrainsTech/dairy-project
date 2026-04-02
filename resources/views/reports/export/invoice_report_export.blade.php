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
                        
<table id="reportTable">
    <!-- Title Row 1 -->
    <tr>
        <td colspan="6" style="text-align: center; font-weight: bold; font-size: 16px;">AASAH FOOD PRODUCT</td>
    </tr>

    <!-- Title Row 2 -->
    <tr>
        <td colspan="6" style="text-align: center; font-weight: bold;">{{$heading}}</td>
    </tr>
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
                            <td colspan="6" class="pl-4">{{ $routeData['route'] }}</td>
                        </tr>
                    @endif

                    @php($isPayMode = !(($routeData['route'] == "Company") || ($routeData['route'] == "Function")) )

                    {{-- Iterate through the routeRecords (grouped by payment mode) --}}
                    @foreach($routeData['routeRecords'] as $payMode => $data)
                        {{-- Display payment mode row --}}
                        @if($isPayMode)
                            <tr class="sub-head-row">
                                <td></td>
                                <td colspan="5">{{ $payMode }}</td>
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
                                <td colspan="4" class="pr-3 text-center">Total ({{ $payMode }})</td>
                                <td class="pr-2">{{ $data['totals']['qty'] }}</td>
                                <td class="pr-2">{{ $data['totals']['amount'] }}</td>
                            </tr>
                        @endif
                    @endforeach

                    {{-- Display route totals --}}
                    <tr class="total-row text-right">
                        <td colspan="4" class="pr-3 text-center">{{ $isPayMode ? 'Route Total' : 'Total' }}</td>
                        <td class="pr-2">{{ $routeData['routeTotals']['qty'] }}</td>
                        <td class="pr-2">{{ $routeData['routeTotals']['amount'] }}</td>
                    </tr>

                    {{-- Add an empty row separator for next route, if any --}}
                    @if($isAllRoutes)
                        <tr class="empty-row">
                            <td colspan="6"></td>
                        </tr>
                    @endif
                @endif
            @endforeach

            @if($isAllRoutes)
            {{-- Display grand totals --}}
                <tr class="grand-total-row text-right">                                                
                    <td colspan="4" class="pr-3 text-center">Grand Total</td>
                    <td class="pr-2">{{ $grandTotals['qty'] }}</td>
                    <td class="pr-2">{{ $grandTotals['amount'] }}</td>
                </tr>

                <tr class="empty-row2">
                    <td colspan="6"></td>
                </tr>
                <tr class="summary-head-row">
                    <td colspan="6" class="pl-4" >Route wise Summary</td>
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
                    <td colspan="6"></td>
                </tr>
                <tr class="summary-head-row">
                    <td colspan="6" class="pl-4">Payment Mode wise Summary</td>
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
                    <td colspan="3" class="pr-3 text-right">Grand Total</td>
                    <td class="text-center">{{ getEmptyForZero($count) }}</td>
                    <td class="text-right pr-2">{{ $grandTotals['qty'] }}</td>
                    <td class="text-right pr-2">{{ $grandTotals['amount'] }}</td>
                </tr>    
        @endif                                    
        </tbody>
    </table>
               