<?php

use Illuminate\Foundation\Application;

use Illuminate\Support\Facades\Route;

use Inertia\Inertia;

Route::get('/', function () {

    return Inertia::render('Dashboard', [

        'canLogin' => Route::has('login'),

        'canRegister' => Route::has('register'),

        'laravelVersion' => Application::VERSION,

        'phpVersion' => PHP_VERSION,

    ]);

});

Route::middleware([

    'auth:sanctum',

    config('jetstream.auth_session'),

    'verified',

])->group(function () {

    Route::get('/dashboard', fn() => Inertia::render('Dashboard'))->name('dashboard');

    Route::prefix('user')->name('user.')->group(function () {

        Route::get('/', fn() => Inertia::render('User/Index'))->name('index');

        Route::get('/create', fn() => Inertia::render('User/Create'))->name('create');

        Route::get('/{id}/edit', function (\Illuminate\Http\Request $request) {
            return Inertia::render('User/Edit', [
                'user' => $request->input('user', [])
            ]);
        })->name('edit');
    });
    
    Route::prefix('role')->name('role.')->group(function () {
        Route::get('/', fn() => Inertia::render('Role/Index'))->name('index');
        Route::get('/create', fn() => Inertia::render('Role/Create'))->name('create');
        Route::get('/{id}/edit', function (\Illuminate\Http\Request $request) {
            return Inertia::render('Role/Edit', [
                'role' => $request->input('role', [])
            ]);
        })->name('edit');
    });
    
    Route::prefix('department')->name('department.')->group(function () {
        Route::get('/', fn() => Inertia::render('Department/Index'))->name('index');
        Route::get('/create', fn() => Inertia::render('Department/Create'))->name('create');
        Route::get('/{id}/edit', function (\Illuminate\Http\Request $request) {
            return Inertia::render('Department/Edit', [
                'department' => $request->input('department', [])
            ]);
        })->name('edit');
    });
    
    Route::prefix('project')->name('project.')->group(function () {
        Route::get('/', fn() => Inertia::render('Project/Index'))->name('index');
        Route::get('/create', fn() => Inertia::render('Project/Create'))->name('create');
        Route::get('/{id}/edit', function (\Illuminate\Http\Request $request) {
            return Inertia::render('Project/Edit', [
                'project' => $request->input('project', [])
            ]);
        })->name('edit');
    });
    
    Route::prefix('task')->name('task.')->group(function () {
        Route::get('/', fn() => Inertia::render('Task/Index'))->name('index');
        Route::get('/create', fn() => Inertia::render('Task/Create'))->name('create');
        Route::get('/{id}/edit', function (\Illuminate\Http\Request $request) {
            return Inertia::render('Task/Edit', [
                'task' => $request->input('task', [])
            ]);
        })->name('edit');
    });
    
    Route::get('/attendance', fn() => Inertia::render('Attendance/Index'))->name('attendance.index');
    
    Route::get('/profile', fn() => Inertia::render('Profile/Index'))->name('profile.index');

});

// Fallback route - redirect to dashboard if route not found
Route::fallback(function () {
    return redirect('/dashboard');
});
