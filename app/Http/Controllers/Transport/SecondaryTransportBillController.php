<?php

namespace App\Http\Controllers\Transport;

use App\Http\Controllers\Controller;
use App\Models\Transport\SecondaryTransport;
use App\Models\Transport\SecondaryTransportBill;
use App\Models\Transport\SecondaryTransportBillItem;
use App\Models\Transport\SupplierTransporter;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Contracts\View\View;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SecondaryTransportBillController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request): View
    {
        $query = SecondaryTransportBill::with('supplierTransporter');

        if ($request->filled('supplier_transporter_id')) {
            $query->where('supplier_transporter_id', $request->supplier_transporter_id);
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

        $bills        = $query->latest('bill_date')->get();
        $transporters = SupplierTransporter::active()->pluck('name', 'id');

        return view('transport.secondary-transport-bills.index',
            compact('bills', 'transporters'));
    }

    public function create(): View
    {
        $transporters = SupplierTransporter::active()->pluck('name', 'id');
        $billNumber   = SecondaryTransportBill::generateBillNumber();
        return view('transport.secondary-transport-bills.create',
            compact('transporters', 'billNumber'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'bill_number'             => 'required|string|max:50|unique:secondary_transport_bills,bill_number',
            'bill_date'               => 'required|date',
            'bill_period_from'        => 'required|date',
            'bill_period_to'          => 'required|date|after_or_equal:bill_period_from',
            'supplier_transporter_id' => 'required|exists:supplier_transporters,id',
            'tds_percentage'          => 'nullable|numeric|min:0|max:100',
            'other_deductions'        => 'nullable|numeric|min:0',
            'due_date'                => 'nullable|date',
            'remarks'                 => 'nullable|string',
            'transport_ids'           => 'required|array|min:1',
            'transport_ids.*'         => 'exists:secondary_transports,id',
        ]);

        try {
            DB::transaction(function () use ($request) {
                $bill = SecondaryTransportBill::create([
                    'bill_number'             => $request->bill_number,
                    'bill_date'               => $request->bill_date,
                    'bill_period_from'        => $request->bill_period_from,
                    'bill_period_to'          => $request->bill_period_to,
                    'supplier_transporter_id' => $request->supplier_transporter_id,
                    'tds_percentage'          => $request->tds_percentage  ?? 0,
                    'other_deductions'        => $request->other_deductions ?? 0,
                    'due_date'                => $request->due_date,
                    'remarks'                 => $request->remarks,
                    'created_by'              => Auth::id(),
                    'updated_by'              => Auth::id(),
                ]);

                // Add line items and mark transports as billed
                $transports = SecondaryTransport::whereIn('id', $request->transport_ids)->get();

                foreach ($transports as $transport) {
                    SecondaryTransportBillItem::create([
                        'secondary_transport_bill_id' => $bill->id,
                        'secondary_transport_id'      => $transport->id,
                        'transport_date'              => $transport->transport_date,
                        'from_location'               => $transport->from_location,
                        'to_location'                 => $transport->to_location,
                        'qty'                         => $transport->loaded_qty,
                        'rate'                        => $transport->rate,
                    ]);
                    $transport->update(['status' => 'billed']);
                }

                // Recalculate bill totals
                $bill->recalculateTotals();
            });

            return redirect()
                ->route('transport.secondary-transport-bills.index')
                ->with('success', 'Secondary transport bill created successfully.');
        }
        catch (\Throwable $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    public function show(SecondaryTransportBill $secondaryTransportBill): View
    {
        $secondaryTransportBill->load([
            'supplierTransporter',
            'items.secondaryTransport',
            'approvedBy',
        ]);
        return view('transport.secondary-transport-bills.show',
            compact('secondaryTransportBill'));
    }

    public function edit(SecondaryTransportBill $secondaryTransportBill): View
    {
        if ($secondaryTransportBill->status === 'approved') {
            return redirect()
                ->route('transport.secondary-transport-bills.show', $secondaryTransportBill)
                ->with('error', 'Approved bills cannot be edited.');
        }

        $transporters = SupplierTransporter::active()->pluck('name', 'id');
        return view('transport.secondary-transport-bills.edit',
            compact('secondaryTransportBill', 'transporters'));
    }

    public function update(Request $request, SecondaryTransportBill $secondaryTransportBill): RedirectResponse
    {
        if ($secondaryTransportBill->status === 'approved') {
            return back()->with('error', 'Approved bills cannot be edited.');
        }

        $request->validate([
            'bill_date'        => 'required|date',
            'tds_percentage'   => 'nullable|numeric|min:0|max:100',
            'other_deductions' => 'nullable|numeric|min:0',
            'due_date'         => 'nullable|date',
            'remarks'          => 'nullable|string',
        ]);

        try {
            $secondaryTransportBill->update(array_merge(
                $request->only([
                    'bill_date', 'tds_percentage', 'other_deductions',
                    'due_date', 'remarks',
                ]),
                ['updated_by' => Auth::id()]
            ));

            return redirect()
                ->route('transport.secondary-transport-bills.index')
                ->with('success', 'Bill updated successfully.');
        }
        catch (QueryException $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    public function destroy(SecondaryTransportBill $secondaryTransportBill): RedirectResponse
    {
        if ($secondaryTransportBill->status === 'approved') {
            return back()->with('error', 'Approved bills cannot be deleted.');
        }

        try {
            DB::transaction(function () use ($secondaryTransportBill) {
                // Unlink transport records — set back to pending
                $transportIds = $secondaryTransportBill->items
                    ->pluck('secondary_transport_id');

                SecondaryTransport::whereIn('id', $transportIds)
                    ->update(['status' => 'pending']);

                $secondaryTransportBill->items()->delete();
                $secondaryTransportBill->delete();
            });

            return redirect()
                ->route('transport.secondary-transport-bills.index')
                ->with('success', 'Bill deleted successfully.');
        }
        catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function approve(SecondaryTransportBill $secondaryTransportBill): RedirectResponse
    {
        if ($secondaryTransportBill->status !== 'draft') {
            return back()->with('error', 'Only draft bills can be approved.');
        }

        $secondaryTransportBill->approve(Auth::id());

        return back()->with('success', 'Bill approved successfully.');
    }

    public function recordPayment(Request $request, SecondaryTransportBill $secondaryTransportBill): RedirectResponse
    {
        $request->validate([
            'paid_amount' => 'required|numeric|min:0.01|max:' . $secondaryTransportBill->balance_amount,
        ]);

        $secondaryTransportBill->update([
            'paid_amount' => $secondaryTransportBill->paid_amount + $request->paid_amount,
            'updated_by'  => Auth::id(),
        ]);

        return back()->with('success', 'Payment recorded successfully.');
    }
}
