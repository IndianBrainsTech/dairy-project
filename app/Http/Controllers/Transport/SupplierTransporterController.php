<?php

namespace App\Http\Controllers\Transport;

use App\Http\Controllers\Controller;
use App\Models\Transport\SupplierTransporter;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Contracts\View\View;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;

class SupplierTransporterController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request): View
    {
        $query = SupplierTransporter::withCount([
            'vehicles',
            'secondaryTransportBills',
        ]);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name',       'like', "%{$search}%")
                  ->orWhere('phone',      'like', "%{$search}%")
                  ->orWhere('gst_number', 'like', "%{$search}%");
            });
        }

        $transporters = $query->latest()->get();

        return view('transport.supplier-transporters.index', compact('transporters'));
    }

    public function create(): View
    {
        return view('transport.supplier-transporters.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name'                => 'required|string|max:150',
            'contact_person'      => 'nullable|string|max:100',
            'phone'               => 'nullable|string|max:15',
            'alt_phone'           => 'nullable|string|max:15',
            'email'               => 'nullable|email|max:100',
            'address'             => 'nullable|string',
            'city'                => 'nullable|string|max:100',
            'state'               => 'nullable|string|max:100',
            'pincode'             => 'nullable|string|max:10',
            'gst_number'          => 'nullable|string|max:20',
            'pan_number'          => 'nullable|string|max:15',
            'bank_name'           => 'nullable|string|max:100',
            'bank_account_number' => 'nullable|string|max:30',
            'bank_ifsc'           => 'nullable|string|max:15',
            'bank_branch'         => 'nullable|string|max:100',
            'status'              => 'required|in:active,inactive',
            'remarks'             => 'nullable|string',
        ]);

        try {
            SupplierTransporter::create(array_merge(
                $request->only([
                    'name', 'contact_person', 'phone', 'alt_phone', 'email',
                    'address', 'city', 'state', 'pincode', 'gst_number',
                    'pan_number', 'bank_name', 'bank_account_number',
                    'bank_ifsc', 'bank_branch', 'status', 'remarks',
                ]),
                ['created_by' => Auth::id(), 'updated_by' => Auth::id()]
            ));

            return redirect()
                ->route('transport.supplier-transporters.index')
                ->with('success', 'Supplier transporter added successfully.');
        }
        catch (QueryException $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    public function show(SupplierTransporter $supplierTransporter): View
    {
        $supplierTransporter->load([
            'vehicles',
            'secondaryTransportBills' => fn($q) => $q->latest()->limit(10),
        ]);

        $unpaidBalance = $supplierTransporter
            ->secondaryTransportBills()
            ->unpaid()
            ->sum('balance_amount');

        return view('transport.supplier-transporters.show',
            compact('supplierTransporter', 'unpaidBalance'));
    }

    public function edit(SupplierTransporter $supplierTransporter): View
    {
        return view('transport.supplier-transporters.edit', compact('supplierTransporter'));
    }

    public function update(Request $request, SupplierTransporter $supplierTransporter): RedirectResponse
    {
        $request->validate([
            'name'                => 'required|string|max:150',
            'contact_person'      => 'nullable|string|max:100',
            'phone'               => 'nullable|string|max:15',
            'alt_phone'           => 'nullable|string|max:15',
            'email'               => 'nullable|email|max:100',
            'address'             => 'nullable|string',
            'city'                => 'nullable|string|max:100',
            'state'               => 'nullable|string|max:100',
            'pincode'             => 'nullable|string|max:10',
            'gst_number'          => 'nullable|string|max:20',
            'pan_number'          => 'nullable|string|max:15',
            'bank_name'           => 'nullable|string|max:100',
            'bank_account_number' => 'nullable|string|max:30',
            'bank_ifsc'           => 'nullable|string|max:15',
            'bank_branch'         => 'nullable|string|max:100',
            'status'              => 'required|in:active,inactive',
            'remarks'             => 'nullable|string',
        ]);

        try {
            $supplierTransporter->update(array_merge(
                $request->only([
                    'name', 'contact_person', 'phone', 'alt_phone', 'email',
                    'address', 'city', 'state', 'pincode', 'gst_number',
                    'pan_number', 'bank_name', 'bank_account_number',
                    'bank_ifsc', 'bank_branch', 'status', 'remarks',
                ]),
                ['updated_by' => Auth::id()]
            ));

            return redirect()
                ->route('transport.supplier-transporters.index')
                ->with('success', 'Supplier transporter updated successfully.');
        }
        catch (QueryException $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    public function destroy(SupplierTransporter $supplierTransporter): RedirectResponse
    {
        if ($supplierTransporter->secondaryTransportBills()->exists()) {
            return back()->with('error', 'Cannot delete — bills exist for this transporter.');
        }

        $supplierTransporter->delete();

        return redirect()
            ->route('transport.supplier-transporters.index')
            ->with('success', 'Supplier transporter deleted successfully.');
    }
}
