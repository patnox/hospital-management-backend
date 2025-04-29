<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Display a listing of the users.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $perPage = $request->query('per_page', 10);
        $role = $request->query('role');
        $search = $request->query('search');

        $query = User::query()
            ->with(['doctor', 'patient'])
            ->when($role, fn($q, $role) => $q->where('role', $role))
            ->when($search, fn($q, $search) => $q->where(function($q) use ($search) {
                $q->where('name', 'like', "%$search%")
                  ->orWhere('email', 'like', "%$search%");
            }));

        $users = $query->paginate($perPage);

        return response()->json([
            'users' => $users,
            'current_page' => $users->currentPage(),
            'per_page' => $users->perPage(),
            'total' => $users->total(),
            'last_page' => $users->lastPage()
        ]);
    }

    /**
     * Store a newly created user.
     *
     * @param  \App\Http\Requests\User\StoreUserRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreUserRequest $request)
    {
        $userData = $request->validated();
        
        // Create the base user
        $user = User::create([
            'name' => $userData['name'],
            'email' => $userData['email'],
            'password' => Hash::make($userData['password']),
            'role' => $userData['role']
        ]);

        // Create associated doctor or patient record
        if ($userData['role'] === 'doctor') {
            Doctor::create([
                'user_id' => $user->id,
                'specialization' => $userData['specialization'] ?? '',
                'department' => $userData['department'] ?? ''
            ]);
        } elseif ($userData['role'] === 'patient') {
            Patient::create([
                'user_id' => $user->id,
                'medical_history' => $userData['medical_history'] ?? '',
                'emergency_contact' => $userData['emergency_contact'] ?? ''
            ]);
        }

        return response()->json([
            'message' => 'User created successfully',
            'user' => $user->load(['doctor', 'patient'])
        ], 201);
    }

    /**
     * Update the specified user.
     *
     * @param  \App\Http\Requests\User\UpdateUserRequest  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(UpdateUserRequest $request, $id)
    {
        $user = User::findOrFail($id);
        
        // Update base user data
        $userData = $request->validated();
        $user->update([
            'name' => $userData['name'],
            'email' => $userData['email'],
            'role' => $userData['role']
        ]);

        // Update associated doctor or patient record
        if ($user->role === 'doctor') {
            $user->doctor()->update([
                'specialization' => $userData['specialization'] ?? $user->doctor->specialization,
                'department' => $userData['department'] ?? $user->doctor->department
            ]);
        } elseif ($user->role === 'patient') {
            $user->patient()->update([
                'medical_history' => $userData['medical_history'] ?? $user->patient->medical_history,
                'emergency_contact' => $userData['emergency_contact'] ?? $user->patient->emergency_contact
            ]);
        }

        return response()->json([
            'message' => 'User updated successfully',
            'user' => $user->load(['doctor', 'patient'])
        ]);
    }

    /**
     * Remove the specified user.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $user = User::findOrFail($id);
        
        // Delete associated doctor or patient record
        if ($user->role === 'doctor') {
            $user->doctor()->delete();
        } elseif ($user->role === 'patient') {
            $user->patient()->delete();
        }

        $user->delete();

        return response()->json([
            'message' => 'User deleted successfully'
        ]);
    }
}