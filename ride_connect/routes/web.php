<?php

use App\Http\Controllers\ConnectController;
use App\Http\Controllers\FavoritesController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\OnboardingController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\VehicleController;
use App\Http\Controllers\VehicleReservationController;
use App\Http\Controllers\Reservation\MessageController as ReservationMessageController;
use App\Http\Controllers\Reservation\OwnerActionController;
use App\Http\Controllers\Reservation\RenterActionController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    // 車両登録
    Route::get('/vehicles/create', [VehicleController::class, 'create'])->name('vehicles.create');
    Route::post('/vehicles', [VehicleController::class, 'store'])->name('vehicles.store');
    Route::get('/vehicles/{vehicle}/edit', [VehicleController::class, 'edit'])->name('vehicles.edit');
    Route::put('/vehicles/{vehicle}', [VehicleController::class, 'update'])->name('vehicles.update');
    Route::post('/vehicles/{vehicle}/reservations', [VehicleReservationController::class, 'store'])->name('vehicles.reservations.store');

    Route::get('/connect', [ConnectController::class, 'index'])->name('connect.index');
    Route::post('/reservations/{reservation}/approve', [OwnerActionController::class, 'approve'])->name('reservations.approve');
    Route::post('/reservations/{reservation}/reject', [OwnerActionController::class, 'reject'])->name('reservations.reject');
    Route::post('/reservations/{reservation}/pre-check', [OwnerActionController::class, 'completePreCheck'])->name('reservations.pre_check.complete');
    Route::post('/reservations/{reservation}/confirm-return', [OwnerActionController::class, 'confirmReturn'])->name('reservations.return.confirm');
    Route::post('/reservations/{reservation}/post-check', [OwnerActionController::class, 'completePostCheck'])->name('reservations.post_check.complete');
    Route::post('/reservations/{reservation}/complete', [OwnerActionController::class, 'completeDeal'])->name('reservations.complete');

    Route::post('/reservations/{reservation}/start', [RenterActionController::class, 'startRide'])->name('reservations.start');
    Route::post('/reservations/{reservation}/end', [RenterActionController::class, 'requestEnd'])->name('reservations.end');

    Route::post('/reservations/{reservation}/messages', [ReservationMessageController::class, 'store'])->name('reservations.messages.store');
    Route::get('/favorites', [FavoritesController::class, 'index'])->name('favorites.index');
    Route::post('/vehicles/{vehicle}/favorite', [FavoritesController::class, 'toggle'])->name('favorites.toggle');

    // プロフィールオンボーディング
    Route::get('/onboarding/profile', [OnboardingController::class, 'showProfileForm'])->name('onboarding.profile');
    Route::post('/onboarding/profile', [OnboardingController::class, 'storeProfile'])->name('onboarding.profile.store');
    Route::get('/onboarding/complete', [OnboardingController::class, 'complete'])->name('onboarding.profile.complete');
});

Route::get('/', [OnboardingController::class, 'landing'])->name('landing');
Route::get('/vehicles', [VehicleController::class, 'index'])->name('vehicles.index');
Route::get('/vehicles/{vehicle}', [VehicleController::class, 'show'])->name('vehicles.show');
Route::get('/api/prefectures/{prefecture}/cities', [VehicleController::class, 'getCities']);

Route::get('/home', HomeController::class)->middleware(['auth', 'verified'])->name('home');
Route::get('/dashboard', HomeController::class)->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    // プロフィール編集画面
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');

    // プロフィール更新処理
    Route::patch('/profile/edit', [ProfileController::class, 'update'])->name('profile.update');

    // アカウント削除
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
