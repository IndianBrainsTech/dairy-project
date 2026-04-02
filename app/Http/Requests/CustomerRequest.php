<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CustomerRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            // 'customer_name' => 'bail|required|unique:customers,customer_name|max:50',
            'customer_name' => 'bail|required|max:50',
            'customer_code' => 'required|max:20',
            'customer_group' => 'required',
            'route' => 'required',
            'area' => 'required',
            'address' => 'required',
            'pincode' => 'nullable|integer|digits:6',
            'contact_number' => 'required|integer|digits_between:10,15',
            'alternate_number' => 'nullable|integer|digits_between:10,15|different:contact_number',
            'email' => 'nullable|email:rfc,dns',
            'staff_id' => 'required',
            'billing_name' => 'required|max:50',
            'credit_limit' => 'nullable|numeric|min:0',
            'gst_type' => 'required',
            'gst_number' => 'nullable|alpha_num|size:15|required_if:gst_type,Interstate Registered,Intrastate Registered',            
            'pan_number' => 'nullable|alpha_num|size:10',
            'outstanding' => 'nullable|numeric',
            'tcs_status' => 'required',
            'tds_status' => 'required',
            'payment_mode' => 'required',
            'link_customer' => 'nullable|required_with:link_cust_chk',
            'customer_since' => 'nullable|date|before_or_equal:today',
            'dob' => 'nullable|date|before:-18 years',
            'aadhaar' => 'nullable|integer|digits:16',
            'profile_image' => 'mimes:jpg,png,jpeg,gif,svg',
            'shop_photo' => 'mimes:jpg,png,jpeg,gif,svg',
            'ifsc' => 'nullable|alpha_num|size:11',
            'acc_number' => 'nullable|alpha_num|max:20'
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'customer_name.required' => 'Input the customer\'s name here',
            // 'customer_name.unique' => 'Customer name already exists',
            'customer_name.max' => 'Name is limited to 50 characters',
            'customer_code.required' => 'Please Enter Customer Code',            
            'customer_code.max' => 'Customer ID is limited to 20 characters',
            'customer_group.required' => 'Please Select Customer Group',
            'route.required' => 'Please Select Route',
            'area.required' => 'Please Select Area',
            'address.required' => 'Please Enter Address',
            'pincode.integer' => 'Please Enter Valid Pin Code',
            'pincode.digits' => 'Pin Code requires 6 digits',
            'contact_number.required' => 'Please Enter Contact Number',
            'contact_number.integer' => 'Please Enter Valid Contact Number',
            'contact_number.digits_between' => 'Please Enter Valid Contact Number',
            'alternate_number.integer' => 'Please Enter Valid Contact Number',
            'alternate_number.digits_between' => 'Please Enter Valid Phone Number',
            'alternate_number.different' => 'A different phone number should be provided as an alternative',
            'email' => 'Please Provide Valid Email ID',
            'staff_id.required' => 'Please Select Staff Incharge',
            'billing_name.required' => 'Input the billing name here',
            'billing_name.max' => 'Name is limited to 50 characters',
            'credit_limit.numeric' => 'Given is not a valid amount',
            'credit_limit.min' => 'Negative value not allowed',
            'gst_type' => 'Please Select GST Type',
            'gst_number.alpha_num' => 'Only Numbers and Alphabets are allowed',
            'gst_number.size' => 'GST Number must have 15 chars',
            'gst_number.required_if' => 'GST Number is required for GST Type as Registered',
            'pan_number.alpha_num' => 'Only Numbers and Alphabets are allowed',
            'pan_number.size' => 'PAN must have 10 chars',
            'outstanding.numeric' => 'Given is not a valid amount',
            'tcs_status' => 'Please Select TCS Status',
            'tds_status' => 'Please Select TDS Status',
            'payment_mode' => 'Please Select Payment Mode',
            'link_customer.required_with' => 'Please select customer to link',
            'customer_since.before_or_equal' => 'Future date not allowed',
            'dob.before' => 'Please check date of birth (Min Age: 18 Years)',
            'aadhaar.integer' => 'Given is not a valid number',
            'aadhaar.digits' => 'Aadhaar Number must have 16 digits',
            'profile_image.mimes' => 'Uploaded file is not an image',
            'shop_photo.mimes' => 'Uploaded file is not an image',
            'ifsc.alpha_num' => 'Only Numbers and Alphabets are allowed',
            'ifsc.size' => 'IFSC must have 11 chars',
            'acc_number.alpha_num' => 'Only Numbers and Alphabets are allowed',
            'acc_number.max' => 'Size exceeds Maximum Limit'
        ]; 
    }
}
