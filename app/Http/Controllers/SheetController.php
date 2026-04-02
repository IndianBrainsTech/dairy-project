<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use App\Models\Orders\Order;
use App\Models\Orders\OrderItem;
use App\Models\Orders\SalesInvoice;
use App\Models\Orders\TaxInvoice;
use App\Models\Places\MRoute;
use App\Models\Profiles\Customer;
use App\Models\Products\Product;
use App\Models\Products\ProductUnit;
use App\Http\Traits\SalesUtility;

class SheetController extends Controller
{
    use SalesUtility;

    public function __construct()
    {
        $this->middleware('auth');
    }    

    public function showLoadingSheet(Request $request)
    {   
        $date    = $request->has('lsdate') ? $request->lsdate : date('Y-m-d');
        $lstype  = $request->has('lstype') ? $request->lstype : "Route";
        $routeId = $request->has('route')  ? $request->route  : 0;

        $routes = MRoute::select('id','name')->orderBy('name')->get();
        $routeName = $routeId == 0 ? "" : MRoute::where('id', $routeId)->value('name');

        if($lstype == "Route")
            $lsdata = $this->getRouteWiseLoadingData($date, $routeId, $routes);
        else
            $lsdata = $this->getCustomerWiseLoadingData($date, $routeId);

        $data = [
            'date'       => $date,
            'lstype'     => $lstype,
            'route_id'   => $routeId,
            'route_name' => $routeName,
            'routes'     => $routes,
        ];

        $data = array_merge($data, $lsdata);

        // return response()->json($data);
        return view('transactions.sheets.loading_sheet', $data);
    }

    public function showTripSheet(Request $request)
    {
        $routeId = $request->route ?? 0;
        $date = $request->tsDate ?? date('Y-m-d');
        
        $routes = MRoute::select('id','name')->orderBy('name')->get();
        $routeName = "";
        $dataRows = [];
        $totals = [];
        if($routeId) {
            $routeName = MRoute::where('id', $routeId)->value('name');
            $salesInvoices = SalesInvoice::select('invoice_num','customer_name','qty','net_amt','crates','created_at')
                ->where('invoice_date',$date)
                ->where('route_id',$routeId)
                ->where('invoice_status','Generated')
                ->get();
            $taxInvoices = TaxInvoice::select('invoice_num','customer_name','qty','net_amt','crates','created_at')
                ->where('invoice_date',$date)
                ->where('route_id',$routeId)
                ->where('invoice_status','Generated')
                ->get();

            $mergedInvoices = $salesInvoices->concat($taxInvoices);
            $dataRows = $mergedInvoices->sortBy('customer_name')->values();
            $totals = $this->calculateTripSheetTotals($dataRows);
        }

        // return response()->json([
        return view('transactions.sheets.trip_sheet', [
            'date'      => $date,
            'routeId'   => $routeId,
            'routeName' => $routeName,
            'routes'    => $routes,
            'dataRows'  => $dataRows,
            'totals'    => $totals
        ]);
    }

    private function getRouteWiseLoadingData($date, $routeId, $routes)
    {
        $products = Product::where('status','Active')->orderBy('display_index')->get(['id','name']);
        $routeTables = [];
        $summaryTable = [];

        if($routeId == 0) {
            foreach($routes as $route) {
                $orders = Order::select('id','order_num','customer_id','invoice_date')
                    ->where('invoice_date',$date)
                    ->where('route_id',$route->id)
                    ->where('invoice_status','Generated')
                    ->get();

                if ($orders->isNotEmpty()) {
                    $routeTables[] = $this->generateRouteTable($route->name, $products, $orders);
                }
            }
            $summaryTable = $this->generateRouteSummaryTable($routeTables);
        }
        else {
            $routeName = MRoute::where('id',$routeId)->value('name');
            $orders = Order::select('id','order_num','customer_id','invoice_date')
                ->where('invoice_date', $date)
                ->where('route_id', $routeId)
                ->where('invoice_status','Generated')
                ->get();

            if ($orders->isNotEmpty()) {
                $routeTables[] = $this->generateRouteTable($routeName, $products, $orders);
            }
        }

        return [
            'route_tables'  => $routeTables,
            'summary_table' => $summaryTable,
        ];
    }

    private function getCustomerWiseLoadingData($date, $routeId)
    {
        $tables = [];
        $totals = [];
        if($routeId > 0) {
            $orders = Order::select('order_num','customer_id')
                ->where('invoice_date',$date)
                ->where('route_id',$routeId)
                ->where('invoice_status','Generated')
                ->get();

            if ($orders->isNotEmpty()) {
                $totals['qty']    = 0;
                $totals['crates'] = 0;
                $totals['liters'] = 0;
                $totals['others'] = 0;

                foreach($orders as $order) {
                    $tableData = array();
                    $tableData['customer'] = Customer::where('id', $order->customer_id)->value('customer_name');
                    $tableData['orderNum'] = $order->order_num;
                    $orderItems = OrderItem::select('id','product_id','product_name','qty','unit_name','unit_id')->where('order_num',$order->order_num)->get();
                    $rows = array();
                    $totalRow['qty'] = 0;
                    $totalRow['crates'] = 0;
                    $totalRow['liters'] = 0;
                    $totalRow['others'] = 0;

                    foreach($orderItems as $orderItem) {
                        $row = array();
                        $row['product']    = $orderItem->product_name;
                        $row['order_qty']  = $orderItem->qty;
                        $row['order_unit'] = $orderItem->unit_name;
                        $row['unit_id']    = $orderItem->unit_id;

                        /* Conversion to Primary */
                        $primaryData = $this->convertToPrimary($orderItem->product_id, $orderItem->qty, $orderItem->unit_id);
                        $row['prim_qty']  = $primaryData['prim_qty'];
                        $row['prim_unit'] = $primaryData['prim_unit'];

                        /* Conversion to Crates, Liters and Others */
                        $conversionData = $this->convertToCratesAndLiters($orderItem->product_id, $row['prim_qty']);
                        if($conversionData['hasCrate']) {
                            $row['crates'] = $conversionData['crates'];
                            $row['liters'] = $conversionData['liters'];
                            $row['others'] = 0;
                        }
                        else {
                            $row['crates'] = 0;
                            $row['liters'] = 0;
                            $row['others'] = $orderItem->qty;
                            $row['prim_qty'] = 0;
                        }
                        
                        array_push($rows, $row);

                        $totalRow['qty']    += $row['prim_qty'];
                        $totalRow['crates'] += $row['crates'];
                        $totalRow['liters'] += $row['liters'];
                        $totalRow['others'] += $row['others'];
                    }
                    $tableData['items'] = $rows;

                    // Total Liters to 'Crates and Liters'
                    $extCrates = intdiv($totalRow['liters'], 12); // 12 Liters = 1 Crate
                    $totalRow['crates'] += $extCrates;
                    $totalRow['liters'] -= $extCrates * 12;

                    $totalRow['qty']     = getTwoDigitPrecision($totalRow['qty']);
                    $totalRow['liters']  = getTwoDigitPrecision($totalRow['liters']);
                    $tableData['totals'] = $totalRow;
                    
                    array_push($tables, $tableData);

                    $totals['qty']    += $totalRow['qty'];
                    $totals['crates'] += $totalRow['crates'];
                    $totals['liters'] += $totalRow['liters'];
                    $totals['others'] += $totalRow['others'];

                    // (Grand) Total Liters to 'Crates and Liters'
                    $extCrates = intdiv($totals['liters'], 12);
                    $totals['crates'] += $extCrates;
                    $totals['liters'] -= $extCrates * 12;

                    $totals['qty']    = getTwoDigitPrecision($totals['qty']);
                    $totals['liters'] = getTwoDigitPrecision($totals['liters']);
                }
            }
        }

        return [
            'tables' => $tables,
            'totals' => $totals,
        ];
    }
    
    private function convertToCratesAndLiters($productId, $qty)
    {
        $productUnits = ProductUnit::select('product_id','unit_id','price','prim_unit','conversion')
            ->where('product_id',$productId)
            ->orderByDesc('prim_unit')
            ->get();

        $crates = 0;
        $liters = 0;
        $hasCrate = false;

        foreach($productUnits as $productUnit) {
            if($productUnit->unit_id == config('constants.UNIT_CRATE_ID')) {
                $conversion = $productUnit->conversion;
                $crates = (int) ($qty / $conversion);
                $liters = $qty - ($crates * $conversion);
                $liters = getTwoDigitPrecision($liters);

                // Handle floating-point precision error
                $ltrs = (float) $liters;
                if($ltrs >= $conversion) {
                    $additional = (int) ($ltrs / $conversion);
                    $ltrs = $ltrs - ($additional * $conversion);
                    $crates += $additional;
                    $liters = getTwoDigitPrecision($ltrs);
                }
                
                $hasCrate = true;
            }
        }

        return [
            'crates'   => $crates,
            'liters'   => $liters,
            'hasCrate' => $hasCrate
        ];
    }

    private function generateRouteTable($route, $products, $orders)
    {
        $table  = [];
        $totals = [];
        $orderNums = $orders->pluck('order_num');
        $orderItems = OrderItem::select('id','product_id','product_name','qty','unit_name','unit_id')->whereIn('order_num',$orderNums)->get();
    
        foreach($products as $product) {
            $items = $orderItems->where('product_id', $product->id)->values();
            if($items->isNotEmpty()) {
                $record = [];
                $record['product'] = $product->name;

                /* Convert order qty to primary qty and sum up */
                $qty = 0;
                foreach($items as $item) {
                    $primaryData = $this->convertToPrimary($item->product_id, $item->qty, $item->unit_id);
                    $qty += $primaryData['prim_qty'];
                }
                $record['prim_qty'] = $qty;

                /* Conversion to Crates, Liters and Others */
                $conversionData = $this->convertToCratesAndLiters($item->product_id, $qty);
                if($conversionData['hasCrate']) {
                    $record['crates'] = $conversionData['crates'];
                    $record['liters'] = $conversionData['liters'];
                    $record['others'] = 0;
                }
                else {
                    $record['crates'] = 0;
                    $record['liters'] = 0;
                    $record['others'] = $qty;
                    $record['prim_qty'] = 0;
                }

                $table[] = $record;
            }
        }

        if(!empty($table)) {
            $totals = [
                'prim_qty' => 0,
                'crates' => 0,
                'liters' => 0,
                'others' => 0,
            ];
            foreach($table as $record) {
                $totals['prim_qty'] += $record['prim_qty'];
                $totals['crates']   += $record['crates'];
                $totals['liters']   += $record['liters'];
                $totals['others']   += $record['others'];
            }
            $totals['prim_qty'] = getTwoDigitPrecision($totals['prim_qty']);
            $totals['liters']   = getTwoDigitPrecision($totals['liters']);
        }

        return [
            'route'  => $route,
            'table'  => $table,
            'totals' => $totals
        ];
    }

    private function generateRouteSummaryTable($routeTables)
    {
        $summaryTable = [];

        $totals = [
            'prim_qty' => 0,
            'crates' => 0,
            'liters' => 0,
            'others' => 0,
        ];
        
        foreach($routeTables as $routeTable) {
            $record['route']    = $routeTable['route'];
            $record['prim_qty'] = $routeTable['totals']['prim_qty'];
            $record['crates']   = $routeTable['totals']['crates'];
            $record['liters']   = $routeTable['totals']['liters'];
            $record['others']   = $routeTable['totals']['others'];
            $summaryTable[] = $record;

            $totals['prim_qty'] += $record['prim_qty'];
            $totals['crates'] += $record['crates'];
            $totals['liters'] += $record['liters'];
            $totals['others'] += $record['others'];
        }

        return [
            'table'  => $summaryTable,
            'totals' => $totals
        ];
    }
    
    private function calculateTripSheetTotals($dataRows)
    {
        $totals = [
            'qty' => 0,
            'net_amt' => 0,
            'crates' => 0
        ];

        foreach ($dataRows as $data) {
            $totals['qty'] += $data->qty;
            $totals['net_amt'] += $data->net_amt;
            $totals['crates'] += $data->crates;
        }

        return $totals;
    }
}