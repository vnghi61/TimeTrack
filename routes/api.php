<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware(['auth:sanctum'])->group(function () {
    Route::apiResource('users', UserController::class);
    Route::apiResource('roles', \App\Http\Controllers\RoleController::class);
    Route::apiResource('departments', \App\Http\Controllers\DepartmentController::class);
    Route::apiResource('projects', \App\Http\Controllers\ProjectController::class);
    Route::apiResource('tasks', \App\Http\Controllers\TaskController::class);
    Route::apiResource('attendances', \App\Http\Controllers\AttendanceController::class);
    Route::post('/attendance/check-in', [\App\Http\Controllers\AttendanceController::class, 'checkIn']);
    Route::post('/attendance/check-out', [\App\Http\Controllers\AttendanceController::class, 'checkOut']);
    Route::get('/attendance/today-status', [\App\Http\Controllers\AttendanceController::class, 'getTodayStatus']);
    Route::get('/dashboard/stats', [\App\Http\Controllers\DashboardController::class, 'getStats']);
    Route::get('/profile', [\App\Http\Controllers\ProfileController::class, 'show']);
    Route::put('/profile', [\App\Http\Controllers\ProfileController::class, 'update']);
    Route::put('/profile/password', [\App\Http\Controllers\ProfileController::class, 'updatePassword']);
    Route::post('/language', [\App\Http\Controllers\LanguageController::class, 'setLanguage']);
    Route::get('/language', [\App\Http\Controllers\LanguageController::class, 'getLanguage']);
});
