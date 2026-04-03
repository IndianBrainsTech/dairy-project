<?php

namespace App\Http\Controllers\Transport;

use App\Http\Controllers\Controller;
use App\Models\Transport\TransportBill;
use App\Models\Transport\TransportBillItem;
use App\Models\Transport\TripSheet;
use App\Models\Transport\Vehicle;
use App\Models\Transport\SupplierTransporter;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Contracts\View\View;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TransportBillController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request): View
    {
        $query = TransportBill::with(['vehicle', 'supplierTransporter']);

        if ($request->filled('vehicle_id')) {
            $query->where('vehicle_id', $request->vehicle_id);
        }
        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('from_date')) {
            $query->whereDate('bill_date', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('bill_date', '<=', $request->to_date);
        }

        $bills    = $query->latest('bill_date')->get();
        $vehicles = Vehicle::active()->pluck('vehicle_number', 'id');

        $summary = (object) [
            'total_gross'   => $bills->sum('gross_amount'),
            'total_net'     => $bills->sum('net_amount'),
            'total_paid'    => $bills->sum('paid_amount'),
            'total_balance' => $bills->sum('balance_amount'),
        ];

        return view('transport.transport-bills.index',
            compact('bills', 'vehicles', 'summary'));
    }

    public function create(): View
    {
        $vehicles     = Vehicle::active()->pluck('vehicle_number', 'id');
        $transporters = SupplierTransporter::active()->pluck('name', 'id');
        $billNumber   = TransportBill::generateBillNumber();
        return view('transport.transport-bills.create',
            compact('vehicles', 'transporters', 'billNumber'));
    }

    // AJAX — get unbilled completed trips for a vehicle + period
    public function getUnbilledTrips(Request $request): JsonResponse
    {
        $trips = TripSheet::with('route')
            ->where('vehicle_id', $request->vehicle_id)
            ->whereBetween('trip_date', [$request->from, $request->to])
            ->unbilled()
            ->completed()
            ->orderBy('trip_date')
            ->get();

        return response()->json($trips);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'bill_number'             => 'required|string|max:50|unique:transport_bills,bill_number',
            'bill_date'               => 'required|date',
            'bill_period_from'        => 'required|date',
            'bill_period_to'          => 'required|date|after_or_equal:bill_period_from',
            'vehicle_id'              => 'required|exists:vehicles,id',
            'supplier_transporter_id' => 'nullable|exists:supplier_transporters,id',
            'bill_type'               => 'required|in:own,hired',
            'other_charges'           => 'nullable|numeric|min:0',
            'tds_percentage'          => 'nullable|numeric|min:0|max:100',
            'due_date'                => 'nullable|date',
            'remarks'                 => 'nullable|string',
            'trip_sheet_ids'          => 'required|array|min:1',
            'trip_sheet_ids.*'        => 'exists:trip_sheets,id',
        ]);

        try {
            DB::transaction(function () use ($request) {
                $bill = TransportBill::create([
                    'bill_number'             => $request->bill_number,
                    'bill_date'               => $request->bill_date,
                    'bill_period_from'        => $request->bill_period_from,
                    'bill_period_to'          => $request->bill_period_to,
                    'vehicle_id'              => $request->vehicle_id,
                    'supplier_transporter_id' => $request->supplier_transporter_id,
                    'bill_type'               => $request->bill_type,
                    'other_charges'           => $request->other_charges ?? 0,
                    'tds_percentage'          => $request->tds_percentage ?? 0,
                    'due_date'                => $request->due_date,
                    'remarks'                 => $request->remarks,
                    'created_by'              => Auth::id(),
                    'updated_by'              => Auth::id(),
                ]);

                // Add trip sheet line items
                $tripSheets = TripSheet::whereIn('id', $request->trip_sheet_ids)->get();

                foreach ($tripSheets as $trip) {
                    TransportBillItem::create([
                        'transport_bill_id' => $bill->id,
                        'trip_sheet_id'     => $trip->id,
                        'trip_date'         => $trip->trip_date,
                        'distance_km'       => $trip->distance_km       ?? 0,
                        'milk_litres'       => $trip->net_milk_litres,
                        'trip_amount'       => $trip->trip_amount,
                        'diesel_amount'     => $trip->diesel_cost,
                        'adjustment_amount' => optional($trip->adjustment)->amount ?? 0,
                    ]);
                }

                // Recalculate bill totals from items
                $bill->recalculateTotals();
            });

            return redirect()
                ->route('transport.transport-bills.index')
                ->with('success', 'Transport bill created successfully.');
        }
        catch (\Throwable $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    public function show(TransportBill $transportBill): View
    {
        $transportBill->load([
            'vehicle',
            'supplierTransporter',
            'items.tripSheet.route',
            'approvedBy',
            'createdBy',
        ]);
        return view('transport.transport-bills.show', compact('transportBill'));
    }

    public function edit(TransportBill $transportBill): View
    {
        if ($transportBill->status === 'approved') {
            return redirect()
                ->route('transport.transport-bills.show', $transportBill)
                ->with('error', 'Approved bills cannot be edited.');
        }

        $vehicles     = Vehicle::active()->pluck('vehicle_number', 'id');
        $transporters = SupplierTransporter::active()->pluck('name', 'id');
        $transportBill->load('items.tripSheet');

        return view('transport.transport-bills.edit',
            compact('transportBill', 'vehicles', 'transporters'));
    }

    public function update(Request $request, TransportBill $transportBill): RedirectResponse
    {
        if ($transportBill->status === 'approved') {
            return back()->with('error', 'Approved bills cannot be edited.');
        }

        $request->validate([
            'bill_date'               => 'required|date',
            'supplier_transporter_id' => 'nullable|exists:supplier_transporters,id',
            'bill_type'               => 'required|in:own,hired',
            'other_charges'           => 'nullable|numeric|min:0',
            'tds_percentage'          => 'nullable|numeric|min:0|max:100',
            'due_date'                => 'nullable|date',
            'remarks'                 => 'nullable|string',
        ]);

        try {
            $transportBill->update(array_merge(
                $request->only([
                    'bill_date', 'supplier_transporter_id', 'bill_type',
                    'other_charges', 'tds_percentage', 'due_date', 'remarks',
                ]),
                ['updated_by' => Auth::id()]
            ));

            return redirect()
                ->route('transport.transport-bills.index')
                ->with('success', 'Transport bill updated successfully.');
        }
        catch (QueryException $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    public function destroy(TransportBill $transportBill): RedirectResponse
    {
        if ($transportBill->status === 'approved') {
            return back()->with('error', 'Approved bills cannot be deleted.');
        }

        try {
            DB::transaction(function () use ($transportBill) {
                $transportBill->items()->delete();
                $transportBill->delete();
            });

            return redirect()
                ->route('transport.transport-bills.index')
                ->with('success', 'Transport bill deleted successfully.');
        }
        catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function approve(TransportBill $transportBill): RedirectResponse
    {
        if ($transportBill->status !== 'draft') {
            return back()->with('error', 'Only draft bills can be approved.');
        }

        $transportBill->approve(Auth::id());

        return back()->with('success', 'Transport bill approved successfully.');
    }

    public function recordPayment(Request $request, TransportBill $transportBill): RedirectResponse
    {
        $request->validate([
            'paid_amount' => 'required|numeric|min:0.01|max:' . $transportBill->balance_amount,
        ]);

        $transportBill->update([
            'paid_amount' => $transportBill->paid_amount + $request->paid_amount,
            'updated_by'  => Auth::id(),
        ]);
        // payment_status and balance auto-updated via model booted()

        return back()->with('success', 'Payment recorded successfully.');
    }
}
