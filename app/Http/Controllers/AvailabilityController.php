<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\Availability\StoreAvailabilityRequest;
use App\Http\Requests\Availability\UpdateAvailabilityRequest;
use App\Models\DoctorAvailability;
use Illuminate\Http\Request;

class AvailabilityController extends Controller
{
    /**
     * Display a listing of the doctor availability.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $perPage = $request->query('per_page', 10);
        $doctorId = $request->query('doctor_id');
        $search = $request->query('search');
        $day = $request->query('day');

        $query = DoctorAvailability::query()
            ->with(['doctor' => function($q) {
                $q->select('id', 'user_id', 'specialization');  // Removed 'name' column
            }])
            ->when($doctorId, fn($q, $doctorId) => $q->where('doctor_id', $doctorId))
            ->when($search, fn($q, $search) => $q->where(function($q) use ($search) {
                $q->whereHas('doctor', fn($q) => $q->where('specialization', 'like', "%$search%"))
                ->orWhere('day', 'like', "%$search%")
                ->orWhere('start_time', 'like', "%$search%")
                ->orWhere('end_time', 'like', "%$search%");
            }))
            ->when($day, fn($q, $day) => $q->where('day', $day))
            ->orderBy('day')
            ->orderBy('start_time');

        $availability = $query->paginate($perPage);

        return response()->json([
            'availability' => $availability,
            'current_page' => $availability->currentPage(),
            'per_page' => $availability->perPage(),
            'total' => $availability->total(),
            'last_page' => $availability->lastPage()
        ]);
    }

    /**
     * Store a newly created doctor availability.
     *
     * @param  \App\Http\Requests\Availability\StoreAvailabilityRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreAvailabilityRequest $request)
    {
        $validatedData = $request->validated();

        $availability = DoctorAvailability::create($validatedData);

        return response()->json([
            'message' => 'Availability created successfully',
            'availability' => $availability->load('doctor')
        ], 201);
    }

    /**
     * Update the specified doctor availability.
     *
     * @param  \App\Http\Requests\Availability\UpdateAvailabilityRequest  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(UpdateAvailabilityRequest $request, $id)
    {
        $availability = DoctorAvailability::findOrFail($id);

        $availability->update($request->validated());

        return response()->json([
            'message' => 'Availability updated successfully',
            'availability' => $availability->load('doctor')
        ]);
    }

    /**
     * Remove the specified doctor availability.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $availability = DoctorAvailability::findOrFail($id);
        $availability->delete();

        return response()->json([
            'message' => 'Availability deleted successfully'
        ]);
    }
}