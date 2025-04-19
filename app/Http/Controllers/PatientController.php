<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PatientController extends Controller
{
    public function __construct()
    {
        // $this->middleware('auth');
        $this->middleware('handle.expired.tokens');
        $this->middleware('role:doctor,patient,admin', ['only' => ['index', 'show']]);
    }

    public function index(Request $request)
    {
        if (!Auth::guard('sanctum')->user()->hasAnyRole(['doctor', 'patient', 'admin'])) {
            abort(403);
        }

        $query = Patient::query()
            ->select('patients.*', 'users.name', 'users.email')
            ->join('users', 'users.id', '=', 'patients.user_id');

        // Search by name
        if ($request->has('name')) {
            $query->where('users.name', 'like', '%' . $request->name . '%');
        }

        return response()->json([
            'patients' => $query->paginate(10)
        ]);
    }

    public function show($patientid)
    {
        if (!Auth::guard('sanctum')->user()->hasAnyRole(['doctor', 'patient', 'admin'])) {
            abort(403);
        }

        $patient = Patient::where('patients.id', $patientid)
            ->select('patients.*', 'users.name', 'users.email')
            ->join('users', 'users.id', '=', 'patients.user_id')
            ->first();

        if (!$patient) {
            return response()->json(['message' => 'Patient not found'], 404);
        }

        return response()->json(['patient' => $patient]);
    }

    public function getByUserId($userId)
    {
        $patient = Patient::where('user_id', $userId)
            ->select('id', 'user_id', 'medical_history', 'emergency_contact')
            ->first();

        if (!$patient) {
            return response()->json([
                'message' => 'Patient not found'
            ], 404);
        }

        return response()->json([
            'patient' => $patient
        ]);
    }
}