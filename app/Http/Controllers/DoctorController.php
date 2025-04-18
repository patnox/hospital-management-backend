<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use Illuminate\Http\Request;

class DoctorController extends Controller
{
    public function index(Request $request)
    {
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

    public function show($id)
    {
        $doctor = Doctor::where('id', $id)
            ->select('doctors.*', 'users.name', 'users.email')
            ->join('users', 'users.id', '=', 'doctors.user_id')
            ->first();

        if (!$doctor) {
            return response()->json(['message' => 'Doctor not found'], 404);
        }

        return response()->json(['doctor' => $doctor]);
    }
}