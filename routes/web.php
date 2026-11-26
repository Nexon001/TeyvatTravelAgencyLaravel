<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\BookingController;
use App\Http\Middleware\AdminIdleTimeout;
use App\Http\Middleware\EnsureUserIsAdmin;
use Illuminate\Support\Facades\Route;

// CompanyProfile is now the site's home page.
Route::view('/', 'CompanyProfile')->name('home');

Route::view('/mondstadt', 'Mondstadt')->name('mondstadt');
Route::view('/liyue', 'Liyue')->name('liyue');
Route::view('/inazuma', 'Inazuma')->name('inazuma');
Route::view('/sumeru', 'Sumeru')->name('sumeru');
Route::view('/fontaine', 'Fontaine')->name('fontaine');
Route::view('/natlan', 'Natlan')->name('natlan');
Route::view('/snezhnaya', 'Snezhnaya')->name('snezhnaya');

// Booking: pick a stay length, then a check-in date on the availability calendar.
Route::get('/book', [BookingController::class, 'create'])->name('booking');
Route::get('/book/availability', [BookingController::class, 'availability'])->name('booking.availability')->middleware('throttle:60,1');
Route::get('/book/csrf', [BookingController::class, 'csrf'])->name('booking.csrf')->middleware('throttle:30,1');
Route::post('/book', [BookingController::class, 'store'])->name('booking.store')->middleware('throttle:6,1');

// Accounts: one login form for travellers and administrators (FR-01, 03, 04, 32).
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('login.store');
    Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('/register', [RegisteredUserController::class, 'store'])->name('register.store')->middleware('throttle:10,1');
});

Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->middleware('auth')->name('logout');

// Admin area: must be logged in (auth), be an administrator (FR-33) and not be idle for too long (FR-35).
// no_store stops the browser's Back button from showing an admin page after logging out.
Route::middleware(['auth', EnsureUserIsAdmin::class, AdminIdleTimeout::class, 'cache.headers:no_store;private'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::view('/', 'admin.dashboard')->name('dashboard');
    });
