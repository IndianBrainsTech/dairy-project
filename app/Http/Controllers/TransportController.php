<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Database\QueryException;
use Illuminate\Contracts\View\View;
use App\Models\Transport\Vehicle;
use App\Models\Transport\PetrolBunk;
use App\Models\Masters\Bank;
use App\Models\Masters\PetrolBunkTurnover;
use App\Http\Requests\PetrolBunkRequest;
use App\Enums\FormMode;

class TransportController extends Controller
{
    public function __construct() 
    {
        $this->middleware('auth');
    }

    public function indexVehicles() 
    {        
        $vehicles = Vehicle::select('id','vehicle_number','vehicle_type','make','model')->where('status','Active')->get();
        return view('transport.vehicles', [
            'vehicles' => $vehicles
        ]);        
    }

    public function editVehicle($id)
    {        
    	$vehicle = Vehicle::find($id);
	    return response()->json([
	      'vehicle' => $vehicle
	    ]);
    }

    public function storeVehicle($id)
    {              
        try {
            Vehicle::updateOrCreate(
                [ 'id' => $id ],
                [ 'vehicle_number' => request('vehicle_number'),
                  'vehicle_type' => request('vehicle_type'),
                  'make' => request('make'),
                  'model' => request('model') ]
            );
            return response()->json([ 'success' => true ]);
        }
        catch(QueryException $exception) {            
            return $exception;
        }
    }

    public function destroyVehicle($id)
    {                   
        $vehicle = Vehicle::find($id);
        $vehicle->status = "Inactive";
        $vehicle->save();
        return response()->json([ 'success' => true ]);
    }

    public function indexBunk(): View
    {
        $status = "Active";
        $bunks = PetrolBunk::select('id','name')->where('status','ACTIVE')->get();
        return view('masters.transport.bunks.index', compact('bunks', 'status'));
    }

    public function listBunk(Request $request): View
    {
        $status = $request->get('status', 'Active');

        $bunks = $status === 'Active'
            ? PetrolBunk::select('id','name')->where('status','ACTIVE')->get() 
            : PetrolBunk::select('id','name','status')->orderBy('status')->get();

        return view('masters.transport.bunks.index', compact('bunks', 'status'));
    }

    public function showBunk(PetrolBunk $bunk): View
    {
        $hasDieselBills = $bunk->dieselBills()->exists();
        return view('masters.transport.bunks.show', compact('bunk', 'hasDieselBills'));
    }

    public function createBunk(): View
    {
        $banks = Bank::select('id','name')->orderBy('name')->get();

        return view('masters.transport.bunks.manage', [
            'form_mode'   => FormMode::CREATE,
            'form_action' => route('bunks.store'),
            'page_title'  => 'Add Petrol Bunk',
            'bunk'        => null,
            'banks'       => $banks,
        ]);
    }

    public function editBunk(PetrolBunk $bunk): View
    {
        $banks = Bank::select('id','name')->orderBy('name')->get();

        return view('masters.transport.bunks.manage', [
            'form_mode'   => FormMode::EDIT,
            'form_action' => route('bunks.update', $bunk),
            'page_title'  => 'Edit Petrol Bunk',
            'bunk'        => $bunk,
            'banks'       => $banks,
        ]);
    }

    public function storeBunk(PetrolBunkRequest $request): RedirectResponse
    {
        try {
            // Generate unique code (e.g., PB001, PB002, etc.)
            $code = $this->generateBunkCode();

            // Merge the generated code with validated data
            $data = array_merge($request->validated(), ['code' => $code]);

            // Create the petrol bunk record
            PetrolBunk::create($data);
            
            // Return
            return back()->with('success', 'Petrol Bunk has been added successfully!');
        }
        catch (\Throwable $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    public function updateBunk(PetrolBunkRequest $request, PetrolBunk $bunk): RedirectResponse
    {
        try {
            // Update the petrol bunk with validated data
            $bunk->update($request->validated());
            return back()->with('success', 'Petrol Bunk has been updated successfully!');
        } 
        catch (\Throwable $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    public function updateBunkStatus(Request $request, PetrolBunk $bunk): RedirectResponse
    {
        $bunk->status = $bunk->status === 'ACTIVE' ? 'INACTIVE' : 'ACTIVE';
        $bunk->save();

        return back()->with('success', "{$bunk->name} is now {$bunk->status}");
    }

    public function destroyBunk(PetrolBunk $bunk): JsonResponse
    {
        try {
            $bunk->delete();

            return response()->json([
                'success' => true,
                'message' => 'Petrol bunk has been deleted successfully!'
            ]);
        }
        catch (\Throwable $e) {
            $message = 'Failed to delete petrol bunk.';
            if ($e->getCode() == '23000' && str_contains($e->getMessage(), 'foreign key constraint fails')) {
                $message = "This petrol bunk cannot be deleted because it is linked to other records (e.g., Diesel Bill).";
            }

            return response()->json([
                'success' => false,
                'message' => $message,
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    private function generateBunkCode(): string
    {
        $lastNumber = PetrolBunk::latest('id')->value('code');
        $nextNumber = $lastNumber ? intval(substr($lastNumber, 4)) + 1 : 1;
        return 'BNK-' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
    }

    public function indexBunkTurnover(): View
    {
        // Fetch the petrol bunks
        $bunks = PetrolBunk::select('id', 'name', 'tds_status')
            ->where('status', 'ACTIVE')
            ->orderBy('name')
            ->get();

        // Fetch the active turnover records
        $turnover = PetrolBunkTurnover::select('id', 'bunk_id', 'amount', 'reference_date')
            ->where('status', 'ACTIVE')
            ->get();

        // Convert $turnover to an associative array with bunk_id as the key for easy lookup
        $turnoverData = $turnover->keyBy('bunk_id');

        // Iterate through each petrol bunk and merge the corresponding turnover data
        $petrolBunkData = $bunks->map(function ($bunk) use ($turnoverData) {
            // Check if the bunk has turnover data
            if (isset($turnoverData[$bunk->id])) {
                // Merge amount and date fields with the bunk's data
                $bunk->amount = (int) $turnoverData[$bunk->id]->amount;
                $bunk->date = $turnoverData[$bunk->id]->reference_date->format('Y-m-d');
            } else {
                // Set default values if no turnover data is found
                $bunk->amount = null;
                $bunk->date = null;
            }
            return $bunk;
        });

        // Return the response with merged data
        return view('masters.openings.petrol-bunk-turnover', [
            'bunks' => $petrolBunkData
        ]);
    }

    public function updateBunkTurnover(Request $request): JsonResponse
    {
        try {
            $bunkId = $request->bunk_id;
            $amount = $request->amount;
            $date   = $request->date;

            // Find the active turnover record for the bunk
            $turnover = PetrolBunkTurnover::where('bunk_id', $bunkId)
                ->where('status', 'ACTIVE')
                ->first();

            // Check if both amount and date are null or empty; if so, mark existing record as inactive
            if (empty($amount) && empty($date)) {
                if ($turnover) {
                    // Mark the existing record as inactive
                    $turnover->update(['status' => 'INACTIVE']);
                }
                // Return without adding a new record
                return response()->json(['success' => true]);
            }

            // Check if an active record exists and if it matches the current data
            if ($turnover && ($date != $turnover->reference_date || $amount != $turnover->amount)) {
                // Mark the existing record as inactive if it doesn't match
                $turnover->update(['status' => 'INACTIVE']);
            }

            // Create a new turnover record if no match was found or no active record exists
            if (!$turnover || ($date != $turnover->reference_date || $amount != $turnover->amount)) {
                PetrolBunkTurnover::create([
                    'bunk_id'        => $bunkId,
                    'amount'         => $amount,
                    'reference_date' => $date,
                ]);
            }

            return response()->json(['success' => true]);
        }
        catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}
