<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DoctorController extends Controller
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

        $query = Doctor::query()
            ->select('doctors.*', 'users.name', 'users.email')
            ->join('users', 'users.id', '=', 'doctors.user_id');

        // Search by name
        if ($request->has('name')) {
            $query->where('users.name', 'like', '%' . $request->name . '%');
        }

        // Search by department
        if ($request->has('department')) {
            $query->where('doctors.department', 'like', '%' . $request->department . '%');
        }

        // Search by specialization
        if ($request->has('specialization')) {
            $query->where('doctors.specialization', 'like', '%' . $request->specialization . '%');
        }

        return response()->json([
            'doctors' => $query->paginate(10)
        ]);
    }

    public function show($doctorsid)
    {
        if (!Auth::guard('sanctum')->user()->hasAnyRole(['doctor', 'patient', 'admin'])) {
            abort(403);
        }

        $doctor = Doctor::where('doctors.id', $doctorsid)
            ->select('doctors.*', 'users.name', 'users.email')
            ->join('users', 'users.id', '=', 'doctors.user_id')
            ->first();

        if (!$doctor) {
            return response()->json(['message' => 'Doctor not found'], 404);
        }

        return response()->json(['doctor' => $doctor]);
    }

    public function getByUserId($userId)
    {
        $doctor = Doctor::where('user_id', $userId)
            ->select('id', 'user_id', 'specialization', 'department')
            ->first();

        if (!$doctor) {
            return response()->json([
                'message' => 'Doctor not found'
            ], 404);
        }

        return response()->json([
            'doctor' => $doctor
        ]);
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users,email',
            'specialization' => 'required|string',
            'department' => 'required|string'
        ]);

        // Create user first
        $user = User::create([
            'name' => $validatedData['name'],
            'email' => $validatedData['email'],
            'password' => bcrypt('password123'), // Change this in production
            'role' => 'doctor'
        ]);

        // Create doctor record
        Doctor::create([
            'user_id' => $user->id,
            'specialization' => $validatedData['specialization'],
            'department' => $validatedData['department']
        ]);

        return response()->json(['message' => 'Doctor created successfully']);
    }

    public function update(Request $request, $id)
    {
        $doctor = Doctor::findOrFail($id);
        
        $validatedData = $request->validate([
            'name' => 'sometimes|required|string',
            'email' => 'sometimes|required|email|unique:users,email,' . $doctor->user->id,
            'specialization' => 'sometimes|required|string',
            'department' => 'sometimes|required|string'
        ]);

        // Update user
        $doctor->user->update([
            'name' => $validatedData['name'] ?? $doctor->user->name,
            'email' => $validatedData['email'] ?? $doctor->user->email,
        ]);

        // Update doctor
        $doctor->update([
            'specialization' => $validatedData['specialization'] ?? $doctor->specialization,
            'department' => $validatedData['department'] ?? $doctor->department
        ]);

        return response()->json(['message' => 'Doctor updated successfully']);
    }

    public function destroy($id)
    {
        return Doctor::destroy($id);
    }
}