<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Contracts\View\View;
use App\Models\Places\MRoute;
use App\Models\Products\UOM;
use App\Models\Products\Product;
use App\Models\Products\ProductUnit;
use App\Models\Products\ViewProductUnit;
use App\Models\Profiles\Customer;
use App\Models\Masters\BankMaster;
// use App\Models\Masters\PriceMaster;
use App\Models\Masters\Pricing\PriceMaster;
use App\Models\Masters\TcsMaster;
use App\Models\Masters\TdsMaster;
use App\Models\Masters\RupeeNote;
use App\Models\Masters\CashRegister;
use App\Models\Masters\CashDenomination;
use App\Models\Transactions\Receipt;
use App\Models\Transactions\Expense;
use App\Models\Transactions\BatchDenomination;
use App\Models\Transactions\ExpenseDenomination;
use App\Models\Transactions\IncentivePayout;
use App\Models\Transactions\BankPayment;
use App\Models\Transport\DieselBillPayment;
use App\Models\Orders\Order;
use App\Enums\PaymentType;
use Carbon\Carbon;

class ExplorerController extends Controller
{
    public function priceExplorer(Request $request)
    {              
        $productId = $request->input('productId', "0");
        $products = Product::with([
            'prod_group:id,name', 
            'conversion' => function ($query) {
                $query->orderByDesc('prim_unit'); 
            }
        ])
        ->select('id', 'name', 'group_id') 
        ->when($productId != 0,function($query) use($productId) {
            return $query->where('id',$productId);
        } )
        ->get();
        if ($request->ajax()) {  // Check if it's an Ajax request
            return response()->json(['products' => $products]);
        }
        return view('explorer.product.price_explorer', [
           'products' => $products
        ]);
    }


    public function taxExplorer(Request $request)
    {
        $tax_type = $request->tax_type ?? "All";
    	$products = Product::select('name','group_id','hsn_code','tax_type','gst','sgst','cgst','igst')
                  ->with('prod_group:id,name');
        if($tax_type != "All")
        {
            $products = $products->where('tax_type',$tax_type);
        }
        $products =$products->get();
        // return response()->json([
        return view('explorer.product.tax_explorer', [
            'products' => $products,
            'tax_type' => $tax_type,
        ]);
    }

    public function gstExplorer(Request $request)
    {
        $gst_type = $request->gst_type ?? "All";
        $customers = Customer::select('customer_name', 'route_id', 'gst_type', 'gst_number')
                ->with('route:id,name');
        if ($gst_type !== "All") {
            $customers = $customers->where('gst_type', $gst_type);
        }
        $customers = $customers->get();
        // return response()->json([
        return view('explorer.customer.gst_explorer', [
            'customers' => $customers,
            'gst_type' => $gst_type,
        ]);
    }

    public function tcsExplorer(Request $request)
    {
        $tcs_status = $request->tcs_status ?? "All"; 
        $tcsMaster = TcsMaster::where('effect_date','<=',today())->orderByDesc('effect_date')->first();
    	$customers = Customer::select('customer_name','route_id','tcs_status','pan_number')
                  ->with('route:id,name');
        if($tcs_status != "All")
        {
            $customers = $customers->where('tcs_status',$tcs_status);
        }
        $customers = $customers->get();
        // return response()->json([
        return view('explorer.customer.tcs_explorer', [
            'customers' => $customers,
            'tcsMaster' => $tcsMaster,
            'tcs_status'=> $tcs_status,
        ]);
    }

    public function tdsExplorer(Request $request)
    {
        $tds_status = $request->tds_status ?? "All"; 
        $tdsMaster = TdsMaster::where('effect_date','<=',today())->orderByDesc('effect_date')->first();
    	$customers = Customer::select('customer_name','route_id','tds_status','pan_number')
                  ->with('route:id,name');
        if($tds_status != "All")
        {
            $customers = $customers->where('tds_status',$tds_status);
        }
        $customers = $customers->get();
        // return response()->json([
        return view('explorer.customer.tds_explorer', [
            'customers' => $customers,
            'tdsMaster' => $tdsMaster,
            'tds_status'=> $tds_status,
        ]);
    }

    public function paymentExplorer(Request $request)
    {
        $payment_mode = $request->payment_mode ?? "All"; 
    	$customers = Customer::select('customer_name','route_id','payment_mode')
                  ->with('route:id,name');
        if($payment_mode != "All")
        {
            $customers = $customers->where('payment_mode',$payment_mode);
        }
        $customers = $customers->get();
        // return response()->json([
        return view('explorer.customer.payment_explorer', [
            'customers' => $customers,
            'payment_mode' => $payment_mode,
        ]);
    }

    public function incentiveExplorer(Request $request)
    {
        $incentive_mode = $request->incentive_mode ?? "All"; 
    	$customers = Customer::select('customer_name','route_id','incentive_mode')
                  ->with('route:id,name');
        if($incentive_mode != "All")
        {
           $customers = $customers->where('incentive_mode',$incentive_mode);
        }
        $customers = $customers->get();
        // return response()->json([
        return view('explorer.customer.incentive_explorer', [
            'customers' => $customers,
            'incentive_mode' => $incentive_mode,
        ]);
    }

    public function priceListExplorer(request $request)
    {
        $customerId = $request->customerId ?? 0;
        $priceMasters = PriceMaster::select('txn_id', 'txn_date', 'effect_date', 'narration', 'customer_ids')
            ->whereRaw('JSON_CONTAINS(customer_ids, ?)', [json_encode((string)$customerId)]) // Encode as a string
            ->orderBy('effect_date','desc')
            ->get();        
        $row = "null";
        $today = Carbon::today();

        // Check if records exist
        if ($priceMasters->isNotEmpty()) {
            foreach ($priceMasters as $index => $master) {
                if (Carbon::parse($master->effect_date)->lessThanOrEqualTo($today)) {
                    $row = $index + 1; // 1-based index
                    break;
                }
            }
        }
        // StandardPrice
        $priceList = ProductUnit::select('id','product_id','unit_id','price')
                                ->with('product:id,name')
                                ->where('prim_unit',1)
                                ->orderBy('product_id')
                                ->get();
        $units = UOM::select('id','display_name')->get()->pluck('display_name','id');        
        foreach($priceList as $priceRecord) {
            $priceRecord->unit = $units[$priceRecord->unit_id];
            $priceRecord->other_units = ProductUnit::select('unit_id','conversion')
                                                    ->where('product_id',$priceRecord->product_id)
                                                    ->where('prim_unit',0)
                                                    ->get();
        }
        //PriceMasterList
        $priceMaster = PriceMaster::select('id','txn_id','txn_date','customer_ids','price_list')
                                ->where('status','Active')
                                ->where('effect_date','<=',date('Y-m-d'))
                                ->whereJsonContains('customer_ids', strval($customerId))
                                ->orderByDesc('id')
                                ->get();
        $priceMasterData = [];
        if(count($priceMaster)>0) {
            $priceMasterList = $priceMaster[0]->price_list; // Retrieve the JSON data from the database
            $priceMasterList = json_decode($priceMasterList, true); // Decode the JSON data into an associative array
            $priceMasterData = [
                'txn_id' => $priceMaster[0]->txn_id,
                'priceMasterlist' => $priceMasterList
            ];
        }

        $priceListAll = $priceList->toArray();
        if($priceMasterData) {
            // Update the priceList with master prices
            foreach ($priceListAll as &$item) {               
                $productId = strval($item['product_id']);
                if (array_key_exists($productId, $priceMasterData['priceMasterlist'])) {
                    $item['price'] = floatval($priceMasterData['priceMasterlist'][$productId]);
                    $item['txn_id'] = $priceMasterData['txn_id'];
                }
            }
        }       
        // return response()->json([
        return view('explorer.price_master.price_list',[            
            "priceMasters" => $priceMasters,
            'customerId'   => $customerId,
            'row' => $row,
            'priceListAll'=> $priceListAll            
        ]);

    }

    public function customerPriceExplorer(Request $request)
    {
        $priceMaster = $request->priceMaster ?? "All";
        $customers = Customer::pluck('customer_name', 'id');
        $customerPrice = [];
        foreach ($customers as $customerId => $customerName) {
            $masters = PriceMaster::select('id','document_number', 'customer_ids')
                            ->whereJsonContains('customer_ids', $customerId) 
                            ->orderBy('created_at', 'desc')
                            ->get();
            $customerData = [
                "id" => $customerId,
                'name' => $customerName,
                'document_number' => [],                
            ];            
            if ($masters->isNotEmpty()) {
                foreach ($masters as $master) {
                    $customerData['document_number'][$master->document_number] = $master->id;
                }
            }
            if ($priceMaster == "Nill" && !empty($customerData['document_number'])) {                
                continue;
            }
    
            if ($priceMaster == "Exist" && empty($customerData['document_number'])) {                
                continue;
            }
            $customerPrice[] = $customerData;
        }
        // return response()->json([            
        return view ('explorer.price_master.customer_price',[
            "priceMaster"   => $priceMaster,
            'customers'     => $customerPrice
        ]);
    }

    public function priceVariantExplorer(Request $request)
    {        
        $productId = $request->productId ?? 1; 
        $productName = $request->productName ?? " ";
        
        $products = Product::select('id','short_name','tax_type','gst')
                            ->where('visible_invoice','1')
                            ->where('status','Active')
                            ->orderBy('display_index')
                            ->get();  
         //standardPrice Customer     
        $standard = ProductUnit::select('id','product_id','unit_id','price')                               
                                ->where('prim_unit',1)
                                ->where('product_id',$productId)                                
                                ->first();  
        $prices=[];
        $prices[] = [
            'txn_id'    =>["Standard"],
            'price'     =>$standard->price,
            'customers' => []
        ];
       
        $cust = Customer::pluck('customer_name', 'id');         
        //PriceMasters
        $productData = PriceMaster::select('id', 'txn_id', 'txn_date', 'narration', 'customer_ids', 'price_list')
            ->whereRaw("JSON_EXTRACT(price_list, '$.$productId') IS NOT NULL")
            ->get();   
             
        $allCustomers = []; 
        foreach ($productData as $product) {
            $customer = json_decode($product['customer_ids'], true); 
            $priceList = json_decode($product['price_list'], true);     
            $allCustomers = array_merge($allCustomers, $customer); 
            if (isset($priceList[$productId])) {
                $priceC = $priceList[$productId]; 
            }
           
            $found = false;
            foreach ($prices as &$priceData) {               
                if (!isset($priceData['txn_id']) || !is_array($priceData['txn_id'])) {
                    $priceData['txn_id'] = []; // Initialize as an empty array if it's not an array
                }
                if (!isset($priceData['customers'])) {
                    $priceData['customers'] = [];
                }            
                if ($priceData['price'] == $priceC) {
                    // Append txn_id to the array
                    $priceData['txn_id'][] = $product->txn_id;
                    $priceData['customers'] = array_merge($priceData['customers'], $customer);            
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                $prices[] = [
                    'txn_id'    => [$product->txn_id], 
                    'price'     => $priceC,
                    'customers' => $customer, 
                ];
            }
        }
        $allCustomers = array_unique($allCustomers);
        foreach($cust as $custId => $name)
        {
            if(in_array($custId,$allCustomers))
            {
                continue;
            }
            $prices[0]['customers'][] = $custId;
        }
        usort($prices, function($a, $b) {
            return $b['price'] <=> $a['price']; // Compare price in descending order
        });     

        // $productGroup = [];

        // foreach ($prices as $price) {
        //     if (!isset($productGroup[$price['price']])) {
        //         $productGroup[$price['price']] = [];
        //     }        
        //     // Ensure "Standard" comes first
        //     if ($price['txn_id'] === "Standard") {
        //         array_unshift($productGroup[$price['price']], $price); 
        //     } else {
        //         $productGroup[$price['price']][] = $price;
        //     }
        // }
        // return response()->json([              
        return view ('explorer.price_master.price_variant',[
            "productGroup"   => $prices,   
            'products'    => $products,   
            'productId'=>$productId,
            'productName'=> $productName,
            'customerName'=> $cust     
        ]);
    }

/* Cash Register - Section Start */
    public function cashRegister()
    {
        $notes = RupeeNote::orderBy('display_index')->pluck('note_value');
        return view('explorer.cash_register', ['notes' => $notes]);
    }

    public function getCashRegister(Request $request)
    {
        // return $this->createCashRegister('2025-04-01');

        $date = $request->date;        
        $register = CashRegister::where('record_date', $date)->first();

        if(!$register) {
            $yesterday = getPreviousDate($date);
            $register = CashRegister::where('record_date', $yesterday)->first();

            if($register) {
                $cashRegister = $this->generateCashRegister($date);                
            }
            else {
                $lastDate = CashRegister::max('record_date');
                $dates = getDatesForLoop(getNextDate($lastDate), $yesterday);
                foreach ($dates as $dateObject) {
                    $this->createCashRegister($dateObject->format('Y-m-d'));
                }
                $cashRegister = $this->generateCashRegister($date);
            }
        }
        else {
            $cashRegister = [
                'date'     => $register->record_date,
                'opening'  => [ 'amount' => $register->opening_amount, 'denomination' => json_decode($register->opening_denomination) ],
                'receipts' => [ 'amount' => $register->receipt_amount, 'denomination' => json_decode($register->receipt_denomination) ],
                'expenses' => [ 'amount' => $register->expense_amount, 'denomination' => json_decode($register->expense_denomination) ],
                'closing'  => [ 'amount' => $register->closing_amount, 'denomination' => json_decode($register->closing_denomination) ],
            ];
        }

        return response()->json([
            'date'     => $cashRegister['date'],
            'opening'  => $cashRegister['opening'],
            'receipts' => $cashRegister['receipts'],
            'expenses' => $cashRegister['expenses'],
            'closing'  => $cashRegister['closing'],
        ]);
    }

    public function regenerateCashRegister()
    {
        try {
            // Delete Existing Records Except First Record
            CashRegister::where('id','>',1)->delete();
            DB::statement('ALTER TABLE cash_register AUTO_INCREMENT = 2');

            // Recreate Records
            $dates = getDatesForLoop('2025-04-02', getYesterday());            
            foreach ($dates as $dateObject) {                
                $this->createCashRegister($dateObject->format('Y-m-d'));
            }

            return response()->json([
                'success' => true,
                'message' => "Records Re-generated Successfully!",
            ]);
        } 
        catch (QueryException $exception) {
            return response()->json([
                'success' => false,
                'message' => "Failed to generate cash receipt records.",
                'error'   => $exception->getMessage(),
            ], 500);
        }
    }

    private function generateCashRegister($date) {
        $opening  = $this->getOpeningAmountWithDenomination($date);
        $receipts = $this->getReceiptAmountWithDenomination($date);
        $expenses = $this->getExpenseAmountWithDenomination($date);
        $closing  = $this->calculateClosingAmountWithDenomination($opening, $receipts, $expenses);

        return [
            'date'     => $date, 
            'opening'  => $opening,
            'receipts' => $receipts,
            'expenses' => $expenses,
            'closing'  => $closing,
        ];
    }

    private function createCashRegister($date)
    {
        try {            
            $opening  = $this->getOpeningAmountWithDenomination($date);            
            $receipts = $this->getReceiptAmountWithDenomination($date);                        
            $expenses = $this->getExpenseAmountWithDenomination($date);            
            $closing  = $this->calculateClosingAmountWithDenomination($opening, $receipts, $expenses);

            $data = [
                'record_date' => $date,
                'opening_amount' => $opening['amount'],
                'receipt_amount' => $receipts['amount'],
                'expense_amount' => $expenses['amount'],
                'closing_amount' => $closing['amount'],
                'opening_denomination' => json_encode($opening['denomination']),
                'receipt_denomination' => json_encode($receipts['denomination']),
                'expense_denomination' => json_encode($expenses['denomination']),
                'closing_denomination' => json_encode($closing['denomination']),
            ];

            $record = CashRegister::create($data);

            return response()->json(['success' => true, 'id' => $record->id]);
        }
        catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    private function getOpeningAmountWithDenomination($date) {
        if($date == '2025-04-01') {
            $register = CashDenomination::first(['amount','denomination']);
            $amount = $register->amount;
            $denomination = json_decode($register->denomination);
        }
        else {
            $register = CashRegister::whereDate('record_date', getPreviousDate($date))->first(['closing_amount','closing_denomination']);
            $amount = $register->closing_amount;
            $denomination = json_decode($register->closing_denomination);
        }

        return [
            'amount' => $amount,
            'denomination' => $denomination,
        ];
    }

    private function getReceiptAmountWithDenomination($date) {
        $receipts = Receipt::select('receipt_num','amount','denomination')
            ->where('receipt_date',$date)
            ->where('mode','Cash')
            ->where('status','Approved')
            ->get();
        
        $denominations = [];
        foreach($receipts as &$receipt) {
            if(!is_numeric($receipt->denomination))
                $denominations[] = json_decode($receipt->denomination);
        }

        // Batch Denominations
        $denominationIds = $receipts->filter(function ($receipt) {
            return is_int($receipt['denomination']);
        })->pluck('denomination')->unique();

        $batchDenominations = BatchDenomination::whereIn('id', $denominationIds)->get(['denomination']);
        foreach ($batchDenominations as $batchDenomination) {
            $denominations[] = json_decode($batchDenomination->denomination);
        }

        return [
            'amount' => $receipts->sum('amount'),
            'denomination' => $this->getCumulativeDenomination($denominations)            
        ];
    }

    private function getExpenseAmountWithDenomination($date) {
        $expenses = Expense::select('expense_amount','denomination')
            ->where('expense_date',$date)
            ->where('expense_status','Accepted')
            ->get();

        $denominations = [];
        $denominationIds = $expenses->pluck('denomination');
        $expenseDenominations = ExpenseDenomination::whereIn('id', $denominationIds)->get(['denomination']);
        foreach ($expenseDenominations as $expenseDenomination) {
            $denominations[] = json_decode($expenseDenomination->denomination);
        }

        return [
            'amount' => $expenses->sum('expense_amount'),
            'denomination' => $this->getCumulativeDenomination($denominations)
        ];
    }

    private function calculateClosingAmountWithDenomination($opening, $receipt, $expense)
    {
        $closingAmount = $opening['amount'] + $receipt['amount'] - $expense['amount'];

        $openingDenom = $opening['denomination'];
        $receiptDenom = $receipt['denomination'];
        $expenseDenom = $expense['denomination'];

        // Merge opening and receipts denominations
        $combined = [];
        foreach ([$openingDenom, $receiptDenom] as $source) {
            foreach ($source as $note => $count) {
                $combined[$note] = ($combined[$note] ?? 0) + $count;
            }
        }

        // Subtract expenses
        foreach ($expenseDenom as $note => $count) {
            $combined[$note] = ($combined[$note] ?? 0) - $count;
        }

        // Remove zero or negative denominations if needed
        $closingDenom = array_filter($combined, fn($val) => $val > 0);

        return [
            'amount' => $closingAmount,
            'denomination' => $closingDenom,
        ];
    }

    private function getCumulativeDenomination($denominations) {
        $flatDenominations = [];
        foreach ($denominations as $group) {
            foreach ($group as $denom) {
                $a[] = $denom;
                foreach ($denom as $key => $value) {
                    if (!isset($flatDenominations[$key]))
                        $flatDenominations[$key] = 0;
                    $flatDenominations[$key] += $value;
                }
            }
        }
        return $flatDenominations;
    }

    public function updateCashRegisters($fromDate) {
        $dates = getDatesForLoop($fromDate, getYesterday());
        foreach ($dates as $dateObject) {
            $date = $dateObject->format('Y-m-d');
            $register = CashRegister::where('record_date', $date)->first();

            if(!$register) {
                $lastDate = CashRegister::max('record_date');
                $missingDates = getDatesForLoop(getNextDate($lastDate), getYesterday());
                foreach ($missingDates as $missingDate) {
                    $this->createCashRegister($missingDate->format('Y-m-d'));
                }
                $register = CashRegister::where('record_date', $date)->first(); // Re-fetch the register
            }

            $opening  = [ 'amount' => $register->opening_amount, 'denomination' => json_decode($register->opening_denomination) ];
            $receipts = [ 'amount' => $register->receipt_amount, 'denomination' => json_decode($register->receipt_denomination) ];
            $expenses = $this->getExpenseAmountWithDenomination($date);
            $closing  = $this->calculateClosingAmountWithDenomination($opening, $receipts, $expenses);

            $register->update([
                'expense_amount' => $expenses['amount'],
                'closing_amount' => $closing['amount'],
                'expense_denomination' => json_encode($expenses['denomination']),
                'closing_denomination' => json_encode($closing['denomination']),
            ]);
        }
    }
/* Cash Register - Section End */

/* Bank Payment - Section Start */
    public function indexBankPayment(Request $request): View
    {
        // Determine date range
        if ($request->filled(['from_date', 'to_date'])) {
            [$fromDate, $toDate] = [$request->from_date, $request->to_date];
        } 
        else {
            $latestDate = BankPayment::latest('payment_date')->value('payment_date') ?? now();
            $fromDate = $toDate = Carbon::parse($latestDate)->toDateString();
        }

        // Build query with optional filters
        $records = BankPayment::select('id', 'document_number', 'payment_date', 'payment_type', 'bank_name', 'total_amount')
            ->when($fromDate && $toDate, fn($q) => $q->whereBetween('payment_date', [$fromDate, $toDate]))
            ->when($request->filled('type'), fn($q) => $q->where('payment_type', $request->type))
            ->when($request->filled('bank'), fn($q) => $q->where('bank_id', $request->bank))
            ->get();

        $banks = BankMaster::select('id', 'display_name')->get();

        return view('explorer.payments.index', [
            'dates'   => ['from' => $fromDate, 'to' => $toDate],
            'type'    => $request->type,
            'bank'    => $request->bank,
            'records' => $records,
            'banks'   => $banks,
        ]);
    }

    public function showBankPayment(Request $request): View
    {
        $documentNumber = $request->document;
        $documentList   = $request->document_list;

        $document = BankPayment::select('id', 'document_number', 'payment_date', 'payment_type', 'bank_name', 'total_amount')
            ->where('document_number', $documentNumber)
            ->firstOrFail();

        $document->total_amount = 'Rs. ' . formatIndianNumber($document->total_amount);

        $records = match ($document->payment_type) {
            PaymentType::INCENTIVE   => $this->generateIncentivePaymentRecords($documentNumber),
            PaymentType::DIESEL_BILL => $this->generateDieselBillPaymentRecords($documentNumber),
            default                  => [],
        };

        return view('explorer.payments.show', [
            'document'      => $document,
            'records'       => $records,
            'document_list' => $documentList,
        ]);
    }

    private function generateIncentivePaymentRecords(string $documentNumber): array
    {
        $payments = IncentivePayout::select('id','incentive_number','customer_id','amount')
            ->with(['incentiveNumber:incentive_number,from_date,to_date'])
            ->with(['customer:id,customer_name,acc_number,bank_name'])
            ->where('reference_number', $documentNumber)
            ->get();

        $records = [];
        foreach ($payments as $payment) {
            $records[] = [
                'document_number'  => $payment->incentive_number,
                'period'           => formatDateRangeAsDMY($payment->incentiveNumber->from_date, $payment->incentiveNumber->to_date),
                'name'             => $payment->customer->customer_name,
                'amount'           => formatIndianNumber($payment->amount),
                'account_number'   => $payment->customer->acc_number,
                'bank_name'        => $payment->customer->bank_name,
            ];
        }

        return $records;
    }

    private function generateDieselBillPaymentRecords(string $documentNumber): array
    {
        $payments = DieselBillPayment::select('id','statement_id','amount')
            ->with('statement:id,document_number,from_date,to_date,bunk_id,bunk_name')
            ->where('reference_number', $documentNumber)
            ->get();

        $records = [];
        foreach ($payments as $payment) {
            $statement = $payment->statement;
            $records[] = [
                'document_number'  => $statement->document_number,
                'period'           => $statement->period,
                'name'             => $statement->bunk_name,
                'amount'           => formatIndianNumber($payment->amount),
                'account_number'   => $statement->bunk->account_number,
                'bank_name'        => $statement->bunk->bank->name,
            ];
        }

        return $records;
    }
/* Bank Payment - Section End */
}
