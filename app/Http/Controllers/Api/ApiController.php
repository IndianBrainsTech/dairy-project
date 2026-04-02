<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Profiles\Employee;
use App\Models\Profiles\Customer;
use App\Models\Profiles\Competitor;
use App\Models\Products\Product;
use App\Models\Products\ProductUnit;
use App\Models\Products\ProductGroup;
use App\Models\Products\UOM;
use App\Models\Orders\Order;
use App\Models\Orders\OrderItem;
use App\Models\Transactions\Enquiry;
use App\Models\Transactions\Followup;
use App\Models\Transactions\CompetitorData;
use App\Models\Transactions\MobileData;
use App\Models\Transactions\LocationData;
use App\Models\Transactions\PhotoUpload;
use App\Models\Transactions\Attendance;
use App\Models\Places\Area;
use Storage;

class ApiController extends Controller
{
    public function register(Request $request) 
    {        
        try {
            $employee = Employee::where('mobile_num',$request->mobile_num); 

            if($employee->count() > 0) { // Employee Exists with Mobile Number
                $employee = $employee->get()->first();
            
                if($employee->status == "Active") { // Employee Active
                    
                    $mobileData = MobileData::where('user_id',$employee->id);

                    if($mobileData->count() == 0) { // No Mobile Data

                            $mobileData = MobileData::where('model',$request->model)
                                                    ->where('unique_code',$request->unique_code);                                                    

                            if($mobileData->count() == 0) { // New Registration
                                $mobileData = new MobileData();
                                $mobileData->user_id        = $employee->id;
                                $mobileData->mobile_num     = $request->mobile_num;
                                $mobileData->app_version    = $request->app_version;
                                $mobileData->model          = $request->model;
                                $mobileData->android_version = $request->android_version;
                                $mobileData->unique_code    = $request->unique_code;
                                $mobileData->otp            = '1010'; // $mobileData->otp = rand(1000,9999);
                                $mobileData->save();
                
                                return response()->json([
                                    'status' => "1",
                                    'message' => "OTP has sent to your mobile number",
                                    'otp_verified' => "0",
                                    'user_id' => $employee->id
                                ]);
                            }
                            else { // Mobile Data Exists, but different mobile number entered
                                $id = $mobileData->get()->first()->user_id;
                                return response()->json([
                                    'status' => "0",
                                    'message' => "Mobile Data Duplicate(Ref#" . $id . ")! Contact Admin!"   // Chances to Linked with Another Number                                    
                                ]);
                            }
                    }
                    else {                        
                        $mobileData = $mobileData->get()->first();

                        if($mobileData->mobile_num == $request->mobile_num) {
                            if(($mobileData->model == $request->model) && ($mobileData->unique_code == $request->unique_code)) {
                                return response()->json([
                                    'status' => "1",
                                    'message' => "Already Registered with this Mobile! Login Now!",
                                    'otp_verified' => $mobileData->otp_verified,
                                    'user_id' => $employee->id,
                                    'mobile_num' => $mobileData->mobile_num
                                ]);
                            }
                            else {
                                return response()->json([
                                    'status' => "0",
                                    'message' => "Mobile Data Mismatch! Contact Admin!"
                                ]);
                            }
                        }
                        else {
                            return response()->json([
                                'status' => "0",
                                'message' => "Mobile Number Mismatch! Contact Admin!"
                            ]);
                        }
                    }
                    
                }
                else { // Inactive Employee
                    return response()->json([
                        'status' => "0",
                        'message' => "User Access Deactivated"
                    ]);
                }
            }
            else { // Unknown Mobile Number
                return response()->json([
                    'status' => "0",
                    'message' => "Mobile number not exists in our database"
                ]);
            }
        }
        catch(QueryException $exception) {
            return response()->json([
                'status' => "0",
                'message' => $exception->getMessage()
            ]);
        }   
    }
    
    public function verifyOtp(Request $request)
    {
        try {
            $mobileData = MobileData::where('user_id',$request->user_id); 

            if($mobileData->count() > 0) {                
                $mobileData = $mobileData->get()->last();
            
                if($mobileData->otp == $request->otp) {
                    $mobileData = MobileData::find($mobileData->id);
                    $mobileData->otp_verified = "1";
                    $mobileData->save();

                    return response()->json([
                        'status' => "1",
                        'message' => "User Registered Successfully",
                        'user_id' => $mobileData->user_id,
                        'mobile_num' => $mobileData->mobile_num
                    ]);
                }
                else {
                    return response()->json([
                        'status' => "0",
                        'message' => "Incorrect OTP"
                    ]);
                }                
            }
            else {
                return response()->json([
                    'status' => "0",
                    'message' => "User not exists"
                ]);
            }
        }
        catch(QueryException $exception) {
            return response()->json([
                'status' => "0",
                'message' => $exception->getMessage()
            ]);
        }        
    }

    public function login(Request $request) 
    {       
        $result = [];
        try {

            if($request->has('user_id')) {
                $employee = Employee::where('id',$request->user_id);

                if($employee->count() > 0) {                
                    $employee = $employee->get()->first();
                
                    if($employee->password == $request->password) {
                        $mobileData = MobileData::where('user_id',$request->user_id);

                        if($mobileData->count() == 0) {
                            $result['status'] = 0;
                            $result['message'] = "No Registration Found! Please Register";
                            $result['register'] = 1;
                        }
                        else {
                            $mobileData = $mobileData->get()->first();
                            if($mobileData->mobile_num == $request->mobile_num) {

                                if(($mobileData->model == $request->model) && ($mobileData->unique_code == $request->unique_code)) {

                                    if($mobileData->otp_verified == "1") {
                                        $data = [];
                                        $data['name'] = $employee->name;
                                        $data['code'] = $employee->code;
                                        $data['role_id'] = $employee->role->id;
                                        $data['role_name'] = $employee->role->role_name;
                                        $data['role_sname'] = $employee->role->short_name;
                                        // $result['role_display'] = $employee->role->short_name <> null ? $employee->role->short_name : $employee->role->role_name;
                                        $data['photo'] = "employees/" . $employee->photo;
                                        $data['mobile_num'] = $employee->mobile_num;
                                        $data['email_id'] = $employee->email_id;
                                        $data['address'] = $employee->address;
                
                                        $attendance = Attendance::select('attn_session','time_in','time_out')
                                                                ->where('attn_date',date('Y-m-d'))
                                                                ->where('emp_id',$employee->id)
                                                                ->get();
                                        $data['attendance'] = $attendance;

                                        $result['status'] = 1;
                                        $result['message'] = "Login Success";
                                        $result['data'] = $data;

                                        if($mobileData->app_version <> $request->app_version) {
                                            $mobileData = MobileData::find($mobileData->id);
                                            $mobileData->app_version = $request->app_version;
                                            $mobileData->save();
                                        }   
                                    }
                                    else {
                                        $result['status'] = 0;
                                        $result['message'] = "OTP Not Verified on Last Registration! Please Register Again!";
                                        $result['register'] = 1;
                                    }                                 
                                }
                                else {
                                    $result['status'] = 0;
                                    $result['message'] = "Seems Mobile Changed! Please Register Again!";
                                    $result['register'] = 1;
                                }
                            }
                            else {
                                $result['status'] = 0;
                                $result['message'] = "Seems Mobile Number Changed! Please Register Again!";
                                $result['register'] = 1;
                            }
                        }
                    }
                    else {
                        $result['status'] = 0;
                        $result['message'] = "Incorrect Password";
                        $result['register'] = 0;
                    }
                }
                else {
                    $result['status'] = 0;
                    $result['message'] = "User not exists";
                    $result['register'] = 0;
                }
            }
            else {
                $result['status'] = 0;
                $result['message'] = "Is New User/Mobile? Please Register!";
                $result['register'] = 1;
            }
        }
        catch(QueryException $exception) {
            $result['status'] = 0;
            $result['message'] = $exception->getMessage();
            $result['register'] = 0;
        }  

        return json_encode($result);
    }
    
    public function resetPassword(Request $request) 
    {       
        $result = []; 
        try {
            $employee = Employee::find($request->user_id);
            if(!is_null($employee))
            {
                if($employee->password == $request->old_pwd) {
                    $employee->password = $request->new_pwd;
                    $employee->save();
                    $result['status'] = 1;
                    $result['message'] = "Password Resetted Successfully";
                }
                else {
                    $result['status'] = 0;
                    $result['message'] = "Incorrect Password";
                }
            }
            else {
                $result['status'] = 0;
                $result['message'] = "User Not Found";
            }
        }
        catch(QueryException $exception) {
            $result['status'] = 0;
            $result['message'] = $exception->getMessage();
        }
        return json_encode($result);
    }

    public function fetchDataEnquiry(Request $request)
    {       
        $result = []; 
        try {
            $areas = Area::select('id','name')->orderBy('name')->get();
            $competitors = Competitor::select('id','display_name')->get();
            $prodGroups = ProductGroup::select('id','name')->get();
            $result['status'] = 1;
            $result['message'] = "Data Fetched Successfully";
            $result['areas'] = $areas;
            $result['competitors'] = $competitors;
            $result['prodGroups'] = $prodGroups;
        }
        catch(QueryException $exception) {
            $result['status'] = 0;
            $result['message'] = $exception->getMessage();
        }  
        return json_encode($result);
    }

    public function newEnquiry(Request $request)
    {       
        $result = [];
        // $result['request'] = $request->all();
        try {
            $enquiry = new Enquiry();
            $enquiry->shop_name     = $request->shop_name;
            $enquiry->shop_type     = $request->shop_type;
            $enquiry->area_id       = $request->area_id;
            $enquiry->area_name     = $request->area_name;
            $enquiry->address       = $request->address;
            $enquiry->landmark      = $request->landmark;
            $enquiry->followup_date = $request->followup_date;
            $enquiry->enq_datetime  = $request->enq_datetime;
            $enquiry->latitude      = $request->latitude;
            $enquiry->longitude     = $request->longitude;
            $enquiry->emp_id        = $request->emp_id;
            $enquiry->save();

            $this->addLocationData($request->emp_id,$request->latitude,$request->longitude,"Enquiry",$request->shop_name,"");

            $result['status'] = 1;
            $result['message'] = "Enquiry Saved Successfully";
            $result['enquiry_id'] = $enquiry->id;
        }
        catch(QueryException $exception) {
            $result['status'] = 0;
            $result['message'] = $exception->getMessage();
        }  
        return json_encode($result);
    }

    public function saveEnquiry(Request $request)
    {       
        $result = [];
        // $result['request'] = $request->all();
        try {
            $enquiry = Enquiry::find($request->enquiry_id);
            $enquiry->contact_num   = $request->contact_num;
            $enquiry->contact_name  = $request->contact_name;
            $enquiry->alternate_num = $request->alternate_num;
            $enquiry->alternate_name = $request->alternate_name;
            $enquiry->save();

            /* Add Competitor Data */
            $comp_data = json_decode($request->competitor_data,true);
            foreach($comp_data as $data) {
                $competitorData = new CompetitorData();
                $competitorData->enquiry_id    = $enquiry->id;
                $competitorData->competitor_id = $data['comp_id'];
                
                if(!empty($data['offers']))
                    $competitorData->offers = $data['offers'];
                if(!empty($data['remarks']))
                    $competitorData->remarks = $data['remarks'];
                
                $prod_data = str_replace("\"","",$data['product_data']);
                if(!empty($prod_data))
                    $competitorData->product_data  = $prod_data;
                
                $competitorData->save();
            }
            /*------------------------ */

            /* Add Followup */
            $followup = new Followup();
            $followup->enquiry_id       = $enquiry->id;
            $followup->emp_id           = $enquiry->emp_id;
            $followup->remarks          = "First Visit";
            $followup->followup_status  = "First Visit";
            $followup->next_visit_date  = $enquiry->followup_date;            
            $followup->followup_datetime = $enquiry->enq_datetime;
            $followup->latitude         = $enquiry->latitude;
            $followup->longitude        = $enquiry->longitude;
            $followup->save();
            /*------------------------ */
            
            $result['status'] = 1;
            $result['message'] = "Enquiry Stored Successfully";
            $result['enquiry_id'] = $enquiry->id;
        }
        catch(QueryException $exception) {
            $result['status'] = 0;
            $result['message'] = $exception->getMessage();
        }  
        return json_encode($result);
    }

    public function storeEnquiry(Request $request)
    {       
        $result = [];
        // $result['request'] = $request->all();
        try {
            $enquiry = new Enquiry();
            $enquiry->shop_name     = $request->shop_name;
            $enquiry->shop_type     = $request->shop_type;
            $enquiry->area_id       = $request->area_id;
            $enquiry->area_name     = $request->area_name;
            $enquiry->address       = $request->address;
            $enquiry->landmark      = $request->landmark;
            $enquiry->remarks       = $request->remarks;
            $enquiry->followup_date = $request->followup_date;
            $enquiry->contact_num   = $request->contact_num;
            $enquiry->contact_name  = $request->contact_name;
            $enquiry->alternate_num = $request->alternate_num;
            $enquiry->alternate_name = $request->alternate_name;
            $enquiry->enq_datetime  = $request->enq_datetime;
            $enquiry->latitude      = $request->latitude;
            $enquiry->longitude     = $request->longitude;
            $enquiry->emp_id        = $request->emp_id;
            $enquiry->save();

            // Add Location Data
            $this->addLocationData($request->emp_id,$request->latitude,$request->longitude,"Enquiry",$request->shop_name,$request->remarks);

            /* Add Competitor Data */
            $comp_data = json_decode($request->competitor_data,true);
            foreach($comp_data as $data) {
                $competitorData = new CompetitorData();
                $competitorData->enquiry_id    = $enquiry->id;
                $competitorData->competitor_id = $data['comp_id'];
                
                if(!empty($data['offers']))
                    $competitorData->offers = $data['offers'];
                if(!empty($data['remarks']))
                    $competitorData->remarks = $data['remarks'];
                
                $prod_data = str_replace("\"","",$data['product_data']);
                if(!empty($prod_data))
                    $competitorData->product_data  = $prod_data;
                
                $competitorData->save();
            }
            /*------------------------ */

            /* Add Followup */
            $followup = new Followup();
            $followup->enquiry_id       = $enquiry->id;
            $followup->emp_id           = $enquiry->emp_id;
            $followup->remarks          = $enquiry->remarks;
            $followup->followup_status  = "First Visit";
            $followup->next_visit_date  = $enquiry->followup_date;            
            $followup->followup_datetime = $enquiry->enq_datetime;
            $followup->latitude         = $enquiry->latitude;
            $followup->longitude        = $enquiry->longitude;
            $followup->save();
            /*------------------------ */
            
            $result['status'] = 1;
            $result['message'] = "Enquiry Stored Successfully\nPlease Take Photos to Complete!";
            $result['enquiry_id'] = $enquiry->id;
        }
        catch(QueryException $exception) {
            $result['status'] = 0;
            $result['message'] = $exception->getMessage();
        }  
        return json_encode($result);
    }

    public function uploadPhoto(Request $request)
    {       
        // $result['request'] = $request->all();
        if($request->tag == "Enquiry") {
            $result = $this->uploadEnquiryPhoto($request);
            return json_encode($result);
        }
        else if($request->tag == "Profile") {
            $result = $this->updateProfilePhoto($request);
            return json_encode($result);
        }        
    }
    
    private function uploadEnquiryPhoto(Request $request)
    {
        $result = [];
        try {
            $photoUpload = new PhotoUpload();
            $photoUpload->emp_id = $request->emp_id;
            $photoUpload->tag = $request->tag;
            $photoUpload->tag_id = $request->tag_id;
            $photoUpload->upload_datetime = $request->udt;
            $photoUpload->save();
            
            /*--------- Save Photo -----------*/
            $id = $photoUpload->id;
            $photo_path = 'public/enquiries/';
            if(isset($request->photo)) {                
                $photoName = $id.'.png';
                $request->file('photo')->storeAs($photo_path, $photoName);
            }
            $photoUpload->name = $photoName;
            $photoUpload->save();
            /* ------------------------------ */

            $result['status'] = 1;
            $result['message'] = "Photo Uploaded Successfully";
        }
        catch(QueryException $exception) {
            $result['status'] = 0;
            $result['message'] = $exception->getMessage();
        }  
        return $result;
    }

    private function updateProfilePhoto(Request $request)
    {
        $result = [];
        try {
            $employee = Employee::find($request->emp_id);
            $photo_path = 'public/employees/';
            if(isset($request->photo)) {
                $profile_photo = $photo_path . $employee->photo;
                if(Storage::exists($profile_photo))
                    Storage::delete($profile_photo);

                $imageName = $employee->id.'.'.$request->photo->extension();
                $request->file('photo')->storeAs($photo_path, $imageName);
            }
            $employee->photo = $imageName;
            $employee->save();

            $result['status'] = 1;
            $result['message'] = "Photo Updated Successfully";
            $result['photo'] = "employees/" . $imageName;
        }
        catch(QueryException $exception) {
            $result['status'] = 0;
            $result['message'] = $exception->getMessage();
        }  
        return $result;
    }
    
    private function updateEnquiryStatus($enquiry_id, $status)
    {
        $conversion_status = "";
        if($status == "Followup Again")
            $conversion_status = "Followup in Progress";
        else if($status == "Add as Customer")
            $conversion_status = "Conversion Request in Progress";
        else if($status == "Stop Followup")
            $conversion_status = "Followup Stopped";            
        $enquiry = Enquiry::find($enquiry_id);
        $enquiry->conversion_status = $conversion_status;
        $enquiry->save();
    }

    public function followups(Request $request)
    {
        $result = [];
        try {
            $enq_ids = Followup::where('emp_id',$request->emp_id)
                                ->whereIn('followup_status',['Add as Customer','Converted as Customer','Stop Followup'])
                                ->get('enquiry_id');
            $enqIds = array();
            foreach($enq_ids as $enq_id)
                array_push($enqIds,$enq_id->enquiry_id);

            $followups = Followup::select('id','enquiry_id','next_visit_date','followup_status')
                                ->with('enquiry:id,shop_name,area_name')
                                ->where('emp_id',$request->emp_id)
                                ->whereNotIn('enquiry_id',$enqIds)
                                ->orderByDesc('next_visit_date')
                                ->get();

            $result['status'] = 1;
            $result['message'] = "Followups Retrieved Successfully";
            $result['followups'] = $followups;
            $result['enquiriy_ids'] = $enqIds;
        }
        catch(QueryException $exception) {
            $result['status'] = 0;
            $result['message'] = $exception->getMessage();
        }  
        return $result;
    }

    public function followupHistory(Request $request)
    {
        $result = [];
        try {
            $followups = Followup::select('id','remarks','next_visit_date','followup_status','followup_datetime')
                                ->where('enquiry_id',$request->enquiry_id)
                                ->get();
                                
            $result['status'] = 1;
            $result['message'] = "Followup History Retrieved Successfully";
            $result['followups'] = $followups;
        }
        catch(QueryException $exception) {
            $result['status'] = 0;
            $result['message'] = $exception->getMessage();
        }  
        return $result;
    }

    public function addFollowup(Request $request)
    {       
        $result = [];
        // $result['request'] = $request->all();
        try {
            Followup::create($request->all());

            $this->updateEnquiryStatus($request->enquiry_id, $request->followup_status);

            $message = "Followup Saved Successfully";
            if($request->followup_status == "Add as Customer")
                $message = "Request Sent for Approval";

            $this->addLocationData($request->emp_id,$request->latitude,$request->longitude,"Followup","Shop",$request->remarks);

            $result['status'] = 1;
            $result['message'] = $message;
        }
        catch(QueryException $exception) {
            $result['status'] = 0;
            $result['message'] = $exception->getMessage();
        }  
        return json_encode($result);
    }

    public function products()
    {
        $result = [];
        try {
            $products = Product::select('id','name','short_name','group_id','description','mrp','fat','snf','image')
                                ->with('prod_group:id,name')
                                ->where('visible_app',1)
                                ->where('status','Active')
                                ->orderBy('display_index')
                                ->get();                                                                
            foreach($products as $product) {
                $product['units'] = ProductUnit::select('unit_id','prim_unit')
                                ->with('unit:id,unit_name')
                                ->where('product_id',$product->id)
                                ->orderByDesc('prim_unit')
                                ->get();
                $product['image'] = "products/" . $product['image'];
            }
            $result['status'] = 1;
            $result['message'] = "Products Retrieved Successfully";
            $result['products'] = $products;
        }
        catch(QueryException $exception) {
            $result['status'] = 0;
            $result['message'] = $exception->getMessage();
        }  
        return $result;
    }

    public function units()
    {
        $result = [];
        try {
            $units = UOM::select('id','unit_name','display_name')->get();                                
            $result['status'] = 1;
            $result['message'] = "Units Retrieved Successfully";
            $result['units'] = $units;
        }
        catch(QueryException $exception) {
            $result['status'] = 0;
            $result['message'] = $exception->getMessage();
        }  
        return $result;
    }

    public function areas()
    {
        $result = [];
        try {
            $areas = Area::select('id','name')
                            ->orderBy('name')
                            ->get();
            $result['status'] = 1;
            $result['message'] = "Areas Retrieved Successfully";
            $result['areas'] = $areas;
        }
        catch(QueryException $exception) {
            $result['status'] = 0;
            $result['message'] = $exception->getMessage();
        }  
        return $result;
    }

    public function shops(Request $request)
    {
        $result = [];
        try {
            $shops = Customer::select('id','customer_name')
                            ->where('staff_id',$request->user_id)
                            ->orderBy('customer_name')
                            ->get();
            $result['status'] = 1;
            $result['message'] = "Shops Retrieved Successfully";
            $result['shops'] = $shops;
        }
        catch(QueryException $exception) {
            $result['status'] = 0;
            $result['message'] = $exception->getMessage();
        }  
        return $result;
    }

    public function markAttendance(Request $request)
    {       
        $result = [];
        // $result['request'] = $request->all();
        try {
            if(isset($request->time_in)) {
                $attendance = new Attendance();
                $attendance->emp_id = $request->emp_id;
                $attendance->attn_date = $request->attn_date;
                $attendance->attn_session = $request->attn_session;
                $attendance->time_in = $request->time_in;
                $attendance->latitude_in = $request->latitude;
                $attendance->longitude_in = $request->longitude;
                $attendance->save();                
                $result['status'] = 1;                
                $result['message'] = "Attendance Marked Successfully";
                $result['session'] = $attendance->attn_session;
                $result['time'] = $attendance->time_in;
                $result['type'] = "in";
            }
            else if(isset($request->time_out)) {
                $attendance = Attendance::where('attn_date',$request->attn_date)
                                        ->where('emp_id',$request->emp_id)
                                        ->where('attn_session',$request->attn_session)
                                        ->get()->first();
                $attendance->time_out = $request->time_out;
                $attendance->latitude_out = $request->latitude;
                $attendance->longitude_out = $request->longitude;
                $attendance->save();
                $result['status'] = 1;
                $result['message'] = "Attendance Marked Successfully";
                $result['session'] = $attendance->attn_session;
                $result['time'] = $attendance->time_in;
                $result['type'] = "out";
            }
            
            $this->addLocationData($request->emp_id,$request->latitude,$request->longitude,"Log".$result['type'],$request->attn_session,"");
        }
        catch(QueryException $exception) {
            $result['status'] = 0;
            $result['message'] = $exception->getMessage();
        }  
        return json_encode($result);
    }

    public function updateLocation(Request $request)
    {       
        $result = [];
        // $result['request'] = $request->all();
        try {
            $locationData = new LocationData();
            $locationData->emp_id = $request->emp_id;
            $locationData->latitude = $request->latitude;
            $locationData->longitude = $request->longitude;
            $locationData->tag = $request->tag;
            $locationData->save();
            $result['status'] = 1;
            $result['message'] = "Location Data Added Successfully";
        }
        catch(QueryException $exception) {
            $result['status'] = 0;
            $result['message'] = $exception->getMessage();
        }  
        return json_encode($result);
    }

    private function addLocationData($empId, $latitude, $longitude, $tag, $title, $description)
    {
        $result = [];
        try {
            $locationData = new LocationData();
            $locationData->emp_id       = $empId;
            $locationData->latitude     = $latitude;
            $locationData->longitude    = $longitude;
            $locationData->tag          = $tag;
            $locationData->title        = $title;
            $locationData->description  = $description;
            $locationData->save();
            $result['status'] = 1;
            $result['message'] = "Location Data Added Successfully";
        }
        catch(QueryException $exception) {
            $result['status'] = 0;
            $result['message'] = $exception->getMessage();
        }  
        return json_encode($result);
    }

    public function placeOrder(Request $request)
    {
        $result = [];
        try {
            $customer = Customer::select('id','area_id','route_id')->where('id',$request->shop_id)->get()->first();
            $order = new Order();
            $order->customer_id = $customer->id;
            $order->area_id = $customer->area_id;
            $order->route_id = $customer->route_id;
            $order->user_id = $request->user_id;
            $order->order_status = "Placed";
            $order->order_dt = date('Y-m-d H:i:s');
            $order->save();

            $order->order_num = 1000 + $order->id;
            $order->save();

            $orderInfo = json_decode($request->orders,true);
            foreach($orderInfo as $orderData) {
                $orderItem = new OrderItem();
                $orderItem->order_num   = $order->order_num;
                $orderItem->product_id  = $orderData['prod_id'];
                $orderItem->product_name = $orderData['prod_name'];
                $orderItem->qty         = $orderData['qty'];
                $orderItem->unit_id     = $orderData['unit_id'];
                $orderItem->unit_name   = $orderData['unit_name'];
                $orderItem->save();
            }

            $result['status'] = 1;
            $result['message'] = "Order Placed Successfully";
            // $result['data'] = $orderInfo[0]['prod_id'];
        }
        catch(QueryException $exception) {            
            $result['status'] = 0;
            $result['message'] = $exception->getMessage();
        }
        return json_encode($result);
    }

    public function listOrders(Request $request)
    {
        $result = [];
        try {
            $orders = Order::select('id','order_num','order_dt','customer_id','area_id','order_status')
                            ->with('customer:id,customer_name')
                            ->with('area:id,name')
                            ->where('user_id',$request->user_id)
                            ->orderByDesc('order_dt')
                            ->get();
            foreach($orders as $order) {
                $order->order_dt = getIndiaDateTime($order->order_dt);
            }
            $result['status'] = 1;
            $result['message'] = "Orders Retrieved Successfully";
            $result['orders'] = $orders;
        }
        catch(QueryException $exception) {
            $result['status'] = 0;
            $result['message'] = $exception->getMessage();
        }  
        return $result;
    }

    public function viewOrder(Request $request)
    {
        $result = [];
        try {
            $order = Order::select('id','order_num','order_dt','customer_id','area_id','order_status')
                            ->with('customer:id,customer_name')
                            ->with('area:id,name')
                            ->where('order_num',$request->order_num)
                            ->get()->first();
            $order->order_dt = getIndiaDateTime($order->order_dt);
            
            $orderItems = OrderItem::select('product_id','product_name','qty','unit_name','unit_id')
                            ->where('order_num',$request->order_num)
                            ->get();

            $result['status'] = 1;
            $result['message'] = "Order Information Retrieved Successfully";
            $result['order'] = $order;
            $result['orderItems'] = $orderItems;
        }
        catch(QueryException $exception) {
            $result['status'] = 0;
            $result['message'] = $exception->getMessage();
        }  
        return $result;
    }
}
