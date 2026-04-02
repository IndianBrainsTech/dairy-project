<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Database\QueryException;
use App\Models\Orders\JobWork;
use App\Models\Orders\JobWorkItem;
use App\Models\Products\Product;
use App\Models\Profiles\Customer;
use App\Models\Profiles\Employee;
use App\Models\Transport\Vehicle;
use App\Http\Traits\SalesUtility;

class JobWorkController extends Controller
{
    use SalesUtility;
    private int $driverRoleId;
    
    public function __construct() 
    {
        $this->middleware('auth');
        $this->driverRoleId = config('constants.ROLE_DRIVER_ID');
    }

    public function createJobWork()
    {
        $job_work_num = $this->getReferenceNumber("conversion");

        $products = Product::where('visible_bulkmilk', '1')
            ->where('status', 'Active')
            ->orderBy('display_index')
            ->get(['id', 'name', 'hsn_code']);

        $vehicles = Vehicle::where('status', 'Active')
            ->get(['id', 'vehicle_number']);

        $drivers = Employee::where('role_id', $this->driverRoleId)
            ->where('status', 'Active')
            ->orderBy('name')
            ->get(['id', 'name', 'mobile_num']);        

        // return response()->json([
        return view('transactions.job-work.manage_job_work', [
            'job_work_num' => $job_work_num,
            'products'     => $products,
            'vehicles'     => $vehicles,
            'drivers'      => $drivers,
        ]);
    }

    public function storeJobWork(Request $request)
    {
        try {
            $customer_data = $this->getCustomerBillingData($request->customer_id, $request->billing_addr, $request->delivery_addr);
            $route = Customer::find($request->customer_id)->route;
            $vehicle_id = Vehicle::where('vehicle_number',$request->vehicle_num)->value('id');
            $driver_id = Employee::where('name',$request->driver_name)->where('role_id',$this->driverRoleId)->value('id');
            
            // Create and Save Job Work
            $job_work = JobWork::create([
                'job_work_num'      => $this->getReferenceNumber("conversion"),
                'job_work_date'     => $request->job_work_date,
                'job_work_dt'       => now(),
                'customer_id'       => $request->customer_id,
                'customer_name'     => $request->customer_name,
                'customer_data'     => json_encode($customer_data),
                'route_id'          => $route->id,
                'route_name'        => $route->name,
                'vehicle_id'        => $vehicle_id,
                'vehicle_num'       => $request->vehicle_num,
                'driver_id'         => $driver_id,
                'driver_name'       => $request->driver_name,
                'driver_mobile_num' => $request->driver_mobile_num,
                'item_count'        => $request->item_count,
                'tot_amt'           => $request->tot_amt,                
                'round_off'         => $request->round_off,
                'net_amt'           => $request->net_amt,
            ]);

            $job_work_items = collect($request->job_work_data)->map(function ($data) use ($job_work) {
                return [
                    'job_work_num'  => $job_work->job_work_num,
                    'product_id'    => $data['product_id'],
                    'product_name'  => $data['product_name'],
                    'hsn_code'      => $data['hsn_code'],
                    'qty_kg'        => $data['qty_kg'],
                    'clr'           => $data['clr'],
                    'fat'           => $data['fat'],
                    'snf'           => $data['snf'],
                    'qty_ltr'       => $data['qty_ltr'],
                    'ts'            => $data['ts'],
                    'ts_rate'       => $data['ts_rate'],
                    'rate'          => $data['rate'],
                    'amount'        => $data['amount'],
                ];
            })->toArray();

            JobWorkItem::insert($job_work_items);

            return response()->json([
                'success'      => true,
                'message'      => "Job Work Created Successfully!",
                'job_work_num' => $job_work->job_work_num,
            ]);
        }
        catch (QueryException $exception) {
            return response()->json([
                'success' => false,
                'message' => $exception->getMessage(),
            ], 500);
        }
    }

    public function indexJobWork(Request $request)
    {
        $from_date = $request->input('from_date', date('Y-m-d'));
        $to_date = $request->input('to_date', date('Y-m-d'));

        $job_works = JobWork::select('id','job_work_num','job_work_date','customer_id','customer_name','job_work_status','invoice_status')
            ->whereBetween('job_work_date', [$from_date, $to_date])
            ->get();
        
        foreach($job_works as &$job_work) {
            if($job_work->job_work_status == "Cancelled") {
                $job_work->invoice_status = "Job Work Cancelled";
            }
        }

        // return response()->json([
        return view('transactions.job-work.list_job_work', [
            'dates'     => ['from' => $from_date, 'to' => $to_date],
            'job_works' => $job_works,
        ]);
    }

    public function showJobWork(Request $request)
    {
        $job_work_num = $request->job_work_num;
        $job_works = $request->job_works;

        $job_work = JobWork::where('job_work_num', $job_work_num)->first();
        $customer_data = json_decode($job_work->customer_data, true);

        $job_work_data = [
            'job_work_num'      => $job_work_num,
            'job_work_dt'       => getIndiaDateTime($job_work->created_at),
            'job_work_date'     => displayDate($job_work->job_work_date),
            'job_work_status'   => $job_work->job_work_status,
            'customer'          => $job_work->customer_name,
            'route'             => $job_work->route_name,
            'vehicle_num'       => $job_work->vehicle_num,
            'driver_name'       => $job_work->driver_name,
            'driver_mobile_num' => $job_work->driver_mobile_num,
            'billing_address'   => $customer_data['billAddr'],
            'delivery_address'  => $customer_data['deliAddr'],
            'invoice_status'    => $job_work->invoice_status,
            'cancel_remarks'    => $job_work->cancel_remarks,
            'tot_amt'           => $job_work->tot_amt,            
            'round_off'         => $job_work->round_off,
            'net_amt'           => $job_work->net_amt,
        ];

        $job_work_items = JobWorkItem::select('id','product_name','hsn_code','qty_kg','clr','fat','snf','qty_ltr','ts','ts_rate','rate','amount')
            ->where('job_work_num', $job_work_num)
            ->get();

        // return response()->json([
        return view('transactions.job-work.view_job_work', [
            'job_work'       => $job_work_data,
            'job_work_items' => $job_work_items,
            'job_works'      => $job_works,
        ]);
    }

    public function editJobWork(Request $request)
    {
        $job_work_num = $request->job_work_num;
        $job_work = JobWork::where('job_work_num', $job_work_num)->first();
        $job_work_items = JobWorkItem::where('job_work_num', $job_work_num)->get();
        $job_work->customer_data = json_decode($job_work->customer_data);

        $products = Product::where('visible_bulkmilk', '1')
            ->where('status', 'Active')
            ->orderBy('display_index')
            ->get(['id', 'name', 'hsn_code']);

        $vehicles = Vehicle::where('status', 'Active')
            ->get(['id', 'vehicle_number']);

        $drivers = Employee::where('role_id', $this->driverRoleId)
            ->where('status', 'Active')
            ->orderBy('name')
            ->get(['id', 'name', 'mobile_num']);

        // return response()->json([
        return view('transactions.job-work.manage_job_work', [
            'job_work_num'   => $job_work_num,
            'job_work'       => $job_work,
            'job_work_items' => $job_work_items,
            'products'       => $products,
            'vehicles'       => $vehicles,
            'drivers'        => $drivers,
        ]);
    }

    public function updateJobWork(Request $request)
    {
        try {
            $customer_data = $this->getCustomerBillingData($request->customer_id, $request->billing_addr, $request->delivery_addr);
            $route = Customer::select('id','route_id')->with('route:id,name')->where('id',$request->customer_id)->first()->route;
            $vehicle_id = Vehicle::where('vehicle_number',$request->vehicle_num)->value('id');
            $driver_id = Employee::where('name',$request->driver_name)->where('role_id',$this->driverRoleId)->value('id');

            $job_work = JobWork::where('job_work_num', $request->job_work_num)->first();
            if($job_work) {
                $job_work->update([
                    'job_work_date'      => $request->job_work_date,
                    'customer_id'        => $request->customer_id,
                    'customer_name'      => $request->customer_name,
                    'customer_data'      => json_encode($customer_data),
                    'route_id'           => $route->id,
                    'route_name'         => $route->name,
                    'vehicle_id'         => $vehicle_id,
                    'vehicle_num'        => $request->vehicle_num,
                    'driver_id'          => $driver_id,
                    'driver_name'        => $request->driver_name,
                    'driver_mobile_num'  => $request->driver_mobile_num,
                    'item_count'         => $request->item_count,
                    'tot_amt'            => $request->tot_amt,                    
                    'round_off'          => $request->round_off,
                    'net_amt'            => $request->net_amt,
                ]);

                JobWorkItem::where('job_work_num', $job_work->job_work_num)->delete();
                
                $job_work_items = collect($request->job_work_data)->map(function ($data) use ($job_work) {
                    return [
                        'job_work_num' => $job_work->job_work_num,
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

                JobWorkItem::insert($job_work_items);
            }

            return response()->json([
                'success' => true, 
                'message' => "JobWork Updated Successfully!",                
            ]);
        }
        catch(QueryException $exception) {
            return $exception;
        }
    }

    public function cancelJobWork(Request $request)
    {
        try {
            JobWork::where('job_work_num', $request->job_work_num)->update([
                'job_work_status' => 'Cancelled',
                'cancel_remarks'  => $request->remarks,
            ]);

            // Stock Update
            //

            return response()->json([ 
                'success' => true,
                'message' => "Job Work Cancellation Done!",
            ]);
        }
        catch(QueryException $exception) {
            return $exception;
        }
    }

    public function indexDeliveryChallan(Request $request)
    {
        $from_date = $request->from_date ?? date('Y-m-d');
        $to_date = $request->to_date ?? $from_date;
        $customer_id = $request->customer_id ?? 0;
        $customer_name = $request->customer ?? "";

        $customers = Customer::where('status','Active')
            ->orderBy('customer_name')
            ->get(['id','customer_name']);

        $job_works = JobWork::select('id','job_work_num','job_work_date','customer_id','customer_name')
            ->whereBetween('job_work_date',[$from_date, $to_date])
            ->where('invoice_status','Generated')
            ->when($customer_id<>0, function($query) use($customer_id) { return $query->where('customer_id', $customer_id); })
            ->get();

        // return response()->json([
        return view('transactions.job-work.list_delivery_challans', [
            'dates'     => ['from' => $from_date, 'to' => $to_date],
            'customer'  => ['id' => $customer_id, 'name' => $customer_name],
            'job_works' => $job_works,
            'customers' => $customers,
        ]);
    }

    public function createDeliveryChallan()
    {
        $job_works = JobWork::select('id','job_work_num','job_work_date','customer_id','customer_name','invoice_status')
            ->whereNot('job_work_status','Cancelled')
            ->where('invoice_status','Not Generated')
            ->whereHas('items', fn($q) => $q->where('qty_kg', '>', 0))
            ->get();

        // return response()->json([
        return view('transactions.job-work.make_delivery_challan', [
            'job_works' => $job_works
        ]);
    }

    public function buildDeliveryChallan(Request $request)
    {
        JobWork::where('job_work_num', $request->job_work_num)
            ->update(['invoice_status' => 'Generated']);

        return response()->json([
            'success' => true,
            'message' => "Delivery Challan Generated Successfully!",
        ]);
    }

    public function showDeliveryChallan(Request $request)
    {        
        $job_work_nums = $request->input('job_work_nums');
        $job_work_num = $request->input('job_work_num');        
        $job_work = JobWork::where('job_work_num',$job_work_num)->first();
        $job_work_items = JobWorkItem::where('job_work_num',$job_work->job_work_num)->get();
        $job_work->customer_data = json_decode($job_work->customer_data);

        // return response()->json([
        return view('transactions.job-work.view_delivery_challan', [
            'job_work' => $job_work,
            'job_work_items' => $job_work_items,
            'job_work_nums' => $job_work_nums
        ]);
    }

    public function printDeliveryChallan(Request $request)
    {
        $job_work_num = $request->input('job_work_num');
        $job_work = JobWork::where('job_work_num', $job_work_num)->first();
        $job_work_items = JobWorkItem::where('job_work_num', $job_work->job_work_num)->get();
        $job_work->customer_data = json_decode($job_work->customer_data);

        return view('transactions.job-work.view_delivery_challan', [
            'job_work' => $job_work,
            'job_work_items' => $job_work_items,
            'job_work_nums' => $job_work_num
        ])->render();
    }
}