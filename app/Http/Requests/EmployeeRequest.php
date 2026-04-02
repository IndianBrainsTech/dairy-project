<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EmployeeRequest extends FormRequest
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
            'name' => 'required|max:50',
            'code' => 'required|max:10',            
            'role' => 'required',
            'reporting_head' => 'required',
            'dob' => 'nullable|date|before:-18 years',
            'user_name' => 'required|max:20',
            'password' => 'required',
            'photo' => 'mimes:jpg,png,jpeg,gif',
            'address' => 'required',
            'district' => 'required',
            'pincode' => 'nullable|integer|digits:6',
            'mobile_number' => 'required|integer|digits_between:10,15',
            'alternate_number' => 'nullable|integer|digits_between:10,15|different:mobile_number',
            'email' => 'nullable|email:rfc,dns',
            'aadhaar' => 'nullable|integer|digits:16',
            'license_number' => 'nullable|min:12|max:16',
            'license_validity' => 'nullable|date|required_with:license_number',
            'doj' => 'nullable|date|before_or_equal:today',
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
            'name.required' => 'Please Enter Employee Name',
            'name.max' => 'Name is limited to 50 characters',
            'code.required' => 'Employee Code Required',            
            'code.max' => 'Code is limited to 10 characters',
            'role.required' => 'Please Select Role',
            'reporting_head.required' => 'Please Select Reporting Head',
            'dob.before' => 'Please check date of birth (Min Age: 18 Years)',
            'user_name.required' => 'Please Enter User Name',
            'user_name.max' => 'User Name is limited to 20 characters',
            'password.required' => 'Please Enter Password',
            'photo.mimes' => 'Uploaded file is not an image',
            'address.required' => 'Please Enter Address',
            'district.required' => 'Please Select District',
            'pincode.integer' => 'Please Enter Valid Pin Code',
            'pincode.digits' => 'Pin Code must be 6 digits',
            'mobile_number.required' => 'Please Enter Mobile Number',
            'mobile_number.integer' => 'Please Enter Valid Mobile Number',
            'mobile_number.digits_between' => 'Please Enter Valid Mobile Number',
            'alternate_number.integer' => 'Please Enter Valid Phone Number',
            'alternate_number.digits_between' => 'Please Enter Valid Phone Number',
            'alternate_number.different' => 'A different phone number should be provided as an alternative',
            'email' => 'Please Provide Valid Email ID',
            'aadhaar.integer' => 'Given is not a valid number',
            'aadhaar.digits' => 'Aadhaar Number must have 16 digits',
            'license_number.min' => 'Please Verify the License Number',
            'license_number.max' => 'License Number Length Exceeds Maximum Limits',
            'license_validity.required_with' => 'License Validity Required',
            'doj.before_or_equal' => 'Future date not allowed',
            'ifsc.alpha_num' => 'Only Numbers and Alphabets are allowed',
            'ifsc.size' => 'IFSC must have 11 chars',
            'acc_number.alpha_num' => 'Only Numbers and Alphabets are allowed',
            'acc_number.max' => 'Size exceeds Maximum Limit'            
        ]; 
    }
}
