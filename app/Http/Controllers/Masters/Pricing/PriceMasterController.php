<?php

namespace App\Http\Controllers\Masters\Pricing;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use App\Http\Controllers\Controller;
use App\Http\Traits\HandlesJsonExceptions;
use App\Models\Masters\Pricing\PriceMaster;
use App\Models\Masters\Pricing\PriceAdjustment;
use App\Models\Profiles\Customer;
use App\Models\Products\Product;
use App\Enums\PriceMasterStatus;
use App\Enums\FormMode;
use Carbon\Carbon;

class PriceMasterController extends Controller
{
    use HandlesJsonExceptions;

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function indexPriceMaster(Request $request): View
    {
        $status = strtoupper($request->get('status', 'ACTIVE'));

        $query = PriceMaster::query()
            ->select('id', 'document_number', 'effect_date', 'narration', 'status');

        if ($status !== 'ALL') {
            $query->where('status', $status);
        }

        $masters = $query
            ->orderBy('effect_date', 'desc')
            ->get();

        return view('masters.pricing.price-masters.index', compact('masters', 'status'));
    }

    public function showPriceMaster(PriceMaster $master): View
    {        
        return view('masters.pricing.price-masters.show', compact('master'));
    }

    public function createPriceMaster(): View
    {
        return view('masters.pricing.price-masters.manage', [
            'form_mode'       => FormMode::CREATE,
            'form_action'     => route('price-masters.store'),
            'form_method'     => 'POST',
            'page_title'      => 'Create Price Master',
            'master'          => null,
            'document_number' => $this->generateDocumentNumber(),
            'products'        => $this->fetchProducts(),
            'customers'       => $this->fetchCustomers(),
        ]);
    }

    public function editPriceMaster(PriceMaster $master): View
    {
        return view('masters.pricing.price-masters.manage', [
            'form_mode'       => FormMode::EDIT,
            'form_action'     => route('price-masters.update', $master),
            'form_method'     => 'PUT',
            'page_title'      => 'Edit Price Master',
            'master'          => $master,
            'document_number' => $master->document_number,
            'products'        => $this->fetchProducts(),
            'customers'       => $this->fetchCustomers(),
        ]);
    }

    public function storePriceMaster(Request $request): JsonResponse
    {
        try {
            $validated = $this->validatePriceMasterRequest($request);

            PriceMaster::create([
                'document_number' => $this->generateDocumentNumber(),
                'document_date'   => Carbon::today(),
                'effect_date'     => $validated['effect_date'],
                'narration'       => $validated['narration'],
                'customer_ids'    => $validated['customer_ids'],
                'price_list'      => $validated['price_list'],
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Price master has been created successfully!'
            ]);
        }
        catch (\Throwable $e) {
            return $this->jsonExceptionResponse($e, 'Failed to create price master.');
        }
    }

    public function updatePriceMaster(Request $request, PriceMaster $master): JsonResponse
    {
        try {
            $validated = $this->validatePriceMasterRequest($request);

            $master->update([
                'effect_date'  => $validated['effect_date'],
                'narration'    => $validated['narration'],
                'customer_ids' => $validated['customer_ids'],
                'price_list'   => $validated['price_list'],
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Price master has been updated successfully!'
            ]);
        }
        catch (\Throwable $e) {
            return $this->jsonExceptionResponse($e, 'Failed to update price master.');
        }
    }

    public function createPriceMasterClone(PriceMaster $master): View
    {
        return view('masters.pricing.price-masters.clone', [            
            'master'          => $master,
            'document_number' => $this->generateDocumentNumber(),
            'products'        => $this->fetchProducts(),
            'customers'       => $this->fetchCustomers(),
        ]);
    }

    public function updatePriceMasterClone(Request $request, PriceMaster $master): JsonResponse
    {
        try {
            $validated = $this->validatePriceMasterRequest($request);

            DB::transaction(function () use ($validated, $master) {
                $status = PriceMasterStatus::SCHEDULED;
                $today = date('Y-m-d');

                if($validated['effect_date'] == $today) {
                    $master->update([
                        'status' => PriceMasterStatus::SUPERSEDED,
                    ]);
                    $status = PriceMasterStatus::ACTIVE;
                }

                PriceMaster::create([
                    'document_number' => $this->generateDocumentNumber(),
                    'document_date'   => $today,
                    'effect_date'     => $validated['effect_date'],
                    'narration'       => $validated['narration'],
                    'customer_ids'    => $validated['customer_ids'],
                    'price_list'      => $validated['price_list'],
                    'parent_id'       => $master->id,  
                    'status'          => $status,  
                ]);
            });
            
            return response()->json([
                'success' => true,
                'message' => 'Price master has been cloned successfully!'
            ]);
        }
        catch (\Throwable $e) {
            return $this->jsonExceptionResponse($e, 'Failed to clone price master.');
        }
    }

    public function togglePriceMasterStatus(PriceMaster $master): RedirectResponse
    {
        $master->status = $master->status === PriceMasterStatus::ACTIVE 
            ? PriceMasterStatus::INACTIVE 
            : PriceMasterStatus::ACTIVE;

        $master->save();

        return back()->with('success', "{$master->document_number} is now {$master->status->label()}");
    }

    public function createPriceMasterAdjustment(): View
    {
        $items = Product::select('id','name')->where('status','Active')->get();
        return view('masters.pricing.price-masters.adjust', compact('items'));
    }

    public function fetchMastersForAdjustment(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'item_ids'   => 'required|array|min:1',
            'item_ids.*' => 'required|integer|exists:products,id',
        ]);

        try {
            // Build JSON path parameters
            $paths = array_map(
                fn($id) => '$."' . (int)$id . '"',
                $validated['item_ids']
            );

            $placeholders = implode(',', array_fill(0, count($paths), '?'));
            $sql = "JSON_CONTAINS_PATH(price_list, 'one', {$placeholders})";

            $masters = PriceMaster::select('id','document_number','effect_date','narration')
                ->where('status','ACTIVE')
                ->whereRaw($sql, $paths)
                ->get()
                ->append('effect_date_for_display');

            return response()->json([
                'success' => true,
                'masters' => $masters,
            ]);
        }
        catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch price masters.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    public function storePriceMasterAdjustment(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'items'                => 'required|array|min:1',
            'items.*.item_id'      => 'required|integer|exists:products,id',
            'items.*.adjust_value' => 'required',
            'ids'                  => 'required|array|min:1',
            'ids.*'                => 'required|integer|exists:price_masters,id',
            'effect_date'          => 'required|date|after_or_equal:today',            
        ]);

        try {
            DB::transaction(function () use ($validated) {
                $adjustmentData = $this->getAdjustmentValueJson($validated['items']);
                $masterIds = array_map('intval', $validated['ids']);

                // $this->adjustPriceMasters($masterIds, $adjustmentData);

                // Create price adjustment entry
                $stock = PriceAdjustment::create([
                    'document_date'   => today(),
                    'user_id'         => auth()->id(),
                    'adjustment_data' => $adjustmentData,
                    'masters_list'    => $masterIds,
                    'effect_date'     => $validated['effect_date'],                    
                ]);
            });

            return response()->json([
                'success' => true,
                'message' => 'Price adjustments scheduled successfully.',
            ]);
        }
        catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to save price adjustment.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    private function generateDocumentNumber(): string
    {
        $last = PriceMaster::latest('id')->value('document_number');
        $number = $last ? intval(substr($last, 4)) + 1 : 1;
        return 'PRM-' . str_pad($number, 3, '0', STR_PAD_LEFT);
    }

    private function fetchCustomers(): Collection
    {
        return Customer::select('id','customer_name','group','route_id')
            ->with('route:id,name')
            ->where('status','Active')
            ->orderBy('customer_name')
            ->get();
    }

    private function fetchProducts(): Collection
    {
        return Product::select('id','name')
            ->where('status','Active')
            ->orderBy('display_index')
            ->get();
    }

    private function validatePriceMasterRequest(Request $request): array
    {
        return $request->validate([
            'effect_date'    => 'bail|required|date',
            'narration'      => 'bail|required|string',

            // Customer IDs
            'customer_ids'   => 'bail|required|array|min:1',
            'customer_ids.*' => 'bail|integer|exists:customers,id',

            // Price list (product_id => price)
            'price_list'     => 'bail|required|array|min:1',
            'price_list.*'   => 'bail|numeric|min:0',
        ]);
    }

    private function getAdjustmentValueJson(array $items): array
    {
        $data = [];

        foreach ($items as $item) {
            $value = $item['adjust_value'];

            // Normalize Unicode minus (U+2212) → regular hyphen
            $value = str_replace("\u{2212}", "-", $value);

            $data[$item['item_id']] = $value;
        }

        return $data;
    }

    private function adjustPriceMasters($masterIds, $adjustmentData)
    {
        PriceMaster::whereIn('id', $masterIds)->chunkById(50, function ($masters) use ($adjustmentData) {
            foreach ($masters as $master) {
                $prices = $master->price_list;

                foreach ($adjustmentData as $productId => $adjust) {
                    // Skip if product not present in this master
                    if (! array_key_exists($productId, $prices)) {
                        continue;
                    }

                    // Convert price and adjustment to floats
                    $current = (float) $prices[$productId];
                    $delta   = (float) $adjust;

                    // Apply increase / decrease
                    $prices[$productId] = $current + $delta;
                }

                // Save back to DB
                $master->price_list = $prices;
                $master->save();
            }
        });
    }
}
