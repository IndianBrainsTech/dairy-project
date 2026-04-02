<?php

namespace App\Http\Traits;

use Illuminate\Http\Request;
use Carbon\Carbon;

trait FilterUtility
{
    protected function resolveDateAndCustomerFilters(Request $request, $latestAvailableDate): array
    {
        $customerId    = $request->input('customer_id', 0);
        $customerName  = $request->input('customer_name', "");
        $hasInputDates = $request->has('from_date') && $request->has('to_date');

        // Use input dates if available
        if ($hasInputDates) { 
            $fromDate = $request->from_date;
            $toDate   = $request->to_date;
        }
        else { // No dates supplied, use latest date or fallback to today
            $latestDate = Carbon::parse($latestAvailableDate ?? now())->format('Y-m-d');
            $fromDate = $toDate = $latestDate;
        }

        return [
            'dates'    => ['from' => $fromDate, 'to' => $toDate],
            'customer' => ['id' => $customerId, 'name' => $customerName],
            'from_to_dates' => [$fromDate, $toDate],
        ];
    }
}