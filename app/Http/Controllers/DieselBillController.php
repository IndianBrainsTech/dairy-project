<?php

namespace App\Http\Controllers ;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\View\View;
use App\Http\Requests\DieselBillRequest;
use App\Models\Transport\DieselBill;
use App\Models\Transport\DieselBillStatement;
use App\Models\Transport\DieselBillPayment;
use App\Models\Transport\PetrolBunk;
use App\Models\Transport\Vehicle;
use App\Models\Transactions\BankPayment;
use App\Models\Masters\PetrolBunkTurnover;
use App\Models\Masters\TdsMaster;
use App\Models\Masters\BankMaster;
use App\Models\Profiles\Employee;
use App\Models\Places\MRoute;
use App\Enums\FormMode;
use App\Enums\Roles;
use App\Enums\Status;
use App\Enums\TdsStatus;
use App\Enums\PaymentStatus;
use App\Enums\PaymentType;
use Carbon\Carbon;

class DieselBillController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function indexDieselBill(Request $request): View
    {
        $validated = $request->validate([
            'from_date' => 'nullable|date',
            'to_date'   => 'nullable|date|after_or_equal:from_date',
            'bunk_id'   => 'bail|nullable|exists:petrol_bunks,id',
            'bunk_name' => 'bail|nullable|string|max:100',
        ]);

        $bunkId   = $validated['bunk_id'] ?? 0;
        $bunkName = $validated['bunk_name'] ?? '';
        $fromDate = $request->input('from_date', Carbon::today()->toDateString());
        $toDate   = $request->input('to_date', $fromDate);

        $query = DieselBill::whereBetween('document_date', [$fromDate, $toDate])
            ->when($bunkId != 0, fn ($q) => $q->where('bunk_id', $bunkId));

        $bills = (clone $query)
            ->select('id','document_number','document_date','bunk_name','vehicle_number','fuel','amount','status')
            ->get();

        $totals = (clone $query)
            ->selectRaw('SUM(fuel) as fuel, SUM(amount) as amount')
            ->first();

        $bunks = PetrolBunk::where('status', 'ACTIVE')
            ->orderBy('name')
            ->get(['id','name']);

        $dates = ['from' => $fromDate, 'to' => $toDate];

        // return view('transactions.diesel-bills.entries.index', compact('dates', 'bills'));
        return view('transactions.diesel-bills.entries.index', [
            'dates'  => ['from' => $fromDate, 'to' => $toDate],
            'bunk'   => ['id' => $bunkId, 'name' => $bunkName],
            'bills'  => $bills,
            'totals' => $totals,
            'bunks'  => $bunks,
        ]);
    }

    public function createDieselBill(): View
    {
        $bunks = PetrolBunk::where('status', 'ACTIVE')
            ->orderBy('name')
            ->get(['id','name']);

        $routes = MRoute::orderBy('name')
            ->get(['id','name']);

        $vehicles = Vehicle::where('status', 'Active')
            ->get(['id','vehicle_number']);

        $drivers = Employee::where('role_id', Roles::DRIVER_ID)
            ->where('status', 'Active')
            ->orderBy('name')
            ->get(['id','name']);

        return view('transactions.diesel-bills.entries.manage', compact('bunks', 'routes', 'vehicles', 'drivers'));
    }

    public function storeDieselBill(DieselBillRequest $request): JsonResponse
    {
        try {
            DB::transaction(function () use ($request) {
                $data = $request->validated();

                $openingKm  = $request->opening_km;
                $closingKm  = $request->closing_km;
                $fuel       = $request->fuel;
                $rate       = $request->rate;

                $runningKm  = $closingKm - $openingKm;
                $amount     = $fuel * $rate;
                $kmpl       = $fuel > 0 ? ($runningKm / $fuel) : 0;

                DieselBill::create([
                    ...$data,
                    'amount'     => $amount,
                    'running_km' => $runningKm,
                    'kmpl'       => $kmpl,
                    'created_by' => auth()->id(),
                    'created_at' => now(),
                ]);
            });

            return response()->json([
                'success' => true,
                'message' => 'Diesel bill saved successfully.',
            ]);
        }
        catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to save diesel bill.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    public function updateDieselBill(DieselBillRequest $request, DieselBill $bill): JsonResponse
    {
        try {
            DB::transaction(function () use ($request, $bill) {
                $data = $request->validated();

                $openingKm  = $request->opening_km;
                $closingKm  = $request->closing_km;
                $fuel       = $request->fuel;
                $rate       = $request->rate;

                $runningKm  = $closingKm - $openingKm;
                $amount     = $fuel * $rate;
                $kmpl       = $fuel > 0 ? ($runningKm / $fuel) : 0;

                $bill->update([
                    ...$data,
                    'amount'     => $amount,
                    'running_km' => $runningKm,
                    'kmpl'       => $kmpl,
                    'updated_by' => auth()->id(),
                    'updated_at' => now(),
                ]);
            });

            return response()->json([
                'success' => true,
                'message' => 'Diesel bill updated successfully.',
            ]);
        }
        catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update diesel bill.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    public function showDieselBill(Request $request): View
    {
        $validated = $request->validate([
            'id'      => 'required|exists:diesel_bills,id',
            'id_list' => 'required|string',
        ]);
        
        $bill = DieselBill::where('id', $validated['id'])->firstOrFail();

        return view('transactions.diesel-bills.entries.show', [
            'bill'    => $bill,
            'id_list' => $validated['id_list'],
        ]);
    }

    public function destroyDieselBill(DieselBill $bill): JsonResponse
    {
        try {
            $bill->delete();

            return response()->json([
                'success' => true,
                'message' => 'Diesel bill has been deleted successfully!'
            ]);
        }
        catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete diesel bill.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function fetchDieselBill(DieselBill $bill): JsonResponse
    {
        return response()->json([
            'record' => $bill
        ]);
    }

    public function getPendingBills(): JsonResponse
    {
        $bills = DieselBill::select('id','document_date','bunk_name','vehicle_number','route_name')
            ->where('status', Status::PENDING)
            ->orderByDesc('id')
            ->get();

        return response()->json([
            'records' => $bills
        ]);
    }

    public function getOpeningKilometer(Request $request): JsonResponse
    {
        $kilometer = DieselBill::where('vehicle_id', $request->vehicle_id)
            ->latest()
            ->value('closing_km');

        return response()->json([
            'kilometer' => $kilometer
        ]);
    }

    public function indexDieselBillAccept(): View
    {
        $bills = DieselBill::select('id','document_date','bunk_name','vehicle_number','route_name','bill_number','amount')
            ->where('status', Status::PENDING)
            ->get();

        return view('transactions.diesel-bills.entries.accept', compact('bills'));
    }

    public function updateDieselBillAccept(Request $request): JsonResponse
    {
        $ids = (array) $request->input('ids', []);

        if (empty($ids)) {
            return response()->json([
                'success' => false,
                'message' => 'No diesel bills selected.',
            ], 422);
        }

        try {
            DB::transaction(function () use ($ids) {
                // Fetch all selected bills in one query
                $bills = DieselBill::whereIn('id', $ids)
                    ->orderBy('id') // optional: maintain order
                    ->get();

                $userId = auth()->id();
                $now = now();

                foreach ($bills as $bill) {
                    // Assign document number only if not already assigned
                    if (!$bill->document_number) {
                        $bill->document_number = $this->getDocumentNumber();
                    }
                    $bill->status = Status::ACCEPTED;
                    $bill->actioned_by = $userId;
                    $bill->actioned_at = $now;
                    $bill->save();
                }
            });

            return response()->json([
                'success' => true,
                'message' => 'Diesel bills have been accepted successfully!',
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to accept diesel bills.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    private function getDocumentNumber(): string
    {
        // Get the max numeric part of existing document numbers
        $lastNumber = DieselBill::whereNotNull('document_number')
            ->selectRaw("MAX(CAST(SUBSTRING(document_number, 5) AS UNSIGNED)) as max_num")
            ->value('max_num');

        $nextNumber = $lastNumber ? $lastNumber + 1 : 1;

        return 'DBE-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }

    private function getStatementNumber(): string
    {
        $lastNumber = DieselBillStatement::latest('id')->value('document_number');
        $nextNumber = $lastNumber ? intval(substr($lastNumber, 4)) + 1 : 1;
        return 'DBS-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }

    public function createBillStatement(): View
    {
        $bunks = PetrolBunk::where('status', 'ACTIVE')
            ->orderBy('name')
            ->get(['id','name']);

        return view('transactions.diesel-bills.statements.create', compact('bunks'));
    }

    public function getDocumentDateForBunk(Request $request): JsonResponse
    {
        $record = DieselBill::where('bunk_id', $request->bunk_id)
            ->where('status', Status::ACCEPTED)
            ->orderBy('document_date', 'asc')
            ->first();

        if(!$record) {
            return response()->json([
                'success' => false,
                'message' => 'No bills found for the petrol bunk!',
            ]);
        }

        return response()->json([
            'success' => true,
            'date'    => $record->getDocumentDateForInput(),
        ]);
    }    

    public function loadBillStatement(Request $request): JsonResponse
    {
        $bunkId   = $request->bunk_id;
        $fromDate = $request->from_date;
        $toDate   = $request->to_date;

        $bills = $this->getAcceptedBillsSummary($bunkId, [$fromDate, $toDate]);

        if(!$bills) {
            return response()->json([
                'success' => false,
                'message' => 'No bills available for the petrol bunk!',
            ]);
        }

        return response()->json([
            'success'    => true,
            'records'    => $bills['records'],
            'summary'    => $bills['summary'],
            'date_title' => formatDateRangeAsDMY($fromDate, $toDate),
        ]);
    }

    public function storeBillStatement(Request $request): JsonResponse
    {
        $bunkId   = $request->bunk_id;
        $bunkName = $request->bunk_name;
        $fromDate = $request->from_date;
        $toDate   = $request->to_date;

        $dates = [$fromDate, $toDate];
        $bills = $this->getAcceptedBillsSummary($bunkId, $dates);
    
        if(!$bills) {
            return response()->json([
                'success' => false,
                'message' => 'No bills available for the petrol bunk!',
            ]);
        }

        $itemIds = $bills['records']->pluck('id')->toArray();
        $summary = $bills['summary'];

        $record = [
            'document_number'   => $this->getStatementNumber(),
            'document_date'     => Carbon::today(),
            'bunk_id'           => $bunkId,
            'bunk_name'         => $bunkName,
            'from_date'         => $fromDate,
            'to_date'           => $toDate,
            'item_ids'          => $itemIds,
            'item_count'        => count($itemIds),
            'total_fuel'        => $summary['total_fuel'],
            'total_running_km'  => $summary['total_km'],
            'average_kmpl'      => $summary['average_kmpl'],
            'average_rate'      => $summary['average_rate'],
            'total_amount'      => $summary['total_amount'],
            'tds_amount'        => $summary['tds_amount'],
            'round_off'         => $summary['round_off'],
            'net_amount'        => $summary['net_amount'],
            'created_by'        => auth()->id(),
        ];

        try {
            DB::transaction(function () use ($record, $bunkId, $dates) {
                DieselBillStatement::create($record);

                DieselBill::where('status', Status::ACCEPTED)
                    ->where('bunk_id', $bunkId)
                    ->whereBetween('document_date', $dates)
                    ->update(['status' => Status::GENERATED]);

                $this->updateBunkTdsStatus($bunkId, $record['net_amount']);
            });

            return response()->json([
                'success' => true,
                'message' => 'Diesel bill statement created successfully!',
            ]);
        }
        catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create diesel bill statement.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    private function getAcceptedBillsSummary($bunkId, $dates): ?array
    {
        $records = DieselBill::select('id','document_date','route_name','vehicle_number','driver_name','fuel','rate','amount','opening_km','closing_km','running_km','kmpl')
            ->where('status', Status::ACCEPTED)
            ->where('bunk_id', $bunkId)
            ->whereBetween('document_date', $dates)
            ->orderBy('document_date', 'asc')
            ->get();

        if ($records->isEmpty()) {
            return null; // Indicate no records
        }

        $totalAmount = round($records->sum('amount'), 2);
        $tdsAmount   = $this->calculateTdsAmount($bunkId, $totalAmount);
        $amount      = $totalAmount - $tdsAmount;
        $roundOff    = round($amount) - $amount;
        $netAmount   = round($amount);

        $totalRunKm  = $records->sum('running_km');
        $totalFuel   = $records->sum('fuel');

        $summary = [
            'total_fuel'   => getTwoDigitPrecision($totalFuel),
            'total_km'     => $totalRunKm,
            'average_kmpl' => getTwoDigitPrecision($totalRunKm / $totalFuel),
            'average_rate' => getTwoDigitPrecision($totalAmount / $totalFuel),
            'total_amount' => getTwoDigitPrecision($totalAmount),
            'tds_amount'   => getTwoDigitPrecision($tdsAmount),
            'round_off'    => getRoundOffWithSign($roundOff),
            'net_amount'   => getTwoDigitPrecision($netAmount),
        ];

        return [
            'records' => $records,
            'summary' => $summary,
        ];
    }

    private function calculateTdsAmount(int $bunkId, float $amount): float
    {
        $bunk = PetrolBunk::findOrFail($bunkId);

        if ($bunk->tds_status === TdsStatus::NOT_APPLICABLE) {
            return 0;
        }

        $tdsMaster = $this->getCurrentTdsMaster();
        $percent = $bunk->pan ? $tdsMaster->with_pan : $tdsMaster->without_pan;

        if ($bunk->tds_status === TdsStatus::APPLIED) {
            return round($amount * $percent / 100, 2);
        }

        // TDS Applicable: Check turnover threshold
        $turnover = $this->calculateDieselBillTurnover($bunkId);
        $excessAmount = ($turnover + $amount) - $tdsMaster->tds_limit;

        return $excessAmount > 0
            ? round($excessAmount * $percent / 100, 2)
            : 0;
    }

    private function updateBunkTdsStatus(int $bunkId, float $amount): void
    {
        $bunk = PetrolBunk::findOrFail($bunkId);
        
        if ($bunk->tds_status !== TdsStatus::APPLICABLE) {
            return;
        }

        $tdsMaster = $this->getCurrentTdsMaster();
        $turnover = $this->calculateDieselBillTurnover($bunkId);
        $excessAmount = ($turnover + $amount) - $tdsMaster->tds_limit;

        if ($excessAmount > 0) {
            $bunk->update(['tds_status' => TdsStatus::APPLIED]);
        }
    }

    private function getCurrentTdsMaster(): ?TdsMaster
    {
        return TdsMaster::whereDate('effect_date', '<=', Carbon::today())
            ->orderByDesc('effect_date')
            ->first(['id', 'effect_date', 'tds_limit', 'with_pan', 'without_pan']);
    }

    private function calculateDieselBillTurnover(int $bunkId): float
    {
        [$fyStart, $fyEnd] = getCurrentFinancialYear(Carbon::today());

        $turnover = null;

        if ($fyStart === '2025-04-01') {
            $turnover = PetrolBunkTurnover::where('bunk_id', $bunkId)
                ->where('status', Status::ACTIVE)
                ->first();
        }

        if ($turnover) {
            $dateStart = $turnover->reference_date->copy()->addDay();

            $amount = DieselBill::where('bunk_id', $bunkId)
                ->whereBetween('document_date', [$dateStart, $fyEnd])
                ->sum('amount');

            return $turnover->amount + $amount;
        }

        return DieselBill::where('bunk_id', $bunkId)
            ->whereBetween('document_date', [$fyStart, $fyEnd])
            ->sum('amount');
    }

    public function indexBillStatement(Request $request): View
    {
        $validated = $request->validate([
            'from_date' => 'bail|nullable|date',
            'to_date'   => 'bail|nullable|date|after_or_equal:from_date',
            'bunk_id'   => 'bail|nullable|exists:petrol_bunks,id',
            'bunk_name' => 'bail|nullable|string|max:100',
        ]);

        $bunkId   = $validated['bunk_id'] ?? 0;
        $bunkName = $validated['bunk_name'] ?? '';
        $dates = $this->getDocumentDateRange($validated['from_date'] ?? null, $validated['to_date'] ?? null);

        $records = DieselBillStatement::select('id','document_number','document_date','bunk_name','from_date','to_date','net_amount','status')
            ->whereBetween('document_date', $dates)
            ->when($bunkId != 0, fn($q) => $q->where('bunk_id', $bunkId))
            ->get();

        $bunks = PetrolBunk::where('status', 'ACTIVE')
            ->orderBy('name')
            ->get(['id','name']);

        return view('transactions.diesel-bills.statements.index', [
            'dates'   => ['from' => $dates[0], 'to' => $dates[1]],
            'bunk'    => ['id' => $bunkId, 'name' => $bunkName],
            'records' => $records,
            'bunks'   => $bunks,
        ]);
    }

    public function showBillStatement(Request $request): View|JsonResponse
    {
        $validated = $request->validate([
            'id'      => 'required|exists:diesel_bill_statements,id',
            'id_list' => 'required|string',
        ]);
        
        $record = DieselBillStatement::findOrFail($validated['id']);

        // return response()->json([
        return view('transactions.diesel-bills.statements.show', [
            'record'  => $record,
            'id_list' => $validated['id_list'],            
        ]);
    }

    public function fetchBillStatement(DieselBillStatement $stmt): JsonResponse
    {
        return response()->json([
            'record' => $stmt,
            'bills'  => $stmt->dieselBills,
        ]);
    }

    public function indexStatementAccept(): View
    {
        $records = DieselBillStatement::select('id','document_number','document_date','bunk_name','from_date','to_date','net_amount')
            ->where('status', Status::PENDING)
            ->get();

        return view('transactions.diesel-bills.statements.accept', compact('records'));
    }

    public function updateStatementAccept(Request $request): JsonResponse
    {
        $ids = (array) $request->input('ids', []);

        if (empty($ids)) {
            return response()->json([
                'success' => false,
                'message' => 'No diesel bill statement selected.',
            ], 422);
        }

        try {
            DieselBillStatement::whereIn('id', $ids)
                ->update([
                    'status'      => Status::ACCEPTED,
                    'actioned_by' => auth()->id(),
                    'actioned_at' => now(),
                ]);

            return response()->json([
                'success' => true,
                'message' => 'Diesel bill statement(s) accepted successfully!',
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to accept diesel bill statements.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function cancelStatementAccept(Request $request): JsonResponse
    {
        try {
            DB::transaction(function () use ($request) {
                $record = DieselBillStatement::findOrFail($request->id);

                $record->update([
                    'status'      => Status::CANCELLED,
                    'actioned_by' => auth()->id(),
                    'actioned_at' => now(),
                ]);

                DieselBill::whereIn('id', $record->item_ids)
                    ->update(['status' => Status::ACCEPTED]);
            });

            return response()->json([
                'success' => true,
                'message' => 'Diesel bill statement cancelled successfully!',
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to cancel diesel bill statements.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function createPaymentRequest(Request $request): View|JsonResponse
    {
        $validated = $request->validate([
            'from_date' => 'bail|nullable|date',
            'to_date'   => 'bail|nullable|date|after_or_equal:from_date',
        ]);

        $dates = $this->getDocumentDateRange($validated['from_date'] ?? null, $validated['to_date'] ?? null);

        $records = DieselBillStatement::select('id','document_date','document_number','from_date','to_date','bunk_name','net_amount')
            ->whereBetween('document_date', $dates)
            ->where('payment_status', PaymentStatus::OUTSTANDING)
            ->where('status', Status::ACCEPTED)
            ->get();
        
        foreach($records as $record) {
            $paidAmount = DieselBillPayment::where('statement_id', $record->id)
                ->whereNot('status', Status::CANCELLED)
                ->sum('amount');
            
            $record->payable = $record->net_amount - $paidAmount;
        }

        // Now filter records with payable > 0
        $records = $records->filter(function ($record) {
            return $record->payable > 0;
        })->values(); // Reset keys

        // return response()->json([
        return view('transactions.diesel-bills.payments.create', [
            'dates'   => ['from' => $dates[0], 'to' => $dates[1]],
            'records' => $records,
        ]);
    }

    public function storePaymentRequest(Request $request)
    {
        $data = $request->payment_data;

        try {
            // Prepare records using the fetched data
            $records = [];
            $userId = auth()->id();
            foreach ($data as $record) {
                $records[] = [
                    'statement_id' => $record['id'],
                    'request_date' => Carbon::today(),
                    'amount'       => $record['amount'],
                    'created_by'   => $userId,
                ];
            }

            // Insert all records in a single query
            DieselBillPayment::insert($records);

            return response()->json([
                'success' => true,
                'message' => 'Records have been sent for approval!',
            ]);
        } catch (\Throwable $ex) {
            return response()->json([
                'success' => false,
                'message' => $ex->getMessage(),
            ]);
        }
    }

    public function createPaymentApproval()
    {
        $baseQuery = DieselBillPayment::select('id', 'statement_id', 'request_date', 'amount')
            ->with('statement:id,document_number,bunk_name,from_date,to_date');

        $records = (clone $baseQuery)
            ->where('status', Status::PENDING)
            ->get();

        $paused = (clone $baseQuery)
            ->where('status', Status::PAUSED)
            ->get();

        // Sort by bunk name
        $records = $records->sortBy('bunk_name')->values();

        $banks = BankMaster::get(['id','display_name']);

        // return response()->json([
        return view('transactions.diesel-bills.payments.approve', [
            'records' => $records,
            'paused'  => $paused,
            'banks'   => $banks,  
        ]);
    }

    public function approvePaymentApproval(Request $request)
    {
        $records = collect($request->records);

        // Extract payment IDs and corresponding amounts
        $paymentIds = $records->pluck('payment_id')->map(fn($id) => (int) $id);
        $amountMap  = $records->pluck('amount', 'payment_id');

        try {
            $payId = DB::transaction(function () use ($paymentIds, $amountMap, $request) {
                $userId = auth()->id();
                $now    = now();

                // Fetch all payments at once
                $payments = DieselBillPayment::whereIn('id', $paymentIds)->get();

                // Update each payment
                foreach ($payments as $payment) {
                    $payment->update([
                        'amount'       => $amountMap[$payment->id] ?? $payment->amount,
                        'status'       => Status::APPROVED,
                        'actioned_by'  => $userId,
                        'actioned_at'  => $now,
                    ]);
                }

                // Get all unique statement IDs from the payments
                $statementIds = $payments->pluck('statement_id')->unique();

                // Calculate approved payment totals grouped by statement
                $approvedSums = DieselBillPayment::whereIn('statement_id', $statementIds)
                    ->where('status', Status::APPROVED)
                    ->groupBy('statement_id')
                    ->selectRaw('statement_id, SUM(amount) as total_paid')
                    ->pluck('total_paid', 'statement_id');

                // Update each related DieselBillStatement
                $statements = DieselBillStatement::whereIn('id', $statementIds)->get();

                foreach ($statements as $statement) {
                    $paid = $approvedSums[$statement->id] ?? 0;
                    $status = ((float) $statement->net_amount === (float) $paid)
                        ? PaymentStatus::PAID
                        : PaymentStatus::OUTSTANDING;

                    $statement->update(['payment_status' => $status]);
                }

                // Create the related Bank Payment
                $payNumber = $this->getBankPaymentNumber();

                $bankPayment = BankPayment::create([
                    'document_number'   => $payNumber,
                    'payment_date'      => today()->toDateString(),
                    'payment_type'      => PaymentType::DIESEL_BILL,
                    'bank_id'           => $request->bank_id,
                    'bank_name'         => $request->bank_name,
                    'total_amount'      => $request->total_amount,
                    'reference_numbers' => $paymentIds->all(),
                ]);

                // Update reference_number for all related payments
                DieselBillPayment::whereIn('id', $paymentIds)
                    ->update(['reference_number' => $payNumber]);

                return $bankPayment->id;
            });

            return response()->json([
                'success' => true,
                'message' => 'Diesel bill statements approved successfully!',
                'pay_id'  => $payId,
            ]);
        }
        catch (\Throwable $ex) {
            return response()->json([
                'success' => false,
                'message' => $ex->getMessage(),
            ]);
        }
    }  

    public function updatePaymentApprovalStatus(Request $request)
    {
        try {
            $actionStatusMap = [
                'PAUSE'  => Status::PAUSED,
                'RESUME' => Status::PENDING,
                'CANCEL' => Status::CANCELLED,
            ];

            $status = $actionStatusMap[$request->action];

            DieselBillPayment::findOrFail($request->id)
                ->update([
                    'status'      => $status,
                    'actioned_by' => auth()->id(),
                    'actioned_at' => now(),
                ]);

            return response()->json([
                'success' => true,
                'message' => "Payment record has been updated successfully!",
            ]);
        }
        catch (\Throwable $ex) {
            return response()->json([
                'success' => false,
                'message' => $ex->getMessage(),
            ]);
        }
    }

    private function getDocumentDateRange(?string $fromDate, ?string $toDate): array
    {
        $hasInputDates = !empty($fromDate) && !empty($toDate);

        // Use input dates if available
        if ($hasInputDates) {
            $dates = [$fromDate, $toDate];
        }
        else { // No dates supplied, use latest statement's date or fallback to today
            $latestStatement = DieselBillStatement::latest()->first();
            $latestDate = $latestStatement
                ? $latestStatement->getDocumentDateForInput()
                : Carbon::today()->toDateString();
            $dates = [$latestDate, $latestDate];
        }

        return $dates;
    }

    private function getBankPaymentNumber()
    {
        $last = BankPayment::latest('id')->value('document_number');
        $number = $last ? intval(substr($last, 4)) + 1 : 1;
        return 'BPY-' . str_pad($number, 3, '0', STR_PAD_LEFT);
    }
}
