<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\TagController;
use App\Http\Controllers\Api\TaskAttachmentController;
use App\Http\Controllers\Api\TaskController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// API Version prefix
Route::prefix('v1')->group(function () {
    // Public routes (authentication)
    Route::controller(AuthController::class)->group(function () {
        Route::post('/register', 'register');
        Route::post('/login', 'login');
        Route::post('/guest', 'loginAsGuest');
    });

    // Protected routes
    Route::middleware(['auth:sanctum'])->group(function () {
        // Auth
        Route::post('/logout', [AuthController::class, 'logout'])->name('auth.logout');
        Route::get('/user', [AuthController::class, 'user'])->name('auth.user');

        Route::prefix('tasks')->group(function () {

            Route::controller(TaskController::class)->group(function () {
                // Basic CRUD
                Route::get('/', 'index')->name('tasks.index');
                Route::middleware(['prevent-guest'])->group(function () {
                    Route::post('/', 'store')->name('tasks.store');
                    Route::get('/{id}', 'show')->name('tasks.show');
                    Route::post('/{id}', 'update')->name('tasks.update');
                    Route::delete('/{id}', 'destroy')->name('tasks.delete');

                    // Task Status
                    Route::patch('/complete/{id}', 'complete')->name('tasks.complete');
                    Route::patch('/incomplete/{id}', 'incomplete')->name('tasks.incomplete');
                    Route::patch('/archive/{id}', 'archive')->name('tasks.archive');
                    Route::patch('/restore/{id}', 'restore')->name('tasks.restore');
                });
            });

            Route::controller(TaskAttachmentController::class)->middleware(['prevent-guest'])->group(function () {
                Route::get('/attachments/download/{id}', 'download')->name('tasks.attachments.download');
            });
        });

        Route::prefix('tags')->group(function () {
            Route::controller(TagController::class)->group(function () {
                Route::get('/', 'index')->name('tags.index');
            });
        });
    });
});
