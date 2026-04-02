<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Traits\CustomerUtility;
use App\Models\Profiles\Customer;
use App\Models\Masters\IncentiveMaster;
use App\Models\Masters\BankMaster;
use App\Models\Products\Product;
use App\Models\Orders\SalesInvoice;
use App\Models\Orders\TaxInvoice;
use App\Models\Orders\BulkMilkOrder;
use App\Models\Orders\SalesInvoiceItem;
use App\Models\Orders\TaxInvoiceItem;
use App\Models\Orders\BulkMilkOrderItem;
use App\Models\Transactions\Incentive;
use App\Models\Transactions\IncentiveItem;
use App\Models\Transactions\IncentivePayout;
use App\Models\Transactions\BankPayment;
use App\Services\ExcelTemplateService;
use Carbon\Carbon;

class IncentiveController extends Controller
{
    use CustomerUtility;

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function createIncentive()
    {
        return view('transactions.incentives.create_incentive');
    }

    public function dateIncentive(Request $request)
    {
        $record = Incentive::where('customer_id', $request->customer_id)
            ->where('incentive_status','<>','Cancelled')
            ->orderByDesc('to_date')
            ->first();

        return response()->json([
            'date' => $record 
                ? Carbon::parse($record->to_date)->addDay()->toDateString() // next day
                : '2025-04-01'
        ]);
    }

    public function loadIncentive(Request $request)
    {
        // Get input parameters from request
        $customerId = $request->customer_id;
        $fromDate   = $request->from_date;
        $toDate     = $request->to_date;

        // Load incentive master data for the customer
        $incentiveMaster = $this->getIncentiveMaster($customerId);
        if(!$incentiveMaster) {
            return response()->json([
                'success' => false,
                'message' => 'Incentive Not Set for the Customer',
            ]);
        }

        // Fetch invoice quantity details for the given products and date range
        $itemIds    = collect($incentiveMaster)->pluck('id');
        $invoiceQty = $this->getInvoiceQty($customerId, [$fromDate, $toDate], $itemIds);
        if($invoiceQty->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Incentive Items Not in Orders!',
            ]);
        }

        // Re-fetch only invoiced products, ordered by display index
        $itemIds  = collect($invoiceQty)->pluck('id');
        $products = Product::whereIn('id', $itemIds)->orderBy('display_index')->get(['id','name']);

        // Convert incentive and invoice arrays into ID-keyed maps for faster lookup
        $incentiveMasterMap = collect($incentiveMaster)->keyBy('id');
        $invoiceQtyMap      = collect($invoiceQty)->keyBy('id');

        // Build individual item-wise incentive records
        $records = [];
        foreach ($products as $product) {
            $id = $product->id;

            $qty     = (float) ($invoiceQtyMap[$id]['qty'] ?? 0);
            $incRate = (float) ($incentiveMasterMap[$id]['inc_rate'] ?? 0);
            $lkQty   = (float) ($incentiveMasterMap[$id]['lk_qty'] ?? 0);
            $lkAmt   = (float) ($incentiveMasterMap[$id]['lk_amt'] ?? 0);

            $incAmt  = round($qty * $incRate, 2);
            $lkgQty  = round($qty * $lkQty, 2);
            $lkgAmt  = round($lkgQty * $lkAmt, 2);

            $records[] = [
                'item_id'   => $id,
                'item_name' => $product->name,
                'qty'       => $qty,
                'inc_rate'  => $incRate,
                'inc_amt'   => $incAmt,
                'lkg_qty'   => $lkgQty,
                'lkg_amt'   => $lkgAmt,
            ];
        }

        // Compute total quantities and amounts across all items
        $totals = collect($records)->reduce(function ($carry, $item) {
            $carry['qty']     += $item['qty'];
            $carry['inc_amt'] += $item['inc_amt'];
            $carry['lkg_qty'] += $item['lkg_qty'];
            $carry['lkg_amt'] += $item['lkg_amt'];
            return $carry;
        }, [
            'qty' => 0,
            'inc_amt' => 0,
            'lkg_qty' => 0,
            'lkg_amt' => 0,
        ]);

        // Round totals to 2 decimal places for presentation
        $totals['qty']     = round($totals['qty'], 2);
        $totals['inc_amt'] = round($totals['inc_amt'], 2);
        $totals['lkg_qty'] = round($totals['lkg_qty'], 2);
        $totals['lkg_amt'] = round($totals['lkg_amt'], 2);

        // Compute summary values including TDS, rounding adjustments and net total
        $total      = round($totals['inc_amt'] + $totals['lkg_amt'], 2);
        $tdsAmount  = $this->calcTdsAmount($customerId, $total);
        $grandTotal = round($total - $tdsAmount, 2);
        $netTotal   = round($grandTotal);
        $roundOff   = round($netTotal - $grandTotal, 2);

        $summary = [
            'total'       => $total,
            'tds_amount'  => $tdsAmount,
            'grand_total' => $grandTotal,
            'round_off'   => $roundOff,
            'net_total'   => $netTotal,
        ];

        // Return final structured response as JSON
        return response()->json([
            'success' => true,
            'records' => $records,
            'totals'  => $totals,
            'summary' => $summary,
            'date_title' => formatDateRangeAsDMY($fromDate, $toDate),
        ]);
    }

    public function storeIncentive(Request $request)
    {
        // Get data from loadIncentive()
        $response = $this->loadIncentive($request);

        // Convert JsonResponse to array
        $data = $response->getData(true);

        // If incentive data is not available or failed
        if (!$data['success']) {
            return response()->json([
                'success' => false,
                'message' => $data['message'] ?? 'Incentive data could not be loaded.'
            ]);
        }

        // Extract required components
        $records = $data['records'];
        $totals  = $data['totals'];
        $summary = $data['summary'];

        try {
            $customerName = Customer::where('id',$request->customer_id)->value('customer_name');
            $incentive = Incentive::create([
                'incentive_number' => $this->getIncentiveNumber(),
                'incentive_date'   => date('Y-m-d'),
                'customer_id'      => $request->customer_id,
                'customer_name'    => $customerName,
                'from_date'        => $request->from_date,
                'to_date'          => $request->to_date,
                'incentive_total'  => $totals['inc_amt'],
                'leakage_total'    => $totals['lkg_amt'],
                'tds_amount'       => $summary['tds_amount'],
                'round_off'        => $summary['round_off'],
                'net_amount'       => $summary['net_total'],
            ]);

           $data = collect($records)->map(function ($record) use ($incentive) {
                return [
                    'incentive_number' => $incentive->incentive_number,
                    'item_id'          => $record['item_id'],
                    'item_name'        => $record['item_name'],
                    'qty'              => $record['qty'],
                    'inc_rate'         => $record['inc_rate'],
                    'inc_amt'          => $record['inc_amt'],
                    'lkg_qty'          => $record['lkg_qty'],
                    'lkg_amt'          => $record['lkg_amt'],
                    'created_at'   => now(),
                    'updated_at'   => now(),
                ];
            })->toArray();

            IncentiveItem::insert($data);

            return response()->json([
                'success' => true,
                'message' => 'Incentive Created Successfully!',
            ]);
        }
        catch (\Exception $ex) {
            $error = $ex->getMessage();
            $message = str_contains($error, 'Duplicate Incentive') ? 'Duplicate Incentive!' : 'Failed to save incentive!';
            $icon = str_contains($error, 'Duplicate Incentive') ? 'warning' : 'error';

            return response()->json([
                'success' => false,
                'message' => $message,
                'icon'    => $icon,
            ]);
        }
    }

    public function indexIncentives(Request $request)
    {
        $customerId    = $request->input('customer_id', 0);
        $customerName  = $request->input('customer_name', "");
        $hasInputDates = $request->has('from_date') && $request->has('to_date');

        // Use input dates if available
        if ($hasInputDates) { 
            $fromDate = $request->from_date;
            $toDate   = $request->to_date;
        }
        else { // No dates supplied, use latest incentive's date or fallback to today
            // $latestDate = Incentive::latest()->value('incentive_date') ?? date('Y-m-d');
            $latestDate = Carbon::parse(Incentive::max('incentive_date') ?? now())->format('Y-m-d');
            $fromDate = $toDate = $latestDate;
        }

        $incentives = Incentive::select('incentive_number','incentive_date','customer_name','from_date','to_date','net_amount','incentive_status')
            ->whereBetween('incentive_date', [$fromDate, $toDate])
            ->when($customerId != 0, fn($q) => $q->where('customer_id', $customerId))
            ->get();

        foreach($incentives as $incentive) {
            $incentive->period = formatDateRangeAsDMY($incentive->from_date, $incentive->to_date);
        }

        // return response()->json([
        return view('transactions.incentives.list_incentives', [
            'dates'      => ['from' => $fromDate, 'to' => $toDate],
            'customer'   => ['id' => $customerId, 'name' => $customerName],
            'incentives' => $incentives,
        ]);
    }

    public function showIncentive(Request $request)
    {
        $incentiveNumber = $request->incentive_number;
        $numberList = $request->number_list;

        $incentive = Incentive::where('incentive_number', $incentiveNumber)->first();
        $customer = Customer::with('route:id,name')->where('id', $incentive->customer_id)->first();

        $records = IncentiveItem::select('item_name','qty','inc_rate','inc_amt','lkg_qty','lkg_amt')
            ->where('incentive_number', $incentiveNumber)
            ->get();

        // Formatting output
        $data = (object)[
            'number'   => $incentiveNumber,
            'date'     => displayDate($incentive->incentive_date),
            'status'   => $incentive->incentive_status,
            'customer' => $incentive->customer_name,
            'route'    => $customer->route->name,
            'period'   => formatDateRangeAsDMY($incentive->from_date, $incentive->to_date),
        ];

        $summary = (object)[
            'qty'       => formatIndianNumberWithDecimal($records->sum('qty')),
            'lkg_qty'   => formatIndianNumberWithDecimal($records->sum('lkg_qty')),
            'inc_amt'   => formatIndianNumberWithDecimal($records->sum('inc_amt')),
            'lkg_amt'   => formatIndianNumberWithDecimal($records->sum('lkg_amt')),
            'tot_amt'   => formatIndianNumberWithDecimal($incentive->incentive_total + $incentive->leakage_total),
            'tds_amt'   => formatIndianNumberWithDecimal($incentive->tds_amount),
            'round_off' => getRoundOffWithSign($incentive->round_off),
            'net_amt'   => formatIndianNumberWithDecimal($incentive->net_amount),
        ];

        foreach($records as $record) {
            $record->qty      = getTwoDigitPrecision($record->qty);
            $record->inc_rate = getTwoDigitPrecision($record->inc_rate);
            $record->inc_amt  = getTwoDigitPrecision($record->inc_amt);
            $record->lkg_qty  = getTwoDigitPrecision($record->lkg_qty);
            $record->lkg_amt  = getTwoDigitPrecision($record->lkg_amt);
        }

        if(!$numberList) {
            return response()->json([
                'incentive'   => $data,
                'records'     => $records,
                'summary'     => $summary,
            ]);
        }
        else {            
            // return response()->json([
            return view('transactions.incentives.view_incentive', [
                'incentive'   => $data,
                'records'     => $records,
                'summary'     => $summary,
                'number_list' => $numberList,
            ]);
        }
    }

    public function makeIncentive()
    {
        $incentives = Incentive::select('incentive_number','incentive_date','customer_name','from_date','to_date','net_amount')
            ->where('incentive_status','Pending')
            ->get();

        // Formatting output
        $records = collect();
        foreach($incentives as $incentive) {
            $records[] = (object)[
                'date'     => displayDate($incentive->incentive_date),
                'number'   => $incentive->incentive_number,
                'customer' => $incentive->customer_name,
                'period'   => formatDateRangeAsDMY($incentive->from_date, $incentive->to_date),
                'amount'   => getTwoDigitPrecision($incentive->net_amount),
            ];
        }

        // return response()->json([
        return view('transactions.incentives.make_incentives', [
            'records' => $records
        ]);
    }

    public function actionIncentive(Request $request)
    {
        $status = match($request->action) {
            'Accept' => 'Accepted',
            'Cancel' => 'Cancelled',
            default => null,
        };

        if (!$status) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid action provided.',
            ]);
        }

        try {
            Incentive::where('incentive_number', $request->number)
                    ->update(['incentive_status' => $status]);

            return response()->json([
                'success' => true,
                'message' => "Incentive {$status} Successfully!",
            ]);
        } 
        catch (\Exception $ex) {
            return response()->json([
                'success' => false,
                'message' => $ex->getMessage(),
            ]);
        }
    }

    public function createReceiptPayout(Request $request)
    {        
        $data = $this->getOutstandingIncentives($request);
        $dates = $data['dates'];
        $incentives = $data['incentives'];

        // return response()->json([
        return view('transactions.incentives.payouts.create_receipt_payout', [
            'dates'   => ['from' => $dates[0], 'to' => $dates[1]],
            'incentives' => $incentives,
        ]);
    }

    public function createBankPayout(Request $request)
    {
        $data = $this->getOutstandingIncentives($request);
        $dates = $data['dates'];
        $incentives = collect($data['incentives'])->sortBy('customer_name')->values()->all();

        // return response()->json([
        return view('transactions.incentives.payouts.create_bank_payout', [
            'dates'      => ['from' => $dates[0], 'to' => $dates[1]],
            'incentives' => $incentives,
        ]);
    }

    public function storeBankPayout(Request $request)
    {
        $data = $request->incentive_data;

        try {
            // Fetch all customer_ids at once by incentive_numbers
            $incentiveNumbers = collect($data)->pluck('number')->all();
            $incentives = Incentive::whereIn('incentive_number', $incentiveNumbers)
                ->pluck('customer_id', 'incentive_number');

            // Prepare records using the fetched data
            $records = [];
            foreach ($data as $record) {
                $customerId = $incentives[$record['number']];
                $records[] = [
                    'incentive_number' => $record['number'],
                    'customer_id'      => $customerId,
                    'document_date'    => today()->toDateString(),
                    'amount'           => $record['amount'],
                    'payout_mode'      => 'Bank',
                ];
            }

            // Insert all records in a single query
            IncentivePayout::insert($records);

            return response()->json([
                'success' => true,
                'message' => 'Records have been sent for approval!',
            ]);
        } catch (\Exception $ex) {
            return response()->json([
                'success' => false,
                'message' => $ex->getMessage(),
            ]);
        }
    }

    public function approveBankPayoutList()
    {
        $payouts = IncentivePayout::select('id','incentive_number','document_date','amount')
            ->with('incentiveNumber:incentive_number,customer_name,from_date,to_date')
            ->where('payout_mode','Bank')
            ->where('payout_status','Pending')
            ->get();

        $paused = IncentivePayout::select('id','incentive_number','document_date','amount')
            ->with('incentiveNumber:incentive_number,customer_name,from_date,to_date')
            ->where('payout_mode','Bank')
            ->where('payout_status','Paused')
            ->get();

        foreach($payouts as $payout) {
            $payout->document_date = displayDate($payout->document_date);
            $payout->period = formatDateRangeAsDMY($payout->incentiveNumber->from_date, $payout->incentiveNumber->to_date);
        }

        foreach($paused as $record) {
            $record->document_date = displayDate($record->document_date);
            $record->period = formatDateRangeAsDMY($record->incentiveNumber->from_date, $record->incentiveNumber->to_date);
        }

        // Sort by customer name
        $payouts = collect($payouts)
            ->sortBy(fn($item) => optional($item->incentiveNumber)->customer_name)
            ->values()
            ->all();

        $banks = BankMaster::get(['id','display_name']);

        // return response()->json([
        return view('transactions.incentives.payouts.approve_bank_payout', [
            'payouts' => $payouts,
            'paused'  => $paused,
            'banks'   => $banks,  
        ]);
    }

    public function approveBankPayout(Request $request)
    {
        $records = collect($request->records);

        try {
            // Extract payout IDs and map to amounts
            $payoutIds = $records->pluck('payout_id')->map(fn($id) => (int)$id)->all();
            $amountMap = $records->keyBy('payout_id')->map(fn($r) => $r['amount']);

            // Load all payout models at once
            $payouts = IncentivePayout::whereIn('id', $payoutIds)->get();

            // Update payouts
            foreach ($payouts as $payout) {
                $payout->update([
                    'amount'        => $amountMap[$payout->id] ?? $payout->amount,
                    'payout_status' => 'Approved',
                    'approval_date' => today()->toDateString(),
                ]);
            }

            // Collect unique incentive numbers
            $incentiveNumbers = $payouts->pluck('incentive_number')->unique()->values();

            // Load incentives and sum payouts
            $incentives = Incentive::whereIn('incentive_number', $incentiveNumbers)->get()->keyBy('incentive_number');
            $approvedSums = IncentivePayout::whereIn('incentive_number', $incentiveNumbers)
                ->where('payout_status', 'Approved')
                ->groupBy('incentive_number')
                ->selectRaw('incentive_number, SUM(amount) as total_paid')
                ->pluck('total_paid', 'incentive_number');

            // Update payment_status for each incentive
            foreach ($incentiveNumbers as $number) {
                $incentive = $incentives[$number] ?? null;
                if (!$incentive) continue;

                $netAmount = $incentive->net_amount;
                $paid = $approvedSums[$number] ?? 0;
                $paymentStatus = ($netAmount == $paid) ? 'Paid' : 'Outstanding';

                $incentive->update(['payment_status' => $paymentStatus]);
            }

            // Create Bank Payment
            $payNumber = $this->getBankPaymentNumber();
            $bankPayment = BankPayment::create([
                'document_number'   => $payNumber,
                'payment_date'      => today()->toDateString(),
                'payment_type'      => 'INCENTIVE',
                'bank_id'           => $request->bank_id,
                'bank_name'         => $request->bank_name,
                'total_amount'      => $request->total_amount,
                'reference_numbers' => $payoutIds,
            ]);

            // Update reference_number in all related payouts
            IncentivePayout::whereIn('id', $payoutIds)
                ->update(['reference_number' => $payNumber]);              

            // Response
            return response()->json([
                'success' => true,
                'message' => 'Incentives Approved Successfully!',
                'pay_id'  => $bankPayment->id,
            ]);
        }
        catch (\Exception $ex) {
            return response()->json([
                'success' => false,
                'message' => $ex->getMessage(),
            ]);
        }
    }    

    public function updateBankPayoutStatus(Request $request)
    {
        try {
            $actionStatusMap = [
                'Pause'  => 'Paused',
                'Resume' => 'Pending',
                'Cancel' => 'Cancelled',
            ];

            $status = $actionStatusMap[$request->action];

            IncentivePayout::where('id', $request->id)
                ->update(['payout_status' => $status]);

            return response()->json([
                'success' => true,
                'message' => "Payout has been updated successfully!",
            ]);
        }
        catch (\Exception $ex) {
            return response()->json([
                'success' => false,
                'message' => $ex->getMessage(),
            ]);
        }
    }

    public function downloadBankPayment(Request $request, ExcelTemplateService $excel)
    {
        $bankPayment = BankPayment::find($request->pay_id);
        $bank = $bankPayment->bank_name;
        $date = $bankPayment->payment_date;
        $numbers = $bankPayment->reference_numbers;
        
        if($bank === "KVB") {
            $fileName = "Incentive_Payments_{$date}_KVB.xlsx";
            $records  = $this->generatePaymentRecordsKVB($numbers);
        }
        else if($bank === "HDFC") {
            $fileName = "Incentive_Payments_{$date}_HDFC.xlsx";
            $records  = $this->generatePaymentRecordsHDFC($numbers, $date);
        }
        
        // return response()->json([
        //     'bank'      => $bank,
        //     'file_name' => $fileName,
        //     'records'   => $records,
        // ]);

        $file = $excel->generate($records, $fileName, $bank, $date);

        return $excel->download($file);
    }

    public function indexBankPayments(Request $request)
    {
        $hasInputDates = $request->has('from_date') && $request->has('to_date');

        // Use input dates if available
        if ($hasInputDates) { 
            $fromDate = $request->from_date;
            $toDate   = $request->to_date;
        }
        else { // No dates supplied, use latest payments's date or fallback to today
            $latestDate = BankPayment::where('payment_type','Incentive')->latest('payment_date')->value('payment_date') ?? now();
            $fromDate = $toDate = Carbon::parse($latestDate)->toDateString();
        }

        $records = BankPayment::select('id','document_number','payment_date','bank_name','total_amount')
            ->whereBetween('payment_date', [$fromDate, $toDate])
            ->get();

        // return response()->json([
        return view('transactions.incentives.records.list_bank_payments', [
            'dates'   => ['from' => $fromDate, 'to' => $toDate],
            'records' => $records,
        ]);
    }

    public function showBankPayment(Request $request)
    {
        $data = BankPayment::select('id','document_number','payment_date','bank_name','total_amount')
            ->where('document_number', $request->number)
            ->first();
        $data->date = Carbon::parse($data->payment_date)->format('d-m-Y');
        $data->total_amount = "Rs." . formatIndianNumber($data->total_amount);

        $payouts = IncentivePayout::select('id','incentive_number','customer_id','amount')
            ->with(['incentiveNumber:incentive_number,from_date,to_date'])
            ->with(['customer:id,customer_name,bank_name,branch,ifsc,acc_holder,acc_number'])
            ->where('reference_number', $request->number)
            ->get();

        $records = [];
        foreach ($payouts as $payout) {
            $records[] = [
                'incentive_number' => $payout->incentive_number,
                'period'           => formatDateRangeAsDMY($payout->incentiveNumber->from_date, $payout->incentiveNumber->to_date),
                'customer_name'    => $payout->customer->customer_name,
                'amount'           => formatIndianNumber($payout->amount),
                'account_number'   => $payout->customer->acc_number,
                'bank_name'        => $payout->customer->bank_name,
            ];
        }

        // return response()->json([
        return view('transactions.incentives.records.view_bank_payment', [
            'data'        => $data,
            'records'     => $records,
            'number_list' => $request->number_list,
        ]);
    }

    private function getIncentiveNumber()
    {
        $last = Incentive::latest('id')->value('incentive_number');
        $number = $last ? intval(substr($last, 4)) + 1 : 1;
        return 'INC-' . str_pad($number, 3, '0', STR_PAD_LEFT);
    }

    private function getIncentiveMaster($customerId)
    {
        $incentiveMaster = IncentiveMaster::where('status', 'Active')
            ->where('effect_date', '<=', date('Y-m-d'))
            ->whereJsonContains('customer_ids', strval($customerId))
            ->orderByDesc('id')
            ->value('incentive_data');

        return $incentiveMaster ? json_decode($incentiveMaster, true) : "";
    }

    private function getBankPaymentNumber()
    {
        $last = BankPayment::latest('id')->value('document_number');
        $number = $last ? intval(substr($last, 4)) + 1 : 1;
        return 'BPY-' . str_pad($number, 3, '0', STR_PAD_LEFT);
    }

    private function getInvoiceQty($customerId, $dates, $itemIds)
    {
        $salesInvoiceNumbers = SalesInvoice::whereBetween('invoice_date', $dates)
            ->where('customer_id', $customerId)
            ->where('invoice_status', 'Generated')
            ->pluck('invoice_num');

        $taxInvoiceNumbers = TaxInvoice::whereBetween('invoice_date', $dates)
            ->where('customer_id', $customerId)
            ->where('invoice_status', 'Generated')
            ->pluck('invoice_num');

        $bulkInvoiceNumbers = BulkMilkOrder::whereBetween('invoice_date', $dates)
            ->where('customer_id', $customerId)
            ->where('invoice_status', 'Generated')
            ->pluck('invoice_num');

        $salesInvoiceItems = SalesInvoiceItem::whereIn('invoice_num', $salesInvoiceNumbers)
            ->whereIn('product_id', $itemIds)
            ->where('item_category', 'Regular')
            ->get(['product_id', 'qty']);

        $taxInvoiceItems = TaxInvoiceItem::whereIn('invoice_num', $taxInvoiceNumbers)
            ->whereIn('product_id', $itemIds)
            ->where('item_category', 'Regular')
            ->get(['product_id', 'qty']);

        $bulkInvoiceItems = BulkMilkOrderItem::whereIn('invoice_num', $bulkInvoiceNumbers)
            ->whereIn('product_id', $itemIds)
            ->get(['product_id', 'qty_ltr'])
            ->map(function ($item) {
                return [
                    'product_id' => $item->product_id,
                    'qty' => $item->qty_ltr, // normalize field
                ];
            });

        $invoiceItems = collect()
            ->merge($salesInvoiceItems)
            ->merge($taxInvoiceItems)
            ->merge($bulkInvoiceItems);

        $groupedItems = $invoiceItems
            ->groupBy('product_id')
            ->map(function ($items, $product_id) {
                return [
                    'id'  => $product_id,
                    'qty' => round($items->sum('qty'), 2),
                ];
            })
            ->values();

        return $groupedItems;
    }
    
    private function getOutstandingIncentives(Request $request)
    {
        $hasInputDates = $request->has('from_date') && $request->has('to_date');
        if ($hasInputDates) {
            $dates = [$request->from_date, $request->to_date];
        }
        else { 
            // No dates supplied, use latest incentive's date or fallback to today
            // $latestDate = Incentive::latest()->value('incentive_date') ?? date('Y-m-d');
            $latestDate = Carbon::parse(Incentive::max('incentive_date') ?? now())->format('Y-m-d');
            $dates = [$latestDate, $latestDate];
        }

        $incentives = Incentive::select('incentive_number','incentive_date','customer_id','customer_name','from_date','to_date','net_amount')
            ->whereBetween('incentive_date', $dates)
            ->where('payment_status','Outstanding')
            ->where('incentive_status','Accepted')
            ->get();

        foreach($incentives as $incentive) {
            $incentive->incentive_date = displayDate($incentive->incentive_date);
            $incentive->period = formatDateRangeAsDMY($incentive->from_date, $incentive->to_date);

            $paidAmount = IncentivePayout::where('incentive_number', $incentive->incentive_number)
                ->whereNot('payout_status', 'Cancelled')
                ->sum('amount');

            $incentive->payable = $incentive->net_amount - $paidAmount;
        }

        // Now filter incentives with payable > 0
        $incentives = $incentives->filter(function ($incentive) {
            return $incentive->payable > 0;
        })->values(); // Reset keys

        return [
            'dates'      => $dates,
            'incentives' => $incentives,
        ];
    }

    private function generatePaymentRecordsKVB(array $payoutIds): array
    {
        $records = [];        
        $date = today()->format('d/m/Y');        
        $kvbAccountNumber = BankMaster::where('id',1)->value('acc_number');

        // Fetch all payouts in one go with their related customers
        $payouts = IncentivePayout::select('id', 'customer_id', 'amount')
            ->with(['customer:id,customer_name,email_id,bank_name,branch,ifsc,acc_holder,acc_number'])
            ->whereIn('id', $payoutIds)
            ->get();

        $i = 1;
        foreach ($payouts as $payout) {
            if (!$payout->customer) {
                continue; // Skip if related customer is missing
            }

            $customer = $payout->customer;
            $transactionType = str_starts_with($customer->ifsc, 'KVBL') ? "INTERNAL TRANSFER" : "IMPS TRANSFER-IFSC";

            $records[] = [
                'transaction_type'                => $transactionType,
                'debting_account_number'          => $kvbAccountNumber,
                'beneficiary_ifsc_code'           => $customer->ifsc,
                'beneficiary_account_number'      => $customer->acc_number,
                'beneficiary_name'                => $customer->acc_holder,
                'beneficiary_address_line_1'      => '',
                'beneficiary_address_line_2'      => '',
                'beneficiary_address_line_3'      => '',
                'beneficiary_address_line_4'      => '',
                'transaction_reference_number'    => $i++,
                'amount'                          => $payout->amount,                
                'sender_to_receiver_info'         => $customer->customer_name,
                'additional_info_1_account_type'  => '',
                'additional_info_2_mobile_number' => '',
                'additional_info_3_mmid'          => '',
                'additional_info_4'               => '',
            ];
        }

        return $records;
    }

    private function generatePaymentRecordsHDFC(array $payoutIds, string $date): array
    {
        $records = [];        
        $defaultMail = "assaineft@gmail.com";

        // Fetch all payouts in one go with their related customers
        $payouts = IncentivePayout::select('id','incentive_number','customer_id','amount')
            ->with(['customer:id,customer_name,customer_code,email_id,bank_name,branch,ifsc,acc_holder,acc_number'])
            ->with(['incentiveNumber:incentive_number,from_date,to_date'])
            ->whereIn('id', $payoutIds)
            ->get();            

        foreach ($payouts as $payout) {
            if (!$payout->customer) {
                continue; // Skip if related customer is missing
            }

            $customer = $payout->customer;
            $transactionType = str_starts_with($customer->ifsc, 'HDFC') ? "I" : "N";
            $period = formatDateRangeAsDMY($payout->incentiveNumber->from_date, $payout->incentiveNumber->to_date);

            $records[] = [
                'transaction_type'               => $transactionType,
                'beneficiary_code'               => '',
                'beneficiary_account_number'     => $customer->acc_number,
                'amount'                         => $payout->amount,
                'beneficiary_name'               => $customer->acc_holder,
                'to_be_left_blank1'              => '',
                'to_be_left_blank2'              => '',
                'to_be_left_blank3'              => '',
                'to_be_left_blank4'              => '',
                'to_be_left_blank5'              => '',
                'to_be_left_blank6'              => '',
                'to_be_left_blank7'              => '',
                'to_be_left_blank8'              => '',
                'customer_reference_number'      => $customer->customer_code,
                'payment_detail_1'               => $customer->customer_name,
                'payment_detail_2'               => 'Incentive',
                'payment_detail_3'               => $period,
                'payment_detail_4'               => '',
                'payment_detail_5'               => '',
                'payment_detail_6'               => '',
                'payment_detail_7'               => '',
                'to_be_left_blank9'              => '',
                'inst_date'                      => $date,
                'to_be_left_blank10'             => '',
                'ifsc_code'                      => $customer->ifsc,
                'beneficiary_bank_name'          => $customer->bank_name,
                'beneficiary_bank_branch_name'   => $customer->branch,
                'beneficiary_email_id'           => $customer->email_id ?? $defaultMail,
            ];
        }

        return $records;
    }    
}
