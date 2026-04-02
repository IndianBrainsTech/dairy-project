<?php

namespace App\Http\Controllers;

use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use App\Http\Requests\EmployeeRequest;
use App\Models\Profiles\Employee;
use App\Models\Profiles\Competitor;
use App\Models\Profiles\Designation;
use App\Models\Profiles\ViewDesignation;
use App\Models\Places\District;
use App\Models\Places\Address;
use Storage;

class ProfileController extends Controller
{
    public function __construct() 
    {
        $this->middleware('auth');
    }
    
    public function indexEmployees() 
    {
        $employees = Employee::select('id','name','code','role_id','mobile_num','status')
                        ->with('role:id,role_name,short_name')                        
                        ->orderBy('id')
                        ->get();  
        // return response()->json(['employees' => $employees]);
        return view('masters.profiles.list_employees', [
            'employees' => $employees
        ]);        
    }

    public function createEmployee() 
    {        
        $designations = Designation::select('id','role_name','short_name')->whereNotIn('role_name',['Admin'])->get();
        $districts = District::select('id','name')->orderBy('name')->get();
        return view('masters.profiles.manage_employee', [
            'designations' => $designations,
            'districts' => $districts
        ]);
    }

    public function editEmployee(Request $request)
    {        
        $id = $request->input('id');
        $employee = Employee::find($id);
        $designations = Designation::select('id','role_name','short_name')->whereNotIn('role_name',['Admin'])->get();
        $districts = District::select('id','name')->orderBy('name')->get();
        return view('masters.profiles.manage_employee', [
            'employee' => $employee,
            'designations' => $designations,
            'districts' => $districts
        ]);
    }

    public function storeEmployee(EmployeeRequest $request)
    {           
        // return $request->all();

        try {
            $employee = new Employee();
            $this->setEmployeeInfo($employee,$request);
            $employee->save();

            /* Save Profile Image */
            $id = $employee->id;

            $photo_path = 'public/employees/';
            if(isset($request->photo)) {
                $imageName = $id.'.'.$request->photo->extension();
                $request->file('photo')->storeAs($photo_path, $imageName);
            }
            else {
                $imageName = $id.'.jpg';
                Storage::copy('public/avatar2.jpg', $photo_path.$imageName);
            }
            $employee->photo = $imageName;

            $employee->save();
            /* ------------------------------ */
                        
            return back()->with('success', 'Employee Added Successfully');
        }
        catch(QueryException $exception) {                        
            return back()->with('error', $exception->getMessage())->withInput();
        }
    }

    public function updateEmployee($id, EmployeeRequest $request)
    {    
        // return $request->all();       
        try {
            $employee = Employee::find($id);
            $this->setEmployeeInfo($employee,$request);
            $employee->save();
            return back()->with('success', 'Employee Updated Successfully');
        }
        catch(QueryException $exception) {            
            return back()->with('error', $exception->getMessage())->withInput();
        }
    }

    private function setEmployeeInfo(Employee $employee, EmployeeRequest $request)
    {        
        $employee->name         = $request->name;
        $employee->code         = $request->code;
        $employee->role_id      = $request->role;
        $employee->manager_id   = $request->reporting_head;
        $employee->dob          = $request->dob;
        $employee->gender       = $request->gender;
        $employee->user_name    = $request->user_name;
        $employee->password     = $request->password;
        $employee->remarks      = $request->remarks;
        $employee->address      = $request->address;
        $employee->district     = $request->district;
        $employee->state        = $request->state;
        $employee->landmark     = $request->landmark;
        $employee->pincode      = $request->pincode;
        $employee->mobile_num   = $request->mobile_number;
        $employee->alternate_num = $request->alternate_number;
        $employee->email_id     = $request->email;
        $employee->father_name  = $request->father_name;
        $employee->aadhaar_num  = $request->aadhaar;
        $employee->license_num  = $request->license_number;
        $employee->license_validity = $request->license_validity;
        $employee->blood_group  = $request->blood_group;
        $employee->doj          = $request->doj;
        $employee->bank_name    = $request->bank_name;
        $employee->branch       = $request->branch;
        $employee->ifsc         = $request->ifsc;
        $employee->acc_holder   = $request->acc_holder;
        $employee->acc_number   = $request->acc_number;
    }

    public function showEmployee(Request $request)
    { 
        $id = $request->input('id');
        $employee = Employee::find($id);
        if($employee->manager_id == 0) {
            $reporting_head = "Admin";
        }
        else {
            $manager = ViewDesignation::select('emp_id','emp_name','role_name','short_name')
                            ->where('emp_id',$employee->manager_id)
                            ->first();
            if($manager->short_name)
                $manager->role_name = $manager->short_name;
            $reporting_head = sprintf("%s (%s)",$manager->emp_name,$manager->role_name);
        }
        // return response()->json(['employee' => $employee, 'reporting_head' => $reporting_head]);
        return view('masters.profiles.view_employee', [
            'employee' => $employee,
            'reporting_head' => $reporting_head
        ]);
    }

    public function statusEmployee($id)
    {        
        $employee = Employee::find($id);
        $status = ($employee->status == "Active") ? "Inactive" : "Active";
        $employee->status = $status;
        $employee->save();
        if($status == "Active")
            return back()->with('success', 'Employee is now Active');
        else
            return back()->with('success', 'Employee is now Inactive');
    }

    public function managerEmployee($id)
    {
        $designation = Designation::find($id);
        $mgrs = explode(',',$designation->reporting_roles);
        $employees = Employee::select('id','name','role_id')
                                ->whereIn('role_id',$mgrs)
                                ->with('role:id,role_name,short_name')
                                ->get();
        $managers = array();
        foreach($employees as $employee)
        {
            if($employee->role->short_name)
                $employee->role->role_name = $employee->role->short_name;
            $display = sprintf("%s (%s)",$employee->name,$employee->role->role_name);
            $managers[] = array('id'=>$employee->id, 'display'=>$display);
        }
        return response()->json(['managers' => $managers]);
    }

    public function indexRoles()
    {
        $roles = Designation::all();        
        for($i=0; $i<$roles->count(); $i++) {
            if($roles[$i]->reporting_roles) {
                $role_list = explode(',',$roles[$i]->reporting_roles);
                $role_list = Designation::select('role_name')->whereIn('id',$role_list)->get();
                $data = "";
                foreach($role_list->reverse() as $role)
                    $data = $data . $role->role_name . "/ ";
                $roles[$i]->reporting_roles = substr($data,0,-2);
            }
        }
        // return $roles;
        return view('masters.profiles.view_roles', [
            'roles' => $roles
        ]);
    }

    public function indexCompetitors() 
    {
        $competitors = Competitor::all();
        return view('masters.profiles.list_competitors', [
            'competitors' => $competitors
        ]);
    }

    public function editCompetitor($id)
    {        
    	$competitor = Competitor::find($id);
	    return response()->json([
	      'competitor' => $competitor
	    ]);
    }

    public function storeCompetitor($id)
    {              
        try {
            Competitor::updateOrCreate(
                [ 'id' => $id ],
                [ 'comp_name' => request('comp_name'),
                  'display_name' => request('display_name') ]
            );
            return response()->json([ 'success' => true ]);            
        }
        catch(QueryException $exception) {            
            return $exception;
        }
    }

    public function destroyCompetitor($id)
    {                   
        $competitor = Competitor::find($id);
        $competitor->delete();
        return response()->json([ 'success' => true ]);
    }
}