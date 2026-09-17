<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProductivityController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json(['message' => 'Panodoro API is running.']);
});

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth:sanctum');

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/settings', [ProductivityController::class, 'settings']);
    Route::put('/settings', [ProductivityController::class, 'updateSettings']);
    Route::post('/study-logs', [ProductivityController::class, 'addStudyTime']);
    Route::get('/analytics', [ProductivityController::class, 'analytics']);
    Route::get('/history', [ProductivityController::class, 'history']);

    Route::get('/notes', [ProductivityController::class, 'notes']);
    Route::post('/notes', [ProductivityController::class, 'createNote']);
    Route::put('/notes/{note}', [ProductivityController::class, 'updateNote']);
    Route::delete('/notes/{note}', [ProductivityController::class, 'deleteNote']);

    Route::get('/tasks', [ProductivityController::class, 'tasks']);
    Route::post('/tasks', [ProductivityController::class, 'createTask']);
    Route::patch('/tasks/{task}', [ProductivityController::class, 'updateTask']);
    Route::delete('/tasks/{task}', [ProductivityController::class, 'deleteTask']);
});
