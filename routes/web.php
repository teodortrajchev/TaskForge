<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ProjectInvitationController;
use App\Http\Controllers\ProjectMemberController;
use App\Http\Controllers\TaskController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\FileUploadController;
use App\Http\Controllers\SearchController;



Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', [ProjectController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');


    //Github
    Route::resource('projects', ProjectController::class)->only(['index', 'create', 'store', 'show', 'destroy']);
    Route::get('/github/repository', [ProjectController::class, 'githubRepo'])
    ->middleware('throttle:20,1')
    ->name('github.repository');
    Route::post('/github/commits', [ProjectController::class, 'list_commits'])->name('github.commits');


    Route::patch('/projects/{project}/status', [ProjectController::class, 'updateStatus'])->name('projects.status.update');

    Route::get('/projects/{project}/tasks/create', [TaskController::class, 'create'])->name('projects.tasks.create');
    Route::get('/projects/{project}/tasks/{task}', [TaskController::class, 'show'])->name('projects.tasks.show');
    Route::post('/projects/{project}/tasks', [TaskController::class, 'store'])->name('projects.tasks.store');
    Route::get('/projects/{project}/tasks/{task}/edit', [TaskController::class, 'edit'])->name('projects.tasks.edit');
    Route::put('/projects/{project}/tasks/{task}', [TaskController::class, 'update'])->name('projects.tasks.update');
    Route::delete('/projects/{project}/tasks/{task}', [TaskController::class, 'destroy'])->name('projects.tasks.destroy');
    // Route::patch('/projects/{project}/tasks/{task}/complete', [TaskController::class, 'complete'])->name('projects.tasks.complete');
    Route::patch('/projects/{project}/tasks/{task}/status', [TaskController::class, 'updateStatus'])->name('projects.tasks.status');
    Route::patch('/projects/{project}/tasks/{task}/due-date', [TaskController::class, 'updateDueDate'])->name('projects.tasks.due-date');

    Route::get('/projects/{project}/history', [TaskController::class, 'history'])->name('projects.history');
    // Members
    Route::put('/projects/{project}/members/{user}', [ProjectMemberController::class, 'update'])->name('projects.members.update');
    Route::delete('/projects/{project}/members/{user}', [ProjectMemberController::class, 'destroy'])->name('projects.members.destroy');

    // Invitations
    Route::post('/projects/{project}/invitations', [ProjectInvitationController::class, 'store'])->name('projects.invitations.store');
    Route::delete('/projects/{project}/invitations/{invitation}', [ProjectInvitationController::class, 'destroy'])->name('projects.invitations.destroy');
    Route::get('/invitations/{token}', [ProjectInvitationController::class, 'show'])->name('invitations.show');
    Route::post('/invitations/{token}/accept', [ProjectInvitationController::class, 'accept'])->name('invitations.accept');

    // Messages

    Route::get('/projects/{project}/messages', [MessageController::class, 'index'])->name('projects.messages.index');
    Route::post('/projects/{project}/messages', [MessageController::class, 'store'])->name('projects.messages.store');
    Route::delete('/projects/{project}/messages/{message}', [MessageController::class, 'destroy'])->name('projects.messages.destroy');

    Route::get('/projects/{project}/tasks/{task}/messages', [MessageController::class, 'index'])->name('projects.tasks.messages.index');
    Route::post('/projects/{project}/tasks/{task}/messages', [MessageController::class, 'store'])->name('projects.tasks.messages.store');
    Route::delete('/projects/{project}/tasks/{task}/messages/{message}', [MessageController::class, 'destroy'])->name('projects.tasks.messages.destroy');

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/read-all', [NotificationController::class, 'readAll'])->name('notifications.read-all');
    Route::get('/notifications/{notification}', [NotificationController::class, 'open'])->name('notifications.open');

     // File Upload
    Route::post('/projects/{project}/files', [FileUploadController::class, 'store'])->name('projects.files.store');
    Route::get('/projects/{project}/files/{file}', [FileUploadController::class, 'download'])->name('projects.files.download');
    Route::delete('/projects/{project}/files/{file}', [FileUploadController::class, 'destroy'])->name('projects.files.destroy');

    // Search
    Route::get('/search', [SearchController::class, 'index'])->name('search');
    });

require __DIR__.'/auth.php';