<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use App\Models\Places\State;
use App\Models\Places\District;
use App\Models\Places\MRoute;
use App\Models\Places\Area;
use App\Models\Places\Address;
use App\Models\Places\ViewArea;

class PlaceController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function indexStates()
    {
        $states = State::all();
        // return response()->json([
        return view('masters.places.states',[
            'states' => $states
        ]);
    }

    public function editState($id)
    {
    	$state = State::find($id);
	    return response()->json([
	        'state' => $state
	    ]);
    }

    public function storeState($id)
    {
        try {
            $state = State::updateOrCreate(
                [ 'id' => $id ],
                [ 'name' => request('name') ]
            );
            return response()->json([ 
                'success' => true 
            ]);
        }
        catch(QueryException $exception){
            return $exception;
        }
    }

    public function destroyState($id)
    {
        $state = State::find($id);
        $state->delete();
        return response()->json([
            'success' => true
        ]);
    }

    public function listStates()
    {        
        $states = State::select('id','name')->orderBy('id')->get();
        return response()->json([
            'states' => $states
        ]);
    }

    public function getState($district)
    {
    	$district = District::select('name','state_id')
                            ->with('state:id,name')
                            ->where('name',$district)
                            ->first();
        $state = $district->state->name;
	    return response()->json($state);
    }    

    public function indexDistricts()
    {
        $districts = District::select('id','name','state_id')
                                ->with('state:id,name')
                                ->get();
        // return response()->json([
        return view('masters.places.districts',[
            'districts' => $districts
        ]);
    }

    public function editDistrict($id)
    {
        $district = District::find($id);
	    return response()->json([
	      'district' => $district
	    ]);
    }

    public function storeDistrict($id)
    {
        try {
            District::updateOrCreate(
                [ 'id' => $id ],
                [ 'name' => request('name'),
                  'state_id' => request('state_id') ]
            );
            return response()->json([
                'success' => true
            ]);
        }
        catch(QueryException $exception){
            return $exception;
        }
    }

    public function destroyDistrict($id)
    {
        $district = District::find($id);
        $district->delete();
        return response()->json([ 'success' => true ]);
    }

    public function listDistricts()
    {
        $districts = District::select('id','name')->orderBy('name')->get();
        return response()->json([
            'districts' => $districts
        ]);
    }    
    
    public function indexRoutes()
    {
        $routes = MRoute::select('id','name','district_id')
                                ->with('district:id,name')
                                ->get();
        return view('masters.places.mroutes',[
            'routes' => $routes
        ]);
    }

    public function editRoute($id)
    {
    	$route = MRoute::find($id);
	    return response()->json([
	        'route' => $route
	    ]);
    }

    public function storeRoute($id)
    {
        try {
            MRoute::updateOrCreate(
                [ 'id' => $id ],
                [ 'name' => request('name'),
                  'district_id' => request('district_id') ]
            );
            return response()->json([
                'success' => true 
            ]);
        }
        catch(QueryException $exception){
            return $exception;
        }
    }

    public function destroyRoute($id)
    {
        $route = MRoute::find($id);
        $route->delete();
        return response()->json([ 'success' => true ]);
    }

    public function listRoutes()
    {
        $routes = MRoute::select('id','name')->orderBy('name')->get();
        return response()->json([
            'routes' => $routes
        ]);
    }

    public function indexAreas()
    {
        $areas = ViewArea::orderBy('area_id')->get();
        // return response()->json([
        return view('masters.places.areas', [
            'areas' => $areas
        ]);
    }

    public function editArea($id)
    {
    	$area = Area::find($id);
	    return response()->json([
	        'area' => $area
	    ]);
    }

    public function storeArea($id)
    {
        try {
            Area::updateOrCreate(
                [ 'id' => $id ],
                [ 'name' => request('name'),
                  'route_id' => request('route_id') ]
            );
            return response()->json([
                'success' => true
            ]);
        }
        catch(QueryException $exception) {
            return $exception;
        }
    }

    public function destroyArea($id)
    {
        $area = Area::find($id);
        $area->delete();
        return response()->json([ 'success' => true ]);
    }

    public function listAreas($id)
    {        
        $areas = Area::select('id','name')
                        ->where('route_id',$id)
                        ->orderBy('name')
                        ->get();
        return response()->json([
            'areas' => $areas
        ]);
    }

    public function getAreaInfo($id)
    {
        $area = ViewArea::where('area_id',$id)->first();
        return response()->json($area);
    } 
    
    public function editAddress($id)
    {
    	$address = Address::find($id);
	    return response()->json([
	        'address' => $address
	    ]);
    }

    public function storeAddress()
    {
        try {
            $address = Address::updateOrCreate(
                [ 'id' => request('id') ],
                [ 'customer_id'   => request('customer_id'),
                  'customer_name' => request('customer_name'),
                  'address_lines' => request('address_lines'),
                  'district'      => request('district'),
                  'state'         => request('state'),
                  'pincode'       => request('pincode') ]
            );
            return response()->json([
                'success' => true 
            ]);
        }
        catch(QueryException $exception) {
            return response()->json([
                'error' => $exception->getMessage()
            ]);
        }
    }
}
