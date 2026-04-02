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
use App\Models\Stocks\CurrentStock;
use App\Services\StockService;
use App\Enums\StockAction;
use App\Enums\Roles;
use App\Http\Traits\SalesUtility;
use carbon\carbon;

class OrderController extends Controller
{
    use SalesUtility;    
    protected StockService $stockService;

    public function __construct(StockService $stockService) 
    {
        $this->middleware('auth');        
        $this->stockService = $stockService;
    }

    public function createOrder()
    {
        $invoiceDate = Order::whereDate('created_at', date('Y-m-d'))
                            ->latest('created_at')
                            ->value('invoice_date') ?? date('Y-m-d');

        $routes = MRoute::select('id','name')->orderBy('name')->get();

        $cids = Order::whereDate('created_at', date('Y-m-d'))->pluck('customer_id');
        $customers = Customer::select('id','customer_name')
                            // ->whereNotIn('id',$cids)
                            ->where('status','Active')
                            ->orderBy('customer_name')
                            ->get();

        $products = Product::select('id','name','short_name','tax_type','gst')
                            ->where('visible_invoice','1')
                            ->where('status','Active')
                            ->orderBy('display_index')
                            ->get();

        $units = UOM::select('id','unit_name','display_name','hot_key')->get();

        // Stock Update
        // $currentStock = StockEntry::select('product_name','primary_unit','total_stock_qty','created_at','product_id')
        //                     ->orderBy('created_at', 'desc')
        //                     ->get()
        //                     ->groupBy('product_id')
        //                     ->map(function ($group) { return $group->first(); });

        $currentStock = CurrentStock::select('item_id','unit_id','current_stock')->get();

        $productUnits = ProductUnit::select('product_id','unit_id','prim_unit','conversion')
                            ->orderByDesc('prim_unit')
                            ->get();
        //

        // return response()->json([
        return view('transactions.orders.order_place', [
            'invoiceDate' => $invoiceDate,
            'routes'      => $routes,
            'customers'   => $customers,
            'products'    => $products,
            'units'       => $units,
            'currentStock'=> $currentStock,
            'productUnits'=> $productUnits,
        ]);
    }

    public function storeOrder(Request $request)
    {
        try {
            $isExists = Order::where('invoice_date', $request->invoice_date)
                ->where('customer_id', $request->customer_id)
                ->where('invoice_status','Not Generated')
                ->first();
            if($isExists) {
                return response()->json([
                    'success' => false, 
                    'message' => 'Pending Order Exists for the Customer',
                ]);
            }

            $customer = Customer::find($request->customer_id, ['id', 'route_id', 'area_id']);
            $orderData = $request->order_data;
            $order = Order::create([
                'order_num'    => $this->getReferenceNumber("order"),
                'customer_id'  => $request->customer_id,
                'area_id'      => $customer->area_id,
                'route_id'     => $customer->route_id,
                'user_id'      => "1",
                'order_status' => "Placed",
                'invoice_date' => $request->invoice_date,
                'address_data' => $request->address_data,
                'sales_disc'   => $orderData['discounts'][0],
                'tax_disc'     => $orderData['discounts'][1],
                'sales_tcs'    => $orderData['tcs'][0],
                'tax_tcs'      => $orderData['tcs'][1],
                'created_by'   => auth()->id(),
            ]);            
            
            $stockItems = [];
            foreach ($orderData['orderItems'] as $item) {
                $product  = Product::where('id', $item['productId'])->value('name');
                $unit     = UOM::where('id', $item['unit'])->value('unit_name');
            
                OrderItem::create([
                    'order_num'     => $order->order_num,
                    'item_category' => $item['category'],
                    'product_id'    => $item['productId'],
                    'product_name'  => $product,
                    'qty'           => $item['qty'],
                    'unit_id'       => $item['unit'],
                    'unit_name'     => $unit,
                    'qty_str'       => $item['qtyStr'],
                    'price_str'     => $item['priceStr'],
                    'amount'        => $item['amount'],
                    'tax'           => $item['tax'],
                    'total'         => $item['total'],
                    'discount'      => $item['discount'],
                    'taxable'       => ($item['taxType'] == "Taxable") ? 1 : 0,
                ]);

                $stockItems[] = [
                    'item_id' => $item['productId'],
                    'qty'     => $item['qty'],
                    'unit_id' => $item['unit'],
                ];
                // Stock Update
                // $currentStock = StockEntry::where('product_id', $item['productId'])
                //                             ->latest('created_at')
                //                             ->first();
                // $primaryQty = $this->convertToPrimary($item['productId'], $item['qty'], $item['unit']);
                // $currentStock->total_stock_qty -= $primaryQty['prim_qty'];
                // $currentStock->save();
                //
            }

            // Stock Update
            $this->stockService->updateCurrentStock(StockAction::SALES, $stockItems);

            return response()->json([
                'success'   => true, 
                'order_num' => $order->order_num,
            ]);
        }
        catch(QueryException $exception) {            
            return $exception;
        }
    }

    public function indexOrders(Request $request)
    {
        $fromDate = $toDate = $request->input('invoice_date', date('Y-m-d'));
        $routeId = $request->input('route', 0);
        $customerId = $request->input('customer', 0);

        $routes = MRoute::select('id','name')->orderBy('name')->get();
        $customer = Customer::where('id',$customerId)->value('customer_name');
        $customers = Customer::select('id','customer_name','route_id')
                            ->when($routeId<>0, function($query) use($routeId) { return $query->where('route_id', $routeId); })
                            ->where('status','Active')
                            ->orderBy('customer_name')
                            ->get();

        $orders = Order::select('id','order_num','invoice_date','customer_id','route_id','invoice_status','created_at')
                            ->with('customer:id,customer_name')
                            ->with('route:id,name')
                            ->whereBetween('invoice_date',[$fromDate, $toDate])
                            ->when($routeId<>0, function($query) use($routeId) { return $query->where('route_id', $routeId); })
                            ->when($customerId<>0, function($query) use($customerId) { return $query->where('customer_id', $customerId); })
                            ->orderBy('created_at')
                            ->get();

        // return response()->json([
        return view('transactions.orders.list_orders', [
            'invoiceDate' => $fromDate,            
            'routeId'     => $routeId,
            'customerId'  => $customerId,
            'customer'    => $customer,
            'routes'      => $routes,
            'customers'   => $customers,
            'orders'      => $orders,
        ]);
    }        
    
    public function showOrder(Request $request)
    {
        $orderNum = $request->order_num;
        $orders = $request->orders;

        $order = Order::with('customer:id,customer_name')
            ->with('route:id,name')
            ->where('order_num',$orderNum)
            ->first();

        $addressData = json_decode($order->address_data, true)[0];

        $orderData = [
            'order_num'        => $orderNum,
            'order_dt'         => getIndiaDateTime($order->created_at),
            'invoice_date'     => displayDate($order->invoice_date),
            'route'            => $order->route->name,
            'customer_id'      => $order->customer_id,
            'customer'         => $order->customer->customer_name,
            'billing_address'  => $addressData['billing_address'],
            'delivery_address' => $addressData['delivery_address'],
            'sales_disc'       => $order->sales_disc,
            'tax_disc'         => $order->tax_disc,
            'sales_tcs'        => $order->sales_tcs,
            'tax_tcs'          => $order->tax_tcs,
            'invoice_status'   => $order->invoice_status,
            'cancel_remarks'   => $order->cancel_remarks,
            'created_by'       => $order->created_by
                                    ? optional($order->createdBy)->name . ' at ' . displayDateTimeIST($order->created_at)
                                    : null,
            'edited_by'        => $order->edited_by
                                    ? optional($order->editedBy)->name . ' at ' . displayDateTimeIST($order->edited_at)
                                    : null,
            'actioned_by'      => $order->actioned_by
                                    ? optional($order->actionedBy)->name . ' at ' . displayDateTimeIST($order->actioned_at)
                                    : null,
        ];

        $orderItems = OrderItem::select('item_category','product_name','qty_str','price_str','amount','tax','total','discount','taxable')
                            ->where('order_num',$orderNum)
                            ->get();

        $orderData['has_taxable'] = $orderItems->contains('taxable', 1);
        $orderData['has_exempted'] = $orderItems->contains('taxable', 0);

        // return response()->json([
        return view('transactions.orders.view_order', [
            'order'      => $orderData,
            'orderItems' => $orderItems,
            'orders'     => $orders,
        ]);
    }

    public function editOrder(Request $request)
    {        
        $orderNum = $request->order_num;

        $order = Order::select('id','invoice_date','customer_id')
                        ->with('customer:id,customer_name')
                        ->where('order_num',$orderNum)
                        ->first();

        $customers = Customer::select('id','customer_name')
                        ->where('status','Active')
                        ->orderBy('customer_name')
                        ->get();

        $products = Product::select('id','name','tax_type','gst')
                        ->where('visible_invoice','1')
                        ->where('status','Active')
                        ->orderBy('display_index')
                        ->get();

        $units = UOM::select('id','unit_name','display_name','hot_key')->get();

        // Stock Update
        // $currentStock = StockEntry::select('product_name','primary_unit','total_stock_qty','created_at','product_id')
        //                     ->orderBy('created_at', 'desc')
        //                     ->get()
        //                     ->groupBy('product_id')
        //                     ->map(function ($group) { return $group->first(); });

        $currentStock = CurrentStock::select('item_id','unit_id','current_stock')->get();

        $productUnits = ProductUnit::select('product_id','unit_id','prim_unit','conversion')
                            ->orderByDesc('prim_unit')
                            ->get();
        //
                
        // return response()->json([
        return view('transactions.orders.order_edit', [
            'orderNum'  => $orderNum,
            'order'     => $order,
            'customers' => $customers,
            'products'  => $products,
            'units'     => $units,
            'currentStock'=> $currentStock,
            'productUnits'=> $productUnits,
        ]);
    }

    public function updateOrder(Request $request)
    {
        try {
            $orderNum = $request->order_num;
            $orderData = $request->order_data;
            $customer = Customer::find($request->customer_id, ['id', 'route_id', 'area_id']);

            Order::where('order_num', $orderNum)->update([
                'customer_id'  => $customer->id,
                'area_id'      => $customer->area_id,
                'route_id'     => $customer->route_id,
                'invoice_date' => $request->invoice_date,
                'address_data' => $request->address_data,
                'sales_disc'   => $orderData['discounts'][0],
                'tax_disc'     => $orderData['discounts'][1],
                'sales_tcs'    => $orderData['tcs'][0],
                'tax_tcs'      => $orderData['tcs'][1],
                'edited_by'    => auth()->id(),
                'edited_at'    => now(),
            ]);

            // Stock Update
            // $orders = OrderItem::where('order_num',$orderNum)->get();
            // foreach($orders as $order) {
            //     $currentStock = StockEntry::where('product_id', $order->product_id)
            //                               ->latest('created_at')
            //                               ->first();
            //     $primaryQty = $this->convertToPrimary($order->product_id, $order->qty, $order->unit_id);
            //     $currentStock->total_stock_qty += $primaryQty['prim_qty'];
            //     $currentStock->save();
            // }

            DB::transaction(function () use ($orderNum, $orderData) {
                $oldItems = [];
                $orderItems = OrderItem::where('order_num',$orderNum)->get(['product_id','qty','unit_id']);
                foreach($orderItems as $orderItem) {
                    $oldItems[] = [
                        'item_id' => $orderItem->product_id,
                        'qty'     => $orderItem->qty,
                        'unit_id' => $orderItem->unit_id,
                    ];
                }

                OrderItem::where('order_num', $orderNum)->delete();            
                $newItems = [];

                foreach ($orderData['orderItems'] as $item) {
                    $product = Product::where('id', $item['productId'])->value('name');
                    $unit = UOM::where('id', $item['unit'])->value('unit_name');
            
                    OrderItem::create([
                        'order_num'     => $orderNum,
                        'item_category' => $item['category'],
                        'product_id'    => $item['productId'],
                        'product_name'  => $product,
                        'qty'           => $item['qty'],
                        'unit_id'       => $item['unit'],
                        'unit_name'     => $unit,
                        'qty_str'       => $item['qtyStr'],
                        'price_str'     => $item['priceStr'],
                        'amount'        => $item['amount'],
                        'tax'           => $item['tax'],
                        'total'         => $item['total'],
                        'discount'      => $item['discount'],
                        'taxable'       => ($item['taxType'] == "Taxable") ? 1 : 0,
                    ]);

                    // Stock Update
                    // $currentStock = StockEntry::where('product_id', $item['productId'])
                    //                           ->latest('created_at')
                    //                           ->first();
                    // $primaryQty = $this->convertToPrimary($item['productId'], $item['qty'], $item['unit']);
                    // $currentStock->total_stock_qty -= $primaryQty['prim_qty'];
                    // $currentStock->save();
                    $newItems[] = [
                        'item_id' => $item['productId'],
                        'qty'     => $item['qty'],
                        'unit_id' => $item['unit'],
                    ];
                    //
                }

                $this->stockService->updateCurrentStock(StockAction::RETURN_ORDER, $oldItems);
                $this->stockService->updateCurrentStock(StockAction::SALES, $newItems);
            });
            
            return response()->json([
                'success' => true, 
                'message' => "Order Updated Successfully!",
            ]);
        }
        catch(Exception $exception) {
            return $exception;
        }
    }

    public function cancelOrder(Request $request)
    {
        try {
            Order::where('order_num', $request->order_num)->update([
                'invoice_status' => 'Cancelled',
                'cancel_remarks' => $request->remarks,
                'actioned_by'    => auth()->id(),
                'actioned_at'    => now(),
            ]);

            // Stock Update
            // $orders = OrderItem::where('order_num', $request->order_num)->get();
            // foreach($orders as $item) {
            //     $currentStock = StockEntry::where('product_id', $item->product_id)
            //              ->latest('created_at')
            //              ->first();
            //     $primaryQty = $this->convertToPrimary($item->product_id, $item->qty, $item->unit_id);
            //     $currentStock->total_stock_qty += $primaryQty['prim_qty'];
            //     $currentStock->save();
            // }
            $orderItems = OrderItem::where('order_num', $request->order_num)->get();
            $stockItems = [];
            foreach($orderItems as $orderItem) {
                $stockItems[] = [
                    'item_id' => $orderItem->product_id,
                    'qty'     => $orderItem->qty,
                    'unit_id' => $orderItem->unit_id,
                ];
            }
            $this->stockService->updateCurrentStock(StockAction::RETURN_ORDER, $stockItems);

            return response()->json([ 
                'success' => true,
                'message' => "Order Cancellation Done!",
            ]);
        }
        catch(QueryException $exception) {
            return $exception;
        }
    }

    public function getOrder($order_num)
    {
        $order = Order::where('order_num', $order_num)
            ->first(['order_num', 'sales_disc', 'tax_disc', 'address_data']);
        $order->address_data = json_decode($order->address_data, true)[0];
 
        $orderItems = OrderItem::where('order_num', $order->order_num)
            ->get(['item_category', 'product_id', 'product_name', 'qty', 'unit_name', 'unit_id']);    
        
        return response()->json([
            'order' => $order,
            'orderItems' => $orderItems,
        ]);
    }

    public function lastOrder($cust_id)
    {
        $order = Order::where('customer_id', $cust_id)
            ->latest('id')
            ->first(['order_num', 'sales_disc', 'tax_disc', 'address_data']);

        $order->address_data = json_decode($order->address_data, true)[0];

        $orderItems = OrderItem::where('order_num', $order->order_num)
            ->where('item_category','Regular')
            ->get(['item_category', 'product_id', 'product_name', 'qty', 'unit_name', 'unit_id']);
        
        return response()->json([
            'order' => $order,
            'orderItems' => $orderItems,
        ]);
    }    

    public function createBulkMilkOrder()
    {
        $orderNum = $this->getReferenceNumber("bulk-milk");

        $products = Product::where('visible_bulkmilk', '1')
            ->where('status', 'Active')
            ->orderBy('display_index')
            ->get(['id', 'name', 'hsn_code']);

        $vehicles = Vehicle::where('status', 'Active')
            ->get(['id', 'vehicle_number']);

        $drivers = Employee::where('role_id', Roles::DRIVER_ID)
            ->where('status', 'Active')
            ->orderBy('name')
            ->get(['id', 'name', 'mobile_num']);        

        // return response()->json([
        return view('transactions.orders.manage_bulk_milk', [
            'order_num' => $orderNum,
            'products'  => $products,
            'vehicles'  => $vehicles,
            'drivers'   => $drivers,
        ]);
    }

    public function storeBulkMilkOrder(Request $request)
    {
        try {
            $customerData = $this->getCustomerBillingData($request->customer_id, $request->billing_addr, $request->delivery_addr);
            $route = Customer::find($request->customer_id)->route;
            $vehicleId = Vehicle::where('vehicle_number',$request->vehicle_num)->value('id');
            $driverId = Employee::where('name',$request->driver_name)->where('role_id',Roles::DRIVER_ID)->value('id');

            // Create and save BulkMilkOrder
            $order = BulkMilkOrder::create([
                'invoice_num'        => $this->getReferenceNumber("bulk-milk"),
                'invoice_date'       => $request->invoice_date,
                'order_dt'           => now(),
                'customer_id'        => $request->customer_id,
                'customer_name'      => $request->customer_name,
                'customer_data'      => json_encode($customerData),
                'route_id'           => $route->id,
                'route_name'         => $route->name,
                'vehicle_id'         => $vehicleId,
                'vehicle_num'        => $request->vehicle_num,
                'driver_id'          => $driverId,
                'driver_name'        => $request->driver_name,
                'driver_mobile_num'  => $request->driver_mobile_num,
                'item_count'         => $request->item_count,
                'tot_amt'            => $request->tot_amt,
                'tcs'                => $request->tcs,
                'round_off'          => $request->round_off,
                'net_amt'            => $request->net_amt,
            ]);

            // Bulk insert BulkMilkOrderItem
            $orderItems = collect($request->order_data)->map(function ($data) use ($order) {
                return [
                    'invoice_num'  => $order->invoice_num,
                    'product_id'   => $data['product_id'],
                    'product_name' => $data['product_name'],
                    'hsn_code'     => $data['hsn_code'],
                    'qty_kg'       => $data['qty_kg'],
                    'clr'          => $data['clr'],
                    'fat'          => $data['fat'],
                    'snf'          => $data['snf'],
                    'qty_ltr'      => $data['qty_ltr'],
                    'ts'           => $data['ts'],
                    'ts_rate'      => $data['ts_rate'],
                    'rate'         => $data['rate'],
                    'amount'       => $data['amount'],
                ];
            })->toArray();

            BulkMilkOrderItem::insert($orderItems);

            return response()->json([
                'success'   => true,
                'message'   => "Bulk Milk Order Placed Successfully!",
                'order_num' => $order->invoice_num,
            ]);
        } 
        catch (QueryException $exception) {
            return response()->json([
                'success' => false,
                'message' => $exception->getMessage(),
            ], 500);
        }
    }

    public function indexBulkMilkOrders(Request $request)
    {
        $fromDate = $request->input('fromDate', date('Y-m-d'));
        $toDate = $request->input('toDate', date('Y-m-d'));

        $orders = BulkMilkOrder::select('id','invoice_num','invoice_date','customer_id','customer_name','net_amt','order_status','invoice_status')
            ->whereBetween('invoice_date', [$fromDate, $toDate])
            ->get();
        
        foreach($orders as &$order) {
            if($order->order_status == "Cancelled") {
                $order->invoice_status = "Order Cancelled";
            }
        }

        // return response()->json([
        return view('transactions.orders.list_bulk_milk', [
            'fromDate' => $fromDate,
            'toDate'   => $toDate,
            'orders'   => $orders,
        ]);
    }

    public function showBulkMilkOrder(Request $request)
    {
        $orderNum = $request->order_num;
        $orders = $request->orders;

        $order = BulkMilkOrder::where('invoice_num', $orderNum)->first();
        $customerData = json_decode($order->customer_data, true);

        $orderData = [
            'order_num'         => $orderNum,
            'order_dt'          => getIndiaDateTime($order->created_at),
            'invoice_date'      => displayDate($order->invoice_date),
            'order_status'      => $order->order_status,
            'customer'          => $order->customer_name,
            'route'             => $order->route_name,
            'vehicle_num'       => $order->vehicle_num,
            'driver_name'       => $order->driver_name,
            'driver_mobile_num' => $order->driver_mobile_num,
            'billing_address'   => $customerData['billAddr'],
            'delivery_address'  => $customerData['deliAddr'],
            'invoice_status'    => $order->invoice_status,
            'cancel_remarks'    => $order->cancel_remarks,
            'tot_amt'           => $order->tot_amt,
            'tcs'               => $order->tcs,
            'round_off'         => $order->round_off,
            'net_amt'           => $order->net_amt,
        ];

        $orderItems = BulkMilkOrderItem::select('id','product_name','hsn_code','qty_kg','clr','fat','snf','qty_ltr','ts','ts_rate','rate','amount')
            ->where('invoice_num', $orderNum)
            ->get();

        // return response()->json([
        return view('transactions.orders.view_bulk_milk', [
            'order'       => $orderData,
            'order_items' => $orderItems,
            'orders'      => $orders,
        ]);
    }

    public function editBulkMilkOrder(Request $request)
    {
        $orderNum = $request->order_num;
        $order = BulkMilkOrder::where('invoice_num', $orderNum)->first();
        $orderItems = BulkMilkOrderItem::where('invoice_num', $orderNum)->get();
        $order->customer_data = json_decode($order->customer_data);

        $products = Product::where('visible_bulkmilk', '1')
            ->where('status', 'Active')
            ->orderBy('display_index')
            ->get(['id', 'name', 'hsn_code']);

        $vehicles = Vehicle::where('status', 'Active')
            ->get(['id', 'vehicle_number']);

        $drivers = Employee::where('role_id', Roles::DRIVER_ID)
            ->where('status', 'Active')
            ->orderBy('name')
            ->get(['id', 'name', 'mobile_num']);

        // return response()->json([
        return view('transactions.orders.manage_bulk_milk', [
            'order_num'   => $orderNum,
            'order'       => $order,
            'order_items' => $orderItems,
            'products'    => $products,
            'vehicles'    => $vehicles,
            'drivers'     => $drivers,
        ]);
    }

    public function updateBulkMilkOrder(Request $request)
    {
        try {
            $customerData = $this->getCustomerBillingData($request->customer_id, $request->billing_addr, $request->delivery_addr);
            $route = Customer::select('id','route_id')->with('route:id,name')->where('id',$request->customer_id)->first()->route;
            $vehicleId = Vehicle::where('vehicle_number',$request->vehicle_num)->value('id');
            $driverId = Employee::where('name',$request->driver_name)->where('role_id',Roles::DRIVER_ID)->value('id');

            $order = BulkMilkOrder::where('invoice_num', $request->order_num)->first();
            if($order) {
                $order->update([
                    'invoice_date'       => $request->invoice_date,
                    'customer_id'        => $request->customer_id,
                    'customer_name'      => $request->customer_name,
                    'customer_data'      => json_encode($customerData),
                    'route_id'           => $route->id,
                    'route_name'         => $route->name,
                    'vehicle_id'         => $vehicleId,
                    'vehicle_num'        => $request->vehicle_num,
                    'driver_id'          => $driverId,
                    'driver_name'        => $request->driver_name,
                    'driver_mobile_num'  => $request->driver_mobile_num,
                    'item_count'         => $request->item_count,
                    'tot_amt'            => $request->tot_amt,
                    'tcs'                => $request->tcs,
                    'round_off'          => $request->round_off,
                    'net_amt'            => $request->net_amt,
                ]);

                BulkMilkOrderItem::where('invoice_num', $order->invoice_num)->delete();

                // Bulk insert BulkMilkOrderItem
                $orderItems = collect($request->order_data)->map(function ($data) use ($order) {
                    return [
                        'invoice_num'  => $order->invoice_num,
                        'product_id'   => $data['product_id'],
                        'product_name' => $data['product_name'],
                        'hsn_code'     => $data['hsn_code'],
                        'qty_kg'       => $data['qty_kg'],
                        'clr'          => $data['clr'],
                        'fat'          => $data['fat'],
                        'snf'          => $data['snf'],
                        'qty_ltr'      => $data['qty_ltr'],
                        'ts'           => $data['ts'],
                        'ts_rate'      => $data['ts_rate'],
                        'rate'         => $data['rate'],
                        'amount'       => $data['amount'],
                    ];
                })->toArray();

                BulkMilkOrderItem::insert($orderItems);
            }

            return response()->json([
                'success' => true, 
                'message' => "Bulk Milk Outward Updated Successfully!",
                'alert'   => "Update",
            ]);
        }
        catch(QueryException $exception) {
            return $exception;
        }
    }

    public function cancelBulkMilkOrder(Request $request)
    {
        try {
            BulkMilkOrder::where('invoice_num', $request->order_num)->update([
                'order_status'   => 'Cancelled',
                'cancel_remarks' => $request->remarks,
            ]);

            // Stock Update
            //

            return response()->json([ 
                'success' => true,
                'message' => "Bulk Milk Order Cancellation Done!",
            ]);
        }
        catch(QueryException $exception) {
            return $exception;
        }
    }
}
