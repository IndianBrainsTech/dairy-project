<?php

namespace App\Http\Traits;

use App\Models\Profiles\Customer;
use App\Models\Orders\SalesInvoice;
use App\Models\Orders\TaxInvoice;
use App\Models\Masters\TcsMaster;
use App\Models\Masters\TdsMaster;
use App\Models\Masters\DiscountMaster;

trait CustomerUtility
{
    protected function getTotalTurnover($customerId)
    {
        // Get the start and end dates of the current financial year
        list($fyStart, $fyEnd) = getCurrentFinancialYear(date('Y-m-d'));

        // Get sales and tax invoice turnover
        $salesInvTurnover = SalesInvoice::where('customer_id',$customerId)->whereBetween('order_dt', [$fyStart, $fyEnd])->sum('net_amt');
        $taxInvTurnover = TaxInvoice::where('customer_id',$customerId)->whereBetween('order_dt', [$fyStart, $fyEnd])->sum('net_amt');

        // Calculate total turnover and return it
        $totalTurnOver = $salesInvTurnover + $taxInvTurnover;
        return $totalTurnOver;
    }

    protected function getDiscountList($customerId)
    {
        $discountList = DiscountMaster::where('status', 'Active')
            ->where('effect_date', '<=', date('Y-m-d'))
            ->whereJsonContains('customer_ids', strval($customerId))            
            ->orderByDesc('id')
            ->value('discount_list');

        return $discountList ? json_decode($discountList, true) : "";
    }

    private function getCurrentTdsMaster()
    {
        return TdsMaster::whereDate('effect_date', '<=', date('Y-m-d'))
            ->orderByDesc('effect_date')
            ->first(['id', 'effect_date', 'tds_limit', 'with_pan', 'without_pan']);
    }

    protected function getCurrentTcsMaster()
    {
        $tcsMaster = TcsMaster::whereDate('effect_date', '<=', date('Y-m-d'))
            ->orderByDesc('effect_date')
            ->first(['id', 'effect_date', 'tcs_limit', 'with_pan', 'without_pan']);
        
        return $tcsMaster;
    }
/*
    protected function getTcsAmount($customer, $totalAmt)
    {
        $tcsAmt = null;
        if($customer->tcs_status == "TCS Applied") {
            $tcsMaster = $this->getCurrentTcsMaster();
            $percent = $customer->pan_number ? $tcsMaster->with_pan : $tcsMaster->without_pan;            
            $tcsAmt = floor($totalAmt) * $percent / 100;
        }
        else if($customer->tcs_status == "TCS Applicable") {
            $tcsMaster = $this->getCurrentTcsMaster();
            $turnover = $this->getTotalTurnover($customer->id);
            if(($turnover + $totalAmt) > $tcsMaster->tcs_limit) {  // Check if the customer comes under TCS, with this invoice
                $excessAmt = ($turnover + $totalAmt) - $tcsMaster->tcs_limit;
                $percent = $customer->pan_number ? $tcsMaster->with_pan : $tcsMaster->without_pan;
                $tcsAmt = floor($excessAmt) * $percent / 100;

                $customer = Customer::find($customer->id);
                $customer->tcs_status = "TCS Applied";
                $customer->save();
            }
        }
        return $tcsAmt;
    }
*/

    protected function getTcsAmount($customer, $totalAmt)
    {
        if (!in_array($customer->tcs_status, ["TCS Applied", "TCS Applicable"])) {
            return null;
        }

        $tcsMaster = $this->getCurrentTcsMaster();
        $percent = $customer->pan_number ? $tcsMaster->with_pan : $tcsMaster->without_pan;

        if ($customer->tcs_status === "TCS Applied") {
            return floor($totalAmt) * $percent / 100;
        }

        // Handle "TCS Applicable"
        $turnover = $this->getTotalTurnover($customer->id);        
        $excessAmt = ($turnover + $totalAmt) - $tcsMaster->tcs_limit;

        if ($excessAmt > 0) {
            // Update Status as 'Applied'
            $customer->update(['tcs_status' => "TCS Applied"]); 
            // Calculate and return TCS Amount
            $tcsAmt = floor($excessAmt) * $percent / 100;
            return $tcsAmt;
        }

        return null;
    }

    protected function getTdsAmount($customer, $totalAmt)
    {
        if (!in_array($customer->tds_status, ["TDS Applied", "TDS Applicable"])) {
            return null;
        }

        $tdsMaster = $this->getCurrentTdsMaster();
        $percent = $customer->pan_number ? $tdsMaster->with_pan : $tdsMaster->without_pan;

        if ($customer->tds_status === "TDS Applied" || $customer->tds_status === "TDS Applicable") {            
            return floor($totalAmt) * $percent / 100;
        }
    }

    protected function calcTdsAmount($customer_id, $amount)
    {
        $customer = Customer::where('id',$customer_id)->first();

        if (!in_array($customer->tds_status, ["TDS Applied", "TDS Applicable"])) {
            return 0;
        }

        $tds_master = $this->getCurrentTdsMaster();
        $percent = $customer->pan_number ? $tds_master->with_pan : $tds_master->without_pan;

        if ($customer->tds_status === "TDS Applied") {
            $tds_amt = $amount * $percent / 100;
            return round($tds_amt, 2);
        }

        // Handle "TDS Applicable"
        $incentive = $this->getTotalIncentive($customer->id);
        $excess_amt = ($incentive + $amount) - $tds_master->tds_limit;

        if ($excess_amt > 0) {
            // Update Status as 'Applied'
            $customer->update(['tds_status' => "TDS Applied"]); 
            // Calculate and return TDS Amount
            $tds_amt = $excess_amt * $percent / 100;
            return round($tds_amt, 2);
        }

        return 0;
    }

    private function getTotalIncentive($customer_id)
    {
        // To-Do: Calculate Total Incentive
        return 0;
    }
}