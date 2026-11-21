
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ActivityController;
use App\Http\Controllers\Api\CommentController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\StageController;
use App\Http\Controllers\Api\SubmissionController;
use App\Http\Controllers\Api\SupervisorController;
use App\Http\Controllers\Api\ThesisController;

// Route yang tidak membutuhkan login
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Route yang membutuhkan token Sanctum
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    Route::get('/dashboard', [DashboardController::class, 'index']);
    Route::get('/supervisors', [SupervisorController::class, 'index']);

    Route::apiResource('theses', ThesisController::class)->parameters(['theses' => 'id']);

    Route::get('/stages/{id}', [StageController::class, 'show']);
    Route::post('/stages/{id}/submissions', [SubmissionController::class, 'store']);

    Route::post('/submissions/bulk-delete', [SubmissionController::class, 'bulkDestroy']);
    Route::patch('/submissions/{id}/status', [SubmissionController::class, 'updateStatus']);
    Route::delete('/submissions/{id}', [SubmissionController::class, 'destroy']);
    Route::post('/submissions/{id}/comments', [CommentController::class, 'store']);

    Route::get('/activities', [ActivityController::class, 'index']);
});
});
