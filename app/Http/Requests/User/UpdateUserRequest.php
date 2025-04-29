<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
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
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|unique:users,email,' . $this->user,
            'role' => 'sometimes|required|in:admin,doctor,patient',
            'specialization' => 'sometimes|string|max:100',
            'department' => 'sometimes|string|max:100',
            'medical_history' => 'sometimes|string',
            'emergency_contact' => 'sometimes|string|max:255'
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
            'email.unique' => 'This email address is already in use.',
            'role.in' => 'Invalid user role specified.',
            'name.max' => 'Name cannot be longer than 255 characters.',
            'email.email' => 'Please enter a valid email address.',
            'specialization.max' => 'Specialization cannot be longer than 100 characters.',
            'department.max' => 'Department cannot be longer than 100 characters.',
            'emergency_contact.max' => 'Emergency contact cannot be longer than 255 characters.'
        ];
    }
}