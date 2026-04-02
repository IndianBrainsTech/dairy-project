<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Profiles\Customer;
use App\Models\Profiles\Employee;
use App\Models\Products\Product;
use App\Models\User;
use Storage;

class PhotoController extends Controller
{
    public function __construct() 
    {
        $this->middleware('auth');
    }
    
    public function updatePhoto(Request $request)
    {           
        // return $request->all();
        if(isset($request->image_file)) 
        {
            $id = $request->id;
            $user = $request->user;
            $tag = $request->tag;

            if($user == "customer") {
                $this->updateCustomerPhoto($id, $tag, $request);
            }
            else if($user == "employee") {
                $this->updateEmployeePhoto($id, $request);
            }
            else if($user == "product") {
                $this->updateProductPhoto($id, $request);
            }
            else if($user == "user") {
                $this->updateUserPhoto($id, $request);
            }

            return back()->with('success', 'Photo Updated Successfully');
        }
        else
        {
            return back()->with('error', 'No Image Uploaded');
        }
    }

    private function updateCustomerPhoto($id, $tag, Request $request)
    {
        $customer = Customer::find($id);
        $image_path = 'public/customers/' . $tag . '/';
        if($tag == "shop")
            $image_name = $customer->shop_photo;
        else
            $image_name = $customer->profile_image;                
        
        if(Storage::exists($image_path . $image_name))
            Storage::delete($image_path . $image_name);

        $image_name = $id.'.'.$request->image_file->extension();
        $request->file('image_file')->storeAs($image_path, $image_name);

        if($tag == "shop")
            $customer->shop_photo = $image_name;
        else
            $customer->profile_image = $image_name;

        $customer->save();
    }

    private function updateEmployeePhoto($id, Request $request)
    {
        $employee = Employee::find($id);
        $image_path = 'public/employees/';
        $image_name = $employee->photo;
        
        if(Storage::exists($image_path . $image_name))
            Storage::delete($image_path . $image_name);

        $image_name = $id.'.'.$request->image_file->extension();
        $request->file('image_file')->storeAs($image_path, $image_name);
        $employee->photo = $image_name;

        $employee->save();
    }
    
    private function updateProductPhoto($id, Request $request)
    {
        $product = Product::find($id);
        $image_path = 'public/products/';
        $image_name = $product->image;
        
        if(Storage::exists($image_path . $image_name))
            Storage::delete($image_path . $image_name);

        $image_name = $id.'.'.$request->image_file->extension();
        $request->file('image_file')->storeAs($image_path, $image_name);
        $product->image = $image_name;

        $product->save();
    }

    private function updateUserPhoto(int $id, Request $request): void
    {
        $user = User::findOrFail($id);

        $directory = 'public/users/';
        $oldPhoto = $user->photo;

        // Delete old photo if it exists
        if ($oldPhoto && Storage::exists($directory . $oldPhoto)) {
            Storage::delete($directory . $oldPhoto);
        }

        // Generate new file name
        $newFileName = $id . '.' . $request->file('image_file')->extension();

        // Store the new image
        $request->file('image_file')->storeAs($directory, $newFileName);

        // Update user's photo and save
        $user->photo = $newFileName;
        $user->save();
    }
}
