<?php

namespace App\Services;

use App\Models\Orders\SalesInvoice;
use App\Models\Orders\TaxInvoice;
use App\Models\Orders\BulkMilkOrder;
use App\Models\Transactions\Receipt;
use App\Models\Transactions\ReceiptData;
use App\Models\Masters\Outstanding;
use App\Models\Transactions\CreditNotes\CreditNote;
use App\Models\Transactions\CreditNotes\CreditNoteItem;

class ReceivableService
{
    public function getReceivables(int $customerId): array
    {
        $salesInvoices = SalesInvoice::select('id','invoice_num','invoice_date','net_amt')
            ->where('receipt_status','Outstanding')
            ->where('invoice_status','<>','Cancelled')
            ->where('customer_id',$customerId)
            ->whereDate('invoice_date','<=',date('Y-m-d'))
            ->get();
        $taxInvoices = TaxInvoice::select('id','invoice_num','invoice_date','net_amt')
            ->where('receipt_status','Outstanding')
            ->where('invoice_status','<>','Cancelled')
            ->where('customer_id',$customerId)
            ->whereDate('invoice_date','<=',date('Y-m-d'))
            ->get();
        $bulkMilkInvoices = BulkMilkOrder::select('id','invoice_num','invoice_date','net_amt')
            ->where('receipt_status','Outstanding')
            ->where('invoice_status','<>','Cancelled')
            ->where('customer_id',$customerId)
            ->whereDate('invoice_date','<=',date('Y-m-d'))
            ->get();

        // Concatenate the collections
        $invoices = $salesInvoices->concat($taxInvoices)->concat($bulkMilkInvoices);        

        // Convert the collection to an array
        $invoices = $invoices->toArray();

        // Sort the array by invoice_date
        usort($invoices, function($a, $b) {
            return $a['invoice_date'] <=> $b['invoice_date'];
        });

        $creditNoteIds = CreditNote::approved()->where('customer_id', $customerId)->pluck('id');

        $approved = CreditNoteItem::whereIn('credit_note_id', $creditNoteIds)
            ->selectRaw('invoice_number, SUM(adjusted_amount) as total_adjusted')
            ->groupBy('invoice_number')
            ->pluck('total_adjusted', 'invoice_number');

        // Add 'outstanding' attribute to each invoice
        foreach ($invoices as &$invoice) {
            $rcvdAmt = ReceiptData::where('invoice_number',$invoice['invoice_num'])->sum('amount');
            $adjtAmt = $approved[$invoice['invoice_num']] ?? 0;
            $invoice['paid_amt'] = $rcvdAmt + $adjtAmt;
            $invoice['outstanding'] = $invoice['net_amt'] - $invoice['paid_amt'];
            $invoice['invoice_date'] = displayDate($invoice['invoice_date']);
        }

        // Add 'outstanding' record at first, if exists
        $outstanding = Outstanding::select('id','amount','txn_date')
            ->where('customer_id',$customerId)
            ->where('status','Active')
            ->where('receipt_status','Outstanding')
            ->first();

        if($outstanding) {
            $invNum = $customerId . " - OpeningAmt";
            $rcvdAmt = ReceiptData::where('invoice_number',$invNum)->sum('amount');
            array_unshift($invoices, [
                "id"           => 0,
                "invoice_num"  => "Opening Amount",
                "invoice_date" => displayDate($outstanding->txn_date),
                "net_amt"      => $outstanding->amount,
                "paid_amt"     => $rcvdAmt, 
                "outstanding"  => $outstanding->amount - $rcvdAmt,
            ]);
        }
        
        $creditNoteIds = CreditNote::draft()->where('customer_id', $customerId)->pluck('id');

        $adjustments = CreditNoteItem::whereIn('credit_note_id', $creditNoteIds)
            ->selectRaw('invoice_number, SUM(adjusted_amount) as total_adjusted')
            ->groupBy('invoice_number')
            ->pluck('total_adjusted', 'invoice_number');

        // Adjust invoice outstanding amounts by subtracting total draft credit note adjustments,
        // then remove fully settled invoices (outstanding ≤ 0) and reindex the result
        $invoices = collect($invoices)
            ->map(function ($invoice) use ($adjustments) {
                $adjustment = $adjustments[$invoice['invoice_num']] ?? null;
                if(!empty($adjustment)) {
                    $invoice['draft_amt'] = (float)$adjustment;
                    $invoice['outstanding'] -= $adjustment;
                }
                else {
                    $invoice['draft_amt'] = 0;
                }
                return $invoice;
            })            
            ->filter(fn ($invoice) => ($invoice['outstanding'] > 0 || $invoice['draft_amt'] > 0))
            ->values()
            ->all();

        $amount = Receipt::select('id', 'excess_amt')
            ->where('customer_id', $customerId)
            ->orderBy('receipt_date', 'desc') // Order by the receipt_date in descending order
            ->first(); // Get the latest (most recent) record

        return [
            'invoices'   => $invoices,
            'amount'     => $amount,
        ];
    }
}