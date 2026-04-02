<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use App\Models\Orders\SalesInvoice;
use App\Models\Orders\TaxInvoice;
use App\Models\Orders\BulkMilkOrder;

class InvoiceService
{
    public function updateStatusAsPaid(array $invoiceNumbers)
    {        
        DB::transaction(function () use ($invoiceNumbers) {            
            SalesInvoice::whereIn('invoice_num', $invoiceNumbers)->update(['receipt_status' => 'Paid']);
            TaxInvoice::whereIn('invoice_num', $invoiceNumbers)->update(['receipt_status' => 'Paid']);
            BulkMilkOrder::whereIn('invoice_num', $invoiceNumbers)->update(['receipt_status' => 'Paid']);
        });
    }
}