<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\Contracts\View\View;
use App\Models\Masters\Bank;
use App\Models\Masters\BankBranch;
use App\Http\Requests\BankBranchRequest;

class BankController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function indexBank(): View
    {
        $banks = Bank::select('id','name','short_name')->get();
        return view('masters.banks.banks', compact('banks'));
    }

    public function storeBank(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name'       => 'bail|required|string|max:50|unique:banks,name',
                'short_name' => 'bail|required|string|max:10|unique:banks,short_name',
            ]);

            Bank::create([
                'name'       => $validated['name'],
                'short_name' => $validated['short_name'],
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Bank has been added successfully!'
            ]);
        }
        catch (\Throwable $e) {
            $message = $e instanceof ValidationException 
                ? $e->getMessage()
                : 'Failed to add bank.';

            return response()->json([
                'success' => false,
                'message' => $message,
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    public function editBank(Bank $bank): JsonResponse
    {
        try {
            return response()->json([
                'success' => true,
                'data'    => $bank->only(['id','name','short_name']),
            ]);
        }
        catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch bank.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function updateBank(Request $request, Bank $bank): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name'       => 'bail|required|string|max:50|unique:banks,name,'.$bank->id,
                'short_name' => 'bail|required|string|max:10|unique:banks,short_name,'.$bank->id,
            ]);

            $bank->update([
                'name'       => $validated['name'],
                'short_name' => $validated['short_name'],
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Bank has been updated successfully!'
            ]);
        }
        catch (\Throwable $e) {
            $message = $e instanceof ValidationException 
                ? $e->getMessage()
                : 'Failed to update bank.';

            return response()->json([
                'success' => false,
                'message' => $message,
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    public function destroyBank(Bank $bank): JsonResponse
    {
        try {
            if ($bank->branches()->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete bank. Associated branches exists.',
                ], 403);
            }

            $bank->delete();

            return response()->json([
                'success' => true,
                'message' => 'Bank has been deleted successfully!'
            ]);
        } 
        catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete bank.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function fetchBank(): JsonResponse
    {
        try {
            $banks = Bank::select('id','name')->orderBy('name')->get();
            return response()->json([
                'success' => true,
                'data'    => $banks,
            ]);
        }
        catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch banks.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function indexBranch(): View
    {
        $banks = Bank::select('id','name')->orderBy('name')->get();
        $branches = BankBranch::select('id','bank_id','name','ifsc')->with('bank:id,name')->get();
        return view('masters.banks.branches', compact('banks', 'branches'));
    }

    public function storeBranch(BankBranchRequest $request): JsonResponse
    {
        try {
            BankBranch::create($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Branch has been added successfully!'
            ]);
        }
        catch (\Throwable $e) {
            $message = $e instanceof ValidationException 
                ? $e->getMessage()
                : 'Failed to add branch.';

            return response()->json([
                'success' => false,
                'message' => $message,
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    public function editBranch(BankBranch $branch): JsonResponse
    {
        try {
            return response()->json([
                'success' => true,
                'data'    => $branch->only(['id','bank_id','name','ifsc']),
            ]);
        }
        catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch branch.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function updateBranch(BankBranchRequest $request, BankBranch $branch): JsonResponse
    {
        try {
            $branch->update($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Branch has been updated successfully!'
            ]);
        }
        catch (\Throwable $e) {
            $message = $e instanceof ValidationException 
                ? $e->getMessage()
                : 'Failed to update branch.';

            return response()->json([
                'success' => false,
                'message' => $message,
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    public function destroyBranch(BankBranch $branch): JsonResponse
    {
        try {
            $branch->delete();

            return response()->json([
                'success' => true,
                'message' => 'Branch has been deleted successfully!'
            ]);
        } 
        catch (\Throwable $e) {
            $message = 'Failed to delete branch.';
            if ($e->getCode() == '23000' && str_contains($e->getMessage(), 'foreign key constraint fails')) {
                $message = "This branch cannot be deleted because it is linked to other records (e.g., Petrol Bunks).";
            }

            return response()->json([
                'success' => false,
                'message' => $message,
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function fetchBranch(Bank $bank): JsonResponse
    {
        try {
            $branches = BankBranch::select('id','name','ifsc')                
                ->where('bank_id', $bank->id)
                ->orderBy('name')
                ->get();
            return response()->json([
                'success' => true,
                'data'    => $branches,
            ]);
        }
        catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch branches.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}
