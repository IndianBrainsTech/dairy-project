<?php

namespace App\Http\Controllers\Transport;

use App\Http\Controllers\Controller;
use App\Models\Transport\Vehicle;
use App\Models\Transport\VehicleService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Contracts\View\View;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class VehicleServiceController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request): View
    {
        $query = VehicleService::with('vehicle');

        if ($request->filled('vehicle_id')) {
            $query->where('vehicle_id', $request->vehicle_id);
        }
        if ($request->filled('service_type')) {
            $query->where('service_type', $request->service_type);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('from_date')) {
            $query->whereDate('service_date', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('service_date', '<=', $request->to_date);
        }

        $services  = $query->latest('service_date')->get();
        $vehicles  = Vehicle::active()->pluck('vehicle_number', 'id');
        $dueSoon   = VehicleService::dueSoon(30)->with('vehicle')->get();

        return view('transport.vehicle-services.index',
            compact('services', 'vehicles', 'dueSoon'));
    }

    public function create(): View
    {
        $vehicles      = Vehicle::active()->pluck('vehicle_number', 'id');
        $serviceNumber = VehicleService::generateServiceNumber();
        return view('transport.vehicle-services.create',
            compact('vehicles', 'serviceNumber'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'vehicle_id'        => 'required|exists:vehicles,id',
            'service_number'    => 'required|string|max:50|unique:vehicle_services,service_number',
            'service_date'      => 'required|date|before_or_equal:today',
            'service_type'      => 'required|in:routine,repair,breakdown,tyre,other',
            'service_center'    => 'nullable|string|max:150',
            'mechanic_name'     => 'nullable|string|max:100',
            'odometer_reading'  => 'nullable|integer|min:0',
            'next_service_km'   => 'nullable|integer|min:0',
            'next_service_date' => 'nullable|date|after:service_date',
            'labour_cost'       => 'required|numeric|min:0',
            'parts_cost'        => 'required|numeric|min:0',
            'other_cost'        => 'nullable|numeric|min:0',
            'work_done'         => 'nullable|string',
            'document'          => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'status'            => 'required|in:scheduled,in_progress,completed',
            'remarks'           => 'nullable|string',
        ]);

        try {
            $data = $request->only([
                'vehicle_id', 'service_number', 'service_date', 'service_type',
                'service_center', 'mechanic_name', 'odometer_reading',
                'next_service_km', 'next_service_date', 'labour_cost',
                'parts_cost', 'work_done', 'status', 'remarks',
            ]);

            $data['other_cost'] = $request->other_cost ?? 0;

            if ($request->hasFile('document')) {
                $data['document_path'] = $request->file('document')
                    ->store('transport/services', 'public');
            }

            $data['created_by'] = Auth::id();
            $data['updated_by'] = Auth::id();

            VehicleService::create($data);
            // total_cost auto-calculated in model booted()

            return redirect()
                ->route('transport.vehicle-services.index')
                ->with('success', 'Service record added successfully.');
        }
        catch (QueryException $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    public function show(VehicleService $vehicleService): View
    {
        $vehicleService->load('vehicle');
        return view('transport.vehicle-services.show', compact('vehicleService'));
    }

    public function edit(VehicleService $vehicleService): View
    {
        $vehicles = Vehicle::active()->pluck('vehicle_number', 'id');
        return view('transport.vehicle-services.edit',
            compact('vehicleService', 'vehicles'));
    }

    public function update(Request $request, VehicleService $vehicleService): RedirectResponse
    {
        $request->validate([
            'vehicle_id'        => 'required|exists:vehicles,id',
            'service_number'    => 'required|string|max:50|unique:vehicle_services,service_number,' . $vehicleService->id,
            'service_date'      => 'required|date|before_or_equal:today',
            'service_type'      => 'required|in:routine,repair,breakdown,tyre,other',
            'service_center'    => 'nullable|string|max:150',
            'mechanic_name'     => 'nullable|string|max:100',
            'odometer_reading'  => 'nullable|integer|min:0',
            'next_service_km'   => 'nullable|integer|min:0',
            'next_service_date' => 'nullable|date|after:service_date',
            'labour_cost'       => 'required|numeric|min:0',
            'parts_cost'        => 'required|numeric|min:0',
            'other_cost'        => 'nullable|numeric|min:0',
            'work_done'         => 'nullable|string',
            'document'          => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'status'            => 'required|in:scheduled,in_progress,completed',
            'remarks'           => 'nullable|string',
        ]);

        try {
            $data = $request->only([
                'vehicle_id', 'service_number', 'service_date', 'service_type',
                'service_center', 'mechanic_name', 'odometer_reading',
                'next_service_km', 'next_service_date', 'labour_cost',
                'parts_cost', 'work_done', 'status', 'remarks',
            ]);

            $data['other_cost'] = $request->other_cost ?? 0;

            if ($request->hasFile('document')) {
                if ($vehicleService->document_path) {
                    Storage::disk('public')->delete($vehicleService->document_path);
                }
                $data['document_path'] = $request->file('document')
                    ->store('transport/services', 'public');
            }

            $data['updated_by'] = Auth::id();
            $vehicleService->update($data);

            return redirect()
                ->route('transport.vehicle-services.index')
                ->with('success', 'Service record updated successfully.');
        }
        catch (QueryException $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    public function destroy(VehicleService $vehicleService): RedirectResponse
    {
        if ($vehicleService->document_path) {
            Storage::disk('public')->delete($vehicleService->document_path);
        }

        $vehicleService->delete();

        return redirect()
            ->route('transport.vehicle-services.index')
            ->with('success', 'Service record deleted successfully.');
    }
}
