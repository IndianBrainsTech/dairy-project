<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserRequest extends FormRequest
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
    public function rules(): array
    {
        $userId = $this->route('user')?->id; // Works for both create and update

        return [
            'name' => [
                'required',
                'string',
                'max:50',
                'unique:users,name' . ($userId ? ',' . $userId : ''),
            ],
            'role_id' => 'required|exists:roles,id',
            'email' => 'nullable|email:rfc,dns',
            'user_name' => [
                'required',
                'string',
                'max:50',
                'unique:users,user_name' . ($userId ? ',' . $userId : ''),
            ],
            'password' => $userId ? 'nullable|string|min:4' : 'required|string|min:4',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ];
    }
}
