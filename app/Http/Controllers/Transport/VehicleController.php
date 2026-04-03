<?php

namespace App\Http\Controllers\Transport;

use App\Http\Controllers\Controller;
use App\Models\Transport\Vehicle;
use App\Models\Transport\VehicleCategory;
use App\Models\Transport\SupplierTransporter;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Contracts\View\View;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;

class VehicleController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request): View
    {
        $query = Vehicle::with(['category', 'supplierTransporter', 'activeInsurance']);

        // Filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('ownership_type')) {
            $query->where('ownership_type', $request->ownership_type);
        }
        if ($request->filled('vehicle_category_id')) {
            $query->where('vehicle_category_id', $request->vehicle_category_id);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('vehicle_number', 'like', "%{$search}%")
                  ->orWhere('vehicle_name',  'like', "%{$search}%")
                  ->orWhere('driver_name',   'like', "%{$search}%");
            });
        }

        $vehicles   = $query->latest()->get();
        $categories = VehicleCategory::active()->pluck('name', 'id');

        return view('transport.vehicles.index', compact('vehicles', 'categories'));
    }

    public function create(): View
    {
        $categories   = VehicleCategory::active()->pluck('name', 'id');
        $transporters = SupplierTransporter::active()->pluck('name', 'id');
        return view('transport.vehicles.create', compact('categories', 'transporters'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'vehicle_category_id'     => 'required|exists:vehicle_categories,id',
            'vehicle_number'          => 'required|string|max:20|unique:vehicles,vehicle_number',
            'vehicle_name'            => 'nullable|string|max:100',
            'vehicle_type'            => 'required|in:Lorry,Truck,Van,Two Wheeler',
            'make'                    => 'nullable|string|max:30',
            'model'                   => 'nullable|string|max:30',
            'year_of_manufacture'     => 'nullable|integer|min:1990|max:' . now()->year,
            'capacity_litres'         => 'nullable|numeric|min:0',
            'fuel_type'               => 'required|in:diesel,petrol,electric,cng',
            'ownership_type'          => 'required|in:own,hired,leased',
            'supplier_transporter_id' => 'nullable|exists:supplier_transporters,id',
            'driver_name'             => 'nullable|string|max:100',
            'driver_phone'            => 'nullable|string|max:15',
            'rc_number'               => 'nullable|string|max:50',
            'rc_expiry_date'          => 'nullable|date',
            'status'                  => 'required|in:Active,Inactive',
            'remarks'                 => 'nullable|string',
        ]);

        try {
            Vehicle::create(array_merge(
                $request->only([
                    'vehicle_category_id', 'vehicle_number', 'vehicle_name',
                    'vehicle_type', 'make', 'model', 'year_of_manufacture',
                    'capacity_litres', 'fuel_type', 'ownership_type',
                    'supplier_transporter_id', 'driver_name', 'driver_phone',
                    'rc_number', 'rc_expiry_date', 'status', 'remarks',
                ]),
                ['created_by' => Auth::id(), 'updated_by' => Auth::id()]
            ));

            return redirect()
                ->route('transport.vehicles.index')
                ->with('success', 'Vehicle added successfully.');
        }
        catch (QueryException $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    public function show(Vehicle $vehicle): View
    {
        $vehicle->load([
            'category',
            'supplierTransporter',
            'routeMappings.route',
            'insurance',
            'services'    => fn($q) => $q->latest('service_date')->limit(10),
            'tripSheets'  => fn($q) => $q->latest('trip_date')->limit(10),
        ]);

        return view('transport.vehicles.show', compact('vehicle'));
    }

    public function edit(Vehicle $vehicle): View
    {
        $categories   = VehicleCategory::active()->pluck('name', 'id');
        $transporters = SupplierTransporter::active()->pluck('name', 'id');
        return view('transport.vehicles.edit', compact('vehicle', 'categories', 'transporters'));
    }

    public function update(Request $request, Vehicle $vehicle): RedirectResponse
    {
        $request->validate([
            'vehicle_category_id'     => 'required|exists:vehicle_categories,id',
            'vehicle_number'          => 'required|string|max:20|unique:vehicles,vehicle_number,' . $vehicle->id,
            'vehicle_name'            => 'nullable|string|max:100',
            'vehicle_type'            => 'required|in:Lorry,Truck,Van,Two Wheeler',
            'make'                    => 'nullable|string|max:30',
            'model'                   => 'nullable|string|max:30',
            'year_of_manufacture'     => 'nullable|integer|min:1990|max:' . now()->year,
            'capacity_litres'         => 'nullable|numeric|min:0',
            'fuel_type'               => 'required|in:diesel,petrol,electric,cng',
            'ownership_type'          => 'required|in:own,hired,leased',
            'supplier_transporter_id' => 'nullable|exists:supplier_transporters,id',
            'driver_name'             => 'nullable|string|max:100',
            'driver_phone'            => 'nullable|string|max:15',
            'rc_number'               => 'nullable|string|max:50',
            'rc_expiry_date'          => 'nullable|date',
            'status'                  => 'required|in:Active,Inactive',
            'remarks'                 => 'nullable|string',
        ]);

        try {
            $vehicle->update(array_merge(
                $request->only([
                    'vehicle_category_id', 'vehicle_number', 'vehicle_name',
                    'vehicle_type', 'make', 'model', 'year_of_manufacture',
                    'capacity_litres', 'fuel_type', 'ownership_type',
                    'supplier_transporter_id', 'driver_name', 'driver_phone',
                    'rc_number', 'rc_expiry_date', 'status', 'remarks',
                ]),
                ['updated_by' => Auth::id()]
            ));

            return redirect()
                ->route('transport.vehicles.index')
                ->with('success', 'Vehicle updated successfully.');
        }
        catch (QueryException $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    public function destroy(Vehicle $vehicle): RedirectResponse
    {
        if ($vehicle->tripSheets()->exists() || $vehicle->transportBills()->exists()) {
            return back()->with('error', 'Cannot delete — trip sheets or bills are linked to this vehicle.');
        }

        $vehicle->delete();

        return redirect()
            ->route('transport.vehicles.index')
            ->with('success', 'Vehicle deleted successfully.');
    }
}
