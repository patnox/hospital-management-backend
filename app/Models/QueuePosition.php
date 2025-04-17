<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class QueuePosition extends Model
{
    use HasFactory;

    protected $fillable = [
        'appointment_id',
        'position',
        'called_at',
        'status'
    ];

    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }
}