<?php

namespace App\Http\Controllers\Transport;

use App\Http\Controllers\Controller;
use App\Models\Transport\TransportAdjustment;
use App\Models\Transport\TripSheet;
use App\Models\Transport\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Contracts\View\View;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;

class TransportAdjustmentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request): View
    {
        $query = TransportAdjustment::with(['vehicle', 'tripSheet', 'approvedBy']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('adjustment_type')) {
            $query->where('adjustment_type', $request->adjustment_type);
        }
        if ($request->filled('vehicle_id')) {
            $query->where('vehicle_id', $request->vehicle_id);
        }
        if ($request->filled('from_date')) {
            $query->whereDate('adjustment_date', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('adjustment_date', '<=', $request->to_date);
        }

        $adjustments = $query->latest('adjustment_date')->get();
        $vehicles    = Vehicle::active()->pluck('vehicle_number', 'id');

        return view('transport.transport-adjustments.index',
            compact('adjustments', 'vehicles'));
    }

    public function create(): View
    {
        $vehicles         = Vehicle::active()->pluck('vehicle_number', 'id');
        $adjustmentNumber = TransportAdjustment::generateAdjustmentNumber();
        return view('transport.transport-adjustments.create',
            compact('vehicles', 'adjustmentNumber'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'adjustment_number'  => 'required|string|max:50|unique:transport_adjustments,adjustment_number',
            'adjustment_date'    => 'required|date|before_or_equal:today',
            'vehicle_id'         => 'nullable|exists:vehicles,id',
            'trip_sheet_id'      => 'nullable|exists:trip_sheets,id',
            'adjustment_type'    => 'required|in:debit,credit',
            'reason'             => 'required|in:damage,shortage,delay,toll,loading_unloading,other',
            'reason_description' => 'nullable|string|max:255',
            'amount'             => 'required|numeric|min:0.01',
            'remarks'            => 'nullable|string',
        ]);

        try {
            TransportAdjustment::create(array_merge(
                $request->only([
                    'adjustment_number', 'adjustment_date', 'vehicle_id',
                    'trip_sheet_id', 'adjustment_type', 'reason',
                    'reason_description', 'amount', 'remarks',
                ]),
                [
                    'status'     => 'pending',
                    'created_by' => Auth::id(),
                    'updated_by' => Auth::id(),
                ]
            ));

            return redirect()
                ->route('transport.transport-adjustments.index')
                ->with('success', 'Transport adjustment created successfully.');
        }
        catch (QueryException $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    public function show(TransportAdjustment $transportAdjustment): View
    {
        $transportAdjustment->load(['vehicle', 'tripSheet', 'approvedBy', 'createdBy']);
        return view('transport.transport-adjustments.show', compact('transportAdjustment'));
    }

    public function edit(TransportAdjustment $transportAdjustment): View
    {
        if ($transportAdjustment->status === 'approved') {
            return redirect()
                ->route('transport.transport-adjustments.show', $transportAdjustment)
                ->with('error', 'Approved adjustments cannot be edited.');
        }

        $vehicles = Vehicle::active()->pluck('vehicle_number', 'id');

        $tripSheets = TripSheet::completed()
            ->select('id', 'trip_number', 'trip_date')
            ->latest('trip_date')
            ->limit(100)
            ->get()
            ->mapWithKeys(fn($t) => [
                $t->id => $t->trip_number . ' (' . $t->trip_date->format('d-m-Y') . ')'
            ]);

        return view('transport.transport-adjustments.edit',
            compact('transportAdjustment', 'vehicles', 'tripSheets'));
    }

    public function update(Request $request, TransportAdjustment $transportAdjustment): RedirectResponse
    {
        if ($transportAdjustment->status === 'approved') {
            return back()->with('error', 'Approved adjustments cannot be edited.');
        }

        $request->validate([
            'adjustment_date'    => 'required|date|before_or_equal:today',
            'vehicle_id'         => 'nullable|exists:vehicles,id',
            'trip_sheet_id'      => 'nullable|exists:trip_sheets,id',
            'adjustment_type'    => 'required|in:debit,credit',
            'reason'             => 'required|in:damage,shortage,delay,toll,loading_unloading,other',
            'reason_description' => 'nullable|string|max:255',
            'amount'             => 'required|numeric|min:0.01',
            'remarks'            => 'nullable|string',
        ]);

        try {
            $transportAdjustment->update(array_merge(
                $request->only([
                    'adjustment_date', 'vehicle_id', 'trip_sheet_id',
                    'adjustment_type', 'reason', 'reason_description',
                    'amount', 'remarks',
                ]),
                ['updated_by' => Auth::id()]
            ));

            return redirect()
                ->route('transport.transport-adjustments.index')
                ->with('success', 'Adjustment updated successfully.');
        }
        catch (QueryException $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    public function destroy(TransportAdjustment $transportAdjustment): RedirectResponse
    {
        if ($transportAdjustment->status === 'approved') {
            return back()->with('error', 'Approved adjustments cannot be deleted.');
        }

        $transportAdjustment->delete();

        return redirect()
            ->route('transport.transport-adjustments.index')
            ->with('success', 'Adjustment deleted successfully.');
    }

    public function approve(TransportAdjustment $transportAdjustment): RedirectResponse
    {
        if ($transportAdjustment->status !== 'pending') {
            return back()->with('error', 'Only pending adjustments can be approved.');
        }

        $transportAdjustment->approve(Auth::id());

        return back()->with('success', 'Adjustment approved successfully.');
    }
}
