<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <title>Diesel Bill Statement</title>
        <meta name="description" content="">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <style type="text/css">
        </style>
    </head>
    
    <body>
        <page size="A4" style="width: 21cm; display: block; margin:10px auto; margin-bottom: 0.5cm; font-family: Arial, Helvetica, sans-serif; font-size: 12px;">
            <div id="div-statement" class="table-responsive dash-social px-2">                
                <h2 id="hdg-bunk" class="app-h2 pt-2">{{ $record->bunk_name }}</h2>
                <h3 id="hdg-duration" class="app-h3 pb-2">{{ $record->getPeriod() }}</h3>
                <table id="tbl-statement" class="app-table text-nowrap">
                    <thead class="thead-light">
                        <tr>
                            <th class="text-center">S.No</th>
                            <th class="text-center">Date</th>
                            <th class="text-left pl-2">Vehicle</th>
                            <th class="text-left pl-2">Driver</th>
                            <th class="text-left pl-2">Route</th>
                            <th class="text-right pr-2">Fuel</th>
                            <th class="text-right pr-2">Pre KM</th>
                            <th class="text-right pr-2">Cur KM</th>
                            <th class="text-right pr-2">Run KM</th>
                            <th class="text-right pr-2">KMPL</th>
                            <th class="text-right pr-2">Rate</th>
                            <th class="text-right pr-2">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($record->diesel_bills as $bill)
                            <tr>
                                <td class="text-center">{{ $loop->iteration }}</td>
                                <td class="text-center">{{ $bill->bill_date }}</td>
                                <td class="text-left pl-2">{{ $bill->vehicle_number}}</td>
                                <td class="text-left pl-2">{{ $bill->driver_name}}</td>
                                <td class="text-left pl-2">{{ $bill->route_name}}</td>
                                <td class="text-right pr-2">{{ $bill->fuel}}</td>
                                <td class="text-right pr-2">{{ $bill->opening_km}}</td>
                                <td class="text-right pr-2">{{ $bill->closing_km}}</td>
                                <td class="text-right pr-2">{{ $bill->running_km}}</td>
                                <td class="text-right pr-2">{{ $bill->kmpl}}</td>
                                <td class="text-right pr-2">{{ $bill->rate}}</td>
                                <td class="text-right pr-2">{{ $bill->amount}}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="text-right">
                        <tr class="thead-light">
                            <th colspan="5" class="text-center">Total / Average</th>
                            <th>{{ $record->total_fuel }}</th>
                            <th>{{ $record->diesel_bills->sum('opening_km') }}</th>
                            <th>{{ $record->diesel_bills->sum('closing_km') }}</th>
                            <th>{{ (int) $record->total_running_km }}</th>
                            <th>{{ getTwoDigitPrecision($record->total_running_km / $record->total_fuel) }}</th>
                            <th>{{ $record->average_rate }}</th>
                            <th>{{ $record->total_amount }}</th>
                        </tr>
                        @if((float) $record->tds_amount)
                            <tr>
                                <th colspan="11">TDS</th>
                                <th>{{ $record->tds_amount }}</th>
                            </tr>
                        @endif
                        <tr>
                            <th colspan="11">Round Off</th>
                            <th>{{ getRoundOffWithSign($record->round_off) }}</th>
                        </tr>
                        <tr>
                            <th colspan="11">Net Amount</th>
                            <th>{{ getTwoDigitPrecision($record->net_amount) }}</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </page>
    </body>
</html>