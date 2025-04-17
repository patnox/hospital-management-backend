<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\QueueController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
// Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
Route::post('/logout', [AuthController::class, 'logout'])->middleware('handle.expired.tokens');

Route::middleware('handle.expired.tokens')->group(function () {
// Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('appointments', AppointmentController::class)->except(['show']);
    Route::get('appointments/availability', [AppointmentController::class, 'getAvailability']);
    Route::post('appointments/{appointment}/queue/join', [QueueController::class, 'joinQueue']);
    Route::post('appointments/{appointment}/queue/call-next', [QueueController::class, 'callNext']);
    Route::get('appointments/{appointment}/queue/position', [QueueController::class, 'getCurrentPosition']);
});