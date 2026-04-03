<?php

namespace App\Http\Controllers\Transport;

use App\Http\Controllers\Controller;
use App\Models\Transport\Vehicle;
use App\Models\Transport\VehicleInsurance;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Contracts\View\View;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class VehicleInsuranceController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request): View
    {
        $query = VehicleInsurance::with('vehicle');

        if ($request->filled('vehicle_id')) {
            $query->where('vehicle_id', $request->vehicle_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('expiry_filter')) {
            match ($request->expiry_filter) {
                'expiring_30' => $query->expiringSoon(30),
                'expiring_7'  => $query->expiringSoon(7),
                'expired'     => $query->expired(),
                default       => null,
            };
        }

        $insurances = $query->orderBy('expiry_date')->get();
        $vehicles   = Vehicle::active()->pluck('vehicle_number', 'id');

        $alertCounts = [
            'expiring_30' => VehicleInsurance::expiringSoon(30)->count(),
            'expired'     => VehicleInsurance::expired()->count(),
        ];

        return view('transport.vehicle-insurance.index',
            compact('insurances', 'vehicles', 'alertCounts'));
    }

    public function create(): View
    {
        $vehicles = Vehicle::active()->pluck('vehicle_number', 'id');
        return view('transport.vehicle-insurance.create', compact('vehicles'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'vehicle_id'        => 'required|exists:vehicles,id',
            'policy_number'     => 'required|string|max:100|unique:vehicle_insurance,policy_number',
            'insurance_company' => 'required|string|max:150',
            'agent_name'        => 'nullable|string|max:100',
            'agent_phone'       => 'nullable|string|max:15',
            'insurance_type'    => 'required|in:comprehensive,third_party,fire_theft',
            'start_date'        => 'required|date',
            'expiry_date'       => 'required|date|after:start_date',
            'premium_amount'    => 'required|numeric|min:0',
            'insured_value'     => 'nullable|numeric|min:0',
            'document'          => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'status'            => 'required|in:active,expired,cancelled',
            'remarks'           => 'nullable|string',
        ]);

        try {
            $data = $request->only([
                'vehicle_id', 'policy_number', 'insurance_company',
                'agent_name', 'agent_phone', 'insurance_type',
                'start_date', 'expiry_date', 'premium_amount',
                'insured_value', 'status', 'remarks',
            ]);

            if ($request->hasFile('document')) {
                $data['document_path'] = $request->file('document')
                    ->store('transport/insurance', 'public');
            }

            $data['created_by'] = Auth::id();
            $data['updated_by'] = Auth::id();

            VehicleInsurance::create($data);

            return redirect()
                ->route('transport.vehicle-insurance.index')
                ->with('success', 'Insurance record added successfully.');
        }
        catch (QueryException $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    public function show(VehicleInsurance $vehicleInsurance): View
    {
        $vehicleInsurance->load('vehicle');
        return view('transport.vehicle-insurance.show', compact('vehicleInsurance'));
    }

    public function edit(VehicleInsurance $vehicleInsurance): View
    {
        $vehicles = Vehicle::active()->pluck('vehicle_number', 'id');
        return view('transport.vehicle-insurance.edit',
            compact('vehicleInsurance', 'vehicles'));
    }

    public function update(Request $request, VehicleInsurance $vehicleInsurance): RedirectResponse
    {
        $request->validate([
            'vehicle_id'        => 'required|exists:vehicles,id',
            'policy_number'     => 'required|string|max:100|unique:vehicle_insurance,policy_number,' . $vehicleInsurance->id,
            'insurance_company' => 'required|string|max:150',
            'agent_name'        => 'nullable|string|max:100',
            'agent_phone'       => 'nullable|string|max:15',
            'insurance_type'    => 'required|in:comprehensive,third_party,fire_theft',
            'start_date'        => 'required|date',
            'expiry_date'       => 'required|date|after:start_date',
            'premium_amount'    => 'required|numeric|min:0',
            'insured_value'     => 'nullable|numeric|min:0',
            'document'          => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'status'            => 'required|in:active,expired,cancelled',
            'remarks'           => 'nullable|string',
        ]);

        try {
            $data = $request->only([
                'vehicle_id', 'policy_number', 'insurance_company',
                'agent_name', 'agent_phone', 'insurance_type',
                'start_date', 'expiry_date', 'premium_amount',
                'insured_value', 'status', 'remarks',
            ]);

            if ($request->hasFile('document')) {
                if ($vehicleInsurance->document_path) {
                    Storage::disk('public')->delete($vehicleInsurance->document_path);
                }
                $data['document_path'] = $request->file('document')
                    ->store('transport/insurance', 'public');
            }

            $data['updated_by'] = Auth::id();
            $vehicleInsurance->update($data);

            return redirect()
                ->route('transport.vehicle-insurance.index')
                ->with('success', 'Insurance record updated successfully.');
        }
        catch (QueryException $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    public function destroy(VehicleInsurance $vehicleInsurance): RedirectResponse
    {
        if ($vehicleInsurance->document_path) {
            Storage::disk('public')->delete($vehicleInsurance->document_path);
        }

        $vehicleInsurance->delete();

        return redirect()
            ->route('transport.vehicle-insurance.index')
            ->with('success', 'Insurance record deleted successfully.');
    }
}
