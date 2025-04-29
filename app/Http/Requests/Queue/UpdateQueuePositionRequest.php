<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateQueuePositionRequest extends FormRequest
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
        return [
            'status' => 'required|in:waiting,called,attended',
            'position' => 'sometimes|integer|min:1',
            'called_at' => 'sometimes|date_format:Y-m-d H:i:s|nullable',
            'attended_at' => 'sometimes|date_format:Y-m-d H:i:s|nullable'
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
            'status.required' => 'Queue position status is required',
            'status.in' => 'Invalid queue position status',
            'position.integer' => 'Position must be an integer',
            'position.min' => 'Position must be at least 1',
            'called_at.date_format' => 'Invalid called at date format',
            'attended_at.date_format' => 'Invalid attended at date format'
        ];
    }

    /**
     * Prepare the status for validation.
     *
     * @param  mixed  $value
     * @return string
     */
    public function prepareForValidation()
    {
        $this->merge([
            'status' => strtolower($this->status)
        ]);
    }
}