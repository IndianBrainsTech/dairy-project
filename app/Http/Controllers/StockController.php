<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use App\Models\Stocks\Stock;
use App\Models\Stocks\StockItem;
use App\Models\Stocks\StockHistory;
use App\Models\Stocks\StockRegister;
use App\Models\Stocks\CurrentStock;
use App\Models\Products\Product;
use App\Models\Products\ProductUnit;
use App\Models\Products\UOM;
use App\Services\StockService;
use App\Enums\StockAction;
use App\Enums\Status;
use Carbon\Carbon;

class StockController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function indexStock(Request $request): View
    {
        $validated = $request->validate([
            'from_date' => 'nullable|date',
            'to_date'   => 'nullable|date|after_or_equal:from_date',
        ]);

        $fromDate = $request->input('from_date', Carbon::today()->toDateString());
        $toDate   = $request->input('to_date', $fromDate);

        $stocks = Stock::select('id', 'document_date', 'document_number', 'status')
            ->whereBetween('document_date', [$fromDate, $toDate])
            ->get();

        $dates = ['from' => $fromDate, 'to' => $toDate];
        
        return view('transactions.stocks.index', compact('dates', 'stocks'));
    }

    public function createStock(): View
    {
        $items = Product::select('id','name')
            ->where('status','Active')
            ->orderBy('display_index')
            ->get();

        $units = UOM::select('id','display_name','hot_key')->get();

        $itemUnits = ProductUnit::select('product_id','unit_id','prim_unit','conversion')
            ->orderByDesc('prim_unit')
            ->get();

        // No stock object for create
        $stock = null;
        $documentNumber = $this->getDocumentNumber();

        return view('transactions.stocks.manage', compact('items', 'units', 'itemUnits', 'stock', 'documentNumber'));
    }

    public function storeStock(Request $request): JsonResponse
    {
        $request->validate([
            'items'           => 'required|array|min:1',
            'items.*.item_id' => 'required|integer|exists:products,id',
            'items.*.unit_id' => 'required|integer|exists:units,id',
            'items.*.qty'     => 'required|numeric|min:0.01',
            'items.*.batch'   => 'nullable|string|max:100',
        ]);

        try {
            DB::transaction(function () use ($request) {
                // Create stock entry
                $stock = Stock::create([
                    'document_number' => $this->getDocumentNumber(),
                    'document_date'   => today(),
                    'created_by'      => auth()->id(),
                ]);

                // Transform stock items for bulk insert
                $now = now();
                $itemsData = collect($request->items)->map(function ($item) use ($stock, $now) {
                    return [
                        'stock_id'     => $stock->id,
                        'item_id'      => $item['item_id'],
                        'item_name'    => $item['item_name'],
                        'batch_number' => $item['batch'],
                        'quantity'     => $item['qty'],
                        'unit_id'      => $item['unit_id'],
                        'created_at'   => $now,
                        'updated_at'   => $now,
                    ];
                })->toArray();

                // Bulk insert stock items
                StockItem::insert($itemsData);
            });

            return response()->json([
                'success' => true,
                'message' => 'Stock items saved successfully.',
            ]);
        }
        catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to save stock.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    public function showStock(Request $request): View
    {
        $validated = $request->validate([
            'number'        => 'required|string|exists:stocks,document_number',
            'number_list'   => 'required|string',
        ]);

        $stock = Stock::with(['items.unit'])
            ->where('document_number', $validated['number'])
            ->firstOrFail();

        $stockHistory = StockHistory::with(['unit:id,display_name', 'user:id,name'])
            ->where('stock_id', $stock->id)
            ->orderBy('version_code')
            ->orderBy('id')
            ->get();

        $history = [];
        $currentState = [];
        $previousState = [];

        foreach ($stockHistory->groupBy('version_code') as $version => $items) {
            $firstItem = $items->first();

            // Snapshot before applying this version
            $previousState = $currentState;

            $actionTitle = $version === 1 ? 'Created' : 'Edited';
            $username    = $firstItem->user->name ?? 'Unknown';
            $updatedAt   = displayDateTimeIST($firstItem->updated_at);

            // Initialize change groups
            $changes = [
                'INSERT' => [],
                'UPDATE' => [],
                'DELETE' => [],
            ];

            foreach ($items as $item) {
                if ($version !== 1) {
                    if ($item->action_type === 'UPDATE') {
                        $changes['UPDATE'][] = [
                            'before' => $previousState[$item->record_id] ?? ['record_id' => null, 'item_name' => '', 'batch' => '', 'qty' => '', 'unit' => ''],
                            'after'  => [
                                'record_id'  => $item->record_id,
                                'item_name'  => $item->item_name,
                                'batch'      => $item->batch_number,
                                'qty'        => (float) $item->quantity,
                                'unit'       => $item->unit->display_name,
                            ],
                        ];
                    } 
                    else {
                        $changes[$item->action_type][] = [
                            'record_id'  => $item->record_id,
                            'item_name'  => $item->item_name,
                            'batch'      => $item->batch_number,
                            'qty'        => (float) $item->quantity,
                            'unit'       => $item->unit->display_name,
                        ];
                    }
                }

                // Update current state
                switch ($item->action_type) {
                    case 'CREATE':
                    case 'INSERT':
                    case 'UPDATE':
                        $currentState[$item->record_id] = [
                            'record_id'  => $item->record_id,
                            'item_name'  => $item->item_name,
                            'batch'      => $item->batch_number,
                            'qty'        => (float) $item->quantity,
                            'unit'       => $item->unit->display_name,
                        ];
                        break;
                    case 'DELETE':
                        unset($currentState[$item->record_id]);
                        break;
                }
            }

            // Remove empty change groups
            $changes = array_filter($changes, fn($group) => !empty($group));

            // Sort records by record_id
            $sortedRecords = collect($currentState)
                ->sortBy('record_id')
                ->values()
                ->toArray();

            $history[] = [
                'version' => $version,
                'title'   => "{$actionTitle} by {$username} at {$updatedAt}",
                'changes' => $version === 1 ? '' : $changes,
                'records' => $sortedRecords,
            ];
        }

        return view('transactions.stocks.show', [
            'stock'         => $stock,
            'stock_history' => $history,
            'number_list'   => $validated['number_list'],
        ]);
    }

    public function editStock(Stock $stock): View
    {
        $items = Product::select('id', 'name')
            ->where('status', 'Active')
            ->orderBy('display_index')
            ->get();

        $units = UOM::select('id', 'display_name', 'hot_key')->get();

        $itemUnits = ProductUnit::select('product_id', 'unit_id', 'prim_unit', 'conversion')
            ->orderByDesc('prim_unit')
            ->get();

        // Load stock with items & related units
        $stock = Stock::with(['items.unit'])->findOrFail($stock->id);
        $documentNumber = $stock->document_number;

        return view('transactions.stocks.manage', compact('items', 'units', 'itemUnits', 'stock', 'documentNumber'));
    }

    public function updateStock(Request $request, Stock $stock): JsonResponse
    {
        $request->validate([
            'items'             => 'required|array|min:1',
            'items.*.record_id' => 'required|integer|min:-1',
            'items.*.item_id'   => 'required|integer|exists:products,id',
            'items.*.item_name' => 'required|string|exists:products,name',
            'items.*.unit_id'   => 'required|integer|exists:units,id',
            'items.*.qty'       => 'required|numeric|min:0.01',
            'items.*.batch'     => 'nullable|string|max:100',
        ]);

        try {
            DB::transaction(function () use ($request, $stock) {
                $stockId = $stock->id;

                // Get last version number
                $lastVersion = DB::table('stock_history')
                    ->where('stock_id', $stockId)
                    ->max('version_code') ?? 0;
                $newVersion = $lastVersion + 1;

                // Fetch current items from DB
                $currentItems = DB::table('stock_items')
                    ->where('stock_id', $stockId)
                    ->get()
                    ->keyBy('id');

                $changes = [];
                $now = now();

                if($newVersion === 1) {
                    foreach($currentItems as $item) {
                        $changes[] = [
                            'stock_id'     => $stockId,
                            'item_id'      => $item->item_id,
                            'item_name'    => $item->item_name,
                            'batch_number' => $item->batch_number,
                            'quantity'     => $item->quantity,
                            'unit_id'      => $item->unit_id,
                            'version_code' => 1,
                            'user_id'      => $stock->created_by,
                            'record_id'    => $item->id, 
                            'action_type'  => 'CREATE',
                            'created_at'   => $item->created_at,
                            'updated_at'   => $now,
                        ];
                    }
                    $newVersion = 2;
                }

                foreach ($request->items as $item) {
                    $recordId = (int) $item['record_id'];

                    $record = [
                        'stock_id'     => $stockId,
                        'item_id'      => $item['item_id'],
                        'item_name'    => $item['item_name'],
                        'batch_number' => $item['batch'],
                        'quantity'     => $item['qty'],
                        'unit_id'      => $item['unit_id'],
                        'created_at'   => $now,
                        'updated_at'   => $now,
                    ];

                    if ($recordId === -1) {
                        // NEW record → INSERT
                        $newId = DB::table('stock_items')->insertGetId($record);

                        $changes[] = array_merge($record, [
                            'version_code' => $newVersion,
                            'user_id'      => auth()->id(),
                            'record_id'    => $newId,
                            'action_type'  => 'INSERT',                            
                        ]);
                    }
                    elseif (isset($currentItems[$recordId])) {
                        // EXISTING record → UPDATE check
                        $current = (array) $currentItems[$recordId];

                        if (
                            $current['item_id']      != $item['item_id'] ||
                            $current['item_name']    !== $item['item_name'] ||
                            $current['batch_number'] !== $item['batch'] ||
                            $current['quantity']     != $item['qty'] ||
                            $current['unit_id']      != $item['unit_id']
                        )
                        {
                            DB::table('stock_items')
                                ->where('id', $recordId)
                                ->update([
                                    'item_id'      => $item['item_id'],
                                    'item_name'    => $item['item_name'],
                                    'batch_number' => $item['batch'],
                                    'quantity'     => $item['qty'],
                                    'unit_id'      => $item['unit_id'],
                                    'updated_at'   => $now,
                                ]);

                            $changes[] = array_merge($record, [
                                'version_code' => $newVersion,
                                'user_id'      => auth()->id(),
                                'record_id'    => $current['id'],
                                'action_type'  => 'UPDATE',
                                'created_at'   => $current['created_at'],
                            ]);
                        }

                        unset($currentItems[$recordId]); // mark processed
                    }
                }

                // DELETE detection → remaining currentItems
                foreach ($currentItems as $deleted) {
                    $changes[] = [
                        'stock_id'     => $stockId,
                        'item_id'      => $deleted->item_id,
                        'item_name'    => $deleted->item_name,
                        'batch_number' => $deleted->batch_number,
                        'quantity'     => $deleted->quantity,
                        'unit_id'      => $deleted->unit_id,
                        'version_code' => $newVersion,
                        'user_id'      => auth()->id(),
                        'record_id'    => $deleted->id,
                        'action_type'  => 'DELETE',
                        'created_at'   => $deleted->created_at,
                        'updated_at'   => $now,
                    ];

                    DB::table('stock_items')
                        ->where('id', $deleted->id)
                        ->delete();
                }

                // Insert all changes into history
                if (!empty($changes)) {
                    DB::table('stock_history')->insert($changes);
                }

                // Force update stock header — use query builder to always write to DB
                DB::table('stocks')->where('id', $stockId)->update([
                    'updated_by' => auth()->id(),
                    'updated_at' => $now,
                ]);
            });

            return response()->json([
                'success' => true,
                'message' => 'Stock items updated successfully.',
            ]);
        } 
        catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update stock.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function cancelStock(Request $request, Stock $stock): JsonResponse
    {
        $request->validate([
            'remarks' => 'required|string|max:255',
        ]);

        try {
            $stock->update([
                'status'      => Status::CANCELLED,
                'remarks'     => $request->remarks,
                'actioned_by' => auth()->id(),
                'actioned_at' => now(),  
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Stock cancellation done successfully.',
            ]);
        } 
        catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to cancel stock.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function fetchStock(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'number' => 'required|string|exists:stocks,document_number',            
        ]);

        try {
            $stock = Stock::with(['items.unit','createdBy:id,name','updatedBy:id,name'])
                ->where('document_number', $validated['number'])
                ->firstOrFail();

            $stock->document_date_formatted = displayDate($stock->document_date);
            $stock->creation_by = $stock->createdBy->name;
            $stock->creation_at = displayDateTimeIST($stock->created_at);
            $stock->updation_by = optional($stock->updatedBy)->name;
            $stock->updation_at = $stock->updated_by ? displayDateTimeIST($stock->updated_at) : null;

            return response()->json([
                'success' => true,
                'stock'   => $stock,
            ]);
        } 
        catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch stock.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function indexStockApproval(): View
    {
        $stocks = Stock::select('id', 'document_date', 'document_number')
            ->where('status', Status::PENDING)
            ->get();

        return view('transactions.stocks.approve', compact('stocks'));
    }

    public function updateStockApproval(Request $request, StockService $stockService): JsonResponse
    {
        $validated = $request->validate([
            'number' => 'required|string|exists:stocks,document_number',
            'action' => 'required|string|in:Approve,Reject',
        ]);

        try {
            DB::transaction(function () use ($validated, $stockService) {
                $stock = Stock::where('document_number', $validated['number'])->firstOrFail();

                // Prevent duplicate approval updates
                if ($stock->status === Status::APPROVED) {
                    throw new \RuntimeException("Stock {$validated['number']} has already been approved.");
                }

                $statusMap = [
                    'Approve' => ['status' => Status::APPROVED, 'actionText' => 'approved'],
                    'Reject'  => ['status' => Status::REJECTED, 'actionText' => 'rejected'],
                ];

                $statusInfo = $statusMap[$validated['action']];

                // Update stock status
                $stock->update([
                    'status'      => $statusInfo['status'],
                    'actioned_by' => auth()->id(),
                    'actioned_at' => now(),
                ]);

                // If approved, update production stock
                if ($validated['action'] === 'Approve') {
                    $items = StockItem::where('stock_id', $stock->id)
                        ->get(['item_id', 'unit_id', 'quantity as qty'])
                        ->toArray();

                    $stockService->updateCurrentStock(StockAction::PRODUCTION, $items);
                }
            });

            return response()->json([
                'success' => true,
                'message' => "Stock {$validated['action']} successfully.",
            ]);
        } 
        catch (\RuntimeException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        } 
        catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => "Failed to " . strtolower($validated['action']) . " stock.",
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function currentStock(): View
    {
        $stocks = $this->getCurrentStocks();
        return view('explorer.stocks.current', compact('stocks'));
    }

    public function currentStockJson(): JsonResponse
    {
        $stocks = $this->getCurrentStocks();
        return response()->json($stocks);
    }

    public function stockRegister(Request $request): View
    {
        $stocks = $this->getStockRegister($request);
        return view('explorer.stocks.register', compact('stocks'));
    }

    public function stockRegisterJson(Request $request): JsonResponse
    {
        $stocks = $this->getStockRegister($request);
        return response()->json($stocks);
    }

    private function getDocumentNumber(): string
    {
        $last = Stock::latest('id')->value('document_number');
        $number = $last ? intval(substr($last, 3)) + 1 : 1;
        return 'ST-' . str_pad($number, 3, '0', STR_PAD_LEFT);
    }

    private function getCurrentStocks(): Collection
    {
        return CurrentStock::select('id', 'item_id', 'item_name', 'unit_id', 'current_stock')
            ->with(['unit:id,display_name', 'item:id,display_index'])
            ->where('current_stock','!=',0)
            ->get()
            ->sortBy(fn($stock) => $stock->item->display_index)
            ->values();
    }

    private function getStockRegister(Request $request): Collection
    {
        $today = Carbon::today()->toDateString();
        $date = $request->input('date', $today);
        if($date === $today) {
            $stocks = CurrentStock::select('id','item_id','item_name','unit_id','opening_qty','production_qty','sales_qty','return_qty','current_stock as closing_qty')
                ->with(['unit:id,display_name', 'item:id,display_index'])
                ->get();
        }
        else {
            $stocks = StockRegister::select('id','item_id','item_name','unit_id','opening_qty','production_qty','sales_qty','return_qty','closing_qty')
                ->with(['unit:id,display_name', 'item:id,display_index'])
                ->where('record_date', $date)
                ->get();
        }

        $stocks = $stocks->sortBy(fn($stock) => $stock->item->display_index)->values();

        return $stocks;
    }
}
