<?php

namespace App\Http\Controllers\Masters\Purchase\Items;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Contracts\View\View;
use App\Http\Controllers\Controller;
use App\Models\Masters\Purchase\Items\PurchaseItemGroup;
use App\Http\Traits\HandlesJsonExceptions;
use App\Enums\MasterStatus;

class PurchaseItemGroupController extends Controller
{
    use HandlesJsonExceptions;

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(): View
    {
        $status = 'Active';
        $masters = PurchaseItemGroup::select('id','code','name')->where('status', MasterStatus::ACTIVE)->get();
        return view('masters.purchase.items.groups', compact('masters', 'status'));
    }

    public function list(Request $request): View
    {
        $status = $request->get('status', 'Active');

        $masters = $status === 'Active'
            ? PurchaseItemGroup::select('id','code','name')->where('status', MasterStatus::ACTIVE)->get() 
            : PurchaseItemGroup::select('id','code','name','status')->orderBy('status')->get();

        return view('masters.purchase.items.groups', compact('masters', 'status'));
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:100|unique:purchase_item_groups,name',
            ]);

            PurchaseItemGroup::create([
                'code' => $this->generateGroupCode(),
                'name' => $validated['name'],
                'created_by' => auth()->id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Item group has been created successfully!'
            ]);
        }
        catch (\Throwable $e) {
            return $this->jsonExceptionResponse($e, 'Failed to create item group.');
        }
    }

    public function update(Request $request, PurchaseItemGroup $master): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:100|unique:purchase_item_groups,name,'.$master->id
            ]);

            $master->update([
                'name' => $validated['name'],
                'updated_by' => auth()->id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Item group has been updated successfully!'
            ]);
        }
        catch (\Throwable $e) {
            return $this->jsonExceptionResponse($e, 'Failed to update item group.');
        }
    }

    public function destroy(PurchaseItemGroup $master): JsonResponse
    {
        try {
            $master->delete();

            return response()->json([
                'success' => true,
                'message' => 'Item group has been deleted successfully!'
            ]);
        } 
        catch (\Throwable $e) {
            $message = 'Failed to delete item group.';
            if ($e->getCode() == '23000' && str_contains($e->getMessage(), 'foreign key constraint fails')) {
                $message = "This item group cannot be deleted because it is linked to other records (e.g., Purchase items).";
            }

            return response()->json([
                'success' => false,
                'message' => $message,
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function toggle(Request $request, PurchaseItemGroup $master): JsonResponse
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
                'message' => "Item group has been {$action}d successfully!",
            ]);
        }
        catch (\Throwable $e) {
            return $this->jsonExceptionResponse($e, "Failed to {$action} item group.");
        }
    }

    private function generateGroupCode(): string
    {
        $last = PurchaseItemGroup::latest('id')->value('code');
        $number = $last ? intval(substr($last, 7)) + 1 : 1;
        return 'PR-ITG-' . str_pad($number, 3, '0', STR_PAD_LEFT);
    }
}