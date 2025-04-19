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
}