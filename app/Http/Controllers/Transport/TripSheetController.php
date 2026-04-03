<?php

namespace App\Http\Controllers\Transport;

use App\Http\Controllers\Controller;
use App\Models\Transport\TripSheet;
use App\Models\Transport\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Contracts\View\View;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TripSheetController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request): View
    {
        $query = TripSheet::with(['vehicle', 'route']);

        if ($request->filled('vehicle_id')) {
            $query->where('vehicle_id', $request->vehicle_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('from_date')) {
            $query->whereDate('trip_date', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('trip_date', '<=', $request->to_date);
        }

        // Default: current month
        if (!$request->filled('from_date') && !$request->filled('to_date')) {
            $query->whereMonth('trip_date', now()->month)
                  ->whereYear('trip_date',  now()->year);
        }

        $tripSheets = $query->latest('trip_date')->get();
        $vehicles   = Vehicle::active()->pluck('vehicle_number', 'id');
        $routes     = DB::table('routes')->orderBy('name')->pluck('name', 'id');

        // Summary totals
        $totals = (object) [
            'total_trips'  => $tripSheets->count(),
            'total_milk'   => $tripSheets->sum('net_milk_litres'),
            'total_amount' => $tripSheets->sum('trip_amount'),
            'total_diesel' => $tripSheets->sum('diesel_cost'),
        ];

        return view('transport.trip-sheets.index',
            compact('tripSheets', 'vehicles', 'routes', 'totals'));
    }

    public function create(): View
    {
        $vehicles   = Vehicle::active()->pluck('vehicle_number', 'id');
        $routes     = DB::table('routes')->orderBy('name')->pluck('name', 'id');
        $tripNumber = TripSheet::generateTripNumber();
        return view('transport.trip-sheets.create',
            compact('vehicles', 'routes', 'tripNumber'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'trip_number'           => 'required|string|max:50|unique:trip_sheets,trip_number',
            'trip_date'             => 'required|date|before_or_equal:today',
            'vehicle_id'            => 'required|exists:vehicles,id',
            'route_id'              => 'required|exists:routes,id',
            'shift'                 => 'nullable|string|max:50',
            'driver_name'           => 'nullable|string|max:100',
            'driver_phone'          => 'nullable|string|max:15',
            'departure_time'        => 'nullable|date_format:H:i',
            'arrival_time'          => 'nullable|date_format:H:i',
            'odometer_start'        => 'nullable|integer|min:0',
            'odometer_end'          => 'nullable|integer|min:0',
            'milk_collected_litres' => 'required|numeric|min:0',
            'milk_rejected_litres'  => 'nullable|numeric|min:0',
            'rate_per_litre'        => 'nullable|numeric|min:0',
            'payment_mode'          => 'required|in:flat_rate,per_litre,per_km',
            'flat_rate_amount'      => 'nullable|numeric|min:0',
            'diesel_consumed'       => 'nullable|numeric|min:0',
            'diesel_cost'           => 'nullable|numeric|min:0',
            'status'                => 'required|in:pending,completed,cancelled',
            'remarks'               => 'nullable|string',
        ]);

        try {
            $data = $request->only([
                'trip_number', 'trip_date', 'vehicle_id', 'route_id',
                'shift', 'driver_name', 'driver_phone', 'departure_time',
                'arrival_time', 'odometer_start', 'odometer_end',
                'milk_collected_litres', 'milk_rejected_litres',
                'rate_per_litre', 'payment_mode', 'flat_rate_amount',
                'diesel_consumed', 'diesel_cost', 'status', 'remarks',
            ]);

            $data['milk_rejected_litres'] = $data['milk_rejected_litres'] ?? 0;
            $data['created_by']           = Auth::id();
            $data['updated_by']           = Auth::id();

            TripSheet::create($data);
            // net_milk, distance, trip_amount auto-calculated in model booted()

            return redirect()
                ->route('transport.trip-sheets.index')
                ->with('success', 'Trip sheet created successfully.');
        }
        catch (QueryException $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    public function show(TripSheet $tripSheet): View
    {
        $tripSheet->load([
            'vehicle',
            'route',
            'adjustment',
            'transportBillItems.transportBill',
        ]);
        return view('transport.trip-sheets.show', compact('tripSheet'));
    }

    public function edit(TripSheet $tripSheet): View
    {
        if ($tripSheet->isBilled()) {
            return redirect()
                ->route('transport.trip-sheets.show', $tripSheet)
                ->with('error', 'This trip sheet is already billed and cannot be edited.');
        }

        $vehicles = Vehicle::active()->pluck('vehicle_number', 'id');
        $routes   = DB::table('routes')->orderBy('name')->pluck('name', 'id');
        return view('transport.trip-sheets.edit',
            compact('tripSheet', 'vehicles', 'routes'));
    }

    public function update(Request $request, TripSheet $tripSheet): RedirectResponse
    {
        if ($tripSheet->isBilled()) {
            return back()->with('error', 'This trip sheet is already billed and cannot be edited.');
        }

        $request->validate([
            'trip_date'             => 'required|date|before_or_equal:today',
            'vehicle_id'            => 'required|exists:vehicles,id',
            'route_id'              => 'required|exists:routes,id',
            'shift'                 => 'nullable|string|max:50',
            'driver_name'           => 'nullable|string|max:100',
            'driver_phone'          => 'nullable|string|max:15',
            'departure_time'        => 'nullable|date_format:H:i',
            'arrival_time'          => 'nullable|date_format:H:i',
            'odometer_start'        => 'nullable|integer|min:0',
            'odometer_end'          => 'nullable|integer|min:0',
            'milk_collected_litres' => 'required|numeric|min:0',
            'milk_rejected_litres'  => 'nullable|numeric|min:0',
            'rate_per_litre'        => 'nullable|numeric|min:0',
            'payment_mode'          => 'required|in:flat_rate,per_litre,per_km',
            'flat_rate_amount'      => 'nullable|numeric|min:0',
            'diesel_consumed'       => 'nullable|numeric|min:0',
            'diesel_cost'           => 'nullable|numeric|min:0',
            'status'                => 'required|in:pending,completed,cancelled',
            'remarks'               => 'nullable|string',
        ]);

        try {
            $data = $request->only([
                'trip_date', 'vehicle_id', 'route_id', 'shift',
                'driver_name', 'driver_phone', 'departure_time', 'arrival_time',
                'odometer_start', 'odometer_end', 'milk_collected_litres',
                'milk_rejected_litres', 'rate_per_litre', 'payment_mode',
                'flat_rate_amount', 'diesel_consumed', 'diesel_cost',
                'status', 'remarks',
            ]);

            $data['updated_by'] = Auth::id();
            $tripSheet->update($data);

            return redirect()
                ->route('transport.trip-sheets.index')
                ->with('success', 'Trip sheet updated successfully.');
        }
        catch (QueryException $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    public function destroy(TripSheet $tripSheet): RedirectResponse
    {
        if ($tripSheet->isBilled()) {
            return back()->with('error', 'Cannot delete — trip sheet is linked to a transport bill.');
        }

        $tripSheet->delete();

        return redirect()
            ->route('transport.trip-sheets.index')
            ->with('success', 'Trip sheet deleted successfully.');
    }
}
