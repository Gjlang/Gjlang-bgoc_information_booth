<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ItemDashboardController;
use App\Http\Controllers\AdminUserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

Route::get('/', fn () => redirect()->route('login'));

// Auth routes should be accessible to guests
require __DIR__.'/auth.php';

Route::middleware(['auth','verified'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/dashboard', [ItemDashboardController::class, 'index'])->name('dashboard');

    Route::prefix('dashboard')->name('dashboard.')->group(function () {
        Route::prefix('items')->name('items.')->group(function () {
            Route::get('/',       [ItemDashboardController::class, 'index'])->name('index');
            Route::get('/list',   [ItemDashboardController::class, 'list'])->name('list');
            Route::get('/events', [ItemDashboardController::class, 'events'])->name('events');
            Route::post('/',      [ItemDashboardController::class, 'store'])->name('store');
            Route::patch('/{id}', [ItemDashboardController::class, 'update'])->whereNumber('id')->name('update');
            Route::patch('/{id}/status', [ItemDashboardController::class, 'updateStatus'])->whereNumber('id')->name('status');
            Route::get('/{id}',   [ItemDashboardController::class, 'show'])->whereNumber('id')->name('show');

            // ⬇️ EDIT PAYLOAD (pemilik/admin saja lewat policy) — BUKAN di group admin-only
            Route::get('/{id}/edit-payload', [ItemDashboardController::class, 'editPayload'])
                ->whereNumber('id')
                ->name('editPayload');

            // ⬇️ Admin only
            Route::middleware('role:admin')->group(function () {
                Route::get('/export', [ItemDashboardController::class, 'export'])->name('export');
                Route::delete('/{id}', [ItemDashboardController::class, 'destroy'])->whereNumber('id')->name('destroy');
            });
        });
    });

    Route::middleware(['role:admin'])->group(function () {
        // Endpoint untuk submit form register user/admin
        Route::post('/admin/users', [AdminUserController::class, 'store'])->name('admin.users.store');
    });

    Route::post('/logout', function (Request $request) {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    })->name('logout');
});
