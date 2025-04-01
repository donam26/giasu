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
use App\Http\Controllers\Tutor\DashboardController as TutorDashboardController;
use App\Http\Controllers\Tutor\ProfileController as TutorProfileController;
use App\Http\Controllers\Tutor\BookingController as TutorBookingController;
use App\Http\Controllers\Tutor\ScheduleController as TutorScheduleController;
use App\Http\Controllers\Tutor\EarningController as TutorEarningController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\Student\StudentBookingController;
use App\Http\Controllers\Student\ReviewController;
use App\Http\Controllers\PasswordController;
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

// Route cho đăng ký trở thành gia sư (public)
Route::get('/tutors/register', [TutorController::class, 'register'])->name('tutors.register');
Route::get('/tutors/become', [TutorController::class, 'create'])->name('tutors.create');

// Payment callback routes (không yêu cầu đăng nhập)
Route::get('/payment/vnpay/callback', [PaymentController::class, 'handleCallback'])->name('payment.callback');

Route::middleware('auth')->group(function () {
    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
        ->name('logout');

    // Profile Routes
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::put('/password', [PasswordController::class, 'update'])->name('password.update');

    // Payment routes
    Route::get('/payment/{booking}/create', [PaymentController::class, 'createPayment'])->name('payment.create');

    // Tutor Routes (yêu cầu đăng nhập)
    Route::post('/tutors', [TutorController::class, 'store'])->name('tutors.store');
    Route::get('/tutors/{tutor}/pending', [TutorController::class, 'pending'])->name('tutors.pending');
    Route::get('/tutors/{tutor}/edit', [TutorController::class, 'edit'])->name('tutors.edit');
    Route::patch('/tutors/{tutor}', [TutorController::class, 'update'])->name('tutors.update');

    // Booking Routes
    Route::post('/tutors/{tutor}/book', [TutorController::class, 'book'])->name('tutors.book');
    Route::get('/bookings', [TutorController::class, 'bookings'])->name('tutors.bookings');

    // Student Routes
    Route::prefix('student')->name('student.')->middleware('auth')->group(function () {
        Route::get('/bookings', [StudentBookingController::class, 'index'])->name('bookings.index');
        Route::get('/bookings/tutors', [StudentBookingController::class, 'tutors'])->name('bookings.tutors');
        Route::get('/bookings/create/{tutor}', [StudentBookingController::class, 'create'])->name('bookings.create');
        Route::post('/bookings/{tutor}', [StudentBookingController::class, 'store'])->name('bookings.store');
        Route::get('/bookings/{booking}', [StudentBookingController::class, 'show'])->name('bookings.show');
        Route::post('/bookings/{booking}/cancel', [StudentBookingController::class, 'cancel'])->name('bookings.cancel');
        
        // Review routes
        Route::get('/tutors/{tutor}/review', [ReviewController::class, 'create'])->name('tutors.review');
        Route::post('/tutors/{tutor}/review', [ReviewController::class, 'store'])->name('tutors.review.store');
    });

    // Tutor Routes
    Route::middleware(['auth', 'tutor'])->prefix('tutor')->name('tutor.')->group(function () {
        Route::get('/dashboard', [TutorDashboardController::class, 'index'])->name('dashboard');
        Route::get('/profile', [TutorProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/profile', [TutorProfileController::class, 'update'])->name('profile.update');
        Route::get('/bookings', [TutorBookingController::class, 'index'])->name('bookings.index');
        Route::get('/bookings/{booking}', [TutorBookingController::class, 'show'])->name('bookings.show');
        Route::patch('/bookings/{booking}/status', [TutorBookingController::class, 'updateStatus'])->name('bookings.update-status');
        
        // Lịch rảnh - Sử dụng AvailabilityController
        Route::get('/schedule', [App\Http\Controllers\Tutor\AvailabilityController::class, 'index'])->name('schedule.index');
        Route::get('/schedule/quick', [App\Http\Controllers\Tutor\AvailabilityController::class, 'quickCreate'])->name('schedule.quick');
        Route::post('/schedule/quick-store', [App\Http\Controllers\Tutor\AvailabilityController::class, 'quickStore'])->name('schedule.quick-store');
        Route::get('/schedule/create', [App\Http\Controllers\Tutor\AvailabilityController::class, 'create'])->name('schedule.create');
        Route::post('/schedule', [App\Http\Controllers\Tutor\AvailabilityController::class, 'store'])->name('schedule.store');
        Route::get('/schedule/{availability}/edit', [App\Http\Controllers\Tutor\AvailabilityController::class, 'edit'])->name('schedule.edit');
        Route::put('/schedule/{availability}', [App\Http\Controllers\Tutor\AvailabilityController::class, 'update'])->name('schedule.update');
        Route::delete('/schedule/{availability}', [App\Http\Controllers\Tutor\AvailabilityController::class, 'destroy'])->name('schedule.destroy');
        
        Route::get('/earnings', [TutorEarningController::class, 'index'])->name('earnings.index');
    });
});

// Public Routes
Route::get('/tutors', [TutorController::class, 'index'])->name('tutors.index');
Route::get('/tutors/{tutor}', [TutorController::class, 'show'])->name('tutors.show');

Route::get('/subjects', [SubjectController::class, 'index'])->name('subjects.index');
Route::get('/subjects/{subject}', [SubjectController::class, 'show'])->name('subjects.show');

Route::get('/ai-advisor', [AiAdvisorController::class, 'index'])->name('ai-advisor');
Route::post('/ai-advisor/chat', [AiAdvisorController::class, 'chat'])->name('ai-advisor.chat');
Route::post('/ai-advisor/reset', [AiAdvisorController::class, 'resetConversation'])->name('ai-advisor.reset');

// Trang tĩnh
Route::get('/privacy-policy', function () {
    return view('pages.privacy-policy');
})->name('privacy-policy');

Route::get('/about-us', function () {
    return view('pages.about-us');
})->name('about-us');

Route::get('/contact', function () {
    return view('pages.contact');
})->name('contact');

Route::get('/faq', function () {
    return view('pages.faq');
})->name('faq');

Route::get('/guide', function () {
    return view('pages.guide');
})->name('guide');

Route::get('/terms', function () {
    return view('pages.terms');
})->name('terms');

// Admin routes
Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::resource('users', UserController::class);
    Route::resource('tutors', AdminTutorController::class);
    Route::post('tutors/{tutor}/approve', [AdminTutorController::class, 'approve'])->name('tutors.approve');
    Route::resource('subjects', AdminSubjectController::class);
    Route::resource('bookings', AdminBookingController::class);
});
