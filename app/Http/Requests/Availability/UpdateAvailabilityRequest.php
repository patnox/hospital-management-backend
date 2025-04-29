<?php

namespace App\Http\Requests\Availability;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAvailabilityRequest extends FormRequest
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
            'doctor_id' => 'sometimes|exists:doctors,id',
            'day' => 'sometimes|date|after_or_equal:today',
            'start_time' => 'sometimes|date_format:H:i:s|before:end_time',
            'end_time' => 'sometimes|date_format:H:i:s|after:start_time',
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
            'doctor_id.exists' => 'Invalid doctor selected.',
            'day.date' => 'Invalid date format.',
            'day.after_or_equal' => 'Day must be today or in the future.',
            'start_time.date_format' => 'Invalid time format.',
            'start_time.before' => 'Start time must be before end time.',
            'end_time.date_format' => 'Invalid time format.',
            'end_time.after' => 'End time must be after start time.',
            'is_booked.boolean' => 'Invalid booking status.'
        ];
    }
}