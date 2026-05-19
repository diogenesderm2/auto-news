<?php

use App\Http\Controllers\Admin\PostController as AdminPostController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PostController;
use Illuminate\Support\Facades\Route;

Route::get('/', [BlogController::class, 'index'])->name('home');
Route::get('/noticias/{slug}', [PostController::class, 'show'])->name('posts.show');
Route::get('/categoria/{slug}', [CategoryController::class, 'show'])->name('categories.show');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', DashboardController::class)->name('dashboard');

    Route::prefix('admin')->name('admin.')->group(function () {
        Route::resource('posts', AdminPostController::class)->except(['show']);

        Route::post('posts/{post}/restore', [AdminPostController::class, 'restore'])
            ->name('posts.restore');

        Route::delete('posts/{post}/force', [AdminPostController::class, 'forceDestroy'])
            ->name('posts.force-destroy');
    });
});

require __DIR__.'/settings.php';
