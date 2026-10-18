<?php

use App\Http\Controllers\Admin\BookingAdminController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\MotorcycleController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\SeasonalPriceController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\ZoneController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\Staff\StaffTaskController;
use Illuminate\Support\Facades\Route;

// --- Rute Publik (Pelanggan) ---
Route::get('/', [BookingController::class, 'index'])->name('home');
Route::post('/cek-ketersediaan', [BookingController::class, 'checkAvailability'])->name('booking.check');
Route::post('/booking', [BookingController::class, 'store'])->name('booking.store');
Route::get('/cek-booking', [BookingController::class, 'track'])->name('booking.track');
Route::get('/booking/tiket/{code}', [BookingController::class, 'showTicket'])->name('booking.ticket');

// --- Rute Autentikasi ---
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// --- Rute Admin ---
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [BookingAdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/bookings', [BookingAdminController::class, 'index'])->name('bookings.index');
    Route::get('/bookings/{id}', [BookingAdminController::class, 'show'])->name('bookings.show');
    Route::post('/bookings/{id}/approve', [BookingAdminController::class, 'approve'])->name('bookings.approve');
    Route::post('/bookings/{id}/reject', [BookingAdminController::class, 'reject'])->name('bookings.reject');
    Route::post('/bookings/{id}/reschedule', [BookingAdminController::class, 'reschedule'])->name('bookings.reschedule');
    Route::post('/bookings/{id}/update-status', [BookingAdminController::class, 'updateStatus'])->name('bookings.update-status');
    Route::post('/bookings/{id}/update-dp', [BookingAdminController::class, 'updateDownPayment'])->name('bookings.update-dp');
    Route::post('/bookings/{id}/update-documents', [BookingAdminController::class, 'updateDocuments'])->name('bookings.update-documents');

    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/export-csv', [ReportController::class, 'exportCsv'])->name('reports.export-csv');
    Route::get('/reports/print', [ReportController::class, 'print'])->name('reports.print');

    Route::resource('motorcycles', MotorcycleController::class)->except(['create', 'edit', 'show']);
    Route::resource('categories', CategoryController::class)->except(['create', 'edit', 'show']);
    Route::resource('seasonal-prices', SeasonalPriceController::class)->except(['create', 'edit', 'show']);
    Route::resource('zones', ZoneController::class)->except(['create', 'edit', 'show']);
    Route::resource('users', UserController::class)->except(['create', 'edit', 'show']);
});

// --- Rute Portal Petugas Lapangan (Mobile-Friendly) ---
Route::middleware(['auth', 'role:admin,staff'])->prefix('staff')->name('staff.')->group(function () {
    Route::get('/tasks', [StaffTaskController::class, 'index'])->name('tasks');
    Route::post('/tasks/{id}/claim-delivery', [StaffTaskController::class, 'claimDelivery'])->name('tasks.claim-delivery');
    Route::post('/tasks/{id}/start', [StaffTaskController::class, 'startDelivery'])->name('tasks.start');
    Route::get('/tasks/{id}/handover', [StaffTaskController::class, 'showHandoverForm'])->name('tasks.handover');
    Route::post('/tasks/{id}/handover', [StaffTaskController::class, 'submitHandover'])->name('tasks.handover.submit');
    Route::post('/tasks/{id}/claim-return', [StaffTaskController::class, 'claimReturn'])->name('tasks.claim-return');
    Route::post('/tasks/{id}/start-return', [StaffTaskController::class, 'startReturn'])->name('tasks.start-return');
    Route::get('/tasks/{id}/return', [StaffTaskController::class, 'showReturnForm'])->name('tasks.return');
    Route::post('/tasks/{id}/return', [StaffTaskController::class, 'submitReturn'])->name('tasks.return.submit');
});
