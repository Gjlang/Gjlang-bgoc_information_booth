<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ItemDashboardController;

// Redirect root → login
Route::get('/', fn () => redirect()->route('login'));

// Authenticated area
Route::middleware(['auth', 'verified'])->group(function () {

    // ---- Profile ----
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // ---- Main Dashboard page (legacy-friendly name) ----
    Route::get('/dashboard', [ItemDashboardController::class, 'index'])->name('dashboard');

    // ---- Items under /dashboard/items with names dashboard.items.* ----
    Route::prefix('dashboard')->name('dashboard.')->group(function () {
        Route::prefix('items')->name('items.')->group(function () {
            Route::get('/',        [ItemDashboardController::class, 'index'])->name('index');
            Route::get('/list',    [ItemDashboardController::class, 'list'])->name('list');
            Route::get('/events',  [ItemDashboardController::class, 'events'])->name('events');
            Route::post('/',       [ItemDashboardController::class, 'store'])->name('store');
            Route::get('/{id}',    [ItemDashboardController::class, 'show'])->whereNumber('id')->name('show');
            Route::get('/export', [ItemDashboardController::class, 'export'])->name('export');
        });
    });
});

require __DIR__.'/auth.php';
