<?php

namespace App\Http\Controllers\Transport;

use App\Http\Controllers\Controller;
use App\Models\Transport\TripSheetMarket;
use App\Models\Transport\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Contracts\View\View;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TripSheetMarketController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request): View
    {
        $query = TripSheetMarket::with(['vehicle', 'route']);

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

        $totals = (object) [
            'total_trips'     => $tripSheets->count(),
            'total_delivered' => $tripSheets->sum('delivered_qty'),
            'total_returned'  => $tripSheets->sum('returned_qty'),
            'total_amount'    => $tripSheets->sum('trip_amount'),
        ];

        return view('transport.trip-sheets-market.index',
            compact('tripSheets', 'vehicles', 'routes', 'totals'));
    }

    public function create(): View
    {
        $vehicles   = Vehicle::active()->pluck('vehicle_number', 'id');
        $routes     = DB::table('routes')->orderBy('name')->pluck('name', 'id');
        $tripNumber = TripSheetMarket::generateTripNumber();
        return view('transport.trip-sheets-market.create',
            compact('vehicles', 'routes', 'tripNumber'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'trip_number'    => 'required|string|max:50|unique:trip_sheets_market,trip_number',
            'trip_date'      => 'required|date|before_or_equal:today',
            'vehicle_id'     => 'required|exists:vehicles,id',
            'route_id'       => 'required|exists:routes,id',
            'shift'          => 'nullable|string|max:50',
            'driver_name'    => 'nullable|string|max:100',
            'driver_phone'   => 'nullable|string|max:15',
            'departure_time' => 'nullable|date_format:H:i',
            'arrival_time'   => 'nullable|date_format:H:i',
            'odometer_start' => 'nullable|integer|min:0',
            'odometer_end'   => 'nullable|integer|min:0',
            'loaded_qty'     => 'required|numeric|min:0',
            'delivered_qty'  => 'required|numeric|min:0',
            'returned_qty'   => 'nullable|numeric|min:0',
            'product_type'   => 'nullable|string|max:100',
            'trip_amount'    => 'required|numeric|min:0',
            'diesel_consumed'=> 'nullable|numeric|min:0',
            'diesel_cost'    => 'nullable|numeric|min:0',
            'other_expenses' => 'nullable|numeric|min:0',
            'status'         => 'required|in:pending,completed,cancelled',
            'remarks'        => 'nullable|string',
        ]);

        try {
            $data = $request->only([
                'trip_number', 'trip_date', 'vehicle_id', 'route_id',
                'shift', 'driver_name', 'driver_phone', 'departure_time',
                'arrival_time', 'odometer_start', 'odometer_end',
                'loaded_qty', 'delivered_qty', 'returned_qty', 'product_type',
                'trip_amount', 'diesel_consumed', 'diesel_cost',
                'other_expenses', 'status', 'remarks',
            ]);

            $data['returned_qty']   = $data['returned_qty']   ?? 0;
            $data['other_expenses'] = $data['other_expenses'] ?? 0;
            $data['created_by']     = Auth::id();
            $data['updated_by']     = Auth::id();

            TripSheetMarket::create($data);

            return redirect()
                ->route('transport.trip-sheets-market.index')
                ->with('success', 'Market trip sheet created successfully.');
        }
        catch (QueryException $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    public function show(TripSheetMarket $tripSheetMarket): View
    {
        $tripSheetMarket->load(['vehicle', 'route']);
        return view('transport.trip-sheets-market.show', compact('tripSheetMarket'));
    }

    public function edit(TripSheetMarket $tripSheetMarket): View
    {
        $vehicles = Vehicle::active()->pluck('vehicle_number', 'id');
        $routes   = DB::table('routes')->orderBy('name')->pluck('name', 'id');
        return view('transport.trip-sheets-market.edit',
            compact('tripSheetMarket', 'vehicles', 'routes'));
    }

    public function update(Request $request, TripSheetMarket $tripSheetMarket): RedirectResponse
    {
        $request->validate([
            'trip_date'      => 'required|date|before_or_equal:today',
            'vehicle_id'     => 'required|exists:vehicles,id',
            'route_id'       => 'required|exists:routes,id',
            'shift'          => 'nullable|string|max:50',
            'driver_name'    => 'nullable|string|max:100',
            'driver_phone'   => 'nullable|string|max:15',
            'departure_time' => 'nullable|date_format:H:i',
            'arrival_time'   => 'nullable|date_format:H:i',
            'odometer_start' => 'nullable|integer|min:0',
            'odometer_end'   => 'nullable|integer|min:0',
            'loaded_qty'     => 'required|numeric|min:0',
            'delivered_qty'  => 'required|numeric|min:0',
            'returned_qty'   => 'nullable|numeric|min:0',
            'product_type'   => 'nullable|string|max:100',
            'trip_amount'    => 'required|numeric|min:0',
            'diesel_consumed'=> 'nullable|numeric|min:0',
            'diesel_cost'    => 'nullable|numeric|min:0',
            'other_expenses' => 'nullable|numeric|min:0',
            'status'         => 'required|in:pending,completed,cancelled',
            'remarks'        => 'nullable|string',
        ]);

        try {
            $data = $request->only([
                'trip_date', 'vehicle_id', 'route_id', 'shift',
                'driver_name', 'driver_phone', 'departure_time', 'arrival_time',
                'odometer_start', 'odometer_end', 'loaded_qty', 'delivered_qty',
                'returned_qty', 'product_type', 'trip_amount', 'diesel_consumed',
                'diesel_cost', 'other_expenses', 'status', 'remarks',
            ]);

            $data['updated_by'] = Auth::id();
            $tripSheetMarket->update($data);

            return redirect()
                ->route('transport.trip-sheets-market.index')
                ->with('success', 'Market trip sheet updated successfully.');
        }
        catch (QueryException $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    public function destroy(TripSheetMarket $tripSheetMarket): RedirectResponse
    {
        $tripSheetMarket->delete();

        return redirect()
            ->route('transport.trip-sheets-market.index')
            ->with('success', 'Market trip sheet deleted successfully.');
    }
}
