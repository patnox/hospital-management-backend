<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\DoctorAvailability;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class AppointmentController extends Controller
{
    public function index(Request $request)
    {
        Log::debug('hospitalsystem: Appointments: Got a list request ' . Auth::guard('sanctum')->user());
        // $user = $request->user();
        $user = Auth::guard('sanctum')->user();
        
        if ($user->role === 'patient') {
            return $user->patient->appointments;
        } elseif ($user->role === 'doctor') {
            return $user->doctor->appointments;
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'doctor_id' => 'required|exists:doctors,id',
            'patient_id' => 'required|exists:patients,id',
            'scheduled_time' => 'required|date_format:Y-m-d H:i:s'
        ]);

        $availability = DoctorAvailability::where([
            'doctor_id' => $request->doctor_id,
            'day' => date('Y-m-d', strtotime($request->scheduled_time))
        ])
        ->whereTime('start_time', '<=', date('H:i:s', strtotime($request->scheduled_time)))
        ->whereTime('end_time', '>=', date('H:i:s', strtotime($request->scheduled_time)))
        ->where('is_booked', false)
        ->first();

        if (!$availability) {
            return response()->json(['message' => 'Doctor is not available at this time'], 400);
        }

        $appointment = Appointment::create($request->all());
        $availability->update(['is_booked' => true]);

        return response()->json($appointment, 201);
    }

    public function update(Request $request, $id)
    {
        $appointment = Appointment::findOrFail($id);
        
        $request->validate([
            'status' => 'sometimes|in:pending,completed,cancelled'
        ]);

        $appointment->update($request->all());
        return response()->json($appointment);
    }

    // # Get availability for a specific doctor
    // // GET /appointments/availability?doctor_id=1

    // # Get availability for a date range
    // // GET /appointments/availability?start_date=2025-04-20&end_date=2025-04-26

    // # Get availability for a specific doctor and date range
    // // GET /appointments/availability?doctor_id=1&start_date=2025-04-20&end_date=2025-04-26
    public function getAvailability(Request $request)
    {
        $request->validate([
            'doctor_id' => 'sometimes|exists:doctors,id',
            'start_date' => 'sometimes|date_format:Y-m-d',
            'end_date' => 'sometimes|date_format:Y-m-d'
        ]);

        $query = DoctorAvailability::query()
            ->when($request->input('doctor_id'), function ($query, $doctorId) {
                return $query->where('doctor_id', $doctorId);
            })
            ->when($request->input('start_date'), function ($query, $startDate) {
                return $query->whereDate('day', '>=', $startDate);
            })
            ->when($request->input('end_date'), function ($query, $endDate) {
                return $query->whereDate('day', '<=', $endDate);
            })
            ->where('is_booked', false)
            ->orderBy('day')
            ->orderBy('start_time');

        return response()->json([
            'availability' => $query->get(),
            'message' => 'Doctor availability retrieved successfully'
        ]);
    }
}