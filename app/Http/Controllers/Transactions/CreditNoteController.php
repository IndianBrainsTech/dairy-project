<?php

namespace App\Http\Controllers\Transactions;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Contracts\View\View;

use App\Http\Controllers\Controller;
use App\Http\Traits\FilterUtility;
use App\Http\Requests\Transactions\CreditNoteRequest;

use App\Models\Transactions\CreditNotes\CreditNote;

use App\Services\CreditNoteService;
use App\Services\ReceivableService;
use App\Services\InvoiceService;

use App\Enums\DocumentStatus;
use App\Enums\FormMode;

class CreditNoteController extends Controller
{
    protected $creditNoteService;
    use FilterUtility;

    public function __construct(CreditNoteService $creditNoteService)
    {
        $this->middleware('auth');
        $this->creditNoteService = $creditNoteService;
        $this->middleware('permission:create_credit_note')->only(['create','store']);
        $this->middleware('permission:index_credit_note')->only(['index']);
        $this->middleware('permission:show_credit_note')->only(['navigate']);
        $this->middleware('permission:update_credit_note')->only(['edit','update']);
        // $this->middleware('permission:cancel_credit_note')->only(['']);
        $this->middleware('permission:approve_credit_note')->only(['createApproval','updateApproval']);
    }

    public function create(): View
    {
        $documentNumber = $this->creditNoteService->generateDocumentNumber();

        $record = new CreditNote([
            'document_number' => $documentNumber,
            'document_date'   => today(),
        ]); 

        return view('transactions.credit-notes.manage', [
            'form_mode'   => FormMode::CREATE,
            'form_action' => route('credit-notes.store'),
            'form_method' => 'POST',
            'page_title'  => 'Create Credit Note',
            'record'      => $record,
            'customer'    => null,
        ]);
    }

    public function edit(CreditNote $creditNote, ReceivableService $receivableService): View
    {
        $customer = [
            'id'   => $creditNote->customer->id,
            'name' => $creditNote->customer->customer_name,
        ];

        $receivables = $receivableService->getReceivables($customer['id'])['invoices'];
        $receivables = $this->creditNoteService->adjustReceivables($receivables, $creditNote->id);

        return view('transactions.credit-notes.manage', [
            'form_mode'   => FormMode::EDIT,
            'form_action' => route('credit-notes.update', $creditNote),
            'form_method' => 'PUT',
            'page_title'  => 'Edit Credit Note',
            'record'      => $creditNote,
            'customer'    => $customer,
            'rows'        => $receivables,
        ]);
    }

    public function store(CreditNoteRequest $request): JsonResponse
    {
        try {
            $this->creditNoteService->store($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Credit note has been created successfully.',
                'new_document' => $this->creditNoteService->generateDocumentNumber(),
            ]);
        }
        catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create credit note.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    public function update(CreditNoteRequest $request, CreditNote $creditNote): JsonResponse
    {
        try {
            $this->creditNoteService->update($creditNote, $request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Credit note has been updated successfully.',
            ]);
        }
        catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update credit note.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    public function index(Request $request): View
    {
        $filters = $this->resolveDateAndCustomerFilters($request, CreditNote::max('document_date'));
        $customerId = $filters['customer']['id'];
        
        $records = CreditNote::select('id','document_number','document_date','customer_id','reason','status')
            ->with('customer:id,customer_name')
            ->whereBetween('document_date', $filters['from_to_dates'])
            ->when($customerId != 0, fn($q) => $q->where('customer_id', $customerId))
            ->get();

        return view('transactions.credit-notes.index', [
            'dates'    => $filters['dates'],
            'customer' => $filters['customer'],
            'records'  => $records,
        ]);
    }

    public function navigate(Request $request): View
    {
        $validated = $request->validate([
            'current_document' => 'required|string|exists:credit_notes,document_number',
            'document_list'    => 'required',
            'document_list.*'  => 'required|string|exists:credit_notes,document_number',
        ]);

        $creditNote = CreditNote::with(['items'])
            ->where('document_number', $validated['current_document'])
            ->firstOrFail();

        return view('transactions.credit-notes.navigate', [
            'record'         => $creditNote,
            'document_list'  => $validated['document_list'],
        ]);
    }

    public function createApproval(): View
    {
        $records = CreditNote::select('id','document_number','document_date','customer_id','reason','amount')
            ->with('customer:id,customer_name')
            ->where('status',DocumentStatus::DRAFT)
            ->get();

        return view('transactions.credit-notes.approve', compact('records'));
    }

    public function updateApproval(Request $request, InvoiceService $invoiceService): JsonResponse
    {
        $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['integer', 'exists:credit_notes,id']
        ]);

        $ids = (array) $request->input('ids', []);

        if (empty($ids)) {
            return response()->json([
                'success' => false,
                'message' => 'No credit note selected.',
            ], 422);
        }

        try {
            $this->creditNoteService->updateApproval($ids, $invoiceService);

            return response()->json([
                'success' => true,
                'message' => 'Credit notes have been approved successfully!',
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to approve credit notes.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}