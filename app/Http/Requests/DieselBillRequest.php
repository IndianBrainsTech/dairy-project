<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DieselBillRequest extends FormRequest
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
        // Get ID if updating an existing record
        $dieselBillId = $this->route('diesel_bill')?->id;

        return [            
            'document_date'  => 'bail|required|date',
            'bunk_id'        => 'bail|required|exists:petrol_bunks,id',
            'bunk_name'      => 'bail|required|string|max:100',
            'bill_number'    => 'bail|nullable|string|max:50',
            'bill_date'      => 'bail|required|date',
            'route_id'       => 'bail|required|exists:routes,id',
            'route_name'     => 'bail|required|string|max:50',
            'vehicle_id'     => 'bail|required|exists:vehicles,id',
            'vehicle_number' => 'bail|required|string|max:15',
            'driver_id'      => 'bail|nullable|exists:employees,id',
            'driver_name'    => 'bail|required|string|max:50',
            'fuel'           => 'bail|required|numeric|min:0',
            'rate'           => 'bail|required|numeric|min:0',
            'opening_km'     => 'bail|required|integer|min:0',
            'closing_km'     => [
                'bail',
                'required',
                'integer',
                'min:0',
                function ($attribute, $value, $fail) {
                    $opening = $this->input('opening_km');
                    if ($opening > 0 && $value <= $opening) {
                        $fail('Closing kilometer must be greater than Opening kilometer.');
                    }
                },
            ],
        ];
    }

    /**
     * Custom attribute names for error messages (optional).
     */
    public function attributes(): array
    {
        return [
            'bunk_id'       => 'petrol bunk',
            'bunk_name'     => 'petrol bunk',            
            'route_id'      => 'route',
            'route_name'    => 'route',
            'vehicle_id'    => 'vehicle',
            'driver_id'     => 'driver',
            'opening_km'    => 'opening kilometer',
            'closing_km'    => 'closing kilometer',
        ];
    }
}
