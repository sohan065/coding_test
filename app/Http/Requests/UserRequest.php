<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'bail|required|min:3|max:100',
            'account_type' => 'bail|required|string|in:Individual,Business',
            'balance' => 'bail|required|numeric',
            'email' => 'bail|required|email|unique:users',
            'password' => 'bail|required|string',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'The name field is required.',
            'name.min' => 'The name must be at least :min characters.',
            'name.max' => 'The name may not be greater than :max characters.',
            'account_type.required' => 'The account type field is required.',
            'account_type.in' => 'The account type must be either Individual or Business.',
            'balance.required' => 'The balance field is required.',
            'balance.numeric' => 'The balance must be a number.',
            'email.required' => 'The email field is required.',
            'email.email' => 'The email must be a valid email address.',
            'email.unique' => 'The email has already been taken.',
            'password.required' => 'The password field is required.',
            'password.string' => 'The password must be a string.',
        ];
    }
}
