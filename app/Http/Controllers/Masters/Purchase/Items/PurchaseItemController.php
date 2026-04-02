<?php

namespace App\Http\Controllers\Masters\Purchase\Items;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Contracts\View\View;
use App\Http\Controllers\Controller;
use App\Http\Requests\Masters\PurchaseItemRequest;
use App\Http\Traits\HandlesJsonExceptions;
use App\Models\Masters\Purchase\Items\PurchaseItem;
use App\Models\Masters\Purchase\Items\PurchaseItemGroup;
use App\Models\Masters\Purchase\HsnCode;
use App\Models\Masters\GstMaster;
use App\Enums\MasterStatus;
use App\Enums\FormMode;

class PurchaseItemController extends Controller
{
    use HandlesJsonExceptions;

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(): View
    {
        $status = "Active";
        $masters = PurchaseItem::select('id','name','group_id','hsn_code')
            ->with('group:id,name')
            ->where('status','ACTIVE')
            ->orderBy('sort_order')
            ->get();            
        return view('masters.purchase.items.items.index', compact('masters', 'status'));
    }

    public function list(Request $request): View
    {
        $status = $request->get('status', 'Active');

        $masters = $status === 'Active'
            ? PurchaseItem::select('id','name','group_id','hsn_code')->with('group:id,name')->where('status','ACTIVE')->orderBy('sort_order')->get() 
            : PurchaseItem::select('id','name','group_id','status','hsn_code')->with('group:id,name')->orderBy('status')->orderBy('sort_order')->get();

        return view('masters.purchase.items.items.index', compact('masters', 'status'));
    }

    public function show(PurchaseItem $master): View
    {
        // $hasDieselBills = $master->dieselBills()->exists();
        return view('masters.purchase.items.items.show', compact('master'));
    }

    public function create(): View
    {
        $groups = PurchaseItemGroup::select('id','name')->where('status', MasterStatus::ACTIVE)->get();
        $hsnCodes = HsnCode::select('id','hsn_code','tax_type','gst','sgst','cgst','igst')->orderBy('hsn_code')->get();

        $code = $this->generateItemCode();
        $master = (object)['code' => $code];

        return view('masters.purchase.items.items.manage', [
            'form_mode'   => FormMode::CREATE,
            'form_action' => route('purchase.items.items.store'),
            'page_title'  => 'Create Item',
            'master'      => $master,
            'groups'      => $groups,
            'hsn_codes'   => $hsnCodes,
        ]);
    }

    public function store(PurchaseItemRequest $request): RedirectResponse
    {
        // try {
            // Generate unique code 
            $code = $this->generateItemCode();

            // Merge the generated code with validated data
            $data = array_merge($request->validated(), ['code' => $code]);

            // Create the purchase item master
            PurchaseItem::create($data);
            
            // Return
            return back()->with('success', 'Purchase item has been created successfully!');
        // }
        // catch (\Throwable $e) {
        //     return back()->with('error', $e->getMessage())->withInput();
        // }
    }

    private function generateItemCode(): string
    {
        $lastNumber = PurchaseItem::latest('id')->value('code');
        $nextNumber = $lastNumber ? intval(substr($lastNumber, 7)) + 1 : 1;
        return 'PR-ITM-' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
    }
}
