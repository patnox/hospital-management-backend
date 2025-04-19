<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\QueueController;
use App\Http\Controllers\DoctorController;
use App\Http\Controllers\PatientController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
// Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
Route::post('/logout', [AuthController::class, 'logout'])->middleware('handle.expired.tokens');

Route::middleware('handle.expired.tokens', 'role:doctor,patient,admin')->group(function () {
// Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('appointments', AppointmentController::class)->except(['show']);
    Route::get('appointments/availability', [AppointmentController::class, 'getAvailability']);
    Route::post('appointments/{appointment}/queue/join', [QueueController::class, 'joinQueue']);
    Route::post('appointments/{appointment}/queue/call-next', [QueueController::class, 'callNext']);
    Route::get('appointments/{appointment}/queue/position', [QueueController::class, 'getCurrentPosition']);
    Route::apiResource('doctors', DoctorController::class);
    Route::apiResource('patients', PatientController::class);
    Route::get('/patients/user/{userId}', [PatientController::class, 'getByUserId']);
    Route::get('/doctors/user/{userId}', [DoctorController::class, 'getByUserId']);
});