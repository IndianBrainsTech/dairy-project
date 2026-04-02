<?php

namespace App\Repositories\Reports;

use Illuminate\Support\Facades\DB;
use App\Enums\DocumentStatus;

class DayWiseReportRepository
{
    /* -------------------------------------------------- */
    /* ROUTES + CUSTOMERS */
    /* -------------------------------------------------- */

    public function getRoutes()
    {
        return DB::table('routes')->select('id','name')->get();
    }

    public function getCustomersByRoute($routeId)
    {
        $q = DB::table('customers')
            ->select('id','customer_name','payment_mode');

        if ($routeId == -1) {
            $q->where('group','Company');
        } elseif ($routeId == -2) {
            $q->where('group','Function');
        } else {
            $q->where('route_id',$routeId)
              ->whereIn('group',['Retailer','Distributor','Outlet']);
        }

        return $q->orderBy('payment_mode_order')
                 ->orderBy('customer_name')
                 ->get();
    }

    public function getBanks()
    {
        return DB::table('bank_account')->select('id','display_name')->get();
    }

    /* -------------------------------------------------- */
    /* AGGREGATIONS */
    /* -------------------------------------------------- */

    public function getInvoiceAmounts($ids, $from, $to)
    {
        $tables = ['sales_invoices','tax_invoices','bulk_milk_orders'];

        $union = null;

        foreach ($tables as $t) {
            $q = DB::table($t)
                ->select('customer_id', DB::raw('SUM(net_amt) total'))
                ->whereIn('customer_id',$ids)
                ->where('invoice_status','<>','Cancelled')
                ->whereBetween('invoice_date',[$from,$to])
                ->groupBy('customer_id');

            $union = $union ? $union->unionAll($q) : $q;
        }

        return DB::query()
            ->fromSub($union,'t')
            ->select('customer_id', DB::raw('SUM(total) total'))
            ->groupBy('customer_id')
            ->pluck('total','customer_id')
            ->toArray();
    }

    public function getInvoiceAmountsUpTo($ids, $date)
    {
        $tables = ['sales_invoices','tax_invoices','bulk_milk_orders'];

        $union = null;

        foreach ($tables as $t) {
            $q = DB::table($t)
                ->select('customer_id', DB::raw('SUM(net_amt) total'))
                ->whereIn('customer_id',$ids)
                ->where('invoice_status','Generated')
                ->where('invoice_date','<=',$date)
                ->groupBy('customer_id');

            $union = $union ? $union->unionAll($q) : $q;
        }

        return DB::query()
            ->fromSub($union,'t')
            ->select('customer_id', DB::raw('SUM(total) total'))
            ->groupBy('customer_id')
            ->pluck('total','customer_id')
            ->toArray();
    }

    public function getReceiptAmounts($ids, $from, $to)
    {
        $rows = DB::table('receipts')
            ->select('customer_id','mode','bank_id', DB::raw('SUM(amount) total'))
            ->whereIn('customer_id',$ids)
            ->where('status','Approved')
            ->whereBetween('receipt_date',[$from,$to])
            ->groupBy('customer_id','mode','bank_id')
            ->get();

        $map = [];

        foreach ($rows as $r) {
            $map[$r->customer_id][$r->mode][$r->bank_id ?? 0] = $r->total;
        }

        return $map;
    }

    public function getReceiptAmountsUpTo($ids, $date)
    {
        return DB::table('receipts')
            ->select('customer_id', DB::raw('SUM(amount) total'))
            ->whereIn('customer_id',$ids)
            ->where('status','Approved')
            ->where('receipt_date','<=',$date)
            ->groupBy('customer_id')
            ->pluck('total','customer_id')
            ->toArray();
    }

    public function getReturnAmounts($ids, $from, $to)
    {
        return DB::table('sales_returns')
            ->select('customer_id', DB::raw('SUM(net_amt) total'))
            ->whereIn('customer_id',$ids)
            ->whereBetween('txn_date',[$from,$to])
            ->groupBy('customer_id')
            ->pluck('total','customer_id')
            ->toArray();
    }

    public function getReturnAmountsUpTo($ids, $date)
    {
        return DB::table('sales_returns')
            ->select('customer_id', DB::raw('SUM(net_amt) total'))
            ->whereIn('customer_id',$ids)
            ->where('txn_date','<=',$date)
            ->groupBy('customer_id')
            ->pluck('total','customer_id')
            ->toArray();
    }

    public function getDiscounts($ids, $from, $to)
    {
        return DB::table('credit_notes')
            ->select('customer_id', DB::raw('SUM(amount) total'))
            ->whereIn('customer_id',$ids)
            ->whereBetween('document_date',[$from,$to])
            ->where('status', DocumentStatus::APPROVED)
            ->groupBy('customer_id')
            ->pluck('total','customer_id')
            ->toArray();
    }

    public function getDiscountsUpTo($ids, $date)
    {
        return DB::table('credit_notes')
            ->select('customer_id', DB::raw('SUM(amount) total'))
            ->whereIn('customer_id', $ids)
            ->where('document_date', '<=', $date)
            ->where('status', DocumentStatus::APPROVED)
            ->groupBy('customer_id')
            ->pluck('total', 'customer_id')
            ->toArray();
    }

    public function getOutstanding($ids)
    {
        return DB::table('customer_outstanding')
            ->select('customer_id','amount')
            ->whereIn('customer_id',$ids)
            ->where('status','Active')
            ->pluck('amount','customer_id')
            ->toArray();
    }    
}