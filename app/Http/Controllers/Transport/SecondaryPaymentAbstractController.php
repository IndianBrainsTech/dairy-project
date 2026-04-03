<?php

namespace App\Http\Controllers\Transport;

use App\Http\Controllers\Controller;
use App\Models\Transport\SecondaryPaymentAbstract;
use App\Models\Transport\SecondaryTransportBill;
use App\Models\Transport\SupplierTransporter;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Contracts\View\View;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;

class SecondaryPaymentAbstractController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request): View
    {
        $query = SecondaryPaymentAbstract::with('supplierTransporter');

        if ($request->filled('supplier_transporter_id')) {
            $query->where('supplier_transporter_id', $request->supplier_transporter_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('from_date')) {
            $query->whereDate('abstract_date', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('abstract_date', '<=', $request->to_date);
        }

        $abstracts    = $query->latest('abstract_date')->get();
        $transporters = SupplierTransporter::active()->pluck('name', 'id');

        return view('transport.secondary-payment-abstracts.index',
            compact('abstracts', 'transporters'));
    }

    public function create(): View
    {
        $transporters   = SupplierTransporter::active()->pluck('name', 'id');
        $abstractNumber = SecondaryPaymentAbstract::generateAbstractNumber();
        return view('transport.secondary-payment-abstracts.create',
            compact('transporters', 'abstractNumber'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'abstract_number'         => 'required|string|max:50|unique:secondary_payment_abstracts,abstract_number',
            'abstract_date'           => 'required|date',
            'period_from'             => 'required|date',
            'period_to'               => 'required|date|after_or_equal:period_from',
            'supplier_transporter_id' => 'required|exists:supplier_transporters,id',
            'remarks'                 => 'nullable|string',
        ]);

        try {
            $abstract = SecondaryPaymentAbstract::create([
                'abstract_number'         => $request->abstract_number,
                'abstract_date'           => $request->abstract_date,
                'period_from'             => $request->period_from,
                'period_to'               => $request->period_to,
                'supplier_transporter_id' => $request->supplier_transporter_id,
                'remarks'                 => $request->remarks,
                'created_by'              => Auth::id(),
                'updated_by'              => Auth::id(),
            ]);

            // Auto-calculate totals from approved bills in the period
            $abstract->recalculate();

            return redirect()
                ->route('transport.secondary-payment-abstracts.show', $abstract)
                ->with('success', 'Payment abstract created and calculated successfully.');
        }
        catch (QueryException $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    public function show(SecondaryPaymentAbstract $secondaryPaymentAbstract): View
    {
        $secondaryPaymentAbstract->load('supplierTransporter');

        // Load the bills that make up this abstract
        $bills = SecondaryTransportBill::where(
                'supplier_transporter_id',
                $secondaryPaymentAbstract->supplier_transporter_id
            )
            ->where('status', 'approved')
            ->whereBetween('bill_date', [
                $secondaryPaymentAbstract->period_from->toDateString(),
                $secondaryPaymentAbstract->period_to->toDateString(),
            ])
            ->get();

        return view('transport.secondary-payment-abstracts.show',
            compact('secondaryPaymentAbstract', 'bills'));
    }

    public function edit(SecondaryPaymentAbstract $secondaryPaymentAbstract): View
    {
        if ($secondaryPaymentAbstract->status === 'finalised') {
            return redirect()
                ->route('transport.secondary-payment-abstracts.show', $secondaryPaymentAbstract)
                ->with('error', 'Finalised abstracts cannot be edited.');
        }

        $transporters = SupplierTransporter::active()->pluck('name', 'id');
        return view('transport.secondary-payment-abstracts.edit',
            compact('secondaryPaymentAbstract', 'transporters'));
    }

    public function update(Request $request, SecondaryPaymentAbstract $secondaryPaymentAbstract): RedirectResponse
    {
        if ($secondaryPaymentAbstract->status === 'finalised') {
            return back()->with('error', 'Finalised abstracts cannot be edited.');
        }

        $request->validate([
            'abstract_date'           => 'required|date',
            'period_from'             => 'required|date',
            'period_to'               => 'required|date|after_or_equal:period_from',
            'supplier_transporter_id' => 'required|exists:supplier_transporters,id',
            'remarks'                 => 'nullable|string',
        ]);

        try {
            $secondaryPaymentAbstract->update(array_merge(
                $request->only([
                    'abstract_date', 'period_from', 'period_to',
                    'supplier_transporter_id', 'remarks',
                ]),
                ['updated_by' => Auth::id()]
            ));

            // Recalculate after period change
            $secondaryPaymentAbstract->recalculate();

            return redirect()
                ->route('transport.secondary-payment-abstracts.show', $secondaryPaymentAbstract)
                ->with('success', 'Payment abstract updated and recalculated.');
        }
        catch (QueryException $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    public function destroy(SecondaryPaymentAbstract $secondaryPaymentAbstract): RedirectResponse
    {
        if ($secondaryPaymentAbstract->status === 'finalised') {
            return back()->with('error', 'Finalised abstracts cannot be deleted.');
        }

        $secondaryPaymentAbstract->delete();

        return redirect()
            ->route('transport.secondary-payment-abstracts.index')
            ->with('success', 'Payment abstract deleted successfully.');
    }

    public function finalise(SecondaryPaymentAbstract $secondaryPaymentAbstract): RedirectResponse
    {
        if ($secondaryPaymentAbstract->status !== 'draft') {
            return back()->with('error', 'Only draft abstracts can be finalised.');
        }

        // Recalculate one last time before finalising
        $secondaryPaymentAbstract->recalculate();
        $secondaryPaymentAbstract->update(['status' => 'finalised']);

        return back()->with('success', 'Payment abstract finalised successfully.');
    }

    public function recalculate(SecondaryPaymentAbstract $secondaryPaymentAbstract): RedirectResponse
    {
        $secondaryPaymentAbstract->recalculate();
        return back()->with('success', 'Payment abstract recalculated successfully.');
    }
}
