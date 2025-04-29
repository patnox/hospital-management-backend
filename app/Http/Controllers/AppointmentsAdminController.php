<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\Appointment\StoreAppointmentRequest;
use App\Http\Requests\Appointment\UpdateAppointmentRequest;
use App\Models\Appointment;
use App\Models\DoctorAvailability;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\QueuePosition;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AppointmentsAdminController extends Controller
{
    /**
     * Display a listing of the appointments.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $perPage = $request->query('per_page', 10);
        $doctorId = $request->query('doctor_id');
        $patientId = $request->query('patient_id');
        $status = $request->query('status');
        $search = $request->query('search');

        $query = Appointment::query()
            ->with(['doctor', 'patient', 'queuePosition'])
            ->when($doctorId, fn($q, $doctorId) => $q->where('doctor_id', $doctorId))
            ->when($patientId, fn($q, $patientId) => $q->where('patient_id', $patientId))
            ->when($status, fn($q, $status) => $q->where('status', $status))
            ->when($search, fn($q, $search) => $q->where(function($q) use ($search) {
                $q->whereHas('doctor', fn($q) => $q->where('name', 'like', "%$search%")
                    ->orWhere('specialization', 'like', "%$search%"))
                    ->orWhereHas('patient', fn($q) => $q->where('name', 'like', "%$search%"))
                    ->orWhere('scheduled_time', 'like', "%$search%");
            }))
            ->orderBy('scheduled_time')
            ->orderBy('status');

        $appointments = $query->paginate($perPage);

        return response()->json([
            'appointments' => $appointments,
            'current_page' => $appointments->currentPage(),
            'per_page' => $appointments->perPage(),
            'total' => $appointments->total(),
            'last_page' => $appointments->lastPage()
        ]);
    }

    /**
     * Store a newly created appointment.
     *
     * @param  \App\Http\Requests\Appointment\StoreAppointmentRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreAppointmentRequest $request)
    {
        $validatedData = $request->validated();

        // Check if doctor is available at the scheduled time
        $availability = DoctorAvailability::where('doctor_id', $validatedData['doctor_id'])
            ->where('day', date('Y-m-d', strtotime($validatedData['scheduled_time'])))
            ->where('start_time', '<=', date('H:i', strtotime($validatedData['scheduled_time'])))
            ->where('end_time', '>=', date('H:i', strtotime($validatedData['scheduled_time'])))
            ->where('is_booked', false)
            ->first();

        if (!$availability) {
            return response()->json([
                'message' => 'Doctor is not available at the selected time'
            ], 422);
        }

        // Create appointment
        $appointment = Appointment::create($validatedData);

        // Add to queue
        QueuePosition::create([
            'appointment_id' => $appointment->id,
            'position' => 1,
            'status' => 'waiting'
        ]);

        return response()->json([
            'message' => 'Appointment created successfully',
            'appointment' => $appointment->load(['doctor', 'patient', 'queuePosition'])
        ], 201);
    }

    /**
     * Update the specified appointment.
     *
     * @param  \App\Http\Requests\Appointment\UpdateAppointmentRequest  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(UpdateAppointmentRequest $request, $id)
    {
        $appointment = Appointment::findOrFail($id);

        // Check if doctor is available at the new scheduled time
        if ($request->input('scheduled_time')) {
            $availability = DoctorAvailability::where('doctor_id', $request->input('doctor_id', $appointment->doctor_id))
                ->where('day', date('Y-m-d', strtotime($request->input('scheduled_time'))))
                ->where('start_time', '<=', date('H:i', strtotime($request->input('scheduled_time'))))
                ->where('end_time', '>=', date('H:i', strtotime($request->input('scheduled_time'))))
                ->where('is_booked', false)
                ->first();

            if (!$availability) {
                return response()->json([
                    'message' => 'Doctor is not available at the selected time'
                ], 422);
            }
        }

        $appointment->update($request->validated());

        return response()->json([
            'message' => 'Appointment updated successfully',
            'appointment' => $appointment->load(['doctor', 'patient', 'queuePosition'])
        ]);
    }

    /**
     * Cancel the specified appointment.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function cancel($id)
    {
        $appointment = Appointment::findOrFail($id);

        if ($appointment->status !== 'pending') {
            return response()->json([
                'message' => 'Only pending appointments can be cancelled'
            ], 422);
        }

        $appointment->update([
            'status' => 'cancelled'
        ]);

        // Update queue position
        $queuePosition = QueuePosition::where('appointment_id', $id)->first();
        if ($queuePosition) {
            $queuePosition->delete();
        }

        return response()->json([
            'message' => 'Appointment cancelled successfully'
        ]);
    }

    /**
     * Mark the specified appointment as attended.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function markAttended($id)
    {
        $appointment = Appointment::findOrFail($id);

        if ($appointment->status !== 'called') {
            return response()->json([
                'message' => 'Only called appointments can be marked as attended'
            ], 422);
        }

        $appointment->update([
            'status' => 'attended'
        ]);

        // Update queue position
        $queuePosition = QueuePosition::where('appointment_id', $id)->first();
        if ($queuePosition) {
            $queuePosition->update([
                'status' => 'attended'
            ]);
        }

        return response()->json([
            'message' => 'Appointment marked as attended'
        ]);
    }

    /**
     * Call the next patient in the queue.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function callNextPatient()
    {
        $nextPatient = QueuePosition::where('status', 'waiting')
            ->orderBy('position')
            ->first();

        if (!$nextPatient) {
            return response()->json([
                'message' => 'No patients waiting in queue'
            ], 404);
        }

        $nextPatient->update([
            'status' => 'called',
            'called_at' => now()
        ]);

        return response()->json([
            'message' => 'Next patient called successfully',
            'patient' => $nextPatient->load('appointment.patient')
        ]);
    }
}