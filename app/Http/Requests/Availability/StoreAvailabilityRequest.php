<?php

namespace App\Http\Requests\Availability;

use Illuminate\Foundation\Http\FormRequest;

class StoreAvailabilityRequest extends FormRequest
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
            'doctor_id' => 'required|exists:doctors,id',
            'day' => 'required|date|after_or_equal:today',
            'start_time' => 'required|date_format:H:i|before:end_time',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'is_booked' => 'sometimes|boolean'
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
            'doctor_id.required' => 'Doctor is required.',
            'doctor_id.exists' => 'Invalid doctor selected.',
            'day.required' => 'Day is required.',
            'day.date' => 'Invalid date format.',
            'day.after_or_equal' => 'Day must be today or in the future.',
            'start_time.required' => 'Start time is required.',
            'start_time.date_format' => 'Invalid time format.',
            'start_time.before' => 'Start time must be before end time.',
            'end_time.required' => 'End time is required.',
            'end_time.date_format' => 'Invalid time format.',
            'end_time.after' => 'End time must be after start time.',
            'is_booked.boolean' => 'Invalid booking status.'
        ];
    }
}