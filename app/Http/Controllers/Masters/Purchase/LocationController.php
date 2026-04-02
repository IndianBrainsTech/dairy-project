<?php

namespace App\Http\Controllers\Masters\Purchase;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Contracts\View\View;
use App\Http\Controllers\Controller;
use App\Models\Masters\Purchase\Location;
use App\Http\Traits\HandlesJsonExceptions;
use App\Enums\MasterStatus;

class LocationController extends Controller
{
    use HandlesJsonExceptions;

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request): View
    {
        $status = $request->get('status', 'Active');

        $masters = $status === 'Active'
            ? Location::select('id','code','name')->where('status', MasterStatus::ACTIVE)->get() 
            : Location::select('id','code','name','status')->orderBy('status')->get();

        return view('masters.purchase.locations', compact('masters', 'status'));
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:100|unique:locations,name',
            ]);

            Location::create([
                'code' => $this->generateCode(),
                'name' => $validated['name'],
                'created_by' => auth()->id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Location has been created successfully!'
            ]);
        }
        catch (\Throwable $e) {
            return $this->jsonExceptionResponse($e, 'Failed to create location.');
        }
    }

    public function update(Request $request, Location $location): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:100|unique:locations,name,'.$location->id
            ]);

            $location->update([
                'name' => $validated['name'],
                'updated_by' => auth()->id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Location has been updated successfully!'
            ]);
        }
        catch (\Throwable $e) {
            return $this->jsonExceptionResponse($e, 'Failed to update location.');
        }
    }

    public function destroy(Location $location): JsonResponse
    {
        try {
            $location->delete();

            return response()->json([
                'success' => true,
                'message' => 'Location has been deleted successfully!'
            ]);
        } 
        catch (\Throwable $e) {
            $message = 'Failed to delete location.';
            if ($e->getCode() == '23000' && str_contains($e->getMessage(), 'foreign key constraint fails')) {
                $message = "This location cannot be deleted because it is linked to other records (e.g., Purchase order).";
            }

            return response()->json([
                'success' => false,
                'message' => $message,
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function toggle(Request $request, Location $location): JsonResponse
    {
        try {
            if($location->status === 'ACTIVE') {
                $status = 'INACTIVE';
                $action = 'disable';
            }
            else {
                $status = 'ACTIVE';
                $action = 'enable';
            }

            $location->status = $status;
            $location->save();

            return response()->json([
                'success' => true,
                'message' => "Location has been {$action}d successfully!",
            ]);
        }
        catch (\Throwable $e) {
            return $this->jsonExceptionResponse($e, "Failed to {$action} location.");
        }
    }

    private function generateCode(): string
    {
        $lastNumber = Location::latest('id')->value('code');
        $nextNumber = $lastNumber ? intval(substr($lastNumber, 4)) + 1 : 1;
        return 'LOC-' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
    }
}
