<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TutorController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\AiAdvisorController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\TutorController as AdminTutorController;
use App\Http\Controllers\Admin\SubjectController as AdminSubjectController;
use App\Http\Controllers\Admin\BookingController as AdminBookingController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('pages.home');
})->name('home');

Route::middleware('guest')->group(function () {
    Route::get('register', [RegisteredUserController::class, 'create'])
        ->name('register');
    Route::post('register', [RegisteredUserController::class, 'store']);
    Route::get('login', [AuthenticatedSessionController::class, 'create'])
        ->name('login');
    Route::post('login', [AuthenticatedSessionController::class, 'store']);
});

Route::middleware('auth')->group(function () {
    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
        ->name('logout');

    // Profile Routes
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Tutor Routes
    Route::get('/tutors/become', [TutorController::class, 'create'])->name('tutors.create');
    Route::post('/tutors', [TutorController::class, 'store'])->name('tutors.store');
    Route::get('/tutors/{tutor}/edit', [TutorController::class, 'edit'])->name('tutors.edit');
    Route::patch('/tutors/{tutor}', [TutorController::class, 'update'])->name('tutors.update');

    // Booking Routes
    Route::post('/tutors/{tutor}/book', [TutorController::class, 'book'])->name('tutors.book');
    Route::get('/bookings', [TutorController::class, 'bookings'])->name('tutors.bookings');
});

// Public Routes
Route::get('/tutors', [TutorController::class, 'index'])->name('tutors.index');
Route::get('/tutors/{tutor}', [TutorController::class, 'show'])->name('tutors.show');

Route::get('/subjects', [SubjectController::class, 'index'])->name('subjects.index');
Route::get('/subjects/{subject}', [SubjectController::class, 'show'])->name('subjects.show');

Route::get('/ai-advisor', [AiAdvisorController::class, 'index'])->name('ai-advisor');
Route::post('/ai-advisor/chat', [AiAdvisorController::class, 'chat'])->name('ai-advisor.chat');

// Admin routes
Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::resource('users', UserController::class);
    Route::resource('tutors', AdminTutorController::class);
    Route::resource('subjects', AdminSubjectController::class);
    Route::resource('bookings', AdminBookingController::class);
});
