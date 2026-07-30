<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CalendarController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\TaskController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/logout-all', [AuthController::class, 'logoutAll']);
    Route::get('/user', [AuthController::class, 'me']);
    Route::get('/profile', [ProfileController::class, 'show']);
    Route::put('/profile', [ProfileController::class, 'update']);

    Route::get('/dashboard/summary', [DashboardController::class, 'summary']);
    Route::get('/dashboard/upcoming-tasks', [DashboardController::class, 'upcomingTasks']);
    Route::get('/dashboard/recent-activity', [DashboardController::class, 'recentActivity']);

    Route::apiResource('categories', CategoryController::class)->except(['show']);
    Route::get('/calendar', [CalendarController::class, 'index']);

    Route::apiResource('tasks', TaskController::class);
    Route::patch('/tasks/{task}/status', [TaskController::class, 'updateStatus']);
    Route::get('/tasks/{task}/shares', [TaskController::class, 'shares']);
    Route::post('/tasks/{task}/shares', [TaskController::class, 'share']);
    Route::delete('/tasks/{task}/shares/{user}', [TaskController::class, 'unshare']);
});
