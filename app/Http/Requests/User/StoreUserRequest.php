<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
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
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:6',
            'role' => 'required|in:admin,doctor,patient',
            'specialization' => 'required_if:role,doctor|string|max:100',
            'department' => 'required_if:role,doctor|string|max:100',
            'medical_history' => 'required_if:role,patient|string',
            'emergency_contact' => 'required_if:role,patient|string|max:255'
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
            'name.required' => 'Name is required.',
            'name.max' => 'Name cannot be longer than 255 characters.',
            'email.required' => 'Email is required.',
            'email.email' => 'Please enter a valid email address.',
            'email.unique' => 'This email address is already in use.',
            'password.required' => 'Password is required.',
            'password.min' => 'Password must be at least 6 characters long.',
            'role.required' => 'User role is required.',
            'role.in' => 'Invalid user role specified.',
            'specialization.required_if' => 'Specialization is required for doctors.',
            'specialization.max' => 'Specialization cannot be longer than 100 characters.',
            'department.required_if' => 'Department is required for doctors.',
            'department.max' => 'Department cannot be longer than 100 characters.',
            'medical_history.required_if' => 'Medical history is required for patients.',
            'emergency_contact.required_if' => 'Emergency contact is required for patients.',
            'emergency_contact.max' => 'Emergency contact cannot be longer than 255 characters.'
        ];
    }
}