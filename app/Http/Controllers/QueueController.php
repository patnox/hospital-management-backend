<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\QueuePosition;
use Illuminate\Http\Request;

class QueueController extends Controller
{
    public function joinQueue(Request $request, $appointmentId)
    {
        $appointment = Appointment::findOrFail($appointmentId);
        
        if ($appointment->status !== 'pending') {
            return response()->json(['message' => 'Appointment is not pending'], 400);
        }

        $lastPosition = QueuePosition::where('appointment_id', $appointmentId)
            ->where('status', 'waiting')
            ->max('position') ?? 0;

        $queuePosition = QueuePosition::create([
            'appointment_id' => $appointmentId,
            'position' => $lastPosition + 1,
            'status' => 'waiting'
        ]);

        return response()->json($queuePosition);
    }

    public function callNext(Request $request, $appointmentId)
    {
        $queuePositions = QueuePosition::where('appointment_id', $appointmentId)
            ->where('status', 'waiting')
            ->orderBy('position')
            ->get();

        if ($queuePositions->isEmpty()) {
            return response()->json(['message' => 'No patients in queue'], 404);
        }

        $nextPatient = $queuePositions->first();
        $nextPatient->update([
            'called_at' => now(),
            'status' => 'called'
        ]);

        return response()->json($nextPatient);
    }

    public function getCurrentPosition(Request $request, $appointmentId)
    {
        $queuePosition = QueuePosition::where('appointment_id', $appointmentId)
            ->where('status', 'waiting')
            ->orderBy('position')
            ->get();

        $position = 0;
        foreach ($queuePosition as $pos) {
            if ($pos->id === $request->user()->patient->appointments()
                ->where('id', $appointmentId)
                ->first()
                ->queuePosition
                ->id) {
                break;
            }
            $position++;
        }

        return response()->json(['position' => $position + 1]);
    }
}