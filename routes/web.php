<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ItemDashboardController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application.
| These routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Redirect root → login
Route::get('/', function () {
    return redirect()->route('login');
});

// Main dashboard (landing page)
Route::get('/dashboard', [ItemDashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');
// Authenticated routes group
Route::middleware('auth')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Profile Routes
    |--------------------------------------------------------------------------
    */
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    /*
    |--------------------------------------------------------------------------
    | Items Dashboard Routes
    |--------------------------------------------------------------------------
    */

    Route::get('/dashboard/items', [ItemDashboardController::class, 'index'])
        ->name('items.dashboard');

    Route::get('/dashboard/items/list', [ItemDashboardController::class, 'list'])
        ->name('items.list');

    Route::get('/dashboard/items/events', [ItemDashboardController::class, 'events'])
        ->name('items.events');

    Route::get('/dashboard/items/{id}', [ItemDashboardController::class, 'show'])
        ->name('items.show');

});

// Authentication routes (login/register/reset)
require __DIR__.'/auth.php';
