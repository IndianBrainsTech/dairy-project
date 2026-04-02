<?php

namespace App\Http\Controllers;

use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use App\Http\Requests\CustomerRequest;
use App\Http\Requests\EmployeeRequest;
use App\Models\Profiles\Customer;
use App\Models\Profiles\Employee;
use App\Models\Profiles\Competitor;
use App\Models\Profiles\Designation;
use App\Models\Profiles\ViewDesignation;
use App\Models\Places\MRoute;
use App\Models\Places\Area;
use App\Models\Places\District;
use App\Models\Places\Address;
use App\Models\Masters\Outstanding;
use App\Models\Masters\Turnover;
use App\Models\Transactions\Enquiry;
use App\Models\Transactions\Followup;
use App\Models\Orders\SalesInvoice;
use App\Models\Orders\TaxInvoice;
use App\Http\Traits\CustomerUtility;
use App\Http\Traits\SalesUtility;
use Storage;

class CustomerController extends Controller
{
    use CustomerUtility;
    use SalesUtility;

    public function __construct() 
    {
        $this->middleware('auth');
    }

    public function indexCustomers() 
    {
        $customers = Customer::select('id','customer_name','customer_code','group','route_id','area_id','contact_num','payment_mode','status')
                        ->with('route:id,name')
                        ->with('area:id,name')
                        ->orderBy('id')
                        ->get();
                        
        // Sort the customers based on the numeric part of 'customer_code'
        $sortedCustomers = $customers->sortBy(function ($customer) {
            return (int) filter_var($customer->customer_code, FILTER_SANITIZE_NUMBER_INT);
        });

        // Reverse the sorted customers if needed
        $reversedCustomers = $sortedCustomers->reverse();

        $customers = $reversedCustomers->values();

        // return response()->json(['customers' => $customers]);
        return view('masters.profiles.list_customers', [
            'customers' => $customers
        ]);
    }

    public function createCustomer() 
    {        
        $routes = MRoute::select('id','name')->orderBy('id')->get();
        $link_customers = Customer::select('id','customer_name')
                            ->where('status','Active')
                            ->orderBy('customer_name')
                            ->get();
        $employees = ViewDesignation::select('emp_id','emp_name','role_name','short_name')
                            ->where('department','Sales')
                            ->orderBy('emp_name')
                            ->get();
        return view('masters.profiles.manage_customer', [
            'routes' => $routes,
            'staffs' => $employees,
            'link_customers' => $link_customers,
        ]);
    }
    
    public function storeCustomer(CustomerRequest $request)
    {           
        // return $request->all();

        try {
            $customer = new Customer();
            $this->setCustomerInfo($customer,$request);
            $customer->save();

            /* Save Profile Image and Shop Photo*/
            $id = $customer->id;

            $profile_path = 'public/customers/profile/';
            if(isset($request->profile_image)) {
                $imageName = $id.'.'.$request->profile_image->extension();
                $request->file('profile_image')->storeAs($profile_path, $imageName);
            }
            else {
                $imageName = $id.'.png';
                Storage::copy('public/avatar1.png', $profile_path.$imageName);
            }
            $customer->profile_image = $imageName;

            $shop_photo_path = 'public/customers/shop/';
            if(isset($request->shop_photo)) {
                $imageName = $id.'.'.$request->shop_photo->extension();
                $request->file('shop_photo')->storeAs($shop_photo_path, $imageName);
            }
            else {
                $imageName = $id.'.jpg';
                Storage::copy('public/no-image.jpg', $shop_photo_path.$imageName);
            }
            $customer->shop_photo = $imageName;

            $customer->save();
            /* ------------------------------ */

            $message = 'Customer Added Successfully';
            if(!empty($request->enq_id)) {
                $this->updateEnquiryStatus($request->enq_id, $customer);
                $message = 'Shop Converted to Customer Successfully';
            }

            return back()->with('success', $message);
        }
        catch(QueryException $exception) {            
            //dd($exception);
            return back()->with('error', $exception->getMessage())->withInput();
        }
    }

    public function showCustomer(Request $request)
    {
        $id = $request->input('id');
        $customer = Customer::find($id);
        $link_customer = Customer::select('id','customer_name')
                            ->where('id',$customer->link_cust_id)
                            ->first();
        
        if($customer->staff_id == 0) {
            $staff_incharge = "Admin";
        }
        else {
            $employee = ViewDesignation::select('emp_id','emp_name','role_name','short_name')
                            ->where('emp_id',$customer->staff_id)
                            ->first();
            if($employee->short_name)
                $employee->role_name = $employee->short_name;
            // $staff_incharge = sprintf("%s [%s](%s)",$employee->emp_name,$employee->emp_id,$employee->role_name);
            $staff_incharge = sprintf("%s (%s)",$employee->emp_name,$employee->role_name);
        }

        $addresses = Address::select('id','address_lines','district','state','pincode')->where('customer_id',$id)->get();
        $districts = District::select('id','name')->orderBy('name')->get();

        // return response()->json([
        return view('masters.profiles.view_customer', [
            'customer' => $customer,
            'staff_incharge' => $staff_incharge,
            'link_customer' => $link_customer,
            'addresses' => $addresses,
            'districts' => $districts
        ]);
    }

    public function editCustomer(Request $request)
    {        
        $id = $request->input('id');
        $customer = Customer::find($id);
        $routes = MRoute::select('id','name')->orderBy('id')->get();
        $link_customers = Customer::select('id','customer_name')
                            ->where('status','Active')
                            ->orderBy('customer_name')
                            ->get();
        $employees = ViewDesignation::select('emp_id','emp_name','role_name','short_name')
                            ->where('department','Sales')                            
                            ->orderBy('emp_name')
                            ->get();
        // return response()->json([
        return view('masters.profiles.manage_customer', [
            'customer' => $customer,        
            'routes' => $routes,
            'staffs' => $employees,
            'link_customers' => $link_customers
        ]);
    }

    public function updateCustomer($id, CustomerRequest $request)
    {    
        // return $request->all();       
        try {
            $customer = Customer::find($id);
            $this->setCustomerInfo($customer,$request);
            $customer->save();
            return back()->with('success', 'Customer Updated Successfully');
        }
        catch(QueryException $exception) {            
            return back()->with('error', $exception->getMessage())->withInput();
        }
    }

    public function destroyCustomer($id)
    {        
        $customer = Customer::find($id);
        $customer->delete();
        return response()->json([ 'success' => true ]);
    }

    public function statusCustomer($id)
    {        
        $customer = Customer::find($id);
        $status = ($customer->status == "Active") ? "Inactive" : "Active";
        $customer->status = $status;
        $customer->save();
        if($status == "Active")
            return back()->with('success', 'Customer is now Active');
        else
            return back()->with('success', 'Customer is now Inactive');
    }

    public function convertCustomer(Request $request)
    {
        $id = $request->input('id');
        $enquiry = Enquiry::find($id);
        $route = Area::select('id','name','route_id')->where('id',$enquiry->area_id)->first();
        $conversion = [];
        $conversion['enquiry_id']       = $enquiry->id;
        $conversion['customer_name']    = $enquiry->shop_name;
        $conversion['group']            = $enquiry->shop_type;
        $conversion['route_id']         = $route->route_id;
        $conversion['area_id']          = $enquiry->area_id;
        $conversion['address']          = $enquiry->address;
        $conversion['landmark']         = $enquiry->landmark;
        $conversion['contact_num']      = $enquiry->contact_num;
        $conversion['contact_name']     = $enquiry->contact_name;
        $conversion['alternate_num']    = $enquiry->alternate_num;
        $conversion['alternate_name']   = $enquiry->alternate_name;
        $conversion['emp_id']           = $enquiry->emp_id;
        $conversion['customer_since']   = date('Y-m-d');
        
        $routes = MRoute::select('id','name')->orderBy('id')->get();
        $link_customers = Customer::select('id','customer_name')
                            ->where('status','Active')
                            ->orderBy('customer_name')
                            ->get();
        $employees = ViewDesignation::select('emp_id','emp_name','role_name','short_name')
                            ->where('department','Sales')
                            ->orderBy('emp_name')
                            ->get();
                 
        // return response()->json([
        return view('masters.profiles.manage_customer', [
            'conversion' => $conversion,
            'routes' => $routes,
            'staffs' => $employees,
            'link_customers' => $link_customers,
            'route' => $route
        ]);
    }

    public function getCustomersByRoute($routeId)
    {       
        $customers = Customer::select('id','customer_name')
                        ->when($routeId<>0, function($query) use($routeId) { return $query->where('route_id', $routeId); })                        
                        ->where('status','Active')
                        ->orderBy('customer_name')
                        ->get();
        return response()->json([
            'customers' => $customers
        ]);
    }

    public function customerOutstanding()
    {
        // Fetch the active customers
        $customers = Customer::select('id', 'customer_name')
                            ->where('status', 'Active')
                            ->orderBy('customer_name')
                            ->get();

        // Fetch the active outstanding records
        $oustd = Outstanding::select('id', 'customer_id', 'customer_name', 'amount', 'txn_date')
                            ->where('status', 'Active')
                            ->get();

        // Convert $oustd to an associative array with customer_id as the key for easy lookup
        $outstandingData = $oustd->keyBy('customer_id');

        // Iterate through each customer and merge the corresponding outstanding data
        $customerData = $customers->map(function ($customer) use ($outstandingData) {
            // Check if the customer has outstanding data
            if (isset($outstandingData[$customer->id])) {
                // Merge amount and txn_date fields with the customer's data
                $customer->amount = $outstandingData[$customer->id]->amount;
                $customer->date = $outstandingData[$customer->id]->txn_date;
            } else {
                // Set default values if no outstanding data is found
                $customer->amount = null;
                $customer->date = null;
            }
            return $customer;
        });

        // Return the response with merged data
        // return response()->json([
        return view('masters.finance.customer_outstanding', [
            'customers' => $customerData            
        ]);
    }

    public function updateCustomerOutstanding(Request $request)
    {
        $customerId = $request->cust_id;
        $date = $request->tdate;
        $amount = round($request->amount);

        // Find the active outstanding record for the customer
        $oustd = Outstanding::where('customer_id', $customerId)
                            ->where('status', 'Active')
                            ->first();

        // Check if both amount and date are null or empty; if so, mark existing record as inactive
        if (empty($amount) && empty($date)) {
            if ($oustd) {
                // Mark the existing record as inactive
                $oustd->update(['status' => 'Inactive']);
            }
            // Return without adding a new record
            return response()->json(['success' => true]);
        }

        // Check if an active record exists and if it matches the current data
        if ($oustd && ($date != $oustd->txn_date || $amount != $oustd->amount)) {
            // Mark the existing record as inactive if it doesn't match
            $oustd->update(['status' => 'Inactive']);
        }

        // Create a new outstanding record if no match was found or no active record exists
        if (!$oustd || ($date != $oustd->txn_date || $amount != $oustd->amount)) {
            Outstanding::create([
                'customer_id' => $customerId,
                'customer_name' => $request->name,
                'amount' => $amount,
                'txn_date' => $date,
            ]);
        }

        return response()->json(['success' => true]);
    }

    public function customerCreditLimit()
    {
        // Fetch the active customers
        $customers = Customer::select('id', 'customer_name','credit_limit')
                            ->where('status', 'Active')
                            ->orderBy('customer_name')
                            ->get();        

        // Return the response with merged data
        // return response()->json([
        return view('masters.finance.customer_credit_limit', [
            'customers' => $customers            
        ]);
    }

    public function updateCustomerCreditLimit(Request $request)
    {
        $customerId = $request->cust_id;
        $amount = round($request->amount);
        $customer = Customer::find($customerId);
        $customer->credit_limit = $amount;
        $customer->save();
        
        return response()->json(['success' => true]);
    }

    public function customerTurnover()
    {
        // Fetch the active customers
        $customers = Customer::select('id', 'customer_name', 'tcs_status')
                            ->where('status', 'Active')
                            ->orderBy('customer_name')
                            ->get();

        // Fetch the active turnover records
        $turnover = Turnover::select('id', 'customer_id', 'customer_name', 'amount', 'txn_date')
                            ->where('status', 'Active')
                            ->get();

        // Convert $turnover to an associative array with customer_id as the key for easy lookup
        $turnoverData = $turnover->keyBy('customer_id');

        // Iterate through each customer and merge the corresponding turnover data
        $customerData = $customers->map(function ($customer) use ($turnoverData) {
            // Check if the customer has turnover data
            if (isset($turnoverData[$customer->id])) {
                // Merge amount and txn_date fields with the customer's data
                $customer->amount = $turnoverData[$customer->id]->amount;
                $customer->date = $turnoverData[$customer->id]->txn_date;
            } else {
                // Set default values if no turnover data is found
                $customer->amount = null;
                $customer->date = null;
            }
            return $customer;
        });

        // Return the response with merged data
        // return response()->json([
        return view('masters.finance.customer_turnover', [
            'customers' => $customerData            
        ]);
    }

    public function updateCustomerTurnover(Request $request)
    {
        $customerId = $request->cust_id;
        $date = $request->tdate;
        $amount = $request->amount;

        // Find the active turnover record for the customer
        $turnover = Turnover::where('customer_id', $customerId)
                            ->where('status', 'Active')
                            ->first();

        // Check if both amount and date are null or empty; if so, mark existing record as inactive
        if (empty($amount) && empty($date)) {
            if ($turnover) {
                // Mark the existing record as inactive
                $turnover->update(['status' => 'Inactive']);
            }
            // Return without adding a new record
            return response()->json(['success' => true]);
        }

        // Check if an active record exists and if it matches the current data
        if ($turnover && ($date != $turnover->txn_date || $amount != $turnover->amount)) {
            // Mark the existing record as inactive if it doesn't match
            $turnover->update(['status' => 'Inactive']);
        }

        // Create a new turnover record if no match was found or no active record exists
        if (!$turnover || ($date != $turnover->txn_date || $amount != $turnover->amount)) {
            Turnover::create([
                'customer_id' => $customerId,
                'customer_name' => $request->name,
                'amount' => $amount,
                'txn_date' => $date,
            ]);
        }

        return response()->json(['success' => true]);
    }

    public function getAddressData($cust_id)
    {                
        $customer = Customer::select('address_lines','district','state','pincode')
                        ->where('id', $cust_id)
                        ->first();
        
        $addresses = [];
        $address['id']            = 0;
        $address['address_lines'] = $customer->address_lines;
        $address['district']      = $customer->district;
        $address['state']         = $customer->state;
        $address['pincode']       = $customer->pincode;
        $addresses[] = $address;

        $addAddr = Address::select('id','address_lines','district','state','pincode')->where('customer_id',$cust_id)->get()->toArray();
        $addresses = array_merge($addresses, $addAddr);

        return response()->json([ 'addresses' => $addresses ]);
    }

    public function getAddressAndTcsData($cust_id)
    {                
        $customer = Customer::select('id','pan_number','address_lines','district','state','pincode','tcs_status')
                        ->where('id',$cust_id)
                        ->first();
        
        $addresses = [];
        $address['id']       = 0;
        $address['address_lines'] = $customer->address_lines;
        $address['district'] = $customer->district;
        $address['state']    = $customer->state;
        $address['pincode']  = $customer->pincode;
        $addresses[] = $address;

        $addAddr = Address::select('id','address_lines','district','state','pincode')->where('customer_id',$cust_id)->get()->toArray();
        $addresses = array_merge($addresses, $addAddr);

        $tcsInfo = [];
        $tcsInfo['tcs_status'] = $customer->tcs_status;
        if($customer->tcs_status == "TCS Applied" || $customer->tcs_status == "TCS Applicable") {
            $tcsMaster = $this->getCurrentTcsMaster();
            $tcsInfo['tcs_percent'] = $customer->pan_number ? $tcsMaster->with_pan : $tcsMaster->without_pan;
            if($customer->tcs_status == "TCS Applicable") {
                $tcsInfo['turnover'] = $this->getTotalTurnover($customer->id);
                $tcsInfo['tcs_limit'] = $tcsMaster->tcs_limit;
            }
        }

        return response()->json([
            'addresses' => $addresses,
            'tcs_info' => $tcsInfo
        ]);
    }

    public function getCustomerBillingData($cust_id)
    {
        // return response()->json([
        //     'success' => true,
        //     'data'     => $request->all()
        // ]);
        $customer = Customer::select('id','tcs_status','pan_number','address_lines','district','state','pincode','credit_limit')
                        ->where('id',$cust_id)
                        ->first();
        
        $addresses = [];
        $address['id']       = 0;
        $address['address_lines'] = $customer->address_lines;
        $address['district'] = $customer->district;
        $address['state']    = $customer->state;
        $address['pincode']  = $customer->pincode;
        $addresses[] = $address;
        $creditLimit    = $customer->credit_limit;

        $addAddr = Address::select('id','address_lines','district','state','pincode')->where('customer_id',$cust_id)->get()->toArray();
        $addresses = array_merge($addresses, $addAddr);

        $tcsInfo = [];
        $tcsInfo['tcs_status'] = $customer->tcs_status;        
        if($customer->tcs_status == "TCS Applied" || $customer->tcs_status == "TCS Applicable") {
            $tcsMaster = $this->getCurrentTcsMaster();
            $tcsInfo['tcs_percent'] = $customer->pan_number ? $tcsMaster->with_pan : $tcsMaster->without_pan;
            if($customer->tcs_status == "TCS Applicable") {
                $tcsInfo['turnover'] = $this->getTotalTurnover($customer->id);
                $tcsInfo['tcs_limit'] = $tcsMaster->tcs_limit;
            }
        }

        $priceList = $this->getPriceListWithUnits(strval($cust_id));
        $discountList = $this->getDiscountList($cust_id);

        return response()->json([
            'addresses' => $addresses,
            'tcs_info' => $tcsInfo,
            'price_list' => $priceList,
            'discount_list' => $discountList,
            'credit_limit' => $creditLimit 
        ]);
    }

    private function setCustomerInfo(Customer $customer, CustomerRequest $request)
    {        
        $customer->customer_name    = $request->customer_name;
        $customer->customer_code    = $request->customer_code;
        $customer->group            = $request->customer_group;
        $customer->route_id         = $request->route;
        $customer->area_id          = $request->area;
        $customer->address_lines    = $request->address;
        $customer->district         = $request->district;
        $customer->state            = $request->state;
        $customer->landmark         = $request->landmark;
        $customer->pincode          = $request->pincode;
        $customer->contact_num      = $request->contact_number;
        $customer->contact_name     = $request->contact_name;
        $customer->alternate_num    = $request->alternate_number;
        $customer->alternate_name   = $request->alternate_name;
        $customer->email_id         = $request->email;
        $customer->staff_id         = $request->staff_id;
        $customer->remarks          = $request->remarks;
        $customer->billing_name     = $request->billing_name;
        $customer->credit_limit     = $request->credit_limit;
        $customer->gst_type         = $request->gst_type;
        $customer->gst_number       = $request->gst_number;
        $customer->pan_number       = $request->pan_number;
        $customer->outstanding      = $request->outstanding;
        $customer->incentive_mode   = $request->incentive_mode;
        $customer->payment_mode     = $request->payment_mode;
        $customer->tcs_status       = $request->tcs_status;
        $customer->tds_status       = $request->tds_status;
        $customer->link_customer    = $request->has('link_cust_chk') ? "1" : "0";
        $customer->link_cust_id     = $request->link_customer;
        $customer->customer_since   = $request->customer_since;
        $customer->owner_name       = $request->owner_name;
        $customer->gender           = $request->gender;
        $customer->dob              = $request->dob;
        $customer->aadhaar          = $request->aadhaar;
        $customer->bank_name        = $request->bank_name;
        $customer->branch           = $request->branch;
        $customer->ifsc             = $request->ifsc;
        $customer->acc_holder       = $request->acc_holder;
        $customer->acc_number       = $request->acc_number;
    }

    private function updateEnquiryStatus($enq_id, $customer)
    {
        $enquiry = Enquiry::find($enq_id);
        $enquiry->conversion_status = "Converted as Customer";
        $enquiry->customer_id = $customer->id;
        $enquiry->save();

        $followup = Followup::where('enquiry_id',$enq_id)->where('followup_status','Add as Customer')->first();
        $followup->followup_status = "Converted as Customer";
        $followup->save();

        $customer->latitude = $enquiry->latitude;
        $customer->longitude = $enquiry->longitude;
        $customer->save();

    }
    
/*
    public function getCustomersByArea($areaId) 
    {      
        $customers = Customer::select('id','customer_name','billing_name')
                        ->where('area_id',$areaId)
                        ->where('status','Active')
                        ->orderBy('customer_name')
                        ->get();
        return response()->json([
            'customers' => $customers
        ]);
    }

    public function getTcsData(Request $request)
    {
        $customerId = $request->customer_id;
        $customer = Customer::select('id','tcs_status','pan_number')->where('id',$customerId)->first();

        $tcsInfo = [];
        $tcsInfo['tcs_status'] = $customer->tcs_status;        
        if($customer->tcs_status == "TCS Applied" || $customer->tcs_status == "TCS Applicable") {
            $tcsMaster = $this->getCurrentTcsMaster();
            $tcsInfo['tcs_percent'] = $customer->pan_number ? $tcsMaster->with_pan : $tcsMaster->without_pan;
            if($customer->tcs_status == "TCS Applicable") {
                $tcsInfo['turnover'] = $this->getTotalTurnover($customer->id);
                $tcsInfo['tcs_limit'] = $tcsMaster->tcs_limit;
            }
        }

        return response()->json([ 'tcs_info' => $tcsInfo ]);
    }
*/
}
