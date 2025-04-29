<?php

namespace App\Http\Requests\Appointment;

use Illuminate\Foundation\Http\FormRequest;

class StoreAppointmentRequest extends FormRequest
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
            'patient_id' => 'required|exists:patients,id',
            'scheduled_time' => 'required|date_format:Y-m-d\TH:i|after:now'
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
            'patient_id.required' => 'Patient is required.',
            'patient_id.exists' => 'Invalid patient selected.',
            'scheduled_time.required' => 'Scheduled time is required.',
            'scheduled_time.date_format' => 'Invalid date and time format.',
            'scheduled_time.after' => 'Scheduled time must be in the future.',
            'status.required' => 'Status is required.',
            'status.in' => 'Invalid appointment status.'
        ];
    }
}