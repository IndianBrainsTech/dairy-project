<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Contracts\View\View;
use App\Models\Masters\PriceMaster;
use App\Models\Masters\DiscountMaster;
use App\Models\Masters\IncentiveMaster;
use App\Models\Masters\Outstanding;
use App\Models\Masters\Turnover;
use App\Models\Masters\Setting;
use App\Models\Masters\RupeeNote;
use App\Models\Masters\BankMaster;
use App\Models\Masters\GstMaster;
use App\Models\Masters\TcsMaster;
use App\Models\Masters\TdsMaster;
use App\Models\Products\Product;
use App\Models\Profiles\Customer;
use App\Models\Masters\CashDenomination;
use App\Models\Masters\DateEntrySetting;
use App\Models\Transactions\OpeningBalance;
use App\Http\Traits\CustomerUtility;
use App\Http\Traits\SalesUtility;

class MasterController extends Controller
{
    use CustomerUtility;
    use SalesUtility;

    public function __construct() 
    {
        $this->middleware('auth');
    }
    
    public function indexGstMasters() 
    {
        $gst_masters = GstMaster::all();
        // return response()->json(['gst_masters' => $gst_masters]);
        return view('masters.tax.gst_master', [
            'gst_masters' => $gst_masters
        ]);
    }

    public function editGstMaster($id)
    {
    	$gst_master = GstMaster::find($id);
	    return response()->json([
	      'gst_master' => $gst_master
	    ]);
    }

    public function storeGstMaster($id)
    {              
        try {
            GstMaster::updateOrCreate(
                [ 'id' => $id ],
                [ 'hsn_code' => request('hsn_code'),
                  'description' => request('description'),
                  'tax_type' => request('tax_type'),
                  'gst' => request('gst'),
                  'sgst' => request('sgst'),
                  'cgst' => request('cgst'),
                  'igst' => request('igst') ]
            );
            return response()->json([ 'success' => true ]);
        }
        catch(QueryException $exception) {
            return $exception;
        }
    }

    public function destroyGstMaster($id)
    {                   
        $gst_master = GstMaster::find($id);
        $gst_master->delete();
        return response()->json([ 'success' => true ]);
    }    

    public function listGstMasters() 
    {
        $gst_masters = GstMaster::select('id','hsn_code')->orderBy('hsn_code')->get();
        // echo $gst_masters;
        return response()->json([
            'gst_masters' => $gst_masters
        ]);
    }

    public function getGstInfo($hsn)
    {        
        $gst_info = GstMaster::select('id','hsn_code','tax_type','gst','sgst','cgst','igst')->where('hsn_code',$hsn)->first();
        return response()->json([
            'gst_info' => $gst_info
        ]);
    }

    public function indexTcsMasters()
    {
        $tcs_masters = TcsMaster::orderBy('effect_date','DESC')->get();

        $effect_date = "";
        $row = "";
        $cnt = count($tcs_masters);
        $today = date('Y-m-d');
        if($cnt>0) {
            $effect_date = $tcs_masters[0]->effect_date;
            for($i=0; $i<$cnt; $i++) {
                if($tcs_masters[$i]->effect_date < $today) {
                    $row = $i+1;
                    break;
                }
            }
        }

        // return response()->json([
        return view('masters.tax.tcs_master', [
            'tcs_masters' => $tcs_masters,
            'effect_date' => $effect_date,
            'row'         => $row
        ]);
    }

    public function storeTcsMaster()
    {        
        try {
            TcsMaster::updateOrCreate(
                [ 'id' => request('id') ],
                [ 'effect_date' => request('effect_date'),
                  'tcs_limit'   => request('tcs_limit'),
                  'with_pan'    => request('with_pan'),
                  'without_pan' => request('without_pan') ]
            );
            return response()->json([ 'success' => true ]);
        }
        catch(QueryException $exception) {
            return $exception;
        }
    }    

    public function indexTdsMasters()
    {
        $tds_masters = TdsMaster::orderBy('effect_date','DESC')->get();

        $effect_date = "";
        $row = "";
        $cnt = count($tds_masters);
        $today = date('Y-m-d');
        if($cnt>0) {
            $effect_date = $tds_masters[0]->effect_date;
            for($i=0; $i<$cnt; $i++) {
                if($tds_masters[$i]->effect_date < $today) {
                    $row = $i+1;
                    break;
                }
            }
        }

        // return response()->json([
        return view('masters.tax.tds_master', [
            'tds_masters' => $tds_masters,
            'effect_date' => $effect_date,
            'row'         => $row
        ]);
    }

    public function storeTdsMaster()
    {
        try {
            TdsMaster::updateOrCreate(
                [ 'id' => request('id') ],
                [ 'effect_date' => request('effect_date'),
                  'tds_limit'   => request('tds_limit'),
                  'with_pan'    => request('with_pan'),
                  'without_pan' => request('without_pan') ]
            );
            return response()->json([ 'success' => true ]);
        }
        catch(QueryException $exception) {
            return $exception;
        }
    }

    public function indexPriceMasters()
    {
        if (auth()->user()->name !== 'Admin') {
            abort(403, 'Unauthorized access.');
        }

        $priceMasters = PriceMaster::select('id','txn_id','effect_date','narration')->get();
        // return response()->json([
        return view('masters.pricing.list_price_masters', [
            'price_masters' => $priceMasters
        ]);
    }

    public function createPriceMaster()
    {
        $txnId = $this->getPriceMasterTxnId();
        $products = Product::select('id','name')
                            ->where('status','Active')
                            ->orderBy('display_index')
                            ->get();
        $customers = Customer::select('id','customer_name','group','route_id')
                            ->with('route:id,name')
                            ->where('status','Active')
                            ->orderBy('customer_name')
                            ->get();
        // return response()->json([
        return view('masters.pricing.manage_price_master', [
            'txn_id'    => $txnId,
            'txn_date'  => date('Y-m-d'),
            'products'  => $products,
            'customers' => $customers
        ]);
    }

    public function storePriceMaster(Request $request)
    {
        // return $request->all();
        try {
            $priceMaster = new PriceMaster();
            $priceMaster->txn_id        = $this->getPriceMasterTxnId();
            $priceMaster->txn_date      = date('Y-m-d');
            $priceMaster->effect_date   = $request->effect_date;
            $priceMaster->narration     = $request->narration;
            $priceMaster->customer_ids  = json_encode($request->cust_ids);
            $priceMaster->price_list    = json_encode($request->price_list);
            $priceMaster->save();
            return response()->json([ 
                'success' => true,
                'message' => "Price Master Generated Successfully!"
            ]);
        }
        catch(QueryException $exception) {
            return $exception;
        }
    }

    public function editPriceMaster(Request $request)
    {        
        $id = $request->input('id');
        $priceMaster = PriceMaster::select('id','txn_id','txn_date','effect_date','narration','customer_ids','price_list')->where('id',$id)->first();
        $priceList = json_decode($priceMaster->price_list,true);
        $customerIds = json_decode($priceMaster->customer_ids);
        $applicableCustomers = Customer::whereIn('id', $customerIds)->get(['id', 'customer_name']);

        $products = Product::select('id','name')
                            ->where('status','Active')
                            ->orderBy('display_index')
                            ->get();
        $customers = Customer::select('id','customer_name','group','route_id')
                            ->with('route:id,name')
                            ->where('status','Active')
                            ->orderBy('customer_name')
                            ->get();
        
        // return response()->json([
        return view('masters.pricing.manage_price_master', [
            'id'          => $priceMaster->id,
            'txn_id'      => $priceMaster->txn_id,
            'txn_date'    => $priceMaster->txn_date,
            'effect_date' => $priceMaster->effect_date,
            'narration'   => $priceMaster->narration,
            'products'    => $products,
            'customers'   => $customers,            
            'applicable_customers' => $applicableCustomers,
            'price_list'  => $priceList
        ]);
    }

    public function updatePriceMaster(Request $request)
    {
        // return $request->all();
        try {
            $priceMaster = PriceMaster::find($request->id);
            $priceMaster->effect_date   = $request->effect_date;
            $priceMaster->narration     = $request->narration;
            $priceMaster->customer_ids  = json_encode($request->cust_ids);
            $priceMaster->price_list    = json_encode($request->price_list);
            $priceMaster->save();
            return response()->json([ 
                'success' => true,
                'message' => "Price Master Updated Successfully!"
            ]);
        }
        catch(QueryException $exception) {
            return $exception;
        }
    }

    public function showPriceMaster(Request $request)
    {
        $id = $request->input('id');
        $priceMaster = PriceMaster::select('id','txn_id','txn_date','effect_date','narration','customer_ids','price_list','status')->where('id',$id)->first();
        $customerIds = json_decode($priceMaster->customer_ids);
        $priceList = json_decode($priceMaster->price_list,true);
        
        // Fetch customer data
        $customers = Customer::whereIn('id', $customerIds)->get(['id', 'customer_name']);

        // Fetch product data
        $productIds = array_keys($priceList);
        $products = Product::whereIn('id', $productIds)->get(['id', 'name']);

        // Create a mapping of product IDs to their prices
        $productPrices = [];
        foreach ($products as $product) {
            $productPrices[] = [
                'id' => $product->id,
                'name' => $product->name,
                'price' => $priceList[$product->id],
            ];
        }

        // Create a structured response
        $response = [
            'id' => $priceMaster->id,
            'txn_id' => $priceMaster->txn_id,
            'txn_date' => displayDate($priceMaster->txn_date),
            'effect_date' => displayDate($priceMaster->effect_date),
            'narration' => $priceMaster->narration,
            'customers' => $customers,
            'product_prices' => $productPrices,
            'status' => $priceMaster->status,
        ];

        // return response()->json($response);
        return view('masters.pricing.view_price_master', $response);
    }

    public function statusPriceMaster($id)
    {        
        $priceMaster = PriceMaster::find($id);
        $status = ($priceMaster->status == "Active") ? "Inactive" : "Active";
        $priceMaster->status = $status;
        $priceMaster->save();
        if($status == "Active")
            return back()->with('success', 'Price Master is now Active');
        else
            return back()->with('success', 'Price Master is now Inactive');
    }

    private function getPriceMasterTxnId()
    {
        $lastRecord = PriceMaster::latest('id')->first();
        if ($lastRecord) {
            $lastRecordNumber = substr($lastRecord->txn_id, 3);        
            $newRecordNumber = sprintf('%03d', intval($lastRecordNumber) + 1);
            $priceMasterNum = 'PR-' . $newRecordNumber;
        }
        else {            
            $priceMasterNum = 'PR-001';
        }
        return $priceMasterNum;
    }

    public function indexDiscountMasters()
    {
        // if (auth()->user()->name !== 'Admin') {
        //     abort(403, 'Unauthorized access.');
        // }

        $discountMasters = DiscountMaster::select('id','txn_id','effect_date','narration')->get();
        // return response()->json([
        return view('masters.pricing.list_discount_masters', [
            'discount_masters' => $discountMasters
        ]);
    }

    public function createDiscountMaster()
    {
        $txnId = $this->getDiscountMasterTxnId();
        $products = Product::select('id','name')
                            ->where('status','Active')
                            ->orderBy('display_index')
                            ->get();
        $customers = Customer::select('id','customer_name','group','route_id')
                            ->with('route:id,name')
                            ->where('status','Active')
                            ->orderBy('customer_name')
                            ->get();
        // return response()->json([
        return view('masters.pricing.manage_discount_master', [
            'txn_id'    => $txnId,
            'txn_date'  => date('Y-m-d'),
            'products'  => $products,
            'customers' => $customers
        ]);
    }

    public function storeDiscountMaster(Request $request)
    {
        // return $request->all();
        try {
            $discountMaster = new DiscountMaster();
            $discountMaster->txn_id        = $this->getDiscountMasterTxnId();
            $discountMaster->txn_date      = date('Y-m-d');
            $discountMaster->effect_date   = $request->effect_date;
            $discountMaster->narration     = $request->narration;
            $discountMaster->customer_ids  = json_encode($request->cust_ids);
            $discountMaster->discount_list = json_encode($request->discount_list);
            $discountMaster->save();
            return response()->json([ 
                'success' => true,
                'message' => "Discount Master Generated Successfully!"
            ]);
        }
        catch(QueryException $exception) {
            return $exception;
        }
    }

    public function editDiscountMaster(Request $request)
    {        
        $id = $request->input('id');
        $discountMaster = DiscountMaster::select('id','txn_id','txn_date','effect_date','narration','customer_ids','discount_list')->where('id',$id)->first();
        $discountList = json_decode($discountMaster->discount_list,true);
        $customerIds = json_decode($discountMaster->customer_ids);
        $applicableCustomers = Customer::whereIn('id', $customerIds)->get(['id', 'customer_name']);

        $products = Product::select('id','name')
                            ->where('status','Active')
                            ->orderBy('display_index')
                            ->get();
        $customers = Customer::select('id','customer_name','group','route_id')
                            ->with('route:id,name')
                            ->where('status','Active')
                            ->orderBy('customer_name')
                            ->get();
        
        // return response()->json([
        return view('masters.pricing.manage_discount_master', [
            'id'            => $discountMaster->id,
            'txn_id'        => $discountMaster->txn_id,
            'txn_date'      => $discountMaster->txn_date,
            'effect_date'   => $discountMaster->effect_date,
            'narration'     => $discountMaster->narration,
            'products'      => $products,
            'customers'     => $customers,            
            'applicable_customers' => $applicableCustomers,
            'discount_list' => $discountList
        ]);
    }

    public function updateDiscountMaster(Request $request)
    {
        // return $request->all();
        try {
            $discountMaster = DiscountMaster::find($request->id);
            $discountMaster->effect_date   = $request->effect_date;
            $discountMaster->narration     = $request->narration;
            $discountMaster->customer_ids  = json_encode($request->cust_ids);
            $discountMaster->discount_list = json_encode($request->discount_list);
            $discountMaster->save();
            return response()->json([ 
                'success' => true,
                'message' => "Discount Master Updated Successfully!"
            ]);
        }
        catch(QueryException $exception) {
            return $exception;
        }
    }

    public function showDiscountMaster(Request $request)
    {
        $id = $request->input('id');
        $discountMaster = DiscountMaster::select('id','txn_id','txn_date','effect_date','narration','customer_ids','discount_list','status')->where('id',$id)->first();
        $customerIds = json_decode($discountMaster->customer_ids);
        $discountList = json_decode($discountMaster->discount_list,true);
        
        // Fetch customer data
        $customers = Customer::whereIn('id', $customerIds)->get(['id', 'customer_name']);

        // Fetch product data
        $productIds = array_keys($discountList);
        $products = Product::whereIn('id', $productIds)->get(['id', 'name']);

        // Create a mapping of product IDs to their discounts
        $productDiscounts = [];
        foreach ($products as $product) {
            $productDiscounts[] = [
                'id' => $product->id,
                'name' => $product->name,
                'discount' => $discountList[$product->id],
            ];
        }

        // Create a structured response
        $response = [
            'id' => $discountMaster->id,
            'txn_id' => $discountMaster->txn_id,
            'txn_date' => displayDate($discountMaster->txn_date),
            'effect_date' => displayDate($discountMaster->effect_date),
            'narration' => $discountMaster->narration,
            'customers' => $customers,
            'product_discounts' => $productDiscounts,
            'status' => $discountMaster->status,
        ];

        // return response()->json($response);
        return view('masters.pricing.view_discount_master', $response);
    }

    public function statusDiscountMaster($id)
    {        
        $discountMaster = DiscountMaster::find($id);
        $status = ($discountMaster->status == "Active") ? "Inactive" : "Active";
        $discountMaster->status = $status;
        $discountMaster->save();
        if($status == "Active")
            return back()->with('success', 'Discount Master is now Active');
        else
            return back()->with('success', 'Discount Master is now Inactive');
    }

    private function getDiscountMasterTxnId()
    {
        $lastRecord = DiscountMaster::latest('id')->first();
        if ($lastRecord) {
            $lastRecordNumber = substr($lastRecord->txn_id, 3);        
            $newRecordNumber = sprintf('%03d', intval($lastRecordNumber) + 1);
            $discMasterNum = 'DS-' . $newRecordNumber;
        }
        else {            
            $discMasterNum = 'DS-001';
        }
        return $discMasterNum;
    }    

    public function indexIncentiveMasters()
    {
        // if (auth()->user()->name !== 'Admin') {
        //     abort(403, 'Unauthorized access.');
        // }
        
        $incentiveMasters = IncentiveMaster::select('id','txn_id','effect_date','narration')->get();
        // return response()->json([
        return view('masters.pricing.list_incentive_masters', [
            'incentive_masters' => $incentiveMasters
        ]);
    }

    public function createIncentiveMaster()
    {
        $txnId = $this->getIncentiveMasterTxnId();
        $products = Product::select('id','name')
                            ->where('status','Active')
                            ->orderBy('display_index')
                            ->get();
        $customers = Customer::select('id','customer_name','group','route_id')
                            ->with('route:id,name')
                            ->where('status','Active')
                            ->orderBy('customer_name')
                            ->get();
        // return response()->json([
        return view('masters.pricing.manage_incentive_master', [
            'txn_id'    => $txnId,
            'txn_date'  => date('Y-m-d'),
            'products'  => $products,
            'customers' => $customers
        ]);
    }

    public function storeIncentiveMaster(Request $request)
    {
        // return $request->all();        
        try {
            $incentiveMaster = new IncentiveMaster();
            $incentiveMaster->txn_id   = $this->getIncentiveMasterTxnId();
            $incentiveMaster->txn_date = date('Y-m-d');
            $this->setIncentiveMasterInfo($incentiveMaster, $request);
            $incentiveMaster->save();
            return response()->json([ 
                'success' => true,
                'message' => "Incentive Master Generated Successfully!"
            ]);
        }
        catch(QueryException $exception) {
            return $exception;
        }
    }

    private function setIncentiveMasterInfo(IncentiveMaster $incentiveMaster, Request $request)
    {
        $incentiveMaster->effect_date    = $request->effect_date;
        $incentiveMaster->narration      = $request->narration;
        $incentiveMaster->customer_ids   = json_encode($request->cust_ids);
        $incentiveMaster->incentive_type = $request->incentive_type;
        if($request->incentive_type == "Fixed") {
            $incentiveMaster->incentive_rate = $request->incentive_rate;
            $incentiveMaster->slab_data = null;
        }
        else if($request->incentive_type == "Slab") {
            $incentiveMaster->slab_data = $request->slab_data;
            $incentiveMaster->incentive_rate = null;
        }
        $incentiveMaster->incentive_data = $request->incentive_data;
    }

    public function editIncentiveMaster(Request $request)
    {        
        $id = $request->input('id');
        $incentiveMaster = IncentiveMaster::select('id','txn_id','txn_date','effect_date','narration','customer_ids','incentive_type','incentive_rate','slab_data','incentive_data','status')->where('id',$id)->first();
        $customerIds = json_decode($incentiveMaster->customer_ids);
        $applicableCustomers = Customer::whereIn('id', $customerIds)->get(['id', 'customer_name']);
        $slabData = json_decode($incentiveMaster->slab_data,true);
        $incentiveData = json_decode($incentiveMaster->incentive_data,true);

        $products = Product::select('id','name')
                            ->where('status','Active')
                            ->orderBy('display_index')
                            ->get();
        $customers = Customer::select('id','customer_name','group','route_id')
                            ->with('route:id,name')
                            ->where('status','Active')
                            ->orderBy('customer_name')
                            ->get();
        
        // return response()->json([
        return view('masters.pricing.manage_incentive_master', [
            'id'            => $incentiveMaster->id,
            'txn_id'        => $incentiveMaster->txn_id,
            'txn_date'      => $incentiveMaster->txn_date,
            'effect_date'   => $incentiveMaster->effect_date,
            'narration'     => $incentiveMaster->narration,
            'products'      => $products,
            'customers'     => $customers,
            'applicable_customers' => $applicableCustomers,
            'incentive_type' => $incentiveMaster->incentive_type,
            'incentive_rate' => $incentiveMaster->incentive_rate,
            'slab_data'      => $slabData,
            'incentive_data' => $incentiveData,
        ]);
    }

    public function updateIncentiveMaster(Request $request)
    {
        // return $request->all();
        try {
            $incentiveMaster = IncentiveMaster::find($request->id);
            $this->setIncentiveMasterInfo($incentiveMaster, $request);
            $incentiveMaster->save();
            return response()->json([ 
                'success' => true,
                'message' => "Incentive Master Updated Successfully!"
            ]);
        }
        catch(QueryException $exception) {
            return $exception;
        }
    }

    public function showIncentiveMaster(Request $request)
    {
        $id = $request->input('id');
        $incentiveMaster = IncentiveMaster::select('id','txn_id','txn_date','effect_date','narration','customer_ids','incentive_type','incentive_rate','slab_data','incentive_data','status')->where('id',$id)->first();
        $customerIds = json_decode($incentiveMaster->customer_ids);
        $slabData = json_decode($incentiveMaster->slab_data,true);
        $incentiveData = json_decode($incentiveMaster->incentive_data,true);

        // Fetch customer data
        $customers = Customer::whereIn('id', $customerIds)->get(['id', 'customer_name']);

        // Update product name from product table
        foreach($incentiveData as &$data) {
            $data['product'] = Product::where('id', $data['id'])->value('name');
        }

        // Create a structured response
        $response = [
            'id' => $incentiveMaster->id,
            'txn_id' => $incentiveMaster->txn_id,
            'txn_date' => displayDate($incentiveMaster->txn_date),
            'effect_date' => displayDate($incentiveMaster->effect_date),
            'narration' => $incentiveMaster->narration,
            'customers' => $customers,
            'incentive_type' => $incentiveMaster->incentive_type,
            'incentive_rate' => $incentiveMaster->incentive_rate,
            'slab_data' => $slabData,
            'incentive_data' => $incentiveData,
            'status' => $incentiveMaster->status,
        ];

        // return response()->json($response);
        return view('masters.pricing.view_incentive_master', $response);
    }

    public function statusIncentiveMaster($id)
    {
        $incentiveMaster = IncentiveMaster::find($id);
        $status = ($incentiveMaster->status == "Active") ? "Inactive" : "Active";
        $incentiveMaster->status = $status;
        $incentiveMaster->save();
        if($status == "Active")
            return back()->with('success', 'Incentive Master is now Active');
        else
            return back()->with('success', 'Incentive Master is now Inactive');
    }

    private function getIncentiveMasterTxnId()
    {
        $lastRecord = IncentiveMaster::latest('id')->first();
        if ($lastRecord) {
            $lastRecordNumber = substr($lastRecord->txn_id, 3);
            $newRecordNumber = sprintf('%03d', intval($lastRecordNumber) + 1);
            $incentiveMasterNum = 'IN-' . $newRecordNumber;
        }
        else {
            $incentiveMasterNum = 'IN-001';
        }
        return $incentiveMasterNum;
    }

    public function indexBankAccounts()
    {
        $bankMasters = BankMaster::select('id','display_name')->orderBy('id')->get();
        // return response()->json([
        return view('masters.bank_masters', [
            'bank_masters' => $bankMasters
        ]);
    }

    public function editBankAccount($id)
    {
    	$bankAccount = BankMaster::find($id);
	    return response()->json([
	      'bank_account' => $bankAccount
	    ]);
    }

    public function storeBankAccount($id)
    {              
        try {
            BankMaster::updateOrCreate(
                [ 'id' => $id ],
                [ 'bank_name' => request('bank_name'),
                  'acc_holder' => request('acc_holder'),
                  'acc_number' => request('acc_number'),
                  'ifsc' => request('ifsc'),
                  'branch' => request('branch'),
                  'display_name' => request('display_name') ]
            );
            return response()->json([ 'success' => true ]);
        }
        catch(QueryException $exception) {
            return $exception;
        }
    }

    public function settings()
    {
        $setting = Setting::select('category','key','value')->get();
        // return response()->json([
        return view('masters.settings', [
            'settings' => $setting
        ]);
    }

    public function updateSettings(Request $request)
    {
        $category = $request->category;
        if($category == "Invoice") {
            $this->updateSetting("Invoice", "sales-invoice", $request->sales_invoice);            
            $this->updateSetting("Invoice", "tax-invoice",   $request->tax_invoice);
            $this->updateSetting("Invoice", "bulk-milk",     $request->bulk_milk);
            $this->updateSetting("Invoice", "conversion",    $request->conversion);
            $this->updateSetting("Invoice", "order",         $request->order);
        }
        return response()->json([ 'success' => true ]);
    }

    private function updateSetting($category, $key, $value)
    {
        Setting::where('category', $category)
            ->where('key', $key)
            ->update(['value' => $value]);
    }

    public function test()
    {
        $customerId = 23;
        $total = 107318.92;
        return $this->calcTdsAmount($customerId, $total);
    }

    public function createOpeningCash()
    {
        $notes = RupeeNote::orderBy('display_index')->pluck('note_value');
        return view('masters.finance.opening_cash', [            
            'notes'  => $notes,
        ]);
    }

    public function openingCash(Request $request)
    {
        try {
            $cash = new CashDenomination();
            $cash->edate = date('Y-m-d');
            $cash->amount = $request->amount;
            $cash->denomination = $request->denomination;
            $cash->save();

            $opening = new OpeningBalance();
            $opening->opening_amount = $request->amount;
            $opening->denomination = $request->denomination;
            $opening->type = "Opening";
            $opening->save();

            return response()->json([ 
                'success' => true,
                'message' => "Data Saved Successfully!",
            ]);
        }
        catch(QueryException $exception){
            return $exception;
        }
    }

    public function createDateSetting()
    {
        $settings = DateEntrySetting::select('tag', 'days_before', 'days_after')
            ->get()
            ->keyBy(function ($item) {
                return strtolower($item->tag);   // 'cash', 'bank'
            });

        return view('masters.date-settings', compact('settings'));
    }

    public function updateDateSetting(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'mode'        => 'required|string|in:cash,bank',
            'days_before' => 'required|integer|min:0|max:364',
            'days_after'  => 'required|integer|min:0|max:364',
        ]);

        try {
            $updated = DateEntrySetting::where('tag', strtoupper($validated['mode']))
                ->update([
                    'days_before' => $validated['days_before'],
                    'days_after'  => $validated['days_after'],
                ]);

            if ($updated === 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'No matching date setting found.',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Receipt date setting updated successfully!',
            ]);
        }
        catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update date setting.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }
}
