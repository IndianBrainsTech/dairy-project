<?php

namespace App\Http\Controllers;

use App\Models\Masters\ExpenseType;
use Illuminate\Http\Request;
use App\Models\Products\ViewProductUnit;
use App\Models\Products\Product;
use App\Models\Masters\TcsMaster;
use App\Models\Masters\TdsMaster;
use App\Models\Products\UOM;
use App\Models\Places\MRoute;
use App\Models\Orders\Order;
use App\Models\Products\ProductUnit;
use App\Models\Production\StockEntry;
use App\Models\Production\StockEntryHistory;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use carbon\Carbon;
use App\Models\Orders\OrderItem;
use App\Models\Orders\SalesInvoice;
use App\Models\Orders\TaxInvoice;
use App\Models\Orders\SalesInvoiceItem;
use App\Models\Orders\TaxInvoiceItem;
use App\Models\Orders\SalesReturn;
use App\Models\Transactions\Receipt;
use Illuminate\Http\JsonResponse;
use App\Models\Masters\RupeeNote;
use App\Models\Transactions\OpeningBalance;
use App\Models\Transactions\Expense;
use App\Models\Transactions\ExpenseDenomination;
use App\Models\Masters\IncentiveMaster;
use App\Models\Transactions\IncentivesData;
use App\Models\Profiles\Customer;
use App\Http\Traits\CustomerUtility;
use App\Http\Controllers\ExplorerController;

class WorkController extends Controller
{
    use CustomerUtility;

    protected $explorerController;

    public function __construct(ExplorerController $controller)
    {
        $this->explorerController = $controller;
    }

    public function stockEntry()
    {
        $products = Product::select('id','short_name')
                            ->where('visible_invoice','1')
                            ->where('status','Active')
                            ->orderBy('display_index')
                            ->get();
        
        $units = UOM::select('id','unit_name','display_name','hot_key')->get();  
             

        // return response()->json([
        return view('production.stock_entry', [
            'products'    => $products,
            'units'       => $units
        ]);
    }    

    public function stockEntryStore(Request $request)
    {
        try {    
            foreach ($request->stockDatas['stockData'] as $index => $data) {               
                $group_id = Product::where("id", $data['productId'])->value('group_id');                
                $lastRecord = StockEntry::where('product_id', $data['productId'])
                                        ->where('batch_no',$data['batch'])
                                        ->orderBy('created_at', 'desc')
                                        ->first();
                
                $primary = $this->convertToPrimary($data['productId'], $data['qty'], $data['unit']);
                $currentPrimaryUnitQty = (float) $primary['prim_qty'];
               
                if ($lastRecord) {
                    $totalPrimaryUnitQty = $lastRecord->total_stock_qty + $currentPrimaryUnitQty;
                } else {
                    $totalPrimaryUnitQty = $currentPrimaryUnitQty;
                }      
                if($index === 0){           
                    $lastTxnId = StockEntry::select('txn_id')
                                ->orderBy('txn_id', 'desc') 
                                ->first();   
                    if ($lastTxnId) {
                        $lastNumber = (int) substr($lastTxnId->txn_id, 3);
                        $newUniqueNumber = $lastNumber + 1;  
                        
                    } else {
                        $newUniqueNumber = 1; 
                    }  
                    $newUniqueId = 'ST-' . str_pad($newUniqueNumber, 3, '0', STR_PAD_LEFT);         
                }   
                $stock = new StockEntry();
                $stock->txn_id            = $newUniqueId;
                $stock->product_name      = $data['productName'];
                $stock->product_id        = $data['productId'];
                $stock->batch_no          = $data['batch'];
                $stock->group_id          = $group_id;  // Correctly assign the group_id
                $stock->entry_qty         = $data['qty'];
                $stock->entry_unit        = UOM::where('id', $data['unit'])->value('display_name');
                $stock->primary_unit_qty  = $currentPrimaryUnitQty;
                $stock->primary_unit      = $primary['prim_unit'];                
                $stock->total_stock_qty   = $totalPrimaryUnitQty;
                $stock->save();
            }
            return response()->json([
                'message' => 'Stock entry saved successfully!'
            ]);

        } catch (\Exception $e) {
            // Handle any errors
            return response()->json(['error' => 'Something went wrong: ' . $e->getMessage()]);
        }
    }

    public function stockListview(request $request)
    {   
        $fromDate = $toDate = $request->input('date', date('Y-m-d'));     
        $stocks = StockEntry::select('product_id', 'txn_id', 'created_at')
                    ->whereBetween('created_at', [
                        $fromDate . " 00:00:00", 
                        $toDate . " 23:59:59"
                    ])                                             
                    ->get()
                    ->groupBy('txn_id')
                    ->map(function ($group) {
                        return $group->first(); 
                    });
        // return response()->json([
        return view('production.stock_list_view', [
            'date' => $fromDate,
            'stocks' => $stocks  // Pass the stocks data to the view
        ]);
    }

    public function stockShow(request $request)
    {
        $txn_id = $request->stock_num;
        $entryDate = $request->entryDate;
        $stockEntries   = StockEntry::select('txn_id','product_name','batch_no','entry_qty','entry_unit')
                          ->where('txn_id',$txn_id)
                          ->whereBetween('created_at', [
                            $entryDate . ' 00:00:00',  // Start of the day
                            $entryDate . ' 23:59:59'   // End of the day
                          ])->get();
        $existingTxnIds  = StockEntry::select('txn_id')                         
                          ->whereBetween('created_at', [
                            $entryDate . ' 00:00:00',  // Start of the day
                            $entryDate . ' 23:59:59'   // End of the day
                          ])->distinct()->get();  
        $txnIds = $existingTxnIds->pluck('txn_id');                                
        // return response()->json([
        return view('production.view_stock',[
            "stockEntries"  => $stockEntries,
            'entryDate'     => $entryDate,
            "txn_id"        => $txn_id,
            'existingTxnIds'=> $txnIds
        ]);        
    }
    public function entryEdit(Request $request)
    {
        $txn_id         = $request->code;       
        $oldEntry   = StockEntry::where('txn_id',$txn_id)->get();                
        $products = Product::select('id','short_name')
                            ->where('visible_invoice','1')
                            ->where('status','Active')
                            ->orderBy('display_index')
                            ->get();
        
        $units = UOM::select('id','unit_name','display_name','hot_key')->get();  
        // return response()->json([
        return view('production.stock_entry', [
            'oldEntry'    => $oldEntry,   
            'products'    => $products,
            'units'       => $units,   
            'txn_id'      => $txn_id  
        ]);


    }
    public function stockUpdate(Request $request)
    {
        try {  
            stockEntry::where('txn_id',$request->txn_id)->delete();
            $this->stockEntryStore($request);    
            return response()->json([
                'message' => 'Stock entry Updated successfully!'
            ]);
       }
        catch(QueryException $exception) {            
            return back()->with('error', $exception->getMessage())->withInput();
        }
    }

    public function CurrentStockShow()
    {    
        $stock = StockEntry::select('stock_entries.product_name', 'stock_entries.batch_no', 'stock_entries.primary_unit', 
                                    'stock_entries.total_stock_qty', 'stock_entries.created_at', 'stock_entries.product_id', 
                                    'products.display_index') 
                            ->join('products', 'stock_entries.product_id', '=', 'products.id') 
                            ->orderBy('products.display_index', 'asc') 
                            ->orderBy('stock_entries.created_at', 'desc') 
                            ->get();
        
        $currentStock = $stock->groupBy('product_name')->map(function ($productGroup) {
            return $productGroup->groupBy('batch_no')->map(function ($batchGroup) {               
                return $batchGroup->first();
            });
        });

        // Return the data to the view
        return view('production.current_stock', [
            'currentStock' => $currentStock
        ]);
    }
    
    public function ClosingStockShow(request $request)
    {
        $date = $request->date?? date('Y-m-d');       
        // Products
        $products = Product::select('id', 'name', 'short_name', 'display_index')                    
                    ->orderBy('display_index')
                    ->get();

        // Production
        $production = StockEntry::select('id', 'txn_id', 'product_id', 'primary_unit_qty', 'primary_unit')
                    ->whereBetween('created_at', [$date . ' 00:00:00', $date . ' 23:59:59'])
                    ->get();
        $accumulatedData = [];
        foreach ($production as $entry) {
        $productId = $entry['product_id'];        
        if (isset($accumulatedData[$productId])) {
            $accumulatedData[$productId]['primary_unit_qty'] += $entry['primary_unit_qty'];
        } else {            
            $accumulatedData[$productId] = [
                'id' => $entry['id'],
                'txn_id' => $entry['txn_id'],
                'product_id' => $entry['product_id'],
                'primary_unit_qty' => $entry['primary_unit_qty'],
                'primary_unit' => $entry['primary_unit'],
            ];
        }
        }
        // Sales Orders (Not Cancelled)
        $salesItem = $this->getSalesItems($date, ['Not Generated', 'Generated']);
        $salesTotal = $this->calculateSalesTotal($salesItem);
        // Sales Returns
        $returnItems = SalesReturn::select('txn_date', 'invoice_num', 'return_data')
                    ->whereBetween('created_at', [$date . ' 00:00:00', $date . ' 23:59:59'])
                    ->get();
        $returnData = $this->getReturnData($returnItems);

        // Subtract returns from sales
        foreach ($returnData as $productId => $qtyReturned) {
            if (isset($salesTotal[$productId])) {
                $salesTotal[$productId] -= (float) $qtyReturned;
            }
        }

        // Cancelled Orders and Sales
        $returnItem = $this->getCancelledSalesItems($date);
        $returnTotal = $this->calculateSalesTotal($returnItem);
        $returnDataA = $this->getReturnData($returnItems);

        // Add returned quantities for cancelled sales
        foreach ($returnDataA as $productId => $qtyReturned) {
            if (isset($returnTotal[$productId])) {
                $returnTotal[$productId] += (float) $qtyReturned;
            }
        }

        // Closing Stock
        $closingStock = StockEntryHistory::select('stock_entry_id', 'product_id', 'total_stock_qty','primary_unit', 'created_at')
                        ->whereBetween('created_at', [$date . ' 00:00:00', $date . ' 23:59:59'])
                        ->orderBy('created_at', 'desc')
                        ->get()
                        ->groupBy('product_id')
                        ->map(function ($group) {
                            return $group->first();
                        });
        $closingS = StockEntry::select('id','product_id','total_stock_qty','primary_unit')
                    ->whereBetween('created_at', [$date . ' 00:00:00', $date . ' 23:59:59'])
                    ->orderBy('created_at', 'desc')
                    ->get()
                    ->groupBy('product_id')
                    ->map(function ($group) {
                        return $group->first();
                    });
        
        $closingStockCollection = collect($closingStock);
        $closingSCollection = collect($closingS);        
        $unmatchedProducts = $closingSCollection->filter(function ($item, $key) use ($closingStockCollection) {
            return !$closingStockCollection->has($key);
        });        
        $closingStock = $closingStockCollection->merge($unmatchedProducts)->toArray();  
        // $closingStock = StockEntry::select('id', 'txn_id', 'product_id', 'primary_unit', 'total_stock_qty')
        //                 ->orderBy('updated_at', 'desc')
        //                 ->get()
        //                 ->groupBy('product_id')
        //                 ->map(function ($group) {
        //                     return $group->first();
        //                 });
        //Opening Stock
        $openingStock = StockEntryHistory::select('stock_entry_id', 'product_id', 'total_stock_qty','primary_unit', 'created_at')
                        ->where('created_at', '<', $date . ' 00:00:00')
                        ->orderBy('created_at', 'desc')
                        ->get()
                        ->groupBy('product_id')
                        ->map(function ($group) {
                            return $group->first();
                        });
        $openingS = StockEntry::select('id','product_id','total_stock_qty','primary_unit')
                    ->where('created_at', '<', $date . ' 00:00:00')
                    ->orderBy('created_at', 'desc')
                    ->get()
                    ->groupBy('product_id')
                    ->map(function ($group) {
                        return $group->first();
                    });
        
        $openingStockCollection = collect($openingStock);
        $openingSCollection = collect($openingS);        
        $unmatchedProducts = $openingSCollection->filter(function ($item, $key) use ($openingStockCollection) {
            return !$openingStockCollection->has($key);
        });        
        $mergedOpening = $openingStockCollection->merge($unmatchedProducts)->toArray();  
        $stockOC = []; 
        foreach ($products as $product) {            
            $openingStock = " ";
            $productionStock = " ";
            $salesStock = " ";
            $returnStock = " ";
            $closingStockQty = " ";

            foreach ($mergedOpening as $opening) {
                if ($product->id == $opening['product_id']) {
                    $openingStock = $opening['total_stock_qty'] . " " . $opening['primary_unit'];
                    break;
                }
            }

            foreach ($accumulatedData as $pro) {
                if ($product->id == $pro['product_id']) {
                    $productionStock = $pro['primary_unit_qty'] . " " . $pro['primary_unit'];
                    break;
                }
            }

            foreach ($salesTotal as $productId => $salest) {
                if ($product->id == $productId) {
                    $salesStock = $salest;
                    break;
                }
            }

            foreach ($returnTotal as $productId => $return) {
                if ($product->id == $productId) {
                    $returnStock = $return;
                    break;
                }
            }

            foreach ($closingStock as $cstock) {
                if ($product->id == $cstock['product_id']) {
                    $closingStockQty = $cstock['total_stock_qty'] . " " . $cstock['primary_unit'];
                    break;
                }
            }

            $stockOC[] = [
                'product'   => $product->name,
                'opening'   => $openingStock,
                'production' => $productionStock,
                'sales'     => $salesStock,
                'return'    => $returnStock,
                'closing'   => $closingStockQty,
            ];
        }

        // return response()->json([
        return view('production.closing_stock',[        
            // 'products' => $products,
            // 'production' => $production,
            // 'salesTotal' => $salesTotal,
            // 'ReturnTotal' => $returnTotal,
            // 'closingStock' => $closingStock,            
            // 'mergeOpening'=>$mergedOpening,
            'stockOC' => $stockOC,
            'date'  =>$date
        ]);
    }

    // Function to get sales items based on order status
    private function getSalesItems($date, $statuses)
    {
        $salesOrders = Order::select('order_num', 'invoice_status')
                            ->whereIn('invoice_status', $statuses)
                            ->whereBetween('created_at', [$date . ' 00:00:00', $date . ' 23:59:59'])
                            ->get();

        $salesItem = [];

        foreach ($salesOrders as $order) {
            if ($order->invoice_status == 'Not Generated') {
                $orderInvoice = OrderItem::select('order_num', 'product_id', 'qty', 'unit_id')
                                        ->where('order_num', $order->order_num)
                                        ->get();
                foreach ($orderInvoice as $invoice) {
                    $primQty = $this->convertToPrimary($invoice->product_id, $invoice->qty, $invoice->unit_id);
                    $salesItem[] = [
                        'product_id' => $invoice->product_id,
                        'qty' => $primQty['prim_qty'],
                    ];
                }
            }

            if ($order->invoice_status == 'Generated') {
                $this->addGeneratedInvoiceItems($order, $salesItem);
            }
        }

        return $salesItem;
    }

    // Function to add sales invoice and tax invoice items to salesItem
    private function addGeneratedInvoiceItems($order, &$salesItem)
    {
        $salesInvoice = SalesInvoice::select('invoice_num', 'invoice_status')
                                    ->where('order_num', $order->order_num)
                                    ->whereNot('invoice_status', 'Cancelled')
                                    ->get();

        foreach ($salesInvoice as $invoice) {
            $itemS = SalesInvoiceItem::select('invoice_num', 'product_id', 'qty')
                                    ->where('invoice_num', $invoice->invoice_num)
                                    ->get();
            foreach ($itemS as $item) {
                $salesItem[] = [
                    'product_id' => $item->product_id,
                    'qty' => $item->qty,
                ];
            }
        }

        $taxInvoice = TaxInvoice::select('invoice_num', 'invoice_status')
                                ->where('order_num', $order->order_num)
                                ->whereNot('invoice_status', 'Cancelled')
                                ->get();

        foreach ($taxInvoice as $invoice) {
            $itemS = TaxInvoiceItem::select('invoice_num', 'product_id', 'qty')
                                ->where('invoice_num', $invoice->invoice_num)
                                ->get();
            foreach ($itemS as $item) {
                $salesItem[] = [
                    'product_id' => $item->product_id,
                    'qty' => $item->qty,
                ];
            }
        }
    }

    // Function to get return data and convert it to primary quantity
    private function getReturnData($returnItems)
    {
        $returnData = [];
        foreach ($returnItems as $item) {
            $returnDataArray = json_decode($item->return_data, true);
            foreach ($returnDataArray as $rdata) {
                $primQty = $this->convertToPrimary($rdata['product_id'], $rdata['qty'], $rdata['unit']);
                $returnData[$rdata['product_id']] = $primQty['prim_qty'];
            }
        }

        return $returnData;
    }

    // Function to calculate sales total
    private function calculateSalesTotal($salesItems)
    {
        $salesTotal = [];
        foreach ($salesItems as $item) {
            $productId = $item['product_id'];
            $qty = (float) $item['qty'];

            if (!isset($salesTotal[$productId])) {
                $salesTotal[$productId] = 0;
            }

            $salesTotal[$productId] += $qty;
        }

        return $salesTotal;
    }

    // Function to get cancelled sales items
    private function getCancelledSalesItems($date)
    {
        $returnOrders = Order::select('order_num', 'invoice_status')
                            ->whereIn('invoice_status', ['Cancelled', 'Generated'])
                            ->whereBetween('created_at', [$date . ' 00:00:00', $date . ' 23:59:59'])
                            ->get();

        $returnItem = [];

        foreach ($returnOrders as $order) {
            if ($order->invoice_status == 'Cancelled') {
                $returnInvoice = OrderItem::select('order_num', 'product_id', 'qty', 'unit_id')
                                        ->where('order_num', $order->order_num)
                                        ->get();
                foreach ($returnInvoice as $invoice) {
                    $primQty = $this->convertToPrimary($invoice->product_id, $invoice->qty, $invoice->unit_id);
                    $returnItem[] = [
                        'product_id' => $invoice->product_id,
                        'qty' => $primQty['prim_qty'],
                    ];
                }
            }

            if ($order->invoice_status == 'Generated') {
                $this->addCancelledInvoiceItems($order, $returnItem);
            }
        }

        return $returnItem;
    }

    // Function to add cancelled invoice items to returnItem
    private function addCancelledInvoiceItems($order, &$returnItem)
    {
        $salesInvoiceR = SalesInvoice::select('invoice_num', 'invoice_status')
                                    ->where('order_num', $order->order_num)
                                    ->where('invoice_status', 'Cancelled')
                                    ->get();

        foreach ($salesInvoiceR as $invoice) {
            $itemS = SalesInvoiceItem::select('invoice_num', 'product_id', 'qty')
                                    ->where('invoice_num', $invoice->invoice_num)
                                    ->get();
            foreach ($itemS as $item) {
                $returnItem[] = [
                    'product_id' => $item->product_id,
                    'qty' => $item->qty,
                ];
            }
        }

        $taxInvoiceR = TaxInvoice::select('invoice_num', 'invoice_status')
                                ->where('order_num', $order->order_num)
                                ->where('invoice_status', 'Cancelled')
                                ->get();

        foreach ($taxInvoiceR as $invoice) {
            $itemS = TaxInvoiceItem::select('invoice_num', 'product_id', 'qty')
                                ->where('invoice_num', $invoice->invoice_num)
                                ->get();
            foreach ($itemS as $item) {
                $returnItem[] = [
                    'product_id' => $item->product_id,
                    'qty' => $item->qty,
                ];
            }
        }
    }


    //Primary Unit Conversion Function
    private function convertToPrimary($productId, $qty, $unitId) {
        $primQty = 0;
        $primUnit = '';
        $productUnits = ProductUnit::select('product_id','unit_id','prim_unit','conversion')
                                    ->where('product_id',$productId)
                                    ->orderByDesc('prim_unit')
                                    ->get();

        foreach($productUnits as $productUnit) {
            if($productUnit->prim_unit == 1 ) {
                $primUnit = UOM::where('id',$productUnit->unit_id)->value('display_name');
                $primQty = $qty;
            }
            else if($unitId == $productUnit->unit_id) {
                $primQty = $qty * $productUnit->conversion;
            }
        }

        return [
            'prim_qty' => getTwoDigitPrecision($primQty),
            'prim_unit' => $primUnit
        ];
    } 


    /*Denomination
    Menu Start*/
    public function receiptDenomination(Request $request)
    {
        $date    = $request->date ?? date('Y-m-d');
        $routeId = $request->routeId ?? 0;
        $list    = Receipt::select('id','receipt_num','receipt_date','route_id','customer_name','amount','denomination','created_at')
                            ->whereBetween('created_at',[$date. ' 00:00:00',$date. ' 23:59:59'])
                            ->where('route_id',$routeId)
                            ->orderBy('created_at','asc')
                            ->get();
        $routes = MRoute::select('id','name')->orderBy('name')->get();
         // Process and normalize denominations
         $groupDenomination = [];
         $receiptDenomination = [];
         $noDenomination    = [];
     
         foreach ($list as $item) {
             $denomination = json_decode($item->denomination, true);     
             if (!is_array($denomination)) {
                if($denomination == null)
                {
                    $noDenomination[] = $item;
                }else{
                 $groupDenomination[] = $item; 
                }
             } else {
                 $receiptDenomination[] = $item;
             }
         } 
         $groupDenomination = collect($groupDenomination)->groupBy('denomination');       
        //  return response()->json([    
         return view('denomination.receipt',[      
             'groupDenomination'     => $groupDenomination,  
             'receiptDenomination'   => $receiptDenomination, 
             'noDenomination'        => $noDenomination,         
             'routes'                => $routes,
             'date'                  => $date,
             'routeId'               => $routeId
             
         ]);
    }

    public function receiptDenominationView(Request $request)
    {        
        $date = $request->date;
        $routeId = $request->routeId;
        $receiptNumbers = $request->id;        
        $receipt = explode(',', $receiptNumbers);               
        $receiptNo = [];
        if(count($receipt) == 1)
        {
            $receiptD = Receipt::select('id','receipt_num','denomination','created_at')                       
                        ->where('receipt_num',$receipt)
                        ->first();                       
            if ($receiptD) {
                // Try to decode the denomination
                $decodedDenomination = json_decode($receiptD->denomination);                
            if (is_array($decodedDenomination)) {
                $receiptNo = [$receipt[0]];
            }
            else
            {
                $denoNum = $receiptD->denomination ?? 0;
                $query = Receipt::select('receipt_num')
                                ->whereBetween('created_at', [$date . ' 00:00:00', $date . ' 23:59:59']);
                $query->where('denomination', $denoNum);               
                $receiptNo = $query->get()->pluck('receipt_num');
            }           
            }                 
        }
        else
        {
            $receiptNo = $receipt;
        }           
        $allDenomination = [];
        $customer = [];
        $receiptNumbersList = [];   
        $cusAmount = [];
        $rupeeNotes = RupeeNote::select('note_value')->orderBy('display_index')->get();
        foreach($receiptNo as $receipt)
        {
        $denomination = Receipt::select('id','receipt_num','receipt_date','route_id','customer_name','amount','denomination','created_at')
                        ->with('denominationDetails:id,denomination,amount')
                        ->where('receipt_num',$receipt)
                        ->orderBy('created_at','asc')
                        ->first(); 
        $customer[] = $denomination->customer_name; 
        $receiptNumbersList[] = $denomination->receipt_num;  
        $cusAmount[] = $denomination->amount;      
        }        
        $denominationDetails = $denomination->denominationDetails; // Single model instance or null
        if (!empty($denominationDetails)) { // Check if relation exists
            $allDenomination[] = [
                "date"         => $denomination->created_at,
                'customer'     => $customer,
                'receipt_num'  => $receiptNumbersList,
                'cus_amount'   => $cusAmount,
                'denomination' => json_decode($denominationDetails->denomination ?? '{}', true),
                'amount'       => $denominationDetails->amount ?? 0,
            ];
        } else { // Fallback if no relation exists
            $allDenomination[] = [
                "date"         => $denomination->created_at,
                'customer'     => $customer,
                'receipt_num'  => $receiptNumbersList,
                'cus_amount'   => $cusAmount,
                'denomination' => json_decode($denomination->denomination ?? '{}', true),
                'amount'       => $denomination->amount ?? 0,
            ];
        }
        $list    = Receipt::select('id','receipt_num','route_id')
                            ->whereBetween('created_at',[$date. ' 00:00:00',$date. ' 23:59:59'])
                            ->where('route_id',$routeId)
                            ->whereNot('denomination',null)
                            ->orderBy('created_at','desc')
                            ->get();

        // return response()->json([
        return view('denomination.view_receipt',[    
            'denomination' => $allDenomination,
            'notes'        => $rupeeNotes,
            'listId'       => $list,
            'receiptNo'    => $receiptNo,
            'date'         => $date,
            'routeId'      => $routeId
            // 'denomination'=> $denomination
        ]);
    }

    public function dayRouteDenomination(Request $request)
    { 
        $date = $request->date ?? date('Y-m-d');      
        $denomination = Receipt::select('id','receipt_num','receipt_date','route_id','customer_name','amount','denomination','created_at')
                                ->with('denominationDetails:id,denomination,amount') 
                                ->with('route:id,name')
                                ->whereBetween('created_at', [$date . ' 00:00:00', $date . ' 23:59:59'])
                                ->orderBy('created_at', 'asc')
                                ->get()
                                ->groupBy(function ($item) {
                                    // Group by route name
                                    return $item->route ? $item->route->name : '';
                                });// Grouping by route_id
        $routeDeno = [];
        foreach($denomination as $index => $deno)
        {
            $amount = 0; 
            $totalReceipt = 0; 
            $route_id = null; 
            foreach($deno as $d)
            {   
                if($d->denomination != null)
                {
                    $totalReceipt++;
                    $amount += $d->amount; 
                    $route_id = $d->route_id; 
                }
            }
            $routeDeno[] = [
                'route'         => $index,
                'no_of_receipt' => $totalReceipt,
                'amount'        => $amount,
                'route_id'      => $route_id
            ];

        }
        // return response()->json([
        return view('denomination.day_route',[   
            // 'denpmination' =>$denomination,         
            'date'      => $date,
            'routeDeno' => $routeDeno
        ]);       
    }

    public function routeDenominationView(Request $request)
    {
        $date    = $request->date;
        $routeId = $request->routeId; 
        $routeIds  = Receipt::select('route_id')->whereBetween('created_at', [$date . ' 00:00:00', $date . ' 23:59:59'])->distinct()->get();
        $rupeeNotes = RupeeNote::select('note_value')->orderBy('display_index')->get();
        $denomination = Receipt::select('id','receipt_num','receipt_date','route_id','customer_name','amount','denomination','created_at')
                                ->with('denominationDetails:id,denomination,amount')   
                                ->with('route:id,name')                              
                                ->whereBetween('created_at', [$date . ' 00:00:00', $date . ' 23:59:59'])
                                ->where('route_id',$routeId)
                                ->whereNot('denomination',null)
                                ->orderBy('created_at', 'asc')
                                ->get();                                        
        $allDenomination = [];
        $group = collect();
        $totalReceipt = count($denomination);
        $amount = 0;          
        $route = null;           
        $denomTotal = [];        
        foreach ($denomination as $deno) {           
            $denoDetails = json_decode($deno->denomination, true); 
            if (is_array($denoDetails)) { 
                $route = $deno->route->name;
                $amount += $deno->amount; 
                foreach ($denoDetails as $denomItem) {
                    foreach ($denomItem as $denomKey => $denomValue) {                        
                        if (isset($denomTotal[$denomKey])) {
                            $denomTotal[$denomKey] += $denomValue;
                        } else {
                            $denomTotal[$denomKey] = $denomValue;
                        }
                    }
                }
            }
            else
            {
                $group->push($deno);
            }
        }
        $groupDeno = $group->groupBy('denomination')->map(function($group)
        {
            return $group->first();
        });   
        foreach ($groupDeno as $index => $group) {                              
            $groupDetails = json_decode($group->denominationDetails->denomination, true);             
            $amount += $group->denominationDetails->amount; 
                foreach ($groupDetails as $Item) {
                    if (is_array($Item)) {  
                        foreach ($Item as $Key => $Value) {                                
                            if (isset($denomTotal[$Key])) {
                                $denomTotal[$Key] += $Value;
                            } else {
                                $denomTotal[$Key] = $Value;
                            }
                        }
                    }                    
                }           
        }    
        
        $allDenomination[] = [
            'route'         => $route,
            'total_receipt' => $totalReceipt,
            'total_amount'  => $amount,
            'denom_total'   => $denomTotal, // Include summed denomination totals
        ];
        
        // return response()->json([
        return view('denomination.day_route_view',[
            'date'             => $date,          
            'allDenomination'  => $allDenomination,  
            'notes'            => $rupeeNotes,  
            'routeIds'         => $routeIds,
            'routeId'         => $routeId              
        ]);
    }

    public function dayDenominationView(Request $request)
    {      
        $date = $request->date;
        $rupeeNotes = RupeeNote::select('note_value')->orderBy('display_index')->get();
        $denomination = Receipt::select('id', 'receipt_num', 'receipt_date', 'route_id', 'customer_name', 'amount', 'denomination', 'created_at')
                                ->with('denominationDetails:id,denomination,amount')
                                ->with('route:id,name')
                                ->whereBetween('created_at', [$date . ' 00:00:00', $date . ' 23:59:59'])
                                ->whereNot('denomination', null)
                                ->orderBy('route_id', 'asc')
                                ->get();   
    
        $allDenomination = [];
        $group = collect();
        $totalReceipt = count($denomination);
        $routeNote = [];
        $amount = 0;    
        $denomTotal = [];   
        foreach ($denomination as $deno) {           
            $denoDetails = json_decode($deno->denomination, true); 
            if (is_array($denoDetails)) {                   
                $amount += $deno->amount; 
                $routeExists = false;
                foreach ($routeNote as &$routeData) {
                    if ($routeData['route'] == $deno->route->name) {
                        foreach ($denoDetails as $denomItem) {
                            foreach ($denomItem as $denomKey => $denomValue) {
                                if (!isset($routeData['denom'][$denomKey])) {
                                    $routeData['denom'][$denomKey] = 0;
                                }
                                $routeData['denom'][$denomKey] += $denomValue;
                            }
                        }
                        $routeExists = true;
                        break;
                    }
                }
                if (!$routeExists) {
                    $routeNote[] = [
                        'route' => $deno->route->name,
                        'denom' => $this->flattenDenomination($denoDetails),
                    ];
                }
                foreach ($denoDetails as $denomItem) {
                    foreach ($denomItem as $denomKey => $denomValue) {                        
                        if (!isset($denomTotal[$denomKey])) {
                            $denomTotal[$denomKey] = 0;
                        }
                        $denomTotal[$denomKey] += $denomValue;
                    }
                }
            } else {
                $group->push($deno);
            }
        }
        $groupDeno = $group->groupBy('denomination')->map(function($group) {
            return $group->first();
        }); 
        foreach ($groupDeno as $index => $group) {                              
            $groupDetails = json_decode($group->denominationDetails->denomination, true);             
            $amount += $group->denominationDetails->amount; 
            $routeExists = false;
            foreach ($routeNote as &$routeData) {
                if ($routeData['route'] == $group->route->name) {
                    foreach ($groupDetails as $Item) {
                        if (is_array($Item)) {  
                            foreach ($Item as $Key => $Value) {  
                                if (!isset($routeData['denom'][$Key])) {
                                    $routeData['denom'][$Key] = 0;
                                }
                                if (is_numeric($routeData['denom'][$Key]) && is_numeric($Value)) {
                                    $routeData['denom'][$Key] += $Value;
                                } else {
                                    $routeData['denom'][$Key] = $Value;
                                }
                            }
                        }                    
                    }           
                    $routeExists = true;
                    break;
                }
            }
            if (!$routeExists) {
                $routeNote[] = [
                    'route' => $group->route->name,
                    'denom' => $this->flattenDenomination($groupDetails),
                ];
            }
            foreach ($groupDetails as $Item) {
                if (is_array($Item)) {  
                    foreach ($Item as $Key => $Value) {                                 
                        if (!isset($denomTotal[$Key])) {
                            $denomTotal[$Key] = 0;
                        }
                        $denomTotal[$Key] += $Value;
                    }
                }                    
            }
        }
        $allDenomination[] = [            
            'total_receipt' => $totalReceipt,
            'total_amount'  => $amount,
            'denom_total'   => $denomTotal,
        ];    
        // return response()->json([
        return view('denomination.day_view',[
            'date'             => $date,          
            'allDenomination'  => $allDenomination,  
            'notes'            => $rupeeNotes,               
            'routeNote'        => $routeNote
        ]);
    }
    
    function flattenDenomination($denomDetails) {
        $flattened = [];
        foreach ($denomDetails as $denomItem) {
            foreach ($denomItem as $key => $value) {
                if (isset($flattened[$key])) {
                    $flattened[$key] += $value;
                } else {
                    $flattened[$key] = $value;
                }
            }
        }
        return $flattened;
    }

    /*expense
    Entry */

    public function expenseEntry()
    {
        $date     = date('Y-m-d');   
        $expenses = ExpenseType::all();  
        // return response()->json([   
        return view('transactions.expenses.expense_entry',[
            'date'      => $date,           
            'expenses'  => $expenses
        ]);
    }   
    
    public function expenseEntryStore(Request $request)
    {
        $expenses = ExpenseType::all();  
        $expense                        = new Expense();
        $expense->expense_date          = $request->date;
        $expense->expense_name          = $request->expenseName;
        $expense->expense_narration     = $request->narration;
        $expense->expense_amount        = $request->amount;
        $expense->expense_status        = "created";
        $expense->save();
        return response()->json([
            'message'   => "Expense saved Successfully",
            'expenses'  => $expenses,
        ]);
    }

    public function expenseEntryList(Request $request)
    {
        $date = $request->date ?? date('Y-m-d');
        $expenses = Expense::select('id','expense_date','expense_name','expense_amount','expense_status')
                    ->whereBetween('created_at', [$date. " 00:00:00",$date. " 23:59:59"])
                    ->get();
        return view('transactions.expenses.expense_entry_list',[
            'date'     => $date,
            'expenses' => $expenses
        ]);
    }

    public function expenseEntryView(Request $request)
    {    
        $id           = $request->id;    
        $expenseEntry = Expense::find($id);       
        $date         = $request->entryDate;           
        $expensesQuery = Expense::query();
        if ($date) {
            $expensesQuery->whereDate('created_at', $date);
        } else {
            $expensesQuery->whereIn('expense_status', ['created', 'pending']);
        }
        $expensesId = $expensesQuery->pluck('id');

        // return response()->json(
        return view('transactions.expenses.view_expense',
        [
            'date'          => $date,
            'expenseEntry'  => $expenseEntry,
            'expensesId'    => $expensesId,                
            'id'            => $id,
        ]);
    }

    public function expenseEntryEdit(Request $request)
    {
        $id           = $request->id;    
        $expenseEntry = Expense::find($id);   
        $expenses     = ExpenseType::all();     
        
        // return response()->json(
        return view('transactions.expenses.expense_entry',
        [
            'expenseEntry'  => $expenseEntry,  
            'id'            => $id,
            'expenses'      => $expenses       
        ]);
    }

    public function expenseEntryUpdate(Request $request)
    {       
        $expenses = ExpenseType::all();  
        $expense                        = Expense::find($request->id);
        $expense->expense_date          = $request->date;
        $expense->expense_name          = $request->expenseName;
        $expense->expense_narration     = $request->narration;
        $expense->expense_amount        = $request->amount;        
        $expense->save();
        return response()->json([
            'message'   => "Expense updated successfully",
            'expenses'  => $expenses      
        ]);
    }

    public function expenseTypes()
    {
        $expenses = ExpenseType::all();
        return view('masters.expense.expense_types',[
            'expenses' => $expenses,
        ]);
    }

    public function expenseTypesEdit($id)
    {        
    	$expense = ExpenseType::find($id);
	    return response()->json([
	        'expense' => $expense
	    ]);
    }

    public function expenseTypesStore($id)
    {              
        try {
            $expense = ExpenseType::updateOrCreate(
                [ 'id' => $id ],
                [ 'name' => request('name') ]
            );
            return response()->json(['expense' => $expense]);
            // return response()->json([ 'success' => true ]);
        }
        catch(QueryException $exception) {            
            return $exception;
        }
    }

    public function expenseTypesDestroy($id)
    {                   
        $expense = ExpenseType::find($id);
        $expense->delete();
        return response()->json([ 'success' => true ]);
    }

    public function expenseApproval(Request $request)
    {
        $date = $request->date ?? date('Y-m-d');
        $expenses = Expense::select('id','expense_date','expense_name','expense_amount','expense_status')
                    ->whereIn('expense_status',['Created','Pending'])
                    ->get();
        return view('transactions.expenses.expense_approval',[
            'date'     => $date,
            'expenses' => $expenses
        ]);
    }
    
    public function expenseApprovalStore(Request $request)
    {
        $id      = $request->id;
        $status  = $request->status;
        $expense = Expense::find($id);
        $expense->expense_status = $status;
        $expense->save();        
        return response()->json(
            [
                "id"     => $id,
                "status" => $status
            ]
            );
    }

    public function expensePayment()
    {
        $expenses = Expense::select('id','expense_date','expense_name','expense_amount','expense_status','denomination')
                    ->where('expense_status','Accepted')
                    ->get();
        $rupeeNotes = RupeeNote::select('note_value')->orderBy('display_index')->get();
        return view('transactions.expenses.expense_payment',[
            'expenses' => $expenses,
            'notes'    => $rupeeNotes
        ]);
    }

    public function expenseNonDenomination()
    {        
        $expense = Expense::select('id','expense_name','expense_amount','expense_status','denomination')   
                            ->where('expense_status','Accepted')                       
                            ->whereNull('denomination')
                            ->get();
        return response()->json($expense);
    }

    public function expenseStoreDenomination(Request $request)
    {
        // return response()->json($request->all());
        $denom  = new ExpenseDenomination();
        $denom->denomination = $request->denomination;
        $denom->expense_ids = $request->expense_ids;
        $denom->denomination_amount = $request->amount;
        $denom->save();

        $ids    = json_decode($request->expense_ids,true);        
        $minDate = null; // Initialize the variable to hold the minimum date
        foreach ($ids as $id) {
            $expen = Expense::find($id);
            
            if ($expen) {                
                $expen->denomination = $denom->id;  
                $expen->save();
            }

            // Compare and find the minimum date
            if (!$minDate || $expen->expense_date < $minDate) {
                $minDate = $expen->expense_date; // Update min date if the current record has an earlier date
            }
        }

        if($minDate != date('Y-m-d')) {            
            $this->explorerController->updateCashRegisters($minDate);
        }
        
        return response()->json(['success' => true, 'message' => 'Data saved successfully']);
    }

    public function closingBalance(Request $request)
    {
        $date = $request->date ?? Carbon::now()->format('Y-m-d');
        $yesterday = Carbon::createFromFormat('Y-m-d', $date)->subDay()->format('Y-m-d');
        $rupeeNotes = RupeeNote::select('note_value')->orderBy('display_index')->get();

        $openingBalance = OpeningBalance::whereNotBetween('created_at', [$date . ' 00:00:00', $date . ' 23:59:59'])
                        ->orderBy('created_at', 'desc')
                        ->first();
        $openingAmount = $openingBalance ? $openingBalance->opening_amount : 0;
        $openingDenominations = $openingBalance ? json_decode($openingBalance->denomination, true) : [];        
        $denomination = Receipt::select('id', 'receipt_num', 'receipt_date', 'route_id', 'customer_name', 'amount', 'denomination', 'created_at')
                                ->with('denominationDetails:id,denomination,amount')
                                ->with('route:id,name')
                                ->whereBetween('created_at', [$date . ' 00:00:00', $date . ' 23:59:59'])
                                ->whereNot('denomination', null)
                                ->orderBy('route_id', 'asc')
                                ->get();

        $allDenomination = [];
        $amount = 0;    
        $denomTotal = [];   

        foreach ($denomination as $deno) {           
            $denoDetails = json_decode($deno->denomination, true); 
            if (json_last_error() === JSON_ERROR_NONE && is_array($denoDetails)) {                   
                $amount += $deno->amount; 
                foreach ($denoDetails as $denomItem) {
                    foreach ($denomItem as $denomKey => $denomValue) {                        
                        $denomTotal[$denomKey] = ($denomTotal[$denomKey] ?? 0) + $denomValue;
                    }
                }
            }
        }

        // Expense Denomination and Total
        $expenses = ExpenseDenomination::select('id', 'denomination', 'denomination_amount')
                                        ->whereBetween('created_at', [$date . ' 00:00:00', $date . ' 23:59:59'])
                                        ->get();
        $DenoAmoEx = [];
        $amountEx = 0;    
        $denomEx = [];   

        foreach ($expenses as $expense) {           
            $expenseDeno = json_decode($expense->denomination, true); 
            if (json_last_error() === JSON_ERROR_NONE && is_array($expenseDeno)) {                   
                $amountEx += $expense->denomination_amount; 
                foreach ($expenseDeno as $denom) {
                    foreach ($denom as $dKey => $dValue) {                        
                        $denomEx[$dKey] = ($denomEx[$dKey] ?? 0) + $dValue;
                    }
                }
            } 
        }
        $closingAmount = $openingAmount + $amount - $amountEx;
        $closingDenominations = $openingDenominations;
        foreach ($denomTotal as $key => $value) {
            $closingDenominations[$key] = ($closingDenominations[$key] ?? null) + $value;
        }

        foreach ($denomEx as $key => $value) {
            $newValue = ($closingDenominations[$key] ?? null) - $value;
            $closingDenominations[$key] = ($newValue === 0) ? null : $newValue;
        }
        
        $newOpen = new OpeningBalance();
        $newOpen->opening_amount = $closingAmount;
        $newOpen->denomination   = json_encode($closingDenominations,true);
        $newOpen->type           = "expense";
        $newOpen->save();

        // return response()->json([
        return view('transactions.expenses.closing_balance',[
            'notes'    => $rupeeNotes,
            'date'     => $date,
            'receipt'  => [
                'total_amount'  => $amount,
                'denom_total'   => $denomTotal,  
            ],
            'expense'  => [
                'total_amount'  => $amountEx,
                'denom_total'   => $denomEx,  
            ],
            'closing_balance' => [
                'total_amount'  => $closingAmount,  
                'denom_total'   => $closingDenominations,  
            ],
            'opening_amount'    => $openingAmount,
        ]);
    }

    public function expenseReceipt(Request $request)
    {
        $date = $request->date ?? date('Y-m-d');
        $expenses = ExpenseDenomination::select('id', 'denomination', 'denomination_amount', 'expense_ids')
            ->whereBetween('created_at', [$date . ' 00:00:00', $date . ' 23:59:59'])
            ->get();
        $allDenomination = [];
        foreach ($expenses as $index => $expense) {
            $ids = json_decode($expense->expense_ids, true);             
            $allDenomination[$index] = [];

            if (is_array($ids)) {
                foreach ($ids as $id) {
                    $denom = Expense::select('id', 'expense_date', 'expense_name', 'expense_amount', 'expense_status', 'denomination')
                        ->where('id', $id)
                        ->first(); 
                    if ($denom) {
                        $allDenomination[$index][] = $denom; 
                    }
                }
            } else {
                $denom = Expense::select('id', 'expense_date', 'expense_name', 'expense_amount', 'expense_status', 'denomination')
                    ->where('id', $ids)
                    ->first(); 
                if ($denom) {
                    $allDenomination[$index][] = $denom; 
                }
            }
        }

        // return response()->json([
        return view('transactions.expenses.expense_receipt_list',[
            "expenses" => $allDenomination,
            "date"     => $date
        ]);
    }

    public function expenseReceiptView(Request $request)
    {        
        $date = $request->entryDate;
        $ids  = explode(',', $request->id);     
        $receiptId = []; 
        $rupeeNotes = RupeeNote::select('note_value')->orderBy('display_index')->get();
        $allIds  = Expense::select('id','expense_date','expense_name','expense_amount','expense_status','denomination')
                    ->where('expense_status','Accepted')
                    ->whereBetween('created_at', [$date . ' 00:00:00', $date . ' 23:59:59'])
                    ->pluck('id');  
        if (count($ids) == 1) {
            $denom = Expense::where('id', $ids[0])->value('denomination');                
            if ($denom) {
                $expense = Expense::select('id', 'expense_date', 'expense_name', 'expense_amount', 'expense_status', 'denomination')
                                ->where('denomination', $denom)
                                ->with('ExDeno:id,denomination,denomination_amount')
                                ->get();
                $receiptId = $expense->pluck('id');  
                $receiptDeno = $expense;  
            }
        }
        else {
            $receiptDeno = Expense::select('id', 'expense_date', 'expense_name', 'expense_amount', 'expense_status', 'denomination')
                                ->whereIn('id', $ids) 
                                ->with('ExDeno:id,denomination,denomination_amount')
                                ->get();
            $receiptId = $ids; 
        }
        $firstDeno = $receiptDeno->first(); // Get the first item in the collection
        $denomination = json_decode($firstDeno->ExDeno->denomination, true); // Decode the JSON into an associative array
        // return response()->json([
        return view('transactions.expenses.expense_receipt_view',[
            "denom"    => $receiptDeno,
            "ids"      => $receiptId,
            "date"     => $date,
            'allIds'   => $allIds,
            'notes'    => $rupeeNotes,
            'denomination'      => $denomination
        ]);
    }


    /*Incentives
    code */
    public function makeIncentive()
    {
        $fromDate = null;
        $toDate   = null;
        $records  = [];
        $customers = Customer::select('id','customer_name')                                 
                                 ->where('status','Active')
                                 ->orderBy('customer_name')
                                 ->get();
        return response()->json([
        // return view('transactions.incentives.make_incentive',[
            "fromDate"  => $fromDate,
            "toDate"    => $toDate,
            "records"   =>$records ,   
            "customers" => $customers 
        ]);
    }

    public function IncentiveStore(Request $request)
    {        
        $id = $request->customerId;
        $fromDate = $request->fromDate;
        $toDate = $request->toDate;      
        $customers = Customer::select('id','customer_name')                                 
                    ->where('status','Active')
                    ->orderBy('customer_name')
                    ->get(); 
                    
        $customerName = Customer::select('id', 'customer_name','route_id','tds_status','pan_number')                                
                                ->where('id', $id)
                                ->first();         
        list($invoice, $incentiveCustomer) = $this->incentiveCustomer($id, $fromDate, $toDate);        
        $groupedTotal = $invoice->groupBy('product_id')->map(function ($items, $productId) {
            return [
                'product_id' => $productId,
                'product_name' => $items->first()->product_name, // Access as an object property
                'total_qty' => $items->sum('qty'),
            ];
        })->filter(function ($group) {
            return $group['total_qty'] > 0; // Keep only groups where total_qty is greater than 0
        });        
        $incentiveProducts = $incentiveCustomer ? json_decode($incentiveCustomer->incentive_data, true) : [];
        $allIncentive = [];
        $incAmountTotal = 0;  // To hold the total inc_amount
        $lkAmountTotal = 0;
        foreach ($incentiveProducts as $product) {
            $matchedGroup = $groupedTotal->firstWhere('product_id', $product['id']);
            if (!$matchedGroup) {
                continue; 
            }
            $productData = [
                "id"        => $product['id'],
                "product"   => $product['product'], 
                "qty"       => 0, // Default to 0
                "inc_rate"  => $product['inc_rate'],
                "inc_amount"=> 0,
                "lk_qty"    => 0,
                "lk_amount" => 0,
            ];
            foreach ($groupedTotal as $qty) {
                if ($product['id'] == $qty['product_id']) {
                    $productData["qty"] = $qty['total_qty']; 
                    break; 
                }
            }
            $productData["inc_amount"] = $productData["qty"] * $productData["inc_rate"];
            $productData["lk_qty"] = $productData["qty"] * ($product['lk_qty'] ?? 0); 
            $productData["lk_amount"] = $productData["lk_qty"] * ($product['lk_amt'] ?? 0); 
            $allIncentive[] = $productData;
            $incAmountTotal += $productData["inc_amount"];
            $lkAmountTotal += $productData["lk_amount"];
        }      
          
        $totalAmount = round($incAmountTotal,0) + round($lkAmountTotal,0);

        // Calculate the TDS amount
        $tdsAmount = $this->getTdsAmount($customerName,$totalAmount);
    
        $tdsAmount = (float) str_replace(',', '', $tdsAmount); // Remove commas and convert to float       
        $totalAmount = (float) $totalAmount;

        $netAmount = $totalAmount - $tdsAmount;
        
        $totals = [
            'incAmountTotal' => round($incAmountTotal,0),
            'lkAmountTotal'  => round($lkAmountTotal,0),
            "total"          => $totalAmount,
            "tdsAmount"      => ceil($tdsAmount),
            "netAmount"      => $netAmount
        ];

        $makeIncentive    = new IncentivesData();
        $makeIncentive->customer_name   = $customerName->customer_name;
        $makeIncentive->customer_id     = $id;
        $makeIncentive->route_id        = $customerName->route_id;
        $makeIncentive->from_date       = $fromDate;
        $makeIncentive->to_date         = $toDate;
        $makeIncentive->status          = "Pending";
        $makeIncentive->incentive_data  = json_encode($allIncentive,true);
        $makeIncentive->incentive_total = json_encode($totals,true);
        $makeIncentive->save();

        return response()->json([
        // return view('transactions.incentives.make_incentive',[
            "custom"       => $customerName,
            "fromDate"     => $fromDate,
            "toDate"       => $toDate,
            "customerName" => $customerName->customer_name,
            "allIncentive" => $allIncentive,
            "customers" => $customers ,
            "group" => $groupedTotal,
            "incentiveProducts" => $incentiveProducts,
            "totals"    => $totals
        ]);
    }      

    public function incentiveCustomer($id, $fromDate, $toDate)
    {
        // Fetch incentive data
        $incentiveCustomer = IncentiveMaster::select('id', 'txn_id', 'customer_ids', 'incentive_rate', 'incentive_data')
                                    ->whereJsonContains('customer_ids', $id)
                                    ->first();

        $invoice = collect();

        // Fetch sales invoices
        $salesInvoice = SalesInvoice::select('id', 'invoice_num', 'customer_id')
                                    ->where('customer_id', $id)
                                    ->where('invoice_status', '!=', 'Cancelled')
                                    ->whereBetween('created_at', [$fromDate . " 00:00:00", $toDate . " 23:59:59"])
                                    ->get();

        // Fetch sales invoice items
        $salesItems = SalesInvoiceItem::select('id', 'invoice_num', 'product_id', 'product_name', 'qty')
                                    ->whereIn('invoice_num', $salesInvoice->pluck('invoice_num'))
                                    ->get();
        $invoice = $invoice->merge($salesItems);

        // Fetch tax invoices
        $taxInvoice = TaxInvoice::select('id', 'invoice_num', 'customer_id')
                                ->where('customer_id', $id)
                                ->where('invoice_status', '!=', 'Cancelled')
                                ->whereBetween('created_at', [$fromDate . " 00:00:00", $toDate . " 23:59:59"])
                                ->get();

        // Fetch tax invoice items
        $taxItems = TaxInvoiceItem::select('id', 'invoice_num', 'product_id', 'product_name', 'qty')
                                ->whereIn('invoice_num', $taxInvoice->pluck('invoice_num'))
                                ->get();
        $invoice = $invoice->merge($taxItems);

        return [$invoice, $incentiveCustomer]; 
    }

    public function getIncentiveDate(Request $request)
    {
        $id = $request->customerId;
        $incentiveDate = IncentivesData::select('id','customer_id', 'from_date', 'to_date','status')
                                    ->where('customer_id', $id)
                                    ->orderBy('created_at','desc')
                                    ->first();
        $incentiveCustomer = IncentiveMaster::select('id', 'txn_id', 'customer_ids', 'incentive_rate', 'incentive_data')
                                    ->whereJsonContains('customer_ids', $id)
                                    ->first();
        return response()->json([
            'incentiveDate' => $incentiveDate,
            "incentiveCustomer" => $incentiveCustomer
        ]);       
    }
    public function getCustomerIncentive(Request $request)
    {
        $id = $request->customerId;
        $fromDate = $request->fromDate;
        $toDate = $request->toDate; 
        list($invoice, $incentiveCustomer) = $this->incentiveCustomer($id, $fromDate, $toDate);   
        return response()->json([
            'invoice' => $invoice,
        ]);   
    }

    public function IncetivesList(Request $request)
    {
        // dd($request->all());
        $fromDate = $request->fromDate ?? date("Y-m-d");
        $toDate = $request->toDate ?? date("Y-m-d");
        $route_id   = $request->route_id ?? "All";
        $customerId = $request->customerId ?? "All";
        $routes = MRoute::select('id','name')->orderBy('name')->get();
        $customerName = null;
        if($customerId != "All"){
        $customerName = Customer::select('id', 'customer_name','route_id')                                
                                ->where('id', $customerId)
                                ->first();   
        }          
        $incentive_date = IncentivesData::select('id','customer_id','customer_name','route_id','status','incentive_total','created_at')
                        ->when($route_id != "All", function ($query) use ($route_id) {
                            $query->where('route_id', $route_id);
                        })
                        ->when($customerId != "All", function ($query) use ($customerId) {
                            $query->where('customer_id', $customerId);
                        })
                        ->whereBetween('created_at', [$fromDate . " 00:00:00", $toDate . " 23:59:59"])
                        ->with('route:id,name') // Eager load the route relationship
                        ->get();


        return response()->json([
        // return view('transactions.incentives.list_incentives_old',[
            "fromDate"   => $fromDate,
            "toDate"     => $toDate,
            "routeId"    => $route_id,
            "customerId" => $customerId,
            "incentives" => $incentive_date,
            "routes"     => $routes,
            "custom"     => $customerName
        ]);
    }

    public function incentiveShow(Request $request)
    {
        // dd($request->all());
        $fromDate    = $request->fromDate;
        $toDate      = $request->toDate;
        $route_id    = $request->route_id;
        $customerId  = $request->customerId == null ? "All" : $request->customerId;
        $incentiveId = $request->incentive_id;
        $incentive_Ids = IncentivesData::select('id', 'customer_id', 'customer_name', 'route_id', 'status', 'incentive_total', 'created_at')
                                        ->when($route_id != "All", function ($query) use ($route_id) {
                                            $query->where('route_id', $route_id);
                                        })
                                        ->when($customerId != "All", function ($query) use ($customerId) {
                                            $query->where('customer_id', $customerId);
                                        })
                                        ->whereBetween('created_at', [$fromDate . " 00:00:00", $toDate . " 23:59:59"])
                                        ->pluck('id'); // Pluck only the 'id' field
        $incentive_data = IncentivesData::where('id', $incentiveId)->with('route:id,name')->first();
        return response()->json([
        // return view('transactions.incentives.view_incentive_old',[
            "fromDate"       => $fromDate,
            "toDate"         => $toDate,
            "routeId"        => $route_id,
            "customerId"     => $customerId,
            'incentiveId'    => $incentiveId,
            "incentive_Ids"  => $incentive_Ids,
            'incentive_data' => $incentive_data
        ]);
    }

    public function approveIncentive()
    {
        $incentive_data = IncentivesData::where('status', "Pending")
                                        ->orderBy('created_at',"desc")
                                        ->with('route:id,name')
                                        ->get();
        return response()->json([
        // return view('transactions.incentives.approve_incentives',[        
            "incentives" => $incentive_data,           
        ]);
    }

    public function incentiveStatusUpdate(Request $request)
    {
        $incentive = IncentivesData::find($request->id);
        $incentive->status = $request->status;
        $incentive->save();
        return response()->json([
            "message" => "Incentive status updated successfully"
        ]);
    }
}
