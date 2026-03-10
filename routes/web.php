<?php

use App\Http\Controllers\BookingEngineController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DisasterHistoryController;
use App\Http\Controllers\IotDataController;
use App\Http\Controllers\ObjectDetectionController;
use App\Http\Controllers\PassengerInformController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\RiskAreaController;
use App\Http\Controllers\RoleManagementController;
use App\Http\Controllers\ScheduleManagementController;
use App\Http\Controllers\TicketPriceController;
use App\Http\Controllers\TrainManagementController;
use App\Http\Controllers\UserManagementController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Auth::routes();

Route::get('/', function () {
    return redirect()->route('dashboard');
})->name('welcome');

Route::get('/home', function () {
    return redirect()->route('dashboard');
})->name('home');

Route::middleware(['auth'])->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Admin & Staff - User Management (Admin only)
    Route::middleware('role:admin')->group(function () {
        Route::prefix('admin/users')->name('users.')->group(function () {
            Route::get('/', [UserManagementController::class, 'index'])->name('index');
            Route::get('/data', [UserManagementController::class, 'getUsersData'])->name('data');
            Route::get('/roles', [UserManagementController::class, 'getRoles'])->name('roles');
            Route::post('/', [UserManagementController::class, 'store'])->name('store');
            Route::get('/{user}/show', [UserManagementController::class, 'show'])->name('show');
            Route::get('/{user}/edit', [UserManagementController::class, 'edit'])->name('edit');
            Route::put('/{user}', [UserManagementController::class, 'update'])->name('update');
            Route::delete('/{user}', [UserManagementController::class, 'destroy'])->name('destroy');
            Route::post('/{user}/suspend', [UserManagementController::class, 'suspend'])->name('suspend');
        });

        // Role Management (Admin only)
        Route::prefix('admin/roles')->name('roles.')->group(function () {
            Route::get('/', [RoleManagementController::class, 'index'])->name('index');
            Route::get('/data', [RoleManagementController::class, 'getRolesData'])->name('data');
            Route::get('/permissions', [RoleManagementController::class, 'getPermissions'])->name('permissions');
            Route::post('/', [RoleManagementController::class, 'store'])->name('store');
            Route::get('/{role}/show', [RoleManagementController::class, 'show'])->name('show');
            Route::put('/{role}', [RoleManagementController::class, 'update'])->name('update');
            Route::delete('/{role}', [RoleManagementController::class, 'destroy'])->name('destroy');
        });
    });

    // Admin & Staff - Trains List (for dropdowns)
    Route::get('/admin/trains/list', [TrainManagementController::class, 'getTrainsList'])->name('trains.list');

    // Admin - Train Management
    Route::middleware('role:admin')->group(function () {
        Route::prefix('admin/trains')->name('trains.')->group(function () {
            Route::get('/', [TrainManagementController::class, 'index'])->name('index');
            Route::get('/data', [TrainManagementController::class, 'getTrainsData'])->name('data');
            Route::get('/create', [TrainManagementController::class, 'create'])->name('create');
            Route::post('/', [TrainManagementController::class, 'store'])->name('store');
            Route::get('/{train}/edit', [TrainManagementController::class, 'edit'])->name('edit');
            Route::put('/{train}', [TrainManagementController::class, 'update'])->name('update');
            Route::delete('/{train}', [TrainManagementController::class, 'destroy'])->name('destroy');
            Route::get('/{train}/seats', [TrainManagementController::class, 'getSeats'])->name('seats');
            Route::put('/seat/{seat}', [TrainManagementController::class, 'updateSeat'])->name('seat.update');
        });

        // Ticket Prices (Seat Classes)
        Route::prefix('admin/trains/{train}/prices')->name('prices.')->group(function () {
            Route::get('/', [TicketPriceController::class, 'index'])->name('index');
            Route::get('/data', [TicketPriceController::class, 'getData'])->name('data');
            Route::post('/', [TicketPriceController::class, 'store'])->name('store');
            Route::put('/{seatClass}', [TicketPriceController::class, 'update'])->name('update');
            Route::delete('/{seatClass}', [TicketPriceController::class, 'destroy'])->name('destroy');
        });
    });

    // Admin & Staff - Schedule Management
    Route::middleware('role:admin|staff')->group(function () {
        Route::prefix('admin/schedules')->name('schedules.')->group(function () {
            Route::get('/', [ScheduleManagementController::class, 'index'])->name('index');
            Route::get('/data', [ScheduleManagementController::class, 'getSchedulesData'])->name('data');
            Route::get('/create', [ScheduleManagementController::class, 'create'])->name('create');
            Route::post('/', [ScheduleManagementController::class, 'store'])->name('store');
            Route::get('/{schedule}/edit', [ScheduleManagementController::class, 'edit'])->name('edit');
            Route::put('/{schedule}', [ScheduleManagementController::class, 'update'])->name('update');
            Route::delete('/{schedule}', [ScheduleManagementController::class, 'destroy'])->name('destroy');
            Route::get('/{schedule}/details', [ScheduleManagementController::class, 'getScheduleDetails'])->name('details');
        });
    });

    // Admin & Staff - View All Bookings
    Route::middleware('role:admin|staff')->group(function () {
        Route::get('/admin/bookings', [BookingEngineController::class, 'allBookings'])->name('admin.bookings');
        Route::prefix('admin/bookings')->name('bookings.')->group(function () {
            Route::get('/data', [BookingEngineController::class, 'getAllBookingsData'])->name('data');
            Route::get('/{booking}', [BookingEngineController::class, 'show'])->name('show');
            Route::post('/{booking}/cancel', [BookingEngineController::class, 'cancelBooking'])->name('cancel');
            Route::get('/export', [BookingEngineController::class, 'exportBookings'])->name('export');
        });

        // Staff Booking Creation (Admin & Staff)
        Route::prefix('admin/booking')->name('staff-booking.')->group(function () {
            Route::get('/create', [BookingEngineController::class, 'staffCreateBooking'])->name('create');
            Route::get('/search-users', [BookingEngineController::class, 'searchUsersByEmail'])->name('search-users');
            Route::get('/stations', [BookingEngineController::class, 'getStations'])->name('stations');
            Route::post('/book-for-user', [BookingEngineController::class, 'bookTicketForUser'])->name('book');
        });
    });

    // Booking Engine - Public (all authenticated users)
    Route::prefix('booking')->name('booking.')->group(function () {
        Route::get('/search', [BookingEngineController::class, 'search'])->name('search');
        Route::get('/stations', [BookingEngineController::class, 'getStations'])->name('stations');
        Route::post('/search', [BookingEngineController::class, 'searchSchedules'])->name('search-schedules');
        Route::get('/schedule/{schedule}/seats', [BookingEngineController::class, 'selectSeats'])->name('seats');
        Route::get('/schedule/{schedule}/seat-data', [BookingEngineController::class, 'getSeatData'])->name('seat-data');
        Route::post('/book', [BookingEngineController::class, 'bookTicket'])->name('book');
        Route::get('/my-bookings', [BookingEngineController::class, 'myBookings'])->name('my-bookings');
        Route::get('/bookings-data', [BookingEngineController::class, 'getUserBookings'])->name('bookings-data');
        Route::get('/tracking/{booking}', [BookingEngineController::class, 'trackBooking'])->name('tracking');
        Route::post('/{booking}/cancel', [BookingEngineController::class, 'cancelBooking'])->name('cancel');
        Route::get('/{booking}', [BookingEngineController::class, 'getBookingDetails'])->name('details');
        Route::get('/{booking}/pdf', [BookingEngineController::class, 'generateTicketPDF'])->name('pdf');
    });

    // Passenger Informs
    Route::middleware('permission:notifications.send')->group(function () {
        Route::prefix('admin/passenger-informs')->name('passenger-informs.')->group(function () {
            Route::get('/', [PassengerInformController::class, 'index'])->name('index');
            Route::post('/send', [PassengerInformController::class, 'send'])->name('send');
        });
    });

    // History & Dashboard
    Route::middleware('role:admin|staff')->group(function () {
        Route::get('/admin/history', [IotDataController::class, 'index'])->name('admin.history');
        Route::get('/admin/iot-dashboard', [IotDataController::class, 'dashboard'])->name('admin.iot-dashboard');
    });

    // Risk Areas
    Route::prefix('admin/risk-areas')->name('risk-areas.')->group(function () {
        Route::get('/', [RiskAreaController::class, 'index'])->name('index')->middleware('permission:risk_areas.view');
        Route::get('/create', [RiskAreaController::class, 'create'])->name('create')->middleware('permission:risk_areas.create');
        Route::post('/', [RiskAreaController::class, 'store'])->name('store')->middleware('permission:risk_areas.create');
        Route::get('/{riskArea}/edit', [RiskAreaController::class, 'edit'])->name('edit')->middleware('permission:risk_areas.edit');
        Route::put('/{riskArea}', [RiskAreaController::class, 'update'])->name('update')->middleware('permission:risk_areas.edit');
        Route::delete('/{riskArea}', [RiskAreaController::class, 'destroy'])->name('destroy')->middleware('permission:risk_areas.delete');
    });

    // Disaster & Detection History (Admin & Staff)
    Route::middleware('role:admin|staff')->group(function () {
        // Disaster History
        Route::prefix('admin/disaster-history')->name('disaster-history.')->group(function () {
            Route::get('/', [DisasterHistoryController::class, 'index'])->name('index');
        });

        // Object Detection History
        Route::prefix('admin/detection-history')->name('detection-history.')->group(function () {
            Route::get('/', [ObjectDetectionController::class, 'index'])->name('index');
        });
    });

    // Payment Integration
    Route::prefix('payment')->name('payment.')->group(function () {
        Route::get('/checkout', [PaymentController::class, 'checkout'])->name('checkout');
        Route::post('/process', [PaymentController::class, 'process'])->name('process');
        Route::get('/success', [PaymentController::class, 'success'])->name('success');
    });
});
