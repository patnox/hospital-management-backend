<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use Illuminate\Http\Request;

class PatientController extends Controller
{
    public function index(Request $request)
    {
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

    public function show($id)
    {
        $patient = Patient::where('id', $id)
            ->select('patients.*', 'users.name', 'users.email')
            ->join('users', 'users.id', '=', 'patients.user_id')
            ->first();

        if (!$patient) {
            return response()->json(['message' => 'Patient not found'], 404);
        }

        return response()->json(['patient' => $patient]);
    }
}