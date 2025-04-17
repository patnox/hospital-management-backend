<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'required|string',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6',
            'role' => 'required|in:admin,doctor,patient',
            'specialization' => 'required_if:role,doctor',
            'department' => 'required_if:role,doctor',
            'medical_history' => 'required_if:role,patient',
            'emergency_contact' => 'required_if:role,patient'
        ];
    }
}