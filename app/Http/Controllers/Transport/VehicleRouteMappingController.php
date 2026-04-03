<?php

namespace App\Http\Controllers\Transport;

use App\Http\Controllers\Controller;
use App\Models\Transport\Vehicle;
use App\Models\Transport\VehicleRouteMapping;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Contracts\View\View;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class VehicleRouteMappingController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request): View
    {
        $query = VehicleRouteMapping::with(['vehicle', 'route']);

        if ($request->filled('route_type')) {
            $query->where('route_type', $request->route_type);
        }
        if ($request->filled('vehicle_id')) {
            $query->where('vehicle_id', $request->vehicle_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $mappings = $query->latest()->get();
        $vehicles = Vehicle::active()->pluck('vehicle_number', 'id');
        $routes   = DB::table('routes')->orderBy('name')->pluck('name', 'id');

        return view('transport.vehicle-route-mappings.index',
            compact('mappings', 'vehicles', 'routes'));
    }

    public function create(): View
    {
        $vehicles = Vehicle::active()->pluck('vehicle_number', 'id');
        $routes   = DB::table('routes')->orderBy('name')->pluck('name', 'id');
        return view('transport.vehicle-route-mappings.create',
            compact('vehicles', 'routes'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'vehicle_id'     => 'required|exists:vehicles,id',
            'route_id'       => 'required|exists:routes,id',
            'route_type'     => 'required|in:collection,marketing',
            'shift'          => 'nullable|string|max:50',
            'distance_km'    => 'nullable|numeric|min:0',
            'rate_per_km'    => 'nullable|numeric|min:0',
            'effective_from' => 'required|date',
            'effective_to'   => 'nullable|date|after_or_equal:effective_from',
            'status'         => 'required|in:active,inactive',
            'remarks'        => 'nullable|string',
        ]);

        // Check for duplicate active mapping
        $exists = VehicleRouteMapping::where('vehicle_id',  $request->vehicle_id)
            ->where('route_id',   $request->route_id)
            ->where('route_type', $request->route_type)
            ->where('status',     'active')
            ->exists();

        if ($exists) {
            return back()->withInput()
                ->with('error', 'An active mapping already exists for this vehicle and route.');
        }

        try {
            VehicleRouteMapping::create(array_merge(
                $request->only([
                    'vehicle_id', 'route_id', 'route_type', 'shift',
                    'distance_km', 'rate_per_km', 'effective_from',
                    'effective_to', 'status', 'remarks',
                ]),
                ['created_by' => Auth::id(), 'updated_by' => Auth::id()]
            ));

            return redirect()
                ->route('transport.vehicle-route-mappings.index')
                ->with('success', 'Vehicle route mapping created successfully.');
        }
        catch (QueryException $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    public function show(VehicleRouteMapping $vehicleRouteMapping): View
    {
        $vehicleRouteMapping->load(['vehicle', 'route']);
        return view('transport.vehicle-route-mappings.show',
            compact('vehicleRouteMapping'));
    }

    public function edit(VehicleRouteMapping $vehicleRouteMapping): View
    {
        $vehicles = Vehicle::active()->pluck('vehicle_number', 'id');
        $routes   = DB::table('routes')->orderBy('name')->pluck('name', 'id');
        return view('transport.vehicle-route-mappings.edit',
            compact('vehicleRouteMapping', 'vehicles', 'routes'));
    }

    public function update(Request $request, VehicleRouteMapping $vehicleRouteMapping): RedirectResponse
    {
        $request->validate([
            'vehicle_id'     => 'required|exists:vehicles,id',
            'route_id'       => 'required|exists:routes,id',
            'route_type'     => 'required|in:collection,marketing',
            'shift'          => 'nullable|string|max:50',
            'distance_km'    => 'nullable|numeric|min:0',
            'rate_per_km'    => 'nullable|numeric|min:0',
            'effective_from' => 'required|date',
            'effective_to'   => 'nullable|date|after_or_equal:effective_from',
            'status'         => 'required|in:active,inactive',
            'remarks'        => 'nullable|string',
        ]);

        try {
            $vehicleRouteMapping->update(array_merge(
                $request->only([
                    'vehicle_id', 'route_id', 'route_type', 'shift',
                    'distance_km', 'rate_per_km', 'effective_from',
                    'effective_to', 'status', 'remarks',
                ]),
                ['updated_by' => Auth::id()]
            ));

            return redirect()
                ->route('transport.vehicle-route-mappings.index')
                ->with('success', 'Vehicle route mapping updated successfully.');
        }
        catch (QueryException $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    public function destroy(VehicleRouteMapping $vehicleRouteMapping): RedirectResponse
    {
        $vehicleRouteMapping->delete();

        return redirect()
            ->route('transport.vehicle-route-mappings.index')
            ->with('success', 'Vehicle route mapping deleted successfully.');
    }
}
