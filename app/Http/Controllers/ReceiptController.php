<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use App\Services\ReceivableService;
use App\Models\Masters\BankMaster;
use App\Models\Masters\RupeeNote;
use App\Models\Profiles\Customer;
use App\Models\Places\MRoute;
use App\Models\Orders\Order;
use App\Models\Orders\SalesInvoice;
use App\Models\Orders\TaxInvoice;
use App\Models\Orders\BulkMilkOrder;
use App\Models\Transactions\Receipt;
use App\Models\Transactions\ReceiptData;
use App\Models\Transactions\BatchDenomination;
use App\Models\Transactions\IncentivesData;
use App\Models\Transactions\IncentivePayment;
use App\Models\Transactions\IncentivePayout;
use App\Models\Masters\Outstanding;
use App\Models\Masters\DateEntrySetting;
use Carbon\Carbon;

class ReceiptController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function createReceipt()
    {
        $routes = MRoute::select('id','name')->orderBy('name')->get();
        $notes = RupeeNote::orderBy('display_index')->pluck('note_value');
        $banks = BankMaster::orderBy('id')->get(['id', 'display_name']);
        $dateSettings = $this->fetchDateSetting();

        // return response()->json([
        return view('transactions.receipts.create_receipt', [
            'routes' => $routes,
            'notes'  => $notes,
            'banks'  => $banks,
            'date_settings' => $dateSettings,
        ]);
    }

    public function indexReceipts(Request $request)
    {
        $routes = MRoute::orderBy('name')->get(['id', 'name']);
        $rupeeNotes = RupeeNote::orderBy('display_index')->pluck('note_value');
        $routeId = $request->input('route', 0);
        $date = $request->date ?? date('Y-m-d');
        $data = $this->getReceiptsAndTotals($routeId,$date);

        //  return response()->json([
        return view('transactions.receipts.list_receipts', [
            'route_id'        => $routeId,
            'routes'          => $routes,
            'receipts'        => $data['receipts'],
            'modeTotals'      => $data['modeTotals'],
            'cumulativeTotal' => $data['cumulativeTotal'],
            'notes'           => $rupeeNotes,
            'date'            => $date
        ]);
    }

    public function storeReceipt(Request $request)
    {
        try {
            $routeId = Customer::where('id',$request->cust_id)->value('route_id');
            
            // Create a new receipt
            $receipt = new Receipt([
                'receipt_num'   => $this->generateReceiptNumber(),
                'receipt_date'  => $request->rcpt_date,
                'route_id'      => $routeId,
                'customer_id'   => $request->cust_id,
                'customer_name' => $request->cust_name,
                'amount'        => $request->amount,
                'aggregate_amt' => $request->aggr_amt,
                'advance_amt'   => $request->adv_amt,
                'excess_amt'    => $request->excess_amt,
                'mode'          => $request->mode,
                'receipt_data'  => $request->receipt_data,
            ]);
    
            // Add mode-specific details
            if ($request->mode === "Cash") {
                $receipt->denomination = $request->denomination;
            } 
            elseif ($request->mode === "Bank" || $request->mode === "Deposit") {
                $receipt->bank_id   = $request->bank_id;
                $receipt->trans_num = $request->trans_num;
                $receipt->remarks   = $request->remarks;
            }
            elseif ($request->mode === "Incentive") {
                $receipt->incentive_data = $request->incentive_data;
            }

            $receipt->save();
    
            // Process receipt data
            $receiptDataList = json_decode($receipt->receipt_data, true);
            foreach ($receiptDataList as $data) {
                if (!empty($data['rcvd_amt'])) {
                    $receiptStatus = $data['rcvd_amt'] == $data['oustd_amt'] ? "Paid" : "Outstanding";
                    if($data['inv_num'] === "Opening Amount")
                        $invNum = $request->cust_id . " - OpeningAmt";
                    else
                        $invNum = $data['inv_num'];

                    ReceiptData::create([
                        'receipt_id'     => $receipt->id,
                        'receipt_date'   => $receipt->receipt_date,
                        'invoice_number' => $invNum,
                        'amount'         => $data['rcvd_amt'],
                        'receipt_status' => $receiptStatus,
                    ]);
    
                    if ($receiptStatus === "Paid") {
                        if($data['inv_num'] === "Opening Amount") {
                            $outstanding = Outstanding::where('customer_id',$request->cust_id)->where('status','Active')->first();
                            $outstanding->update(['receipt_status' => 'Paid']);
                        }
                        else {
                            $this->updateReceiptStatus($data['inv_num'], "Paid");
                        }
                    }
                }
            }

            // Update incentive if any
            if ($request->mode === "Incentive") {
                $this->updateIncentiveData($request, $receipt);
            }
    
            return response()->json([
                'success' => true,
                'message' => "Receipt Saved Successfully!",
            ]);
        }
        catch (QueryException $exception) {
            $error = $exception->getMessage();
            $message = str_contains($error, 'Duplicate Receipt') ? 'Duplicate Receipt!' : 'Failed to save receipt!';
            $icon = str_contains($error, 'Duplicate Receipt') ? 'warning' : 'error';

            return response()->json([
                'success' => false,
                'message' => $message,
                'icon'    => $icon,
            ], 500);
        }
    }

    public function showReceipt(Request $request)
    {
        $receipt = Receipt::with('bank:id,display_name')->where('receipt_num',$request->receipt_num)->first();
        $receipt->receipt_data = json_decode($receipt->receipt_data, true);
        $receipt->denomination = json_decode($receipt->denomination, true);
        $receipt->incentive_data = json_decode($receipt->incentive_data, true);

        // return response()->json([
        return view('transactions.receipts.view_receipt', [
            'receipt'  => $receipt,            
            'receipts' => $request->receipts,
        ]);
    }

    public function editReceipt(Request $request)
    {        
        $receipt = Receipt::where('receipt_num',$request->receipt_num)->first();
        $receipt->receipt_data = json_decode($receipt->receipt_data, true);
        $receipt->denomination = json_decode($receipt->denomination, true);
        $receipt->incentive_data = json_decode($receipt->incentive_data, true);

        $notes = RupeeNote::orderBy('display_index')->pluck('note_value');
        $banks = BankMaster::orderBy('id')->get(['id', 'display_name']);
    
        // return response()->json([
        return view('transactions.receipts.edit_receipt', [
            'receipt' => $receipt,            
            'notes'   => $notes,
            'banks'   => $banks,
        ]);
    }

    public function updateReceipt(Request $request)
    {
        try {
            $receipt = Receipt::where('receipt_num', $request->rcpt_num)->firstOrFail();

            $data = [
                'receipt_date'  => $request->rcpt_date,
                'amount'        => $request->amount,
                'aggregate_amt' => $request->aggr_amt,
                'excess_amt'    => $request->excess_amt,
                'mode'          => $request->mode,
                'receipt_data'  => $request->receipt_data,
            ];
            $receipt->update($data);

            $modeSpecificData = match ($request->mode) {
                'Cash'      => ['denomination' => $request->denomination],
                'Bank', 'Deposit' => [
                    'bank_id'   => $request->bank_id,
                    'trans_num' => $request->trans_num,
                    'remarks'   => $request->remarks,
                ],
                'Incentive' => ['incentive_data' => $request->incentive_data],
                default     => [],
            };
            $receipt->update($modeSpecificData);
    
            // Process receipt data [delete and recreate]
            ReceiptData::where('receipt_id', $receipt->id)->delete();
            $receiptDataList = json_decode($receipt->receipt_data, true);
            foreach ($receiptDataList as $data) {
                if (!empty($data['rcvd_amt'])) {
                    $receiptStatus = $data['rcvd_amt'] == $data['oustd_amt'] ? "Paid" : "Outstanding";
                    if($data['inv_num'] === "Opening Amount")
                        $invNum = $receipt->customer_id . " - OpeningAmt";
                    else
                        $invNum = $data['inv_num'];
                    ReceiptData::create([
                        'receipt_id'     => $receipt->id,
                        'receipt_date'   => $receipt->receipt_date,
                        'invoice_number' => $invNum,
                        'amount'         => $data['rcvd_amt'],
                        'receipt_status' => $receiptStatus,
                    ]);

                    $this->updateReceiptStatus($data['inv_num'], $receiptStatus);                    
                }
            }

            // Update incentive if any
            if ($request->mode === "Incentive") {
                IncentivePayment::where('reference_num', $receipt->receipt_num)->delete();
                $this->updateIncentiveData($request, $receipt);
            }
    
            return response()->json([
                'success' => true,
                'message' => "Receipt Updated Successfully!",
            ]);
        } catch (QueryException $exception) {
            return response()->json([
                'success' => false,
                'message' => "Failed to update receipt.",
                'error'   => $exception->getMessage(),
            ], 500);
        }
    }

    public function getReceivables($customerId, ReceivableService $receivableService)
    {
        $data = $receivableService->getReceivables($customerId);

        return [
            'invoices' => array_values(array_filter(
                $data['invoices'],
                fn ($invoice) => $invoice['outstanding'] > 0
            )),
            'amount'   => $data['amount'],
        ];
        /*
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

        // Add 'outstanding' attribute to each invoice
        foreach ($invoices as &$invoice) {
            $rcvdAmt = ReceiptData::where('invoice_number',$invoice['invoice_num'])->sum('amount');
            $invoice['paid_amt'] = $rcvdAmt;
            $invoice['outstanding'] = $invoice['net_amt'] - $rcvdAmt;
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

        // Remove invoices with zero or negative outstanding
        $invoices = array_filter($invoices, function($invoice) {
            return $invoice['outstanding'] > 0;
        });

        // Re-index the array
        $invoices = array_values($invoices);
        
        $amount = Receipt::select('id', 'excess_amt')
            ->where('customer_id', $customerId)
            ->orderBy('receipt_date', 'desc') // Order by the receipt_date in descending order
            ->first(); // Get the latest (most recent) record

        return [
            'invoices'   => $invoices,
            'amount'     => $amount,
        ]; */
    }

    public function createBatchDenomination()
    {        
        $receipts = Receipt::select('id','receipt_num','route_id','customer_name','amount')
            ->with('route:id,name')
            ->whereDate('created_at', date('Y-m-d'))
            ->where('mode','Cash')
            ->whereNull('denomination')
            ->get();

        $denominations = BatchDenomination::select('id','route_id','receipt_numbers','amount')
            ->with('route:id,name')
            ->whereDate('created_at', date('Y-m-d'))
            ->get();
        foreach($denominations as $record) {
            $dataArray = json_decode($record->receipt_numbers, true);
            $record->receipt_count = count($dataArray);
        }

        $rupeeNotes = RupeeNote::orderBy('display_index')->pluck('note_value');

        // return response()->json([
        return view('transactions.receipts.batch_denomination', [
            'receipts'      => $receipts,
            'denominations' => $denominations,
            'notes'         => $rupeeNotes
        ]);
    }

    public function getBatchDenomination($routeId)
    {
        $receipts = Receipt::select('id','receipt_num','customer_name','amount')
            ->whereDate('created_at', date('Y-m-d'))
            ->where('route_id', $routeId)
            ->where('mode','Cash')
            ->whereNull('denomination')
            ->get();

        return response()->json($receipts);
    }

    public function storeBatchDenomination(Request $request)
    {
        try {
            // Create a new BatchDenomination
            $denomination = BatchDenomination::create([
                'route_id'        => $request->route_id,
                'receipt_date'    => now()->format('Y-m-d'),
                'receipt_numbers' => $request->receipt_numbers,
                'amount'          => $request->amount,
                'denomination'    => $request->denomination,
            ]);

            // Update the Receipts table with the denomination ID
            $receiptNumbers = json_decode($request->receipt_numbers);
            Receipt::whereIn('receipt_num', $receiptNumbers)->update(['denomination' => $denomination->id]);

            return response()->json([
                'success' => true,
                'message' => "Denomination Saved Successfully!",
            ]);
        } catch (QueryException $exception) {
            // Return structured error response
            return response()->json([
                'success' => false,
                'message' => "Failed to save denomination.",
                'error'   => $exception->getMessage(),
            ], 500);
        }
    }

    public function indexMakeReceipts(Request $request)
    {
        $date = $request->date ?? date('Y-m-d');
        $routes = MRoute::orderBy('name')->get(['id', 'name']);
        $routeIds = $routes->pluck('id');

        // Fetch all today's receipts for the given routes
        $receipts = Receipt::whereDate('receipt_date', $date)
            ->whereIn('route_id', $routeIds)
            ->select('route_id', 'mode', 'amount', 'status')
            ->get();

        // Group receipts by route
        $groupedReceipts = $receipts->groupBy('route_id');

        $table = [];
        foreach ($routes as $route) {
            $routeReceipts = $groupedReceipts->get($route->id, collect());

            if ($routeReceipts->isNotEmpty()) {
                $table[] = [
                    'route_id'  => $route->id,
                    'route'     => $route->name,
                    'count'     => $routeReceipts->count(),
                    'cash'      => $routeReceipts->where('mode', 'Cash')->sum('amount'),
                    'bank'      => $routeReceipts->where('mode', 'Bank')->sum('amount'),
                    'incentive' => $routeReceipts->where('mode', 'Incentive')->sum('amount'),
                    'deposit'   => $routeReceipts->where('mode', 'Deposit')->sum('amount'),
                    'total'     => $routeReceipts->sum('amount'),
                    'status'    => $routeReceipts->contains('status', 'Pending') ? 'Pending' : 'Approved',
                ];
            }
        }
    
        // return response()->json([
        return view('transactions.receipts.make_receipts', [
            'table' => $table,
            'date'  => $date,
        ]);
    }

    public function showMakeReceipt(Request $request)
    {        
        $routeId = $request->query('id');
        $date = $request->query('date');        
        $routeName = MRoute::where('id', $routeId)->value('name');
        $data = $this->getReceiptsAndTotals($routeId,$date);

        // return response()->json([
        return view('transactions.receipts.make_receipt', [
            'routeId'         => $routeId,
            'routeName'       => $routeName,
            'modeTotals'      => $data['modeTotals'],
            'cumulativeTotal' => $data['cumulativeTotal'],
            'receipts'        => $data['receipts'],
            'date'            => $date,
        ]);
    }

    public function generateReceipts($routeId, Request $request)
    {        
        try {
            $date = $request->query('date');
            $nonDenominations = Receipt::whereDate('receipt_date', $date)
                ->where('route_id', $routeId)
                ->where('mode', 'Cash')
                ->whereNull('denomination')
                ->exists();

            if ($nonDenominations) {
                return response()->json([
                    'success' => false,
                    'message' => "Some receipts are missing denominations. Please complete them and try again.",
                ]);
            }

            Receipt::whereDate('receipt_date', $date)
                ->where('route_id', $routeId)
                ->where('status','Pending')
                ->update(['status' => 'Approved']);

            return response()->json([
                'success' => true,
                'message' => "Receipts generated successfully!",
            ]);
        } 
        catch (QueryException $exception) {
            return response()->json([
                'success' => false,
                'message' => "Failed to generate receipts.",
                'error'   => $exception->getMessage(),
            ], 500);
        }
    }

    private function generateReceiptNumber()
    {
        $lastRecord = Receipt::latest('id')->first();

        // Generate the new receipt number
        $newReceiptNumber = $lastRecord
            ? 'RC-' . sprintf('%03d', intval(substr($lastRecord->receipt_num, 3)) + 1)
            : 'RC-001';

        return $newReceiptNumber;
    }

    private function getReceiptsAndTotals($routeId ,$date)
    {
        $receipts = Receipt::select('id', 'receipt_num', 'route_id', 'customer_name', 'amount', 'mode', 'status')
            ->with('route:id,name')
            ->whereDate('receipt_date', $date)
            ->when($routeId <> 0, function ($query) use ($routeId) { return $query->where('route_id', $routeId); })
            ->get();

        // Define all possible modes
        $allModes = ['Cash', 'Bank', 'Incentive', 'Deposit'];

        // Calculate totals for each mode
        $rawTotals = Receipt::selectRaw('mode, SUM(amount) as total')
            ->whereDate('created_at', $date)
            ->when($routeId <> 0, function ($query) use ($routeId) {
                return $query->where('route_id', $routeId);
            })
            ->groupBy('mode')
            ->pluck('total', 'mode');

        // Ensure all modes are included with zero for missing ones
        $modeTotals = collect($allModes)->mapWithKeys(function ($mode) use ($rawTotals) {
            return [$mode => $rawTotals->get($mode, 0)];
        });

        $cumulativeTotal = $modeTotals->sum();

        return [
            'receipts' => $receipts,
            'modeTotals' => $modeTotals,
            'cumulativeTotal' => $cumulativeTotal,
        ];
    }

    private function getIncentiveData($customerId)
    {
        $incentiveData = IncentivesData::select('id','from_date','to_date','created_at','incentive_total')
            ->where('customer_id', $customerId)
            ->where('payment_status', 'Outstanding')
            ->where('status', 'Accepted')
            ->get();

        $incentives = [];
        if ($incentiveData->isNotEmpty()) {
            foreach ($incentiveData as $data) {
                $incentiveTotal = json_decode($data->incentive_total, true);
                $paidAmount = IncentivePayment::where('incentive_id', $data->id)
                    ->where('payment_status','Outstanding')
                    ->sum('amount');
                $incentives[] = [
                    'id'        => $data->id,
                    'duration'  => displayDate($data->from_date) . " to " . displayDate($data->to_date),
                    'date'      => getIndiaDate($data->created_at),
                    'amount'    => $incentiveTotal['total'],
                    'available' => $incentiveTotal['total'] - $paidAmount,
                ];
            }
        }

        return $incentives;
    }

    private function updateReceiptStatus($invoiceNum, $status)
    {
        $invoice = SalesInvoice::where('invoice_num', $invoiceNum)->first()
                    ?? TaxInvoice::where('invoice_num', $invoiceNum)->first()
                    ?? BulkMilkOrder::where('invoice_num', $invoiceNum)->first();

        if ($invoice) {
            $invoice->receipt_status = $status;
            $invoice->save();
        }
    }

    private function updateIncentiveData($request, $receipt)
    {
        IncentivePayout::create([
            'incentive_number' => $request->incentive_num,
            'customer_id'      => $request->cust_id,
            'document_date'    => date('Y-m-d'),
            'amount'           => $request->amount,
            'payout_mode'      => 'Receipt',
            'reference_number' => $receipt->receipt_num,
        ]);
    }

    private function fetchDateSetting()
    {
        $rawSettings = DateEntrySetting::select('tag', 'days_before', 'days_after')->get();

        $settings = $rawSettings->mapWithKeys(function ($item) {
            $today = Carbon::today();

            return [
                strtolower($item->tag) => [
                    'days_before' => $item->days_before,
                    'days_after'  => $item->days_after,
                    'min_date'    => $today->copy()->subDays($item->days_before)->format('Y-m-d'),
                    'max_date'    => $today->copy()->addDays($item->days_after)->format('Y-m-d'),
                ],
            ];
        });

        return $settings;
    }
}
