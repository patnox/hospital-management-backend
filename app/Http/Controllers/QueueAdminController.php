<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateQueuePositionRequest;
use App\Models\Doctor;
use App\Models\Patients;
use App\Models\QueuePosition;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class QueueAdminController extends Controller
{
    /**
     * Display a listing of the queue positions.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $filters = $request->validate([
            'doctor_id' => 'nullable|exists:doctors,id',
            'status' => 'nullable|in:waiting,called,attended',
            'page' => 'nullable|integer',
            'per_page' => 'nullable|integer'
        ]);

        $query = QueuePosition::query()
            ->with(['appointment.doctor', 'appointment.patient'])
            ->when($filters['doctor_id'], fn($q, $doctorId) => 
                $q->whereHas('appointment', fn($q) => 
                    $q->where('doctor_id', $doctorId)
                )
            )
            ->when($filters['status'], fn($q, $status) => 
                $q->where('status', $status)
            )
            ->orderBy('position')
            ->orderBy('created_at');

        if ($request->has('page')) {
            return $query->paginate($filters['per_page'] ?? 10);
        }

        return $query->get();
    }

    /**
     * Call the next patient in the queue.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function callNextPatient()
    {
        DB::transaction(function () {
            $nextPosition = QueuePosition::where('status', 'waiting')
                ->whereNull('called_at')
                ->orderBy('position')
                ->first();

            if (!$nextPosition) {
                return response()->json([
                    'message' => 'No patients waiting in queue'
                ], 404);
            }

            $nextPosition->update([
                'status' => 'called',
                'called_at' => now()
            ]);

            return response()->json([
                'message' => 'Patient called successfully',
                'patient' => $nextPosition->load(['appointment.doctor', 'appointment.patient'])
            ]);
        });
    }

    /**
     * Call a specific patient in the queue.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function callPatient($id)
    {
        $position = QueuePosition::where('id', $id)
            ->where('status', 'waiting')
            ->first();

        if (!$position) {
            return response()->json([
                'message' => 'Patient not found or already called'
            ], 404);
        }

        DB::transaction(function () use ($position) {
            $position->update([
                'status' => 'called',
                'called_at' => now()
            ]);
        });

        return response()->json([
            'message' => 'Patient called successfully',
            'patient' => $position->load(['appointment.doctor', 'appointment.patient'])
        ]);
    }

    /**
     * Mark a patient as attended.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function markAttended($id)
    {
        $position = QueuePosition::where('id', $id)
            ->where('status', 'called')
            ->first();

        if (!$position) {
            return response()->json([
                'message' => 'Patient not found or not in called status'
            ], 404);
        }

        DB::transaction(function () use ($position) {
            $position->update([
                'status' => 'attended',
                'attended_at' => now()
            ]);
        });

        return response()->json([
            'message' => 'Patient marked as attended',
            'patient' => $position->load(['appointment.doctor', 'appointment.patient'])
        ]);
    }

    /**
     * Get all doctors.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDoctors()
    {
        $doctors = Doctor::with('user')
            ->select(['id', 'user_id', 'specialization', 'department'])
            ->get();

        return response()->json([
            'doctors' => $doctors
        ]);
    }
}