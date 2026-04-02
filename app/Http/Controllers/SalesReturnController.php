<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;
use App\Models\Orders\Order;
use App\Models\Orders\OrderItem;
use App\Models\Orders\OrderDispatch;
use App\Models\Orders\SalesInvoice;
use App\Models\Orders\SalesInvoiceItem;
use App\Models\Orders\TaxInvoice;
use App\Models\Orders\TaxInvoiceItem;
use App\Models\Orders\BulkMilkOrder;
use App\Models\Orders\BulkMilkOrderItem;
use App\Models\Orders\JobWork;
use App\Models\Orders\JobWorkItem;
use App\Models\Orders\SalesReturn;
use App\Models\Places\MRoute;
use App\Models\Places\Address;
use App\Models\Profiles\Customer;
use App\Models\Profiles\Employee;
use App\Models\Products\Product;
use App\Models\Products\UOM;
use App\Models\Products\ProductUnit;
use App\Models\Masters\Setting;
use App\Models\Transport\Vehicle;
use App\Models\Production\StockEntry;
use App\Services\StockService;
use App\Enums\StockAction;
use App\Http\Traits\SalesUtility;

class SalesReturnController extends Controller
{
    use SalesUtility;
    
    public function __construct() 
    {
        $this->middleware('auth');
    }

    public function indexSalesReturns(Request $request)
    {                
        $salesReturns = SalesReturn::select('id','txn_id','route_id','customer_id','invoice_num','net_amt')
                                    ->with('route:id,name')
                                    ->with('customer:id,customer_name')
                                    ->where('txn_date',date('Y-m-d'))
                                    ->get();

        // return response()->json([
        return view('transactions.sales-return.list_sales_return', [            
            'sales_returns' => $salesReturns
        ]);
    }

    public function createSalesReturn()
    {
        $routes = MRoute::select('id','name')->orderBy('name')->get();
        $customers = Customer::select('id','customer_name')                            
                            ->where('status','Active')
                            ->orderBy('customer_name')
                            ->get();
        $units = UOM::select('id','unit_name','display_name','hot_key')->get();
        $salesInvoiceFormat = Setting::where('key', 'sales-invoice')->value('value');
        $taxInvoiceFormat = Setting::where('key', 'tax-invoice')->value('value');

        // return response()->json([
        return view('transactions.sales-return.manage_sales_return', [
            'routes'       => $routes,
            'customers'    => $customers,
            'units'        => $units,
            'salInvFormat' => $salesInvoiceFormat,
            'taxInvFormat' => $taxInvoiceFormat
        ]);
    }
    
    public function storeSalesReturn(Request $request, StockService $stockService)
    {           
        try {
            $customer = Customer::select('id', 'route_id')->where('id', $request->cust_id)->first();
            $salesReturn = new SalesReturn();
            $salesReturn->txn_id       = $this->getSalesReturnTxnId();
            $salesReturn->txn_date     = date('Y-m-d');
            $salesReturn->route_id     = $customer->route_id;
            $salesReturn->customer_id  = $request->cust_id;
            $salesReturn->invoice_num  = $request->invoice_num;
            $salesReturn->invoice_type = $request->invoice_type;
            $salesReturn->return_data  = $request->return_data;
            $salesReturn->amount       = $request->amount;
            if($request->invoice_type == "Tax") {
                $salesReturn->tax_amt   = $request->tax_amt;
                $salesReturn->total_amt = $request->total_amt;
            }
            $salesReturn->round_off    = $request->round_off;
            $salesReturn->net_amt      = $request->net_amt;
            $salesReturn->action       = $request->action;            
            $salesReturn->save();

            // Stock Update
            // foreach($request->stockData as $item) {
            //     $currentStock = StockEntry::where('product_id', $item['product_id'])
            //               ->latest('created_at')
            //               ->first();
            //     $primaryQty = $this->convertToPrimary($item['product_id'], $item['qty'], $item['unit']);
            //     $currentStock->total_stock_qty += $primaryQty['prim_qty'];
            //     $currentStock->save();  
            // }
            $stockItems = [];
            foreach($request->stockData as $item) {
                $stockItems[] = [
                    'item_id' => $item['product_id'],
                    'qty'     => $item['qty'],
                    'unit_id' => $item['unit'],
                ];
            }
            $stockService->updateCurrentStock(StockAction::RETURN, $stockItems);
            
            return response()->json([
                'success' => true, 
                'message' => "Sales Return Saved Successfully!"
            ]);
        }
        catch(QueryException $exception) {            
            return $exception;
        }
    }

    public function showSalesReturn(Request $request)
    {
        $txnId = $request->input('txn_id');
        $idList = $request->input('id_list');

        $salesReturn = SalesReturn::select('id','txn_date','route_id','customer_id','invoice_num','invoice_type','return_data','amount','tax_amt','total_amt','round_off','net_amt','action')
                                    ->where('txn_id',$txnId)
                                    ->with('route:id,name')
                                    ->with('customer:id,customer_name')
                                    ->first();

        // $salesReturn->return_data = json_decode($salesReturn->return_data);
        $invoiceData = $this->getInvoiceItemData($salesReturn->invoice_num);
        $units = UOM::select('id','unit_name','display_name','hot_key')->get();

        // return response()->json([
        return view('transactions.sales-return.view_sales_return', [
            'txn_id'       => $txnId,
            'invoiceItems' => $invoiceData,
            'returnItems'  => $salesReturn,
            'units'        => $units,
            'id_list'      => $idList
        ]);
    }

    public function getSalesReturn(Request $request)
    {
        $txnId = $request->input('txn_id');

        $salesReturn = SalesReturn::select('id','txn_date','route_id','customer_id','invoice_num','invoice_type','return_data','amount','tax_amt','total_amt','round_off','net_amt','action')
                                    ->where('txn_id',$txnId)
                                    ->with('route:id,name')
                                    ->with('customer:id,customer_name')
                                    ->first();

        $salesReturn->txn_date = displayDate($salesReturn->txn_date);
        $salesReturn->return_data = json_decode($salesReturn->return_data);
        $invoiceData = $this->getInvoiceItemData($salesReturn->invoice_num);

        return response()->json([
            'txn_id'       => $txnId,
            'invoiceItems' => $invoiceData,
            'returnItems'  => $salesReturn            
        ]);
    }

    public function getInvoices($cust_id)
    {
        $oneYearAgo = now()->subYear();
        $yesterday = now()->subDay();
        $today = now();

        // Combine recent invoices query
        $recentSalesInvoices = SalesInvoice::select('invoice_num')
            ->where('customer_id', $cust_id)
            ->where(function($query) use ($today, $yesterday) {
                $query->whereDate('invoice_date', $today)
                    ->orWhereDate('invoice_date', $yesterday);
            })
            ->get();

        $recentTaxInvoices = TaxInvoice::select('invoice_num')
            ->where('customer_id', $cust_id)
            ->where(function($query) use ($today, $yesterday) {
                $query->whereDate('invoice_date', $today)
                    ->orWhereDate('invoice_date', $yesterday);
            })
            ->get();

        $recentInvoices = $recentSalesInvoices->concat($recentTaxInvoices)->pluck('invoice_num');

        // Combine year-long invoices query
        $yearSalesInvoices = SalesInvoice::select('invoice_num')
            ->where('customer_id', $cust_id)
            ->whereBetween('invoice_date', [$oneYearAgo, $today])
            ->get();

        $yearTaxInvoices = TaxInvoice::select('invoice_num')
            ->where('customer_id', $cust_id)
            ->whereBetween('invoice_date', [$oneYearAgo, $today])
            ->get();

        $invoices = $yearSalesInvoices->concat($yearTaxInvoices)->pluck('invoice_num');

        return response()->json([
            'recentInvoices' => $recentInvoices,
            'invoices' => $invoices
        ]);
    }

    public function getInvoiceItems($inv_num)
    {
        $data = $this->getInvoiceItemData($inv_num);

        $salesReturn = SalesReturn::select('id','return_data','amount','tax_amt','total_amt','round_off','net_amt','action')
                                  ->where('invoice_num', $inv_num)
                                  ->first();
        if($salesReturn)
            $salesReturn->return_data = json_decode($salesReturn->return_data);

        return response()->json([
            'invoiceType' => $data["invoiceType"],
            'invoiceDate' => $data["invoiceDate"],
            'invoiceItems'  => $data["invoiceItems"],
            'returnItems' => $salesReturn
        ]);
    }

    private function getSalesReturnTxnId()
    {
        $lastRecord = SalesReturn::latest('id')->first();
        if ($lastRecord) {
            $lastRecordNumber = substr($lastRecord->txn_id, 3);        
            $newRecordNumber = sprintf('%03d', intval($lastRecordNumber) + 1);
            $salesReturnNum = 'SR-' . $newRecordNumber;
        }
        else {            
            $salesReturnNum = 'SR-001';
        }
        return $salesReturnNum;
    }

    private function getInvoiceItemData($invoiceNum)
    {
        $invoiceType = $invoiceDate = "";
        $invoiceItems = [];
        $orderItems = collect();

        $salesInvoice = SalesInvoice::select('order_num', 'invoice_date')->where('invoice_num', $invoiceNum)->first();
        if ($salesInvoice) {
            $invoiceType = "Sales";
            $invoiceDate = displayDate($salesInvoice->invoice_date);
            $orderItems = OrderItem::select('item_category', 'product_id', 'product_name', 'qty', 'unit_id', 'unit_name')
                                ->where('order_num', $salesInvoice->order_num)
                                ->get();
            $invoiceItems = $this->fetchInvoiceItems($salesInvoice->order_num, $invoiceNum, 'Sales');
        } 
        else {
            $taxInvoice = TaxInvoice::select('order_num', 'invoice_date')->where('invoice_num', $invoiceNum)->first();
            if ($taxInvoice) {
                $invoiceType = "Tax";
                $invoiceDate = displayDate($taxInvoice->invoice_date);
                $orderItems = OrderItem::select('item_category', 'product_id', 'product_name', 'qty', 'unit_id', 'unit_name')
                                    ->where('order_num', $taxInvoice->order_num)
                                    ->get();
                $invoiceItems = $this->fetchInvoiceItems($taxInvoice->order_num, $invoiceNum, 'Tax');
            }
        }

        return [
            "invoiceType" => $invoiceType,
            "invoiceDate" => $invoiceDate,
            "invoiceItems" => $invoiceItems
        ];
    }

    private function fetchInvoiceItems($orderNum, $invoiceNum, $type)
    {
        $items = $type === 'Sales'
            ? SalesInvoiceItem::select('product_id', 'product_name', 'item_category', 'qty', 'amount')
                ->where('invoice_num', $invoiceNum)
                ->get()
            : TaxInvoiceItem::select('product_id', 'product_name', 'item_category', 'qty', 'amount', 'tax_amt', 'tot_amt')
                ->where('invoice_num', $invoiceNum)
                ->get();

        $orderItems = OrderItem::select('item_category', 'product_id', 'product_name', 'qty', 'unit_id', 'unit_name')
            ->where('order_num', $orderNum)
            ->get();

        return $items->map(function ($item) use ($orderItems) {
            $orderItem = $orderItems->first(function ($orderItem) use ($item) {
                return $orderItem->product_id == $item->product_id
                    && $orderItem->item_category == $item->item_category;
            });

            $units = ProductUnit::select('unit_id', 'conversion')
                ->where('product_id', $item->product_id)
                ->orderByDesc('prim_unit')
                ->get();

            return [
                'category'       => $orderItem->item_category,
                'product_id'     => $orderItem->product_id,
                'product_name'   => $orderItem->product_name,
                'order_qty'      => $orderItem->qty,
                'order_unit'     => $orderItem->unit_id,
                'support_units'  => $units,
                'prim_qty'       => $item->qty,
                'price'          => formatPrice($item->amount / $item->qty),
                'amount'         => $item->amount,
                'tax_amt'        => $item->tax_amt ?? null,
                'tot_amt'        => $item->tot_amt ?? null,
            ];
        });
    }           
}