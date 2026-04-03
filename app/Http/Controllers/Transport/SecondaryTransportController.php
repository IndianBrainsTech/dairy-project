<?php

namespace App\Http\Controllers\Transport;

use App\Http\Controllers\Controller;
use App\Models\Transport\SecondaryTransport;
use App\Models\Transport\SupplierTransporter;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Contracts\View\View;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SecondaryTransportController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request): View
    {
        $query = SecondaryTransport::with(['supplierTransporter', 'route']);

        if ($request->filled('supplier_transporter_id')) {
            $query->where('supplier_transporter_id', $request->supplier_transporter_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('from_date')) {
            $query->whereDate('transport_date', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('transport_date', '<=', $request->to_date);
        }

        // Default: current month
        if (!$request->filled('from_date') && !$request->filled('to_date')) {
            $query->whereMonth('transport_date', now()->month)
                  ->whereYear('transport_date',  now()->year);
        }

        $records      = $query->latest('transport_date')->get();
        $transporters = SupplierTransporter::active()->pluck('name', 'id');

        return view('transport.secondary-transport.index',
            compact('records', 'transporters'));
    }

    public function create(): View
    {
        $transporters    = SupplierTransporter::active()->pluck('name', 'id');
        $routes          = DB::table('routes')->orderBy('name')->pluck('name', 'id');
        $referenceNumber = SecondaryTransport::generateReferenceNumber();
        return view('transport.secondary-transport.create',
            compact('transporters', 'routes', 'referenceNumber'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'reference_number'        => 'required|string|max:50|unique:secondary_transports,reference_number',
            'transport_date'          => 'required|date|before_or_equal:today',
            'supplier_transporter_id' => 'required|exists:supplier_transporters,id',
            'vehicle_number'          => 'required|string|max:20',
            'vehicle_type'            => 'nullable|string|max:100',
            'route_id'                => 'nullable|exists:routes,id',
            'from_location'           => 'nullable|string|max:150',
            'to_location'             => 'nullable|string|max:150',
            'distance_km'             => 'nullable|numeric|min:0',
            'loaded_qty'              => 'required|numeric|min:0',
            'product_type'            => 'nullable|string|max:100',
            'rate'                    => 'required|numeric|min:0',
            'rate_type'               => 'required|in:per_trip,per_km,per_litre',
            'other_charges'           => 'nullable|numeric|min:0',
            'remarks'                 => 'nullable|string',
        ]);

        try {
            $data = $request->only([
                'reference_number', 'transport_date', 'supplier_transporter_id',
                'vehicle_number', 'vehicle_type', 'route_id', 'from_location',
                'to_location', 'distance_km', 'loaded_qty', 'product_type',
                'rate', 'rate_type', 'other_charges', 'remarks',
            ]);

            // Calculate amount based on rate_type
            $data['amount'] = match ($request->rate_type) {
                'per_km'    => ($request->distance_km ?? 0) * $request->rate,
                'per_litre' => $request->loaded_qty * $request->rate,
                default     => $request->rate, // per_trip
            };

            $data['other_charges'] = $data['other_charges'] ?? 0;
            $data['status']        = 'pending';
            $data['created_by']    = Auth::id();
            $data['updated_by']    = Auth::id();

            SecondaryTransport::create($data);
            // total_amount auto-calculated in model booted()

            return redirect()
                ->route('transport.secondary-transport.index')
                ->with('success', 'Secondary transport record added successfully.');
        }
        catch (QueryException $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    public function show(SecondaryTransport $secondaryTransport): View
    {
        $secondaryTransport->load([
            'supplierTransporter',
            'route',
            'billItems.secondaryTransportBill',
        ]);
        return view('transport.secondary-transport.show', compact('secondaryTransport'));
    }

    public function edit(SecondaryTransport $secondaryTransport): View
    {
        if ($secondaryTransport->status === 'billed') {
            return redirect()
                ->route('transport.secondary-transport.show', $secondaryTransport)
                ->with('error', 'Billed records cannot be edited.');
        }

        $transporters = SupplierTransporter::active()->pluck('name', 'id');
        $routes       = DB::table('routes')->orderBy('name')->pluck('name', 'id');
        return view('transport.secondary-transport.edit',
            compact('secondaryTransport', 'transporters', 'routes'));
    }

    public function update(Request $request, SecondaryTransport $secondaryTransport): RedirectResponse
    {
        if ($secondaryTransport->status === 'billed') {
            return back()->with('error', 'Billed records cannot be edited.');
        }

        $request->validate([
            'transport_date'          => 'required|date|before_or_equal:today',
            'supplier_transporter_id' => 'required|exists:supplier_transporters,id',
            'vehicle_number'          => 'required|string|max:20',
            'vehicle_type'            => 'nullable|string|max:100',
            'route_id'                => 'nullable|exists:routes,id',
            'from_location'           => 'nullable|string|max:150',
            'to_location'             => 'nullable|string|max:150',
            'distance_km'             => 'nullable|numeric|min:0',
            'loaded_qty'              => 'required|numeric|min:0',
            'product_type'            => 'nullable|string|max:100',
            'rate'                    => 'required|numeric|min:0',
            'rate_type'               => 'required|in:per_trip,per_km,per_litre',
            'other_charges'           => 'nullable|numeric|min:0',
            'remarks'                 => 'nullable|string',
        ]);

        try {
            $data = $request->only([
                'transport_date', 'supplier_transporter_id', 'vehicle_number',
                'vehicle_type', 'route_id', 'from_location', 'to_location',
                'distance_km', 'loaded_qty', 'product_type', 'rate',
                'rate_type', 'other_charges', 'remarks',
            ]);

            $data['amount'] = match ($request->rate_type) {
                'per_km'    => ($request->distance_km ?? 0) * $request->rate,
                'per_litre' => $request->loaded_qty * $request->rate,
                default     => $request->rate,
            };

            $data['updated_by'] = Auth::id();
            $secondaryTransport->update($data);

            return redirect()
                ->route('transport.secondary-transport.index')
                ->with('success', 'Secondary transport record updated successfully.');
        }
        catch (QueryException $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    public function destroy(SecondaryTransport $secondaryTransport): RedirectResponse
    {
        if ($secondaryTransport->status === 'billed') {
            return back()->with('error', 'Billed records cannot be deleted.');
        }

        $secondaryTransport->delete();

        return redirect()
            ->route('transport.secondary-transport.index')
            ->with('success', 'Record deleted successfully.');
    }

    // AJAX — get unbilled records for a transporter + period
    public function getUnbilledRecords(Request $request): JsonResponse
    {
        $records = SecondaryTransport::with('route')
            ->where('supplier_transporter_id', $request->supplier_transporter_id)
            ->whereBetween('transport_date', [$request->from, $request->to])
            ->unbilled()
            ->orderBy('transport_date')
            ->get();

        return response()->json($records);
    }
}
