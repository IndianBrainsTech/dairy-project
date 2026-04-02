<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BankBranchRequest extends FormRequest
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
        $branchId = $this->route('branch')?->id;

        return [
            'bank_id' => 'bail|required|exists:banks,id',

            'name' => [
                'bail',
                'required',
                'string',
                'max:50',
                Rule::unique('bank_branches')
                    ->where('bank_id', $this->bank_id)
                    ->ignore($branchId),
            ],

            'ifsc' => [
                'bail',
                'required',
                'string',
                'max:20',
                Rule::unique('bank_branches')
                    ->ignore($branchId),
            ],
        ];
    }

    /**
     * Customized validation messages.
     */
    public function messages(): array
    {
        return [
            'bank_id.required' => 'Please select a bank.',
            'name.required'    => 'Branch name is required.',
            'name.unique'      => 'This branch name already exists for the selected bank.',
            'ifsc.required'    => 'IFSC code is required.',
            'ifsc.unique'      => 'This IFSC code is already used.',
        ];
    }
}
