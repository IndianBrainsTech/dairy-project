<?php

namespace App\Http\Requests\Transactions;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Enums\CreditNoteReason;

class CreditNoteRequest extends FormRequest
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
        $creditNoteId = $this->route('credit_note')?->id;
        
        return [

            /*
            |--------------------------------------------------------------------------
            | Basic Info
            |--------------------------------------------------------------------------
            */
            
            'document_date' => [
                'required',
                'date',
            ],

            'customer_id' => [
                'required',
                'integer',
                Rule::exists('customers', 'id'),
            ],

            'reason' => [
                'required',
                Rule::in(array_column(CreditNoteReason::cases(), 'value')),
            ],

            'narration' => [
                'nullable',
                'string',
            ],

            'amount' => [
                'required',
                'numeric',
                'min:0.01',
            ],

            /*
            |--------------------------------------------------------------------------
            | Items
            |--------------------------------------------------------------------------
            */

            'items' => [
                'required',
                'array',
                'min:1',
            ],

            'items.*.invoice_number' => [
                'required',
                'string',
                'max:20',
            ],

            'items.*.adjusted_amount' => [
                'required',
                'numeric',
                'min:0.01',
            ],

            'items.*.record_id' => [
                'integer',
            ],
            'items.*.invoice_date' => [
                'required',
            ],
            'items.*.invoice_amount' => [
                'required',
            ],
            'items.*.paid_amount' => [
                'nullable',
            ],
            'items.*.outstanding_amount' => [
                'required',
            ],
        ];        
    }
    
    /*
    |--------------------------------------------------------------------------
    | Custom Validation
    |--------------------------------------------------------------------------
    */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {

            $items = $this->input('items', []);
            $amount = (float) $this->input('amount', 0);

            $totalAdjusted = collect($items)
                ->sum(function ($item) {
                    return (float) ($item['adjusted_amount'] ?? 0);
                });

            // Optional: round to avoid floating precision issues
            if (round($totalAdjusted, 2) !== round($amount, 2)) {
                $validator->errors()->add(
                    'amount',
                    'Total adjusted amount must be equal to the credit note amount.'
                );
            }
        });
    }

    public function messages(): array
    {
        return [
            'items.required' => 'At least one invoice must be adjusted.',
            'items.min' => 'At least one invoice must be adjusted.',
            'items.*.adjusted_amount.min' => 'Adjusted amount must be greater than zero.',
        ];
    }
}
