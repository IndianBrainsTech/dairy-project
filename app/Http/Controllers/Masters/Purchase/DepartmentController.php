<?php

namespace App\Http\Controllers\Masters\Purchase;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Contracts\View\View;
use App\Http\Controllers\Controller;
use App\Models\Masters\Purchase\Department;
use App\Http\Traits\HandlesJsonExceptions;
use App\Enums\MasterStatus;

class DepartmentController extends Controller
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
            ? Department::select('id','code','name')->where('status', MasterStatus::ACTIVE)->get() 
            : Department::select('id','code','name','status')->orderBy('status')->get();

        return view('masters.purchase.departments', compact('masters', 'status'));
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:100|unique:departments,name',
            ]);

            Department::create([
                'code' => $this->generateCode(),
                'name' => $validated['name'],
                'created_by' => auth()->id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Department has been created successfully!'
            ]);
        }
        catch (\Throwable $e) {
            return $this->jsonExceptionResponse($e, 'Failed to create department.');
        }
    }

    public function update(Request $request, Department $department): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:100|unique:departments,name,'.$department->id
            ]);

            $department->update([
                'name' => $validated['name'],
                'updated_by' => auth()->id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Department has been updated successfully!'
            ]);
        }
        catch (\Throwable $e) {
            return $this->jsonExceptionResponse($e, 'Failed to update department.');
        }
    }

    public function destroy(Department $department): JsonResponse
    {
        try {
            $department->delete();

            return response()->json([
                'success' => true,
                'message' => 'Department has been deleted successfully!'
            ]);
        } 
        catch (\Throwable $e) {
            $message = 'Failed to delete department.';
            if ($e->getCode() == '23000' && str_contains($e->getMessage(), 'foreign key constraint fails')) {
                $message = "This department cannot be deleted because it is linked to other records (e.g., Purchase order).";
            }

            return response()->json([
                'success' => false,
                'message' => $message,
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function toggle(Request $request, Department $department): JsonResponse
    {
        try {
            if($department->status === 'ACTIVE') {
                $status = 'INACTIVE';
                $action = 'disable';
            }
            else {
                $status = 'ACTIVE';
                $action = 'enable';
            }

            $department->status = $status;
            $department->save();

            return response()->json([
                'success' => true,
                'message' => "Department has been {$action}d successfully!",
            ]);
        }
        catch (\Throwable $e) {
            return $this->jsonExceptionResponse($e, "Failed to {$action} department.");
        }
    }

    private function generateCode(): string
    {
        $lastNumber = Department::latest('id')->value('code');
        $nextNumber = $lastNumber ? intval(substr($lastNumber, 4)) + 1 : 1;
        return 'DEP-' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
    }
}
