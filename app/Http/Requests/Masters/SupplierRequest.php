<?php

namespace App\Http\Requests\Masters;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Rules\RequiredIfGstRegistered;
use App\Enums\GstType;
use App\Enums\TcsStatus;
use App\Enums\TdsStatus;

class SupplierRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true; // Handle authorization via policies if required
    }

    /*
    |--------------------------------------------------------------------------
    | Sanitize Input
    |--------------------------------------------------------------------------
    */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'code'  => strtoupper(trim($this->code)),
            'name'  => trim($this->name),
            'gstin' => $this->gstin ? strtoupper(trim($this->gstin)) : null,
            'pan'   => $this->pan ? strtoupper(trim($this->pan)) : null,
            'ifsc'  => $this->ifsc ? strtoupper(trim($this->ifsc)) : null,
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        $supplierId = $this->route('supplier')?->id;

        return [
            /*
            |--------------------------------------------------------------------------
            | Profile Information
            |--------------------------------------------------------------------------
            */

            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('suppliers', 'name')->ignore($supplierId),
            ],

            'code' => [
                'required',
                'string',
                'max:20',
                Rule::unique('suppliers', 'code')->ignore($supplierId),
            ],

            'address'        => ['required', 'string'],
            'city'           => ['required', 'string', 'max:50'],
            'state_id'       => ['required', 'exists:states,id'],
            'landmark'       => ['nullable', 'string', 'max:255'],
            'pin_code'       => ['required', 'digits:6'],

            'contact_number' => ['nullable', 'string', 'max:15'],
            'email'          => ['nullable', 'email', 'max:100'],

             /*
            |--------------------------------------------------------------------------
            | Finance Information
            |--------------------------------------------------------------------------
            */

            'credit_limit' => ['nullable', 'numeric', 'min:0'],
            'credit_days'  => ['nullable', 'integer', 'min:0', 'max:365'],

            'gst_type' => [
                'required',
                Rule::in(array_column(GstType::cases(), 'value')),
            ],

            'gstin' => [                
                Rule::requiredIf(fn () =>
                    in_array($this->gst_type, [
                        GstType::INTRASTATE_REGISTERED->value,
                        GstType::INTERSTATE_REGISTERED->value,
                    ])
                ),
                'nullable',
                'string',
                'size:15',
                'regex:/^[0-9A-Z]{15}$/',
            ],

            'pan' => [
                'nullable',
                'string',
                'size:10',
                'regex:/^[A-Z]{5}[0-9]{4}[A-Z]{1}$/',
            ],

            'payment_terms' => ['nullable', 'string', 'max:255'],

            'tcs_status' => [
                'required',
                Rule::in(array_column(TcsStatus::cases(), 'value')),
            ],

            'tds_status' => [
                'required',
                Rule::in(array_column(TdsStatus::cases(), 'value')),
            ],

            /*
            |--------------------------------------------------------------------------
            | Banking Information
            |--------------------------------------------------------------------------
            */

            'bank_id'   => ['required', 'exists:banks,id'],
            'branch_id' => ['required', 'exists:bank_branches,id'],

            'ifsc' => [
                'required',
                'string',
                'size:11',
                'regex:/^[A-Z]{4}0[A-Z0-9]{6}$/',
            ],

            'account_holder' => ['required', 'string', 'max:100'],
            'account_number' => ['required', 'string', 'max:30'],
        ];
    }

    public function messages(): array
    {
        return [

            /*
            |--------------------------------------------------------------------------
            | Profile Information
            |--------------------------------------------------------------------------
            */

            'name.required' => 'Supplier name is required.',
            'name.string'   => 'Supplier name must be a valid text value.',
            'name.max'      => 'Supplier name may not exceed 100 characters.',
            'name.unique'   => 'This supplier name already exists.',

            'code.required' => 'Supplier code is required.',
            'code.string'   => 'Supplier code must be a valid text value.',
            'code.max'      => 'Supplier code may not exceed 20 characters.',
            'code.unique'   => 'This supplier code already exists.',

            'address.required' => 'Address is required.',
            'address.string'   => 'Address must be a valid text value.',

            'city.required' => 'City is required.',
            'city.string'   => 'City must be a valid text value.',
            'city.max'      => 'City may not exceed 50 characters.',

            'state_id.required' => 'State is required.',
            'state_id.exists'   => 'Selected state is invalid.',

            'landmark.string' => 'Landmark must be a valid text value.',
            'landmark.max'    => 'Landmark may not exceed 255 characters.',

            'pin_code.required' => 'PIN code is required.',
            'pin_code.digits'   => 'PIN code must be exactly 6 digits.',

            'contact_number.string' => 'Contact number must be a valid text value.',
            'contact_number.max'    => 'Contact number may not exceed 15 characters.',

            'email.email' => 'Please enter a valid email address.',
            'email.max'   => 'Email may not exceed 100 characters.',


            /*
            |--------------------------------------------------------------------------
            | Finance Information
            |--------------------------------------------------------------------------
            */

            'credit_limit.numeric' => 'Credit limit must be a numeric value.',
            'credit_limit.min'     => 'Credit limit cannot be negative.',

            'credit_days.integer' => 'Credit days must be a whole number.',
            'credit_days.min'     => 'Credit days cannot be negative.',
            'credit_days.max'     => 'Credit days cannot exceed 365 days.',

            'gst_type.required' => 'GST type is required.',
            'gst_type.in'       => 'Selected GST type is invalid.',

            'gstin.required' => 'GSTIN is required when GST is registered.',
            'gstin.string'   => 'GSTIN must be a valid text value.',
            'gstin.size'     => 'GSTIN must be exactly 15 characters.',
            'gstin.regex'    => 'GSTIN must be a valid 15-character GST number.',

            'pan.string' => 'PAN must be a valid text value.',
            'pan.size'   => 'PAN must be exactly 10 characters.',
            'pan.regex'  => 'PAN must follow standard PAN format (e.g., ABCDE1234F).',

            'payment_terms.string' => 'Payment terms must be a valid text value.',
            'payment_terms.max'    => 'Payment terms may not exceed 255 characters.',

            'tcs_status.required' => 'TCS status is required.',
            'tcs_status.in'       => 'Selected TCS status is invalid.',

            'tds_status.required' => 'TDS status is required.',
            'tds_status.in'       => 'Selected TDS status is invalid.',


            /*
            |--------------------------------------------------------------------------
            | Banking Information
            |--------------------------------------------------------------------------
            */

            'bank_id.required' => 'Bank name is required.',
            'bank_id.exists'   => 'Selected bank is invalid.',

            'branch_id.required' => 'Bank branch is required.',
            'branch_id.exists'   => 'Selected bank branch is invalid.',

            'ifsc.required' => 'IFSC code is required.',
            'ifsc.string'   => 'IFSC code must be a valid text value.',
            'ifsc.size'     => 'IFSC code must be exactly 11 characters.',
            'ifsc.regex'    => 'IFSC must follow standard banking format (e.g., HDFC0001234).',

            'account_holder.required' => 'Account holder name is required.',
            'account_holder.string'   => 'Account holder name must be a valid text value.',
            'account_holder.max'      => 'Account holder name may not exceed 100 characters.',

            'account_number.required' => 'Account number is required.',
            'account_number.string'   => 'Account number must be a valid text value.',
            'account_number.max'      => 'Account number may not exceed 30 characters.',
        ];
    }
}
