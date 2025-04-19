<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Appointment extends Model
{
    use HasFactory;

    protected $fillable = [
        'doctor_id',
        'patient_id',
        'scheduled_time',
        'status'
    ];

    protected $with = ['doctor', 'patient']; // Always eager load doctor and patient relationship

    protected $appends = ['doctor_name', 'doctor_specialization', 'doctor_department', 'patient_name', 'patient_medical_history', 'patient_emergency_contact'];

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function queuePosition()
    {
        return $this->hasOne(QueuePosition::class);
    }

    public function getDoctorNameAttribute()
    {
        return $this->doctor->user->name ?? null;
    }

    public function getDoctorSpecializationAttribute()
    {
        return $this->doctor->specialization ?? null;
    }

    public function getDoctorDepartmentAttribute()
    {
        return $this->doctor->department ?? null;
    }

    public function getPatientNameAttribute()
    {
        return $this->patient->user->name ?? null;
    }

    public function getPatientMedicalHistoryAttribute()
    {
        return $this->patient->medicalHistory ?? null;
    }

    public function getPatientEmergencyContactAttribute()
    {
        return $this->patient->emergencyContact ?? null;
    }
}