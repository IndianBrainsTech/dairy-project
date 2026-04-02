<?php

namespace App\Http\Controllers\Masters\Purchase;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Contracts\View\View;
use App\Http\Controllers\Controller;
use App\Http\Traits\HandlesJsonExceptions;
use App\Http\Requests\Masters\SupplierRequest;
use App\Models\Places\State;
use App\Models\Masters\Bank;
use App\Models\Masters\Purchase\Supplier;
use App\Enums\MasterStatus;
use App\Enums\FormMode;

class SupplierController extends Controller
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
            ? Supplier::select('id','name','code','city','contact_number')->where('status',MasterStatus::ACTIVE)->get() 
            : Supplier::select('id','name','code','city','contact_number','status')->orderBy('status')->get();

        return view('masters.purchase.suppliers.index', compact('masters', 'status'));
    }

    // public function show(Supplier $supplier): View
    // {        
    //     return view('masters.purchase.suppliers.show', compact('master'));
    // }

    public function create(): View
    {
        $code = $this->generateCode();
        $master = (object)[ 'code' => $code ];

        $states = State::select('id','name')->orderBy('id')->get();
        $banks = Bank::select('id','name')->orderBy('name')->get();

        return view('masters.purchase.suppliers.manage', [
            'form_mode'   => FormMode::CREATE,
            'form_action' => route('suppliers.store'),
            'page_title'  => 'Create Supplier',
            'master'      => $master,
            'states'      => $states,
            'banks'       => $banks,
        ]);
    }

    public function store(SupplierRequest $request): RedirectResponse
    {
        Supplier::create($request->validated());
        
        // Return
        // return back()->with('success', 'Supplier has been created successfully!');

        return redirect()
            ->route('suppliers.index')
            ->with('success', 'Supplier has been created successfully!');
    }

    private function generateCode(): string
    {
        $maxNumber = Supplier::query()
            ->where('code', 'like', 'SUP-%')
            ->selectRaw("MAX(CAST(SUBSTRING(code, 5) AS UNSIGNED)) as max_number")
            ->value('max_number');

        $nextNumber = $maxNumber ? $maxNumber + 1 : 1;

        return 'SUP-' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
    }
}
