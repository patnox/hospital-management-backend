<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DoctorAvailability extends Model
{
    use HasFactory;

    protected $fillable = [
        'doctor_id',
        'day',
        'start_time',
        'end_time',
        'is_booked'
    ];

    protected $with = ['doctor']; // Always eager load doctor relationship

    protected $appends = ['doctor_name', 'doctor_specialization', 'doctor_department'];

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
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
}