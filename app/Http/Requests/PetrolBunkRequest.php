<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PetrolBunkRequest extends FormRequest
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
        // When updating, ignore unique rule for the current record
        $petrolBunkId = $this->route('bunk')?->id;

        return [
            'name'           => 'bail|required|string|max:100|unique:petrol_bunks,name,' . $petrolBunkId,
            'address'        => 'bail|required|string|max:255',
            'pin_code'       => 'bail|nullable|digits:6',
            'contact_number' => [
                'bail',
                'nullable',
                // 'regex:/^(\+?\d{1,3}[- ]?)?\d{6,15}$/',
            ],
            'email'          => 'bail|nullable|email|max:100',
            // 'pan'            => 'bail|nullable|regex:/^[A-Z]{5}[0-9]{4}[A-Z]{1}$/|size:10',
            // 'gst_number'     => 'bail|nullable|regex:/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/|size:15',
            'pan'            => 'bail|nullable|size:10',
            'gst_number'     => 'bail|nullable|size:15',
            'tds_status'     => 'bail|required|in:NOT_APPLICABLE,APPLICABLE,APPLIED',
            'bank_id'        => 'bail|required|exists:banks,id',
            'branch_id'      => 'bail|required|exists:bank_branches,id',
            'account_holder' => 'bail|required|string|max:100',
            'account_number' => 'bail|required|string|max:30',
        ];
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'name.required'           => 'The petrol bunk name is required.',
            'name.unique'             => 'The petrol bunk name has already been taken.',
            'address.required'        => 'The address is required.',
            'pin_code.digits'         => 'The pin code must be exactly 6 digits.',
            'email.email'             => 'The email seems to be in an incorrect format.',
            'pan.size'                => 'The PAN must be exactly 10 characters.',
            'gst_number.size'         => 'The GST number must be exactly 15 characters.',
            'tds_status.required'     => 'The TDS status is required.',
            'bank_id.required'        => 'The bank name is required.',
            'bank_id.exists'          => 'Bank seems not selected.',
            'branch_id.required'      => 'The branch name is required.',
            'branch_id.exists'        => 'Branch seems not selected.',
            'account_holder.required' => 'The account holder name is required.',
            'account_number.required' => 'The account number is required.',
            // 'pan.regex'            => 'The PAN format is invalid.',
            // 'gst_number.regex'     => 'The GST number format is invalid.',
            // 'contact_number.regex' => 'The contact number format is invalid.',
        ];
    }
}
