<?php

use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\CourseEnrollmentController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\EnrollmentController;
use App\Http\Controllers\GradeController;
use App\Http\Controllers\InstructorController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PortalController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\SectionController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\TraineeController;
use App\Http\Controllers\TrainingSessionController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VehicleController;
use Illuminate\Support\Facades\Route;

// ---------------------------------------------------------------- guests
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

// ---------------------------------------------------------------- authenticated
Route::middleware('auth')->group(function () {
    Route::get('/', fn () => redirect()->route('dashboard'));
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/password', [AuthController::class, 'showPassword'])->name('password.edit');
    Route::post('/password', [AuthController::class, 'updatePassword'])->name('password.update');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // ---- Portals (role-restricted; the administrator can open all of them)
    Route::get('/portal/student', [PortalController::class, 'student'])->middleware('role:student')->name('portal.student');
    Route::get('/portal/trainee', [PortalController::class, 'trainee'])->middleware('role:trainee')->name('portal.trainee');
    Route::get('/portal/teacher', [PortalController::class, 'teacher'])->middleware('role:teacher')->name('portal.teacher');
    Route::middleware('role:instructor')->group(function () {
        Route::get('/portal/instructor', [PortalController::class, 'instructor'])->name('portal.instructor');
        Route::put('/portal/instructor/sessions/{id}', [PortalController::class, 'updateSession'])->name('portal.instructor.session');
    });

    // ---- Senior High School module
    Route::middleware('role:shs_staff')->group(function () {
        Route::resource('students', StudentController::class);
        Route::resource('sections', SectionController::class)->except('show');
        Route::resource('subjects', SubjectController::class)->except('show');
        Route::resource('class-schedules', ScheduleController::class)->except('show');
        Route::resource('enrollments', EnrollmentController::class)->except('show');
    });

    // ---- Grades and attendance (staff + teachers)
    Route::middleware('role:shs_staff,teacher')->group(function () {
        Route::get('/grades', [GradeController::class, 'index'])->name('grades.index');
        Route::post('/grades', [GradeController::class, 'store'])->name('grades.store');
        Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');
        Route::post('/attendance', [AttendanceController::class, 'store'])->name('attendance.store');
    });

    // ---- Driving School module
    Route::middleware('role:driving_staff')->group(function () {
        Route::resource('trainees', TraineeController::class);
        Route::resource('courses', CourseController::class)->except('show');
        Route::resource('course-enrollments', CourseEnrollmentController::class)->except('show');
        Route::resource('instructors', InstructorController::class)->except('show');
        Route::resource('vehicles', VehicleController::class)->except('show');
        Route::resource('training-sessions', TrainingSessionController::class)->except('show');
    });

    // ---- Shared administration (finance, documents, reports, announcements)
    Route::middleware('role:shs_staff,driving_staff')->group(function () {
        Route::get('/payments', [PaymentController::class, 'index'])->name('payments.index');
        Route::get('/payments/receipt/{payment}', [PaymentController::class, 'receipt'])->name('payments.receipt');
        Route::get('/payments/{type}/{id}', [PaymentController::class, 'account'])->name('payments.account');
        Route::post('/payments/{type}/{id}/charges', [PaymentController::class, 'storeCharge'])->name('payments.charge');
        Route::post('/payments/{type}/{id}/payments', [PaymentController::class, 'storePayment'])->name('payments.pay');

        Route::post('/documents/{type}/{id}', [DocumentController::class, 'store'])->name('documents.store');
        Route::get('/documents/{document}/download', [DocumentController::class, 'download'])->name('documents.download');
        Route::delete('/documents/{document}', [DocumentController::class, 'destroy'])->name('documents.destroy');

        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/{type}', [ReportController::class, 'show'])->name('reports.show');

        Route::resource('announcements', AnnouncementController::class)->except('show');
    });

    // ---- Administrator only
    Route::middleware('role:admin')->group(function () {
        Route::resource('users', UserController::class)->except('show');
    });
});
