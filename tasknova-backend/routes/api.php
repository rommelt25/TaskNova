<?php

use App\Http\Controllers\Api\ActivityLogController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CalendarController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\ExportController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\PreferenceController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\StatisticsController;
use App\Http\Controllers\Api\SubtaskController;
use App\Http\Controllers\Api\TaskController;
use App\Http\Controllers\Api\TaskAttachmentController;
use App\Http\Controllers\Api\TrashController;
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

    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::patch('/notifications/read-all', [NotificationController::class, 'markAllAsRead']);
    Route::patch('/notifications/{notification}/read', [NotificationController::class, 'markAsRead']);
    Route::delete('/notifications/{notification}', [NotificationController::class, 'destroy']);

    Route::get('/statistics', [StatisticsController::class, 'show']);

    Route::get('/export/tasks/json', [ExportController::class, 'tasksJson']);
    Route::get('/export/tasks/csv', [ExportController::class, 'tasksCsv']);
    Route::get('/export/categories/json', [ExportController::class, 'categoriesJson']);
    Route::get('/export/profile/json', [ExportController::class, 'profileJson']);

    Route::get('/trash/tasks', [TrashController::class, 'tasks']);
    Route::get('/trash/categories', [TrashController::class, 'categories']);
    Route::post('/trash/tasks/{task}/restore', [TrashController::class, 'restoreTask']);
    Route::post('/trash/categories/{category}/restore', [TrashController::class, 'restoreCategory']);
    Route::delete('/trash/tasks/{task}/force', [TrashController::class, 'forceDeleteTask']);
    Route::delete('/trash/categories/{category}/force', [TrashController::class, 'forceDeleteCategory']);

    Route::get('/preferences', [PreferenceController::class, 'show']);
    Route::put('/preferences', [PreferenceController::class, 'update']);

    Route::get('/activity', [ActivityLogController::class, 'index']);
    Route::get('/activity/latest', [ActivityLogController::class, 'latest']);

    Route::apiResource('categories', CategoryController::class)->except(['show']);
    Route::get('/calendar', [CalendarController::class, 'index']);

    Route::apiResource('tasks', TaskController::class);
    Route::patch('/tasks/{task}/status', [TaskController::class, 'updateStatus']);
    Route::get('/tasks/{task}/subtasks', [SubtaskController::class, 'index']);
    Route::post('/tasks/{task}/subtasks', [SubtaskController::class, 'store']);
    Route::get('/tasks/{task}/subtasks/{subtask}', [SubtaskController::class, 'show']);
    Route::put('/tasks/{task}/subtasks/{subtask}', [SubtaskController::class, 'update']);
    Route::patch('/tasks/{task}/subtasks/{subtask}/status', [SubtaskController::class, 'updateStatus']);
    Route::delete('/tasks/{task}/subtasks/{subtask}', [SubtaskController::class, 'destroy']);
    Route::get('/tasks/{task}/attachments', [TaskAttachmentController::class, 'index']);
    Route::post('/tasks/{task}/attachments', [TaskAttachmentController::class, 'store']);
    Route::delete('/tasks/{task}/attachments/{attachment}', [TaskAttachmentController::class, 'destroy']);
    Route::get('/tasks/{task}/attachments/{attachment}/download', [TaskAttachmentController::class, 'download']);
    Route::get('/tasks/{task}/shares', [TaskController::class, 'shares']);
    Route::post('/tasks/{task}/shares', [TaskController::class, 'share']);
    Route::delete('/tasks/{task}/shares/{user}', [TaskController::class, 'unshare']);
});
