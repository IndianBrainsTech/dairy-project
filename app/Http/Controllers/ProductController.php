<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Products\Product;
use App\Models\Products\ProductUnit;
use App\Models\Products\ProductGroup;
use App\Models\Products\ViewProduct;
use App\Models\Products\ViewProductUnit;
use App\Models\Products\UOM;
use App\Models\Masters\GstMaster;
use App\Models\Stocks\CurrentStock;
use Storage;

class ProductController extends Controller
{
    public function __construct() 
    {
        $this->middleware('auth');
    }

    public function indexUnits() 
    {
        $units = UOM::all();        
        return view('masters.products.list_units', [
            'units' => $units
        ]);
    }

    public function editUnit($id)
    {        
    	$unit = UOM::find($id);
	    return response()->json([
	      'unit' => $unit
	    ]);
    }

    public function storeUnit($id)
    {              
        try {
            UOM::updateOrCreate(
                [ 'id' => $id ],
                [ 'unit_name' => request('unit_name'),
                  'display_name' => request('display_name'),
                  'hot_key' => request('hot_key') ]
            );
            return response()->json([ 'success' => true ]);            
        }
        catch(QueryException $exception) {            
            return $exception;
        }
    }

    public function destroyUnit($id)
    {                   
        $unit = UOM::find($id);
        $unit->delete();
        return response()->json([ 'success' => true ]);
    }

    public function indexProductGroups() 
    {        
        $product_groups = ProductGroup::select('id','name')->get();
        // return response()->json(['product_groups' => $product_groups]);
        return view('masters.products.product_groups', [
            'product_groups' => $product_groups
        ]);
    }

    public function editProductGroup($id)
    {        
    	$product_group = ProductGroup::find($id);
	    return response()->json([
	      'prgroup' => $product_group
	    ]);
    }

    public function storeProductGroup($id)
    {              
        try {
            ProductGroup::updateOrCreate(
                [ 'id' => $id ],
                [ 'name' => request('name') ]
            );
            return response()->json([ 'success' => true ]);
        }
        catch(QueryException $exception) {            
            return $exception;
        }
    }

    public function destroyProductGroup($id)
    {                   
        $product_group = ProductGroup::find($id);
        $product_group->delete();
        return response()->json([ 'success' => true ]);
    }        

    public function indexProducts()
    {
        $products = Product::select('id','name','short_name','group_id','description','image','status')
                        ->with('prod_group:id,name')
                        ->orderBy('display_index')
                        ->get();  
        // return response()->json(['products' => $products]);
        return view('masters.products.list_products', [
            'products' => $products
        ]);
    }

    public function createProduct() 
    {        
        $units = UOM::select('id','unit_name','display_name')->orderBy('id')->get();
        $groups = ProductGroup::select('id','name')->orderBy('name')->get();
        $hsn_codes = GstMaster::select('id','hsn_code')->get();
        return view('masters.products.add_product', [
            'units' => $units,
            'groups' => $groups,
            'hsn_codes' => $hsn_codes
        ]);
    }
    
    public function editProduct(Request $request)
    {        
        $id = $request->input('id');
        $product = Product::find($id);
        $productUnits = ProductUnit::select('unit_id','price','prim_unit','conversion')->where('product_id',$id)->get();
        $units = UOM::select('id','unit_name','display_name')->orderBy('id')->get();
        $groups = ProductGroup::select('id','name')->orderBy('name')->get();
        $hsnCodes = GstMaster::select('id','hsn_code')->get();
        return view('masters.products.edit_product', [
            'product' => $product,
            'productUnits' => $productUnits,
            'units' => $units,
            'groups' => $groups,
            'hsn_codes' => $hsnCodes
        ]);
        // return response()->json(['product' => $product, 'productUnits' => $productUnits, 'units' => $units, 'groups' => $groups, 'hsnCodes' => $hsnCodes]);
    }

    public function storeProduct(Request $request)
    {           
        // return $request->all();
        $validator = $request->validate([
                'prod_name' => 'unique:products,name',
                'short_name' => 'unique:products,short_name',
                'product_image' => 'mimes:jpg,png,jpeg,gif,svg'
            ],
            [                
                'prod_name.unique' => 'Product Name Already Exists',
                'short_name.unique' => 'Short Name Already Assigned',
                'product_image.mimes' => 'Uploaded file is not an image'
            ]);
        // return $validator;

        try {
            $product = new Product();
            $this->saveProduct($product,$request);
            
            // Save Item Code
            $product->item_code = "ITM-" . str_pad($product->id, 2, '0', STR_PAD_LEFT);
            $product->save();

            /* Save Image and Display Index */
            $id = $product->id;
            $products_path = 'public/products/';
            if(isset($request->product_image)) {
                $imageName = $id.'.'.$request->product_image->extension();                  
                $request->file('product_image')->storeAs($products_path, $imageName);
            }
            else {
                $imageName = $id.'.jpg';
                Storage::copy($products_path.'no-image.jpg', $products_path.$imageName);
            }
            $product->display_index = $id;
            $product->image = $imageName;
            $product->save();
            /* ------------------------------ */
            
            $this->saveProductUnits($request,$id);

            CurrentStock::create([
                'item_id'   => $product->id,
                'item_name' => $product->name,
                'unit_id'   => $product->primaryUnit->unit_id,
            ]);
                        
            return back()->with('success', 'Product Created Successfully');
        }
        catch(QueryException $exception) {            
            return back()->with('error', $exception->getMessage());
        }       
    }

    public function updateProduct(Request $request, $id) {
        // return $request->all();

        try {
            $product = Product::find($id);            
            $this->saveProduct($product,$request);

            /* Clear Existing Units of Products */
            $productUnits = ProductUnit::where('product_id',$id);
            $productUnits->delete();
            /* ------------------------------ */

            $this->saveProductUnits($request,$product->id);
            return back()->with('success', 'Product Updated Successfully');
        }
        catch(QueryException $exception) {
            return back()->with('error', $exception->getMessage());
        }
    }

    private function saveProduct(Product $product, Request $request) 
    {        
        $product->name          = $request->prod_name;
        $product->short_name    = $request->short_name;
        $product->group_id      = $request->prod_group;
        $product->description   = $request->prod_desc;
        $product->mrp           = $request->mrp;
        $product->fat           = $request->fat;
        $product->snf           = $request->snf;
        $product->hsn_code      = $request->hsn_code;
        $product->tax_type      = $request->tax_type;        
        $product->visible_app = $request->has('customSwitch1') ? "1" : "0";
        $product->visible_invoice = $request->has('customSwitch2') ? "1" : "0";
        $product->visible_bulkmilk = $request->has('customSwitch3') ? "1" : "0";
        if($product->tax_type == "Taxable") {
            $product->gst       = $request->gst;
            $product->sgst      = $request->sgst;
            $product->cgst      = $request->cgst;
            $product->igst      = $request->igst;
        }
        else {
            $product->gst       = null;
            $product->sgst      = null;
            $product->cgst      = null;
            $product->igst      = null;
        }
        $product->save();
    }

    private function saveProductUnits(Request $request, $id) 
    {        
        $prim_unit = $request->select_primary_unit;
        $prim_unit = explode(',', $prim_unit)[0];
        $productUnit = new ProductUnit();
        $productUnit->product_id    = $id;
        $productUnit->unit_id       = $prim_unit;
        $productUnit->price         = $request->primary_price;
        $productUnit->prim_unit     = "1";
        $productUnit->save();            

        if(isset($request->units)) {
            $units = $request->units;
            foreach($units as $unit) {
                $productUnit = new ProductUnit();
                $productUnit->product_id    = $id;
                $productUnit->unit_id       = $unit['unit_id'];
                $productUnit->price         = $unit['price'];
                $productUnit->conversion    = $unit['conversion'];
                $productUnit->prim_unit     = "0";
                $productUnit->save();
            }                       
        }        
    }

    public function showProduct(Request $request)
    {
        $id = $request->input('id');
        $product = Product::with('prod_group:id,name')->where('id',$id)->get()->first(); 
    	$units = ViewProductUnit::select('unit_id','unit_name','price','prim_unit','conversion')
                    ->where('product_id',$id)                    
                    ->get();  

        // return response()->json([
        return view('masters.products.view_product', [
            'product' => $product,
            'units' => $units
        ]);
    }

    public function statusProduct($id)
    {        
        $product = Product::find($id);
        $status = ($product->status == "Active") ? "Inactive" : "Active";
        $product->status = $status;
        $product->save();
        if($status == "Active")
            return back()->with('success', 'Product is now Active');
        else
            return back()->with('success', 'Product is now Inactive');
    }

    public function uniqueProduct(Request $request) {
        if($request->has('id')) {
            $count1 = DB::select("SELECT count(*) as name_count FROM products WHERE name='" . request('prod_name') . "' and id<>" . request('id'));
            $count2 = DB::select("SELECT count(*) as short_name_count FROM products WHERE short_name='" . request('short_name') . "' and id<>" . request('id'));
        }
        else {
            $count1 = DB::select("SELECT count(*) as name_count FROM products WHERE name='" . request('prod_name') . "'");
            $count2 = DB::select("SELECT count(*) as short_name_count FROM products WHERE short_name='" . request('short_name') . "'");
        }
        return response()->json([ 
            'success' => true,
            'name_count' => $count1[0]->name_count,
            'short_name_count' => $count2[0]->short_name_count
        ]);
    }

    public function reorderProduct(Request $request) {
        // return $request->all();
        try {
            $ids = json_decode($request->input('ids'));
            $i = 1;
            foreach($ids as $id) {
                $product = Product::find($id);
                $product->display_index = $i;
                $product->save();
                $i++;
            }
            return response()->json([ 'success' => true ]);
        }
        catch(QueryException $exception) {
            return $exception;
        }
    }    
}