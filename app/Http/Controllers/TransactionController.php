<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Transactions\Enquiry;
use App\Models\Transactions\Followup;
use App\Models\Transactions\Attendance;
use App\Models\Transactions\CompetitorData;
use App\Models\Transactions\PhotoUpload;
use App\Models\Transactions\LocationData;
use App\Models\Profiles\Customer;
use App\Models\Profiles\ViewDesignation;

class TransactionController extends Controller
{
    public function __construct() 
    {
        $this->middleware('auth');
    }

    public function indexEnquiries(Request $request)
    {
        $fromDate = $toDate = date('Y-m-d');
        $empId = "0";
        if(isset($request->fromDate)) {
            $fromDate = $request->input('fromDate');
            $toDate = $request->input('toDate');
            $empId = $request->input('empId');
        }
        $enquiries = Enquiry::select('id','shop_name','area_name','contact_num','enq_datetime','emp_id','latitude','longitude')
                        ->with('employee:id,name')
                        ->whereBetween('enq_datetime',[$fromDate." 00:00:00",$toDate." 23:59:59"])
                        ->when($empId<>"0", function($query) use($empId) { return $query->where('emp_id', $empId); })
                        ->orderByDesc('enq_datetime')
                        ->get();
                        
        $employees = Enquiry::select('emp_id')
                        ->distinct()
                        ->with('employee:id,name')
                        ->get();

        // return response()->json([ 
        return view('transactions.enquiry.enquiries', [
            'fromDate' => $fromDate,
            'toDate' => $toDate,
            'empId' => $empId,
            'employees' => $employees,
            'enquiries' => $enquiries
        ]);
    }

    public function showEnquiry(Request $request)
    {
        $id = $request->input('id');
        $enquiry = Enquiry::with('employee:id,name')
                            ->where('id',$id)
                            ->get()->first();
        $competitor_data = CompetitorData::select('id','competitor_id','product_data','offers','remarks')
                            ->with('competitor:id,comp_name')
                            ->where('enquiry_id',$id)
                            ->get();
        $photos = PhotoUpload::select('name','description')
                            ->where('tag_id',$id)
                            ->where('tag','Enquiry')
                            ->get();
        // return response()->json(['enquiry' => $enquiry, 'competitor_data' => $competitor_data, 'photos' => $photos]);
        return view('transactions.enquiry.view_enquiry', [
            'enquiry' => $enquiry,
            'competitor_data' => $competitor_data,
            'photos' => $photos
        ]);
    }
    
    public function indexFollowups(Request $request) 
    {
        $fromDate = $toDate = date('Y-m-d');
        $empId = "0";
        if(isset($request->fromDate)) {
            $fromDate = $request->input('fromDate');
            $toDate = $request->input('toDate');
            $empId = $request->input('empId');
        }

        $followups = Followup::select('id','enquiry_id','emp_id','remarks','followup_datetime')
                        ->where('followup_status','<>','Converted as Customer')
                        ->whereBetween('followup_datetime',[$fromDate." 00:00:00",$toDate." 23:59:59"])
                        ->when($empId<>"0", function($query) use($empId) { return $query->where('emp_id', $empId); })
                        ->with('employee:id,name')
                        ->with('enquiry:id,shop_name,area_name')
                        ->orderByDesc('id')
                        ->get();
                        
        $employees = Followup::select('emp_id')
                        ->distinct()
                        ->with('employee:id,name')
                        ->get();
                        
        // return response()->json([
        return view('transactions.enquiry.followups', [
            'fromDate' => $fromDate,
            'toDate' => $toDate,
            'empId' => $empId,
            'employees' => $employees,
            'followups' => $followups
        ]);        
    }

    public function showFollowup(Request $request)
    {
        $id = $request->input('id');
        $followup = Followup::with('employee:id,name')
                            ->with('enquiry:id,shop_name,area_name')
                            ->where('id',$id)
                            ->get();

        $enq_id = $followup[0]->enquiry->id;
        $enquiry = Enquiry::where('id',$enq_id)->get();
        $history = Followup::where('enquiry_id',$enq_id)->with('employee:id,name')->get();

        // return response()->json(['followup' => $followup->first(), 'enquiry' => $enquiry->first(), 'history' => $history]);
        return view('transactions.enquiry.view_followup', [
            'followup' => $followup->first(),
            'enquiry' => $enquiry->first(),
            'history' => $history
        ]);
    }

    public function indexConversions() 
    {
        $followups = Followup::select('id','enquiry_id','emp_id','remarks','followup_datetime')
                            ->with('employee:id,name')
                            ->with('enquiry:id,shop_name,area_name,contact_num,latitude,longitude')
                            ->where('followup_status','Add as Customer')
                            ->orderByDesc('created_at')
                            ->get();  
        // return response()->json(['followups' => $followups]);
        return view('transactions.enquiry.shop_conversions', [
            'followups' => $followups
        ]);
    }

    public function indexAttendances(Request $request)
    {
        $fromDate = $toDate = date('Y-m-d');
        $empId = "0";
        if(isset($request->fromDate)) {
            $fromDate = $request->input('fromDate');
            $toDate = $request->input('toDate');
            $empId = $request->input('empId');
        }

        //SELECT emp_id,attn_date,GROUP_CONCAT(attn_session) as attn_session,GROUP_CONCAT(time_in) as time_in,GROUP_CONCAT(time_out) as time_out FROM `attendances` GROUP BY attn_date,emp_id;
        $attendances = Attendance::select(DB::raw('emp_id,attn_date,GROUP_CONCAT(attn_session) as attn_session,GROUP_CONCAT(time_in) as time_in,GROUP_CONCAT(time_out) as time_out,GROUP_CONCAT(latitude_in) as latitude_in,GROUP_CONCAT(latitude_out) as latitude_out,GROUP_CONCAT(longitude_in) as longitude_in,GROUP_CONCAT(longitude_out) as longitude_out'))
                                ->with('employee:id,name,code')
                                ->whereBetween('attn_date',[$fromDate." 00:00:00",$toDate." 23:59:59"])
                                ->when($empId<>"0", function($query) use($empId) { return $query->where('emp_id', $empId); })
                                ->orderByDesc('id')
                                ->groupBy('attn_date')
                                ->groupBy('emp_id')
                                ->get();

        $employees = Attendance::select('emp_id')
                        ->distinct()
                        ->with('employee:id,name')
                        ->get();

        // return response()->json([
        return view('transactions.attendances', [
            'fromDate' => $fromDate,
            'toDate' => $toDate,
            'empId' => $empId,
            'employees' => $employees,
            'attendances' => $attendances
        ]);
    }

    public function showDayRoute(Request $request)
    {
        $id = $request->input('id');
        $date = $request->input('date');

        $employee = ViewDesignation::select('emp_id','emp_name','emp_code','role_name')
                                        ->where('emp_id',$id)
                                        ->get()
                                        ->first();
        $location_data = LocationData::select('latitude','longitude','tag','title','description','created_at')
                                        ->where('emp_id',$id)
                                        ->whereDate('created_at',$date)
                                        ->orderBy('created_at')
                                        ->get();
        // return response()->json([
        return view('transactions.dayroute', [
            'employee' => $employee,
            'date' => $date,
            'location_data' => $location_data
        ]);
    }
}
