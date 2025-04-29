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

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users,email',
            'medical_history' => 'sometimes|string',
            'emergency_contact' => 'sometimes|string'
        ]);

        // Create user first
        $user = User::create([
            'name' => $validatedData['name'],
            'email' => $validatedData['email'],
            'password' => bcrypt('password123'), // Change this in production
            'role' => 'patient'
        ]);

        // Create patient record
        Patient::create([
            'user_id' => $user->id,
            'medical_history' => $validatedData['medical_history'] ?? '',
            'emergency_contact' => $validatedData['emergency_contact'] ?? ''
        ]);

        return response()->json(['message' => 'Patient created successfully']);
    }

    public function update(Request $request, $id)
    {
        $patient = Patient::findOrFail($id);
        
        $validatedData = $request->validate([
            'name' => 'sometimes|required|string',
            'email' => 'sometimes|required|email|unique:users,email,' . $patient->user->id,
            'medical_history' => 'sometimes|string',
            'emergency_contact' => 'sometimes|string'
        ]);

        // Update user
        $patient->user->update([
            'name' => $validatedData['name'] ?? $patient->user->name,
            'email' => $validatedData['email'] ?? $patient->user->email,
        ]);

        // Update patient
        $patient->update([
            'medical_history' => $validatedData['medical_history'] ?? $patient->medical_history,
            'emergency_contact' => $validatedData['emergency_contact'] ?? $patient->emergency_contact
        ]);

        return response()->json(['message' => 'Patient updated successfully']);
    }

    public function destroy($id)
    {
        return Patient::destroy($id);
    }
}