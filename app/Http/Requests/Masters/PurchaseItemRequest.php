<?php

namespace App\Http\Requests\Masters;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;
use App\Enums\MasterStatus;
use App\Enums\TaxType;

class PurchaseItemRequest extends FormRequest
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
        $itemId = $this->route('purchase_item')?->id;

        return [

            /*
            |--------------------------------------------------------------------------
            | Basic Info
            |--------------------------------------------------------------------------
            */

            'code' => [
                'required',
                'string',
                'max:20',
                Rule::unique('purchase_items', 'code')->ignore($itemId),
            ],

            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('purchase_items', 'name')->ignore($itemId),
            ],

            'group_id' => [
                'required',
                'integer',
                Rule::exists('purchase_item_groups', 'id'),
            ],

            /*
            |--------------------------------------------------------------------------
            | Tax Info
            |--------------------------------------------------------------------------
            */

            'hsn_code' => [
                'required',
                'string',
                'max:10',                
            ],

            'tax_type' => [
                'required',
                new Enum(TaxType::class),
            ],

            'gst' => [
                'nullable',
                'numeric',
                'min:0',
                'max:100',
                'required_if:tax_type,TAXABLE',
            ],

            'sgst' => [
                'nullable',
                'numeric',
                'min:0',
                'max:100',
                'required_if:tax_type,TAXABLE',
            ],

            'cgst' => [
                'nullable',
                'numeric',
                'min:0',
                'max:100',
                'required_if:tax_type,TAXABLE',
            ],

            'igst' => [
                'nullable',
                'numeric',
                'min:0',
                'max:100',
                'required_if:tax_type,TAXABLE',
            ],            
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | ERP-Level Tax Integrity Validation
    |--------------------------------------------------------------------------
    */

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {

            if ($this->tax_type === TaxType::TAXABLE) {

                /*
                 | Ensure tax consistency with HSN master
                 */
                /*
                if (
                    (float)$this->sgst !== (float)$gstMaster->sgst ||
                    (float)$this->cgst !== (float)$gstMaster->cgst ||
                    (float)$this->igst !== (float)$gstMaster->igst ||
                    (float)$this->gst  !== (float)$gstMaster->gst
                ) {
                    $validator->errors()->add(
                        'gst',
                        'Tax values must match GST master configuration.'
                    );
                }
                */

                /*
                 | Ensure arithmetic integrity
                 | sgst + cgst must equal gst
                 */

                if (
                    (float)$this->sgst + (float)$this->cgst !== (float)$this->gst
                ) {
                    $validator->errors()->add(
                        'gst',
                        'SGST + CGST must equal to GST.'
                    );
                }
            }

            /*
             | EXEMPTED must not contain tax values
             */

            if ($this->tax_type === TaxType::EXEMPTED) {

                if (
                    $this->gst !== null ||
                    $this->sgst !== null ||
                    $this->cgst !== null ||
                    $this->igst !== null
                ) {
                    $validator->errors()->add(
                        'gst',
                        'Exempted items cannot contain tax values.'
                    );
                }
            }
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Validation messages
    |--------------------------------------------------------------------------
    */

    public function messages(): array
    {
        return [

            /*
            |--------------------------------------------------------------------------
            | Basic Information
            |--------------------------------------------------------------------------
            */

            'code.required' => 'Item code is required.',
            'code.max' => 'Item code must not exceed 20 characters.',
            'code.unique' => 'This item code already exists.',

            'name.required' => 'Item name is required.',
            'name.max' => 'Item name must not exceed 100 characters.',

            'group_id.required' => 'Item group is required.',
            'group_id.exists' => 'Selected item group is invalid.',

            /*
            |--------------------------------------------------------------------------
            | Tax Information
            |--------------------------------------------------------------------------
            */

            'hsn_code.required' => 'HSN code is required.',            

            'tax_type.required' => 'Tax type is required.',

            'gst.required_if' => 'GST rate is required for taxable items.',
            'gst.numeric' => 'GST must be a valid numeric value.',
            'gst.min' => 'GST cannot be negative.',
            'gst.max' => 'GST cannot exceed 100%.',

            'sgst.required_if' => 'SGST rate is required for taxable items.',
            'sgst.numeric' => 'SGST must be a valid numeric value.',
            'sgst.min' => 'SGST cannot be negative.',
            'sgst.max' => 'SGST cannot exceed 100%.',

            'cgst.required_if' => 'CGST rate is required for taxable items.',
            'cgst.numeric' => 'CGST must be a valid numeric value.',
            'cgst.min' => 'CGST cannot be negative.',
            'cgst.max' => 'CGST cannot exceed 100%.',

            'igst.required_if' => 'IGST rate is required for taxable items.',
            'igst.numeric' => 'IGST must be a valid numeric value.',
            'igst.min' => 'IGST cannot be negative.',
            'igst.max' => 'IGST cannot exceed 100%.',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Sanitize Input
    |--------------------------------------------------------------------------
    */

    protected function prepareForValidation(): void
    {
        $this->merge([
            'code' => strtoupper(trim($this->code)),
            'name' => trim($this->name),
        ]);
    }
}
