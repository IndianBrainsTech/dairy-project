<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
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
use App\Http\Traits\CustomerUtility;
use App\Http\Traits\SalesUtility;
use App\Services\StockService;
use App\Enums\StockAction;

class InvoiceController extends Controller
{
    use CustomerUtility;
    use SalesUtility;
    private int $driverRoleId;
 
    public function __construct()
    {
        $this->middleware('auth');
        $this->driverRoleId = config('constants.ROLE_DRIVER_ID');
    }

    public function indexInvoices(Request $request)
    {
        $from_date     = $request->input('from_date', date('Y-m-d'));
        $to_date       = $request->input('to_date', $from_date);
        $route_id      = $request->input('route_id', 0);
        $route_name    = $request->input('route_name', '');
        $customer_id   = $request->input('customer_id', 0);
        $customer_name = $request->input('customer_name', '');
        $invoice_type  = $request->input('invoice_type', 'Sales & Tax');
        $print_filter  = $request->input('print_filter', 'All');

        $orders = Order::select('id','order_num','invoice_date','customer_id','route_id')
            ->with('customer:id,customer_name')
            ->with('route:id,name')
            ->whereBetween('invoice_date',[$from_date, $to_date])
            ->where('invoice_status','Generated')
            ->when($route_id != 0, fn($query) => $query->where('route_id', $route_id))
            ->when($customer_id != 0, fn($query) => $query->where('customer_id', $customer_id))
            ->get();

        if ($invoice_type == "Sales") {
            foreach ($orders as $order)
                $order->sales_invoice = $this->getInvoiceData(SalesInvoice::class, $order->order_num, $print_filter);
        }
        elseif ($invoice_type == "Tax") {
            foreach ($orders as $order)
                $order->tax_invoice = $this->getInvoiceData(TaxInvoice::class, $order->order_num, $print_filter);
        } 
        else {
            foreach ($orders as $order) {
                $order->sales_invoice = $this->getInvoiceData(SalesInvoice::class, $order->order_num, $print_filter);
                $order->tax_invoice = $this->getInvoiceData(TaxInvoice::class, $order->order_num, $print_filter);
            }
        }

        // Filter Records (Clear Empty Records [Records have no invoice number])
        $orders = $orders->filter(function ($order) use ($invoice_type) {
            return ($invoice_type == "Sales" && $order->sales_invoice?->invoice_num !== null) ||
                ($invoice_type == "Tax" && $order->tax_invoice?->invoice_num !== null) ||
                ($invoice_type == "Sales & Tax" && ($order->sales_invoice?->invoice_num !== null || $order->tax_invoice?->invoice_num !== null));
        })->values();

        // return response()->json([
        return view('transactions.invoices.list_invoices', [
            'dates'        => ['from' => $from_date, 'to' => $to_date],
            'route'        => ['id' => $route_id, 'name' => $route_name],
            'customer'     => ['id' => $customer_id, 'name' => $customer_name],
            'invoice_type' => $invoice_type, 
            'print_filter' => $print_filter,
            'orders'       => $orders,
        ]);
    }

    public function createInvoices()
    {
        $routes = MRoute::select('id','name')->get();
        $vehicles = Vehicle::where('status','Active')->pluck('vehicle_number')->toArray();

        $drivers = Employee::select('id','name','mobile_num')
                            ->where('role_id', $this->driverRoleId)
                            ->where('status','Active')
                            ->orderBy('name')
                            ->get();

        // return response()->json([
        return view('transactions.invoices.make_invoices', [
            'date'     => date('Y-m-d'), 
            'routes'   => $routes,
            'vehicles' => $vehicles,
            'drivers'  => $drivers
        ]);
    }

    public function showInvoice(Request $request)
    {        
        $orders = $request->input('orders');
        $type = $request->input('type');
        $orderNum = $request->input('order_num');
        $salesInvoice = [];
        $taxInvoice = [];

        if($type != "Tax") {
            $invoice = SalesInvoice::where('order_num', $orderNum)->first();
            if ($invoice) {
                $salesInvoice['invoice'] = $invoice;
                $salesInvoice['invoiceItems'] = SalesInvoiceItem::select('id', 'product_name', 'item_category', 'hsn_code', 'crates', 'qty', 'amount')->where('invoice_num', $invoice->invoice_num)->get();
                $this->appendAddressData($salesInvoice['invoice'], $orderNum);
            }
        }

        if($type != "Sales") {
            $invoice = TaxInvoice::where('order_num', $orderNum)->first();
            if ($invoice) {
                $taxInvoice['invoice'] = $invoice;
                $taxInvoice['invoiceItems'] = TaxInvoiceItem::select('id', 'product_name', 'item_category', 'hsn_code', 'crates', 'qty', 'amount', 'tax_amt', 'tot_amt', 'gst', 'sgst', 'cgst', 'igst')->where('invoice_num', $invoice->invoice_num)->get();                
                $taxInvoice['gstTable'] = $this->getGstTable($taxInvoice['invoiceItems']);
                $this->appendAddressData($taxInvoice['invoice'], $orderNum);
            }
        }

        // return response()->json([
        return view('transactions.invoices.view_invoice', [
            'sales_invoice' => $salesInvoice,
            'tax_invoice' => $taxInvoice,
            'orders' => $orders,
            'type' => $type
        ]);
    }

    public function printInvoices(Request $request)
    {
        $order_nums = $request->input('order_nums');
        $invoice_type = $request->input('invoice_type');

        $invoices = [];
        $sales_invoice_nums = [];
        $tax_invoice_nums = [];
        foreach($order_nums as $order_num) {
            $sales_invoice = [];
            $tax_invoice = [];
    
            if($invoice_type != "Tax") {
                $invoice = SalesInvoice::where('order_num', $order_num)->get();
                if(count($invoice)>0) {
                    $sales_invoice['invoice'] = $invoice->first();
                    $sales_invoice['invoiceItems'] = SalesInvoiceItem::select('id','product_name','item_category','hsn_code','crates','qty','amount')->where('invoice_num',$invoice[0]->invoice_num)->get();
                    $this->appendAddressData($sales_invoice['invoice'], $order_num);
                    $sales_invoice_nums[] = $sales_invoice['invoice']->invoice_num;
                }
            }
    
            if($invoice_type != "Sales") {
                $invoice = TaxInvoice::where('order_num', $order_num)->get();
                if(count($invoice)>0) {
                    $tax_invoice['invoice'] = $invoice->first();
                    $tax_invoice['invoiceItems'] = TaxInvoiceItem::select('id','product_name','item_category','hsn_code','crates','qty','amount','tax_amt','tot_amt','gst','sgst','cgst','igst')->where('invoice_num',$invoice[0]->invoice_num)->get();
                    $tax_invoice['gstTable'] = $this->getGstTable($tax_invoice['invoiceItems']);
                    $this->appendAddressData($tax_invoice['invoice'], $order_num);
                    $tax_invoice_nums[] = $tax_invoice['invoice']->invoice_num;
                }
            }

            $invoice = [];
            $invoice['order_number']  = $order_num;
            $invoice['sales_invoice'] = $sales_invoice;
            $invoice['tax_invoice']   = $tax_invoice;
            array_push($invoices, $invoice);
        }

        // Update is_printed status in Invoices 
        if($sales_invoice_nums) {
            SalesInvoice::whereIn('invoice_num', $sales_invoice_nums)
                ->where('is_printed',false)
                ->update(['is_printed' => true]);
        }

        if($tax_invoice_nums) {
            TaxInvoice::whereIn('invoice_num', $tax_invoice_nums)
                ->where('is_printed',false)
                ->update(['is_printed' => true]);
        }

        // return response()->json(['invoices' => $invoices]);
        return view('transactions.invoices.print_invoice', [
            'invoices' => $invoices
        ])->render();
    }

    public function getOrdersForInvoices(Request $request)
    {        
        $route = MRoute::where('id',$request->route_id)->value('name');

        $orders = Order::select('id','invoice_date','order_num','customer_id','invoice_status')
                        ->with('customer:id,customer_name')
                        ->where('invoice_date',$request->inv_date)
                        ->where('route_id',$request->route_id)
                        ->orderByRaw("FIELD(invoice_status, 'Not Generated', 'Cancelled', 'Generated')")
                        ->get();

        foreach($orders as $order) {
            $order->invoice_date = displayDate($order->invoice_date);
        }

        $orderDispatch = OrderDispatch::select('id','vehicle_number','driver_name','mobile_num')
                                       ->where('invoice_date',$request->inv_date)
                                       ->where('route_id',$request->route_id)
                                       ->first();

        return response()->json([
            'route'    => $route,
            'orders'   => $orders,
            'dispatch' => $orderDispatch
        ]);
    }

    public function buildInvoices(Request $request)
    {
        try {
            $invdate    = $request->invoice_date;
            $routeId    = $request->route_id;
            $vehicleNum = $request->vehicle_num;
            $driverName = $request->driver_name;
            $mobileNum  = $request->mobile_num;

            $routeName  = MRoute::where('id',$routeId)->value('name');
            $vehicleId  = Vehicle::where('vehicle_number',$vehicleNum)->value('id');
            $driverId   = Employee::where('role_id', $this->driverRoleId)->where('name',$driverName)->value('id');

            $orders = Order::select('id','order_num','invoice_status')
                            ->where('invoice_date',$invdate)
                            ->where('route_id',$routeId)
                            ->where('invoice_status','Not Generated')
                            ->get();

            $orderDispatch = new OrderDispatch();
            $orderDispatch->invoice_date   = $invdate;
            $orderDispatch->route_id       = $routeId;
            $orderDispatch->route_name     = $routeName;
            $orderDispatch->vehicle_id     = $vehicleId;
            $orderDispatch->vehicle_number = $vehicleNum;
            $orderDispatch->driver_id      = $driverId;
            $orderDispatch->driver_name    = $driverName;
            $orderDispatch->mobile_num     = $mobileNum;
            $orderDispatch->order_nums     = $orders->pluck('order_num');
            $orderDispatch->save();

            $dispatchData = [
                'vehicle_num' => $vehicleNum,
                'driver_name' => $driverName
            ];

            $userId = auth()->id();
            $now = now();
            foreach($orders as $order) {
                $this->generateInvoices($order->order_num, $dispatchData);
                $order->invoice_status = 'Generated';
                $order->actioned_by    = $userId;
                $order->actioned_at    = $now;
                $order->save();
            }
            
            return response()->json([
                'success' => true, 
                'message' => "Invoices Generated Successfully!"
            ]);
        }    
        catch(QueryException $exception) {
            //dd($exception);
            return $exception;
        }
    }

    public function loadInvoicesForCancel()
    {
        $fromDate = date('Y-m-d', strtotime('-1 day'));
        $toDate = getTomorrow();    

        $orders = Order::select('id','order_num','invoice_date','customer_id','route_id','invoice_status')                                
                                ->with('customer:id,customer_name')
                                ->with('route:id,name')
                                ->whereBetween('invoice_date',[$fromDate,$toDate])
                                ->where('invoice_status','Generated')
                                ->get();        

        foreach($orders as $order) {
            $order->sales_inv_num = SalesInvoice::where('order_num', $order->order_num)->where('invoice_status','Generated')->value('invoice_num') ?? '';
            $order->tax_inv_num = TaxInvoice::where('order_num', $order->order_num)->where('invoice_status','Generated')->value('invoice_num') ?? '';
        }

        // Filter Records (Clear Empty Records [Records have no invoice number])
        $orders = $orders->filter(function ($order) {
            return $order->sales_inv_num !== "" || $order->tax_inv_num !== "";
        })->values();        

        // return response()->json([
        return view('transactions.invoices.list_invoice_cancel', [
            'orders' => $orders            
        ]);
    }

    public function showInvoiceForCancel(Request $request)
    {
        $orderNum = $request->order_num;
        $salesInvoice = [];
        $taxInvoice = [];

        $invoice = SalesInvoice::where('order_num', $orderNum)->where('invoice_status','Generated')->first();
        if ($invoice) {
            $salesInvoice['invoice'] = $invoice;
            $salesInvoice['invoiceItems'] = SalesInvoiceItem::select('id', 'product_name', 'item_category', 'hsn_code', 'crates', 'qty', 'amount')->where('invoice_num', $invoice->invoice_num)->get();
            $this->appendAddressData($salesInvoice['invoice'], $orderNum);
        }

        $invoice = TaxInvoice::where('order_num', $orderNum)->where('invoice_status','Generated')->first();
        if ($invoice) {
            $taxInvoice['invoice'] = $invoice;
            $taxInvoice['invoiceItems'] = TaxInvoiceItem::select('id', 'product_name', 'item_category', 'hsn_code', 'crates', 'qty', 'amount', 'tax_amt', 'tot_amt', 'gst', 'sgst', 'cgst', 'igst')->where('invoice_num', $invoice->invoice_num)->get();                
            $taxInvoice['gstTable'] = $this->getGstTable($taxInvoice['invoiceItems']);
            $this->appendAddressData($taxInvoice['invoice'], $orderNum);
        }

        // return response()->json([
        return view('transactions.invoices.cancel_invoice', [
            'sales_invoice' => $salesInvoice,
            'tax_invoice' => $taxInvoice,
        ]);
    }

    public function cancelInvoice(Request $request, StockService $stockService)
    {        
        $invoiceData = $request->invoice_data;
        $remarks = $request->remarks;        

        foreach($invoiceData as $data) {
            if($data['type'] == "SALES") {
                SalesInvoice::where('invoice_num', $data['invoice_num'])
                    ->update([
                        'invoice_status' => 'Cancelled', 
                        'cancel_remarks' => $remarks,
                    ]);
                // Stock Update
                // $invoiceItem = SalesInvoiceItem::where('invoice_num', $data['invoice_num'])->get();
                // foreach($invoiceItem as $item) {
                //     $currentStock = StockEntry::where('product_id', $item->product_id)
                //                     ->latest('created_at')
                //                     ->first();
                //     $currentStock->total_stock_qty += $item->qty;
                //     $currentStock->save();
                // }
                $this->createSalesReturn("Sales", $data['invoice_num'], $stockService);
                // $invoiceItems = SalesInvoiceItem::where('invoice_num', $data['invoice_num'])->get();
                // $stockItems = [];
                // foreach($invoiceItems as $item) {
                //     $stockItems[] = [
                //         'item_id' => $item->product_id,
                //         'qty'     => $item->qty,                        
                //     ];
                // }
                // $stockService->updateCurrentStock(StockAction::RETURN, $stockItems);
            }
            else if($data['type'] == "TAX") {
                TaxInvoice::where('invoice_num', $data['invoice_num'])
                    ->update([
                        'invoice_status' => 'Cancelled', 
                        'cancel_remarks' => $remarks,
                    ]);
                // Stock Update
                // $invoiceItem = TaxInvoiceItem::where('invoice_num',$data['invoice_num'])->get();
                // foreach($invoiceItem as $item) {
                //     $currentStock = StockEntry::where('product_id',$item->product_id)
                //                     ->latest('created_at')
                //                     ->first();
                //     $currentStock->total_stock_qty += $item->qty;
                //     $currentStock->save();
                // }
                $invoiceItems = TaxInvoiceItem::where('invoice_num', $data['invoice_num'])->get();
                $stockItems = [];
                foreach($invoiceItems as $item) {
                    $stockItems[] = [
                        'item_id' => $item->product_id,
                        'qty'     => $item->qty,                        
                    ];
                }
                $stockService->updateCurrentStock(StockAction::RETURN, $stockItems);
                //
            }
        }

        return response()->json([
            'success' => true,
            'message' => "Invoice Cancelled Successfully!"
        ]);
    }

    public function indexBulkMilkInvoices(Request $request)
    {
        $fromDate = $request->fromDate ?? date('Y-m-d');
        $toDate = $request->toDate ?? date('Y-m-d');
        $customerId = $request->customerId ?? 0;
        $customer = $request->customer ?? "";

        $customers = Customer::where('status','Active')
            ->orderBy('customer_name')
            ->get(['id','customer_name']);

        $invoices = BulkMilkOrder::select('id','invoice_num','invoice_date','customer_id','customer_name','net_amt')
            ->whereBetween('invoice_date', [$fromDate, $toDate])
            ->where('invoice_status','Generated')
            ->when($customerId <> 0, function($query) use($customerId) { return $query->where('customer_id', $customerId); })
            ->get();

        // return response()->json([
        return view('transactions.invoices.list_bulk_milk_invoices', [
            'fromDate'   => $fromDate,
            'toDate'     => $toDate,
            'customerId' => $customerId,
            'customer'   => $customer,
            'customers'  => $customers,
            'invoices'   => $invoices
        ]);
    }

    public function createBulkMilkInvoices()
    {
        $orders = BulkMilkOrder::select('id','invoice_num','invoice_date','customer_id','customer_name','net_amt','invoice_status')
            ->where('invoice_status','Not Generated')
            ->where('net_amt','<>',0)
            ->get();

        // return response()->json([
        return view('transactions.invoices.make_bulk_milk_invoices', [
            'orders' => $orders
        ]);
    }

    public function buildBulkMilkInvoice(Request $request)
    {
        BulkMilkOrder::where('invoice_num', $request->invoice_num)
            ->update(['invoice_status' => 'Generated']);

        return response()->json([
            'success' => true,
            'message' => "Invoice Generated Successfully!",
        ]);
    }

    public function showBulkMilkInvoice(Request $request)
    {
        $invoiceNums = $request->input('invoice_nums');
        $invoiceNum = $request->input('invoice_num');
        $invoice = BulkMilkOrder::where('invoice_num', $invoiceNum)->first();
        $invoiceItems = BulkMilkOrderItem::where('invoice_num', $invoice->invoice_num)->get();
        $invoice->customer_data = json_decode($invoice->customer_data);

        // return response()->json([
        return view('transactions.invoices.view_bulk_milk_invoice', [
            'invoice' => $invoice,
            'invoice_items' => $invoiceItems,
            'invoice_nums' => $invoiceNums
        ]);
    }

    public function printBulkMilkInvoice(Request $request)
    {
        $invoiceNum = $request->input('invoice_num');
        $invoice = BulkMilkOrder::where('invoice_num', $invoiceNum)->first();
        $invoiceItems = BulkMilkOrderItem::where('invoice_num', $invoice->invoice_num)->get();
        $invoice->customer_data = json_decode($invoice->customer_data);

        return view('transactions.invoices.view_bulk_milk_invoice', [
            'invoice' => $invoice,
            'invoice_items' => $invoiceItems,
            'invoice_nums' => $invoiceNum
        ])->render();
    }

    private function getInvoiceData($model, $orderNum, $printFilter)
    {
        $invoice = $model::where('order_num', $orderNum)
            ->where('invoice_status', 'Generated')
            ->when($printFilter === 'Unprinted', fn ($query) => $query->where('is_printed', 0))
            ->when($printFilter === 'Printed',   fn ($query) => $query->where('is_printed', 1))
            ->first(['invoice_num', 'is_printed']);
        return $invoice;
    }

    private function generateInvoices($orderNum, $dispatchData)
    {
        $order = Order::select('order_num','customer_id','route_id','invoice_date','sales_disc','tax_disc','created_by','edited_by','created_at','edited_at')
                            ->with('customer:id,customer_name,contact_num,gst_type,gst_number,pan_number,tcs_status')
                            ->with('route:id,name')
                            ->where('order_num',$orderNum)
                            ->first();

        $orderItems = OrderItem::select('product_id','item_category','product_name','qty','unit_name','unit_id')
                            ->where('order_num',$orderNum)
                            ->get();

        // $priceList = $this->getPriceList($order->customer_id);
        $priceList = $this->getPriceList2($order->customer_id);
        $gstType = $order->customer->gst_type;

        foreach($orderItems as $orderItem) {
            $product = Product::select('id','hsn_code','tax_type','gst','sgst','cgst','igst')
                                    ->where('id',$orderItem->product_id)
                                    ->first();
            $orderItem->hsn_code = $product->hsn_code;
            $orderItem->tax_type = $product->tax_type;

            $productUnits = ProductUnit::select('product_id','unit_id','price','prim_unit','conversion')
                                        ->where('product_id',$orderItem->product_id)
                                        ->orderByDesc('prim_unit')
                                        ->get();

            // Update Qty, Unit, Price as Primary Values
            $orderItem->prim_qty = $orderItem->qty;
            foreach($productUnits as $productUnit) {                
                if($productUnit->prim_unit == 1 ) {                    
                    $orderItem->prim_unit = UOM::where('id', $productUnit->unit_id)->value('display_name');
                    $orderItem->prim_price = $productUnit->price;
                }
                else if($orderItem->unit_id == $productUnit->unit_id) {
                    $orderItem->prim_qty = $orderItem->qty * $productUnit->conversion;
                }
            }
            
            // Crates Count
            $orderItem->crate_cnt = 0;
            foreach($productUnits as $productUnit) {
                if($productUnit->unit_id == config('constants.UNIT_CRATE_ID')) {
                    if($productUnit->conversion) {
                        // $orderItem->crate_cnt = ceilToNearestQuarter($orderItem->prim_qty / $productUnit->conversion);
                        $orderItem->crate_cnt = getTwoDigitPrecision($orderItem->prim_qty / $productUnit->conversion);
                        break;
                    }
                }
            }

            if($orderItem->item_category == "Regular") {
                // Update Price if it associate with any price master
                if($priceList) {                    
                    // Check if the product ID exists in the price list
                    if(array_key_exists(strval($orderItem->product_id), $priceList)) {
                        // Retrieve the price corresponding to the product ID and assign it
                        $orderItem->prim_price = $priceList[$orderItem->product_id];
                    }
                }                
                $orderItem->amount = $orderItem->prim_qty * $orderItem->prim_price;

                if($orderItem->tax_type == "Taxable") {
                    $orderItem->tax_amt = $orderItem->amount * $product->gst / 100;
                    $orderItem->tot_amt = $orderItem->amount + $orderItem->tax_amt;
                    $orderItem->gst     = $product->gst;
                    if(str_starts_with($gstType,'Intrastate')) {
                        $orderItem->sgst    = $product->sgst;
                        $orderItem->cgst    = $product->cgst;
                        $orderItem->igst    = NULL;
                    }
                    else if(str_starts_with($gstType,'Interstate')) {
                        $orderItem->igst    = $product->igst;
                        $orderItem->sgst    = NULL;
                        $orderItem->cgst    = NULL;
                    }
                    $orderItem->tax_amt = round($orderItem->tax_amt, 2);
                    $orderItem->tot_amt = round($orderItem->tot_amt, 2);
                }                
            }
            else { // Damage, Spoilage, Sample
                $orderItem->amount = 0;
                if($orderItem->tax_type == "Taxable") {
                    $orderItem->tax_amt = 0;
                    $orderItem->tot_amt = 0;
                    $orderItem->gst     = $product->gst;
                    $orderItem->sgst    = NULL;
                    $orderItem->cgst    = NULL;
                    $orderItem->igst    = NULL;
                }
            }
            
            $orderItem->crate_cnt = round($orderItem->crate_cnt, 2);
            $orderItem->prim_qty  = round($orderItem->prim_qty, 2);
            $orderItem->amount    = round($orderItem->amount, 2);
        }

        // return response()->json([
        //     'order' => $order,
        //     'orderItems' => $orderItems
        // ]);

        // Check if the collection contains items with tax_type as 'Taxable'
        $containsExempted = $orderItems->contains(function ($item) {
            return $item['tax_type'] === 'Exempted';
        });
        if($containsExempted)
            $this->generateSalesInvoice($order, $orderItems, $dispatchData);

        // Check if the collection contains items with tax_type as 'Taxable'
        $containsTaxable = $orderItems->where('tax_type', 'Taxable')->isNotEmpty();
        if($containsTaxable)
            $this->generateTaxInvoice($order, $orderItems, $dispatchData);                
    }

    private function generateSalesInvoice($order, $orderItems, $dispatchData)
    {
        $isExists = SalesInvoice::where('order_num',$order->order_num)->first();
        if($isExists)
            return;

        $total = array();
        $total['crates']    = 0;
        $total['qty']       = 0;
        $total['amount']    = 0;
        $total['round_off'] = 0;
        $total['net_amt']   = 0;

        foreach($orderItems as $orderItem) {
            if($orderItem->tax_type == "Exempted") {
                $total['crates']  += $orderItem->crate_cnt;
                $total['qty']     += $orderItem->prim_qty;
                $total['amount']  += $orderItem->amount; 
            }
        }

        $total['crates']    = round($total['crates'],2);
        $total['qty']       = round($total['qty'],2);
        $total['amount']    = round($total['amount'],2);

        // Calculate the tcs amount, round-off value and net amount
        $total_amt = floatval($total['amount']);
        $total_amt = round($total_amt,2);
        $total['tcs_amt'] = $this->getTcsAmount($order->customer, $total_amt);
        if($total['tcs_amt']) {
            $total['tcs_amt'] = round($total['tcs_amt'],2);
            $total_amt += $total['tcs_amt'];            
        }
        $discount = $order->sales_disc;
        if($discount) {
            $total_amt -= floatval($discount);
        }
        $total_amt = round($total_amt,2);
        $total['round_off'] = round($total_amt) - $total_amt;
        $total['net_amt']   = round($total_amt);        
        $total['round_off'] = round($total['round_off'],2);
        $total['net_amt']   = round($total['net_amt'],2);        

        $invoice = new SalesInvoice();
        $invoice->invoice_num   = $this->getReferenceNumber("sales-invoice");
        $invoice->invoice_date  = $order->invoice_date;
        $invoice->order_num     = $order->order_num;
        $invoice->order_dt      = $order->edited_at ?: $order->created_at;
        $invoice->customer_id   = $order->customer->id;
        $invoice->customer_name = $order->customer->customer_name;
        $invoice->mobile_num    = $order->customer->contact_num;
        $invoice->gst_number    = $order->customer->gst_number;
        $invoice->route_id      = $order->route->id;
        $invoice->route_name    = $order->route->name;
        $invoice->vehicle_num   = $dispatchData['vehicle_num'];
        $invoice->driver_name   = $dispatchData['driver_name'];
        $invoice->route_name    = $order->route->name;
        $invoice->item_count    = $orderItems->count();
        $invoice->crates        = $total['crates'];
        $invoice->qty           = $total['qty'];
        $invoice->amount        = $total['amount'];
        $invoice->tcs           = $total['tcs_amt'];
        $invoice->discount      = $discount;
        $invoice->round_off     = $total['round_off'];
        $invoice->net_amt       = $total['net_amt'];
        $invoice->ordered_by    = $order->edited_by ?: $order->created_by ?: null;
        $invoice->save();

        foreach($orderItems as $orderItem) {
            if($orderItem->tax_type == "Exempted") {
                $invoiceItem = new SalesInvoiceItem();
                $invoiceItem->invoice_num   = $invoice->invoice_num;
                $invoiceItem->product_id    = $orderItem->product_id;
                $invoiceItem->product_name  = $orderItem->product_name;
                $invoiceItem->item_category = $orderItem->item_category;
                $invoiceItem->hsn_code      = $orderItem->hsn_code;
                $invoiceItem->crates        = $orderItem->crate_cnt;
                $invoiceItem->qty           = $orderItem->prim_qty;
                $invoiceItem->amount        = $orderItem->amount;
                $invoiceItem->save();
            }
        }        
    }

    private function generateTaxInvoice($order, $orderItems, $dispatchData)
    {
        // $isExists = TaxInvoice::where('order_num',$order->order_num)->first();
        // if($isExists)
        //     return;

        $total = array();
        $total['crates']    = 0;
        $total['qty']       = 0;
        $total['amount']    = 0;
        $total['tax_amt']   = 0;
        $total['tot_amt']   = 0;
        $total['round_off'] = 0;
        $total['net_amt']   = 0;

        foreach($orderItems as $orderItem) {
            if($orderItem->tax_type == "Taxable") {
                $total['crates']  += $orderItem->crate_cnt;
                $total['qty']     += $orderItem->prim_qty;
                $total['amount']  += $orderItem->amount; 
                $total['tax_amt'] += $orderItem->tax_amt;
                $total['tot_amt'] += $orderItem->tot_amt;
            }
        }

        $total['crates']    = round($total['crates'],2);
        $total['qty']       = round($total['qty'],2);
        $total['amount']    = round($total['amount'],2);
        $total['tax_amt']   = round($total['tax_amt'],2);
        $total['tot_amt']   = round($total['tot_amt'],2);

        // Calculate the round-off value and net amount
        $total_amt = floatval($total['amount']) + floatval($total['tax_amt']);
        $total_amt = round($total_amt,2);
        $total['tcs_amt'] = $this->getTcsAmount($order->customer, $total_amt);
        if($total['tcs_amt']) {
            $total['tcs_amt'] = round($total['tcs_amt'],2);
            $total_amt += $total['tcs_amt'];
        }
        $discount = $order->tax_disc;
        if($discount) {
            $total_amt -= floatval($discount);
        }
        $total_amt = round($total_amt,2);
        $total['round_off'] = round($total_amt) - $total_amt;        
        $total['net_amt']   = round($total_amt);
        $total['round_off'] = round($total['round_off'],2);
        $total['net_amt']   = round($total['net_amt'],2);        

        $invoice = new TaxInvoice();
        $invoice->invoice_num   = $this->getReferenceNumber("tax-invoice");
        $invoice->invoice_date  = $order->invoice_date;
        $invoice->order_num     = $order->order_num;        
        $invoice->order_dt      = $order->edited_at ?: $order->created_at;
        $invoice->customer_id   = $order->customer->id;
        $invoice->customer_name = $order->customer->customer_name;
        $invoice->mobile_num    = $order->customer->contact_num;
        $invoice->gst_number    = $order->customer->gst_number;
        $invoice->route_id      = $order->route->id;
        $invoice->route_name    = $order->route->name;
        $invoice->vehicle_num   = $dispatchData['vehicle_num'];
        $invoice->driver_name   = $dispatchData['driver_name'];        
        $invoice->item_count    = $orderItems->count();
        $invoice->crates        = $total['crates'];
        $invoice->qty           = $total['qty'];
        $invoice->amount        = $total['amount'];
        $invoice->tax_amt       = $total['tax_amt'];        
        $invoice->tot_amt       = $total['tot_amt'];
        $invoice->tcs           = $total['tcs_amt'];
        $invoice->discount      = $discount;
        $invoice->round_off     = $total['round_off'];
        $invoice->net_amt       = $total['net_amt'];
        $invoice->ordered_by    = $order->updated_by ?: $order->created_by ?: null;
        $invoice->save();

        foreach($orderItems as $orderItem) {
            if($orderItem->tax_type == "Taxable") {
                $invoiceItem = new TaxInvoiceItem();
                $invoiceItem->invoice_num   = $invoice->invoice_num;
                $invoiceItem->product_id    = $orderItem->product_id;
                $invoiceItem->product_name  = $orderItem->product_name;
                $invoiceItem->item_category = $orderItem->item_category;
                $invoiceItem->hsn_code      = $orderItem->hsn_code;
                $invoiceItem->crates        = $orderItem->crate_cnt;
                $invoiceItem->qty           = $orderItem->prim_qty;
                $invoiceItem->amount        = $orderItem->amount;
                $invoiceItem->tax_amt       = $orderItem->tax_amt;
                $invoiceItem->tot_amt       = $orderItem->tot_amt;
                $invoiceItem->gst           = $orderItem->gst;
                $invoiceItem->sgst          = $orderItem->sgst;
                $invoiceItem->cgst          = $orderItem->cgst;
                $invoiceItem->igst          = $orderItem->igst;
                $invoiceItem->save();
            }
        }
    }

    private function getGstTable($invoiceItems)
    {
        // Group the items by 'gst'
        $groupedItems = $invoiceItems->groupBy('gst');

        // Map over each group to calculate the sums
        $summedItems = $groupedItems->map(function ($group) {
            $sum = $group->reduce(function ($carry, $item) {
                $carry['amount'] += $item['amount'];
                $carry['gst']  += $item['amount'] * $item['gst'] / 100;
                $carry['sgst'] += $item['amount'] * $item['sgst'] / 100;
                $carry['cgst'] += $item['amount'] * $item['cgst'] / 100;
                $carry['igst'] += $item['amount'] * $item['igst'] / 100;
                return $carry;
            }, [
                'amount' => 0,
                'gst' => 0,
                'sgst' => 0,
                'cgst' => 0,
                'igst' => 0,
            ]);

            return $sum;
        });

        // return $summedItems;

        // Generate GST Table
        $gstTable['rows'] = [];
        $totalAmount = 0;
        $totalGST = 0;
        $totalSGST = 0;
        $totalCGST = 0;
        $totalIGST = 0;

        foreach ($summedItems as $gst => $data) {
            $gstRow['gst_perc'] = $gst;
            $gstRow['amount']   = $data['amount'];
            $gstRow['gst']      = $data['gst'];
            $gstRow['sgst']     = $data['sgst'];
            $gstRow['cgst']     = $data['cgst'];
            $gstRow['igst']     = $data['igst'];
            array_push($gstTable['rows'], $gstRow);

            // Update totals
            $totalAmount += $data['amount'];
            $totalGST    += $data['gst'];
            $totalSGST   += $data['sgst'];
            $totalCGST   += $data['cgst'];
            $totalIGST   += $data['igst'];
        }

        // Add total row
        $totalRow['amount'] = $totalAmount;
        $totalRow['gst'] = $totalGST;
        $totalRow['sgst'] = $totalSGST;
        $totalRow['cgst'] = $totalCGST;
        $totalRow['igst'] = $totalIGST;

        $gstTable['total'] = $totalRow;

        // Return GST Table
        return $gstTable;
    }    
    
    private function appendAddressData(&$invoice, $orderNum)
    {
        $addressData = Order::where('order_num',$orderNum)->first('address_data');
        $addressData = json_decode($addressData['address_data'], true);
        $invoice->billing_address = $addressData[0]['billing_address'];
        $invoice->delivery_address = $addressData[0]['delivery_address'];
    }

    public function getPriceList2($custId) {        
        $priceListItems = $this->getPriceList($custId);
        
        $priceList = [];
        foreach ($priceListItems as $key => $value) {
            $priceList[$key] = $value;
        }
        return $priceList;

        // $priceList = [];
        // foreach ($priceListItems as $item) {
        //     $priceList[$item['product_id']] = $item['price'];
        // }
        // return response()->json([
        //     'priceList' => $priceListItems
        // ]);        
    }

    private function createSalesReturn(string $invoiceType, string $invoiceNumber, $stockService)
    {
        try {
            $productUnits = ProductUnit::where('prim_unit', 1)
                ->pluck('unit_id', 'product_id');

            [$invoice, $invoiceItems] = $this->getInvoiceAndItems($invoiceType, $invoiceNumber);

            $returnData = [];
            $stockItems = [];

            foreach ($invoiceItems as $item) {
                $data = [
                    'product_id' => $item->product_id,
                    'qty'        => $item->qty,
                    'unit'       => $productUnits[$item->product_id] ?? null,
                    'amount'     => $item->amount,
                    'remarks'    => '',
                ];

                if ($invoiceType === "Tax") {
                    $data['tax_amt'] = $item->tax_amt;
                    $data['net_amt'] = $item->tot_amt;
                }

                $returnData[] = $data;
                $stockItems[] = ['item_id' => $item->product_id, 'qty' => $item->qty];
            }

            $amount = $invoice->net_amt - $invoice->round_off;

            $record = [
                'txn_id'       => $this->getSalesReturnTxnId(),
                'txn_date'     => now()->toDateString(),
                'route_id'     => $invoice->route_id,
                'customer_id'  => $invoice->customer_id,
                'invoice_num'  => $invoiceNumber,
                'invoice_type' => $invoiceType,
                'return_data'  => $returnData,
                'amount'       => $amount,
                'round_off'    => $invoice->round_off,
                // 'net_amt'      => $invoice->net_amt,
                'net_amt'      => '0.00',
                'action'       => 'ReturnOrder',
            ];

            if ($invoiceType === "Tax") {
                $record['tax_amt']   = $invoice->tax_amt;
                $record['total_amt'] = $amount + $invoice->tax_amt;
            }

            \DB::transaction(function () use ($record, $stockItems, $stockService) {
                SalesReturn::create($record);
                $stockService->updateCurrentStock(StockAction::RETURN, $stockItems);
            });
        } catch (\Exception $ex) {
            // \Log::error("Sales return failed: {$ex->getMessage()}", ['exception' => $ex]);
            // throw $ex;
            return $ex;
        }
    }

    private function getInvoiceAndItems(string $invoiceType, string $invoiceNumber): array
    {
        if ($invoiceType === "Sales") {
            $invoice = SalesInvoice::select('id','route_id','customer_id','round_off','net_amt')
                ->where('invoice_num', $invoiceNumber)->first();

            $items = SalesInvoiceItem::select('product_id','qty','amount')
                ->where('invoice_num', $invoiceNumber)->get();
        } 
        else {
            $invoice = TaxInvoice::select('id','route_id','customer_id','tax_amt','round_off','net_amt')
                ->where('invoice_num', $invoiceNumber)->first();

            $items = TaxInvoiceItem::select('product_id','qty','amount','tax_amt','tot_amt')
                ->where('invoice_num', $invoiceNumber)->get();
        }

        return [$invoice, $items];
    }

    private function getSalesReturnTxnId(): string
    {
        $lastRecord = SalesReturn::latest('id')->first();
        $nextNumber = $lastRecord
            ? ((int) substr($lastRecord->txn_id, 3)) + 1
            : 1;
        return 'SR-' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
    }
}