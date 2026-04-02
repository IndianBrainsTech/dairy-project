<?php

namespace App\Http\Controllers\Masters\Purchase\Items;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use Illuminate\Contracts\View\View;
use App\Http\Controllers\Controller;
use App\Models\Masters\Purchase\Items\PurchaseItemUnit;
use App\Http\Traits\HandlesJsonExceptions;
use App\Enums\MasterStatus;

class PurchaseItemUnitController extends Controller
{
    use HandlesJsonExceptions;

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(): View
    {
        $status = 'Active';
        $masters = PurchaseItemUnit::select('id','name','abbreviation','hot_key')->where('status', MasterStatus::ACTIVE)->get();
        $availableKeys = $this->getAvailableHotKeys($masters);
        return view('masters.purchase.items.units', compact('masters', 'status', 'availableKeys'));
    }

    public function list(Request $request): View
    {
        $status = $request->get('status', 'Active');

        $masters = $status === 'Active'
            ? PurchaseItemUnit::select('id','name','abbreviation','hot_key')->where('status', MasterStatus::ACTIVE)->get() 
            : PurchaseItemUnit::select('id','name','abbreviation','hot_key','status')->orderBy('status')->get();

        $availableKeys = $this->getAvailableHotKeys($masters);
        return view('masters.purchase.items.units', compact('masters', 'status', 'availableKeys'));
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:100|unique:purchase_item_units,name',
                'abbr' => 'required|string|max:10|unique:purchase_item_units,abbreviation',
                'hot_key' => 'required|string|size:1|alpha|unique:purchase_item_units,hot_key',
            ]);

            PurchaseItemUnit::create([
                'name'         => $validated['name'],
                'abbreviation' => $validated['abbr'],
                'hot_key'      => $validated['hot_key'],
                'created_by'   => auth()->id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Item unit has been created successfully!'
            ]);
        }
        catch (\Throwable $e) {
            return $this->jsonExceptionResponse($e, 'Failed to create item unit.');
        }
    }

    public function update(Request $request, PurchaseItemUnit $master): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:100|unique:purchase_item_units,name,'.$master->id,
                'abbr' => 'required|string|max:10|unique:purchase_item_units,abbreviation,'.$master->id,
                'hot_key' => 'required|string|size:1|alpha|unique:purchase_item_units,hot_key,'.$master->id,
            ]);

            $master->update([
                'name'         => $validated['name'],
                'abbreviation' => $validated['abbr'],
                'hot_key'      => $validated['hot_key'],
                'updated_by'   => auth()->id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Item unit has been updated successfully!'
            ]);
        }
        catch (\Throwable $e) {
            return $this->jsonExceptionResponse($e, 'Failed to update item unit.');
        }
    }

    public function destroy(PurchaseItemUnit $master): JsonResponse
    {
        try {
            $master->delete();

            return response()->json([
                'success' => true,
                'message' => 'Item unit has been deleted successfully!'
            ]);
        }
        catch (\Throwable $e) {
            $message = 'Failed to delete item unit.';
            if ($e->getCode() == '23000' && str_contains($e->getMessage(), 'foreign key constraint fails')) {
                $message = "This item unit cannot be deleted because it is linked to other records (e.g., Purchase items).";
            }

            return response()->json([
                'success' => false,
                'message' => $message,
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function toggle(Request $request, PurchaseItemUnit $master): JsonResponse
    {
        try {
            if($master->status === 'ACTIVE') {
                $status = 'INACTIVE';
                $action = 'disable';
            }
            else {
                $status = 'ACTIVE';
                $action = 'enable';
            }

            $master->status = $status;
            $master->save();

            return response()->json([
                'success' => true,
                'message' => "Item unit has been {$action}d successfully!",
            ]);
        }
        catch (\Throwable $e) {
            return $this->jsonExceptionResponse($e, "Failed to {$action} item unit.");
        }
    }

    private function getAvailableHotKeys(Collection $masters): Collection
    {
        $allHotkeys = collect(range('A', 'Z'));
        $usedHotkeys = $masters->pluck('hot_key');
        $availableHotkeys = $allHotkeys->diff($usedHotkeys)->values();
        return $availableHotkeys;
    }
}
