<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\AssistanceController;
use App\Http\Controllers\ComplaintController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HouseholdController;
use App\Http\Controllers\DisasterController;
use App\Http\Controllers\ReliefController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ResidentController;
use App\Http\Controllers\ServiceRequestController;
use App\Models\Announcement;
use App\Models\Incident;
use Inertia\Inertia;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
});

// Guest routes
Route::middleware('guest')->group(function () {
    Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('login', [AuthenticatedSessionController::class, 'store'])->name('login.store');
});

// Authenticated routes
Route::middleware(['auth'])->group(function () {
    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');

    // Disaster information (read-only for authenticated users)
    Route::get('/calamities', [DisasterController::class, 'calamities'])->name('calamities.index');
    Route::get('/evacuation-centers', [DisasterController::class, 'evacuationCenters'])->name('evacuation-centers.index');
    Route::get('/evacuations', [DisasterController::class, 'evacuations'])->name('evacuations.index');
    Route::get('/relief-inventory', [ReliefController::class, 'inventory'])->name('relief-inventory.index');
    Route::get('/relief-distributions', [ReliefController::class, 'distributions'])->name('relief-distributions.index');

    Route::middleware('can:view households')->group(function () {
        Route::get('/households', [HouseholdController::class, 'index'])->name('households.index');
    });

    // Assistance
    Route::middleware('can:view assistance')->group(function () {
        Route::get('/assistance', [AssistanceController::class, 'index'])->name('assistance.index');
    });

    Route::middleware('can:create assistance')->group(function () {
        Route::get('/my-assistance', [AssistanceController::class, 'myAssistance'])->name('my-assistance');
        Route::get('/my-assistance/create', [AssistanceController::class, 'create'])->name('my-assistance.create');
        Route::post('/my-assistance', [AssistanceController::class, 'store'])->name('my-assistance.store');
    });

    // Member-facing community information
    Route::get('/announcements', function () {
        return Inertia::render('Announcements/Index', [
            'announcements' => Announcement::where('status', 'published')
                ->latest('published_at')
                ->get(),
        ]);
    })->name('announcements.index');

    Route::get('/incidents', function () {
        return Inertia::render('Incidents/Index', [
            'incidents' => Incident::with('purok')
                ->whereNotIn('status', ['resolved', 'closed'])
                ->latest('incident_datetime')
                ->get(),
        ]);
    })->name('incidents.index');

    // Residents (admin/moderator)
    Route::middleware('can:view residents')->group(function () {
        Route::get('/residents', [ResidentController::class, 'index'])->name('residents.index');
        Route::get('/residents/create', [ResidentController::class, 'create'])->name('residents.create')->middleware('can:create residents');
        Route::post('/residents', [ResidentController::class, 'store'])->name('residents.store')->middleware('can:create residents');
        Route::get('/residents/{resident}', [ResidentController::class, 'show'])->name('residents.show');
        Route::get('/residents/{resident}/edit', [ResidentController::class, 'edit'])->name('residents.edit')->middleware('can:update residents');
        Route::put('/residents/{resident}', [ResidentController::class, 'update'])->name('residents.update')->middleware('can:update residents');
        Route::delete('/residents/{resident}', [ResidentController::class, 'destroy'])->name('residents.destroy')->middleware('can:delete residents');
        Route::post('/residents/{resident}/verify', [ResidentController::class, 'verify'])->name('residents.verify')->middleware('can:verify residents');
        Route::post('/residents/{resident}/reject-verification', [ResidentController::class, 'rejectVerification'])->name('residents.reject-verification')->middleware('can:verify residents');
    });

    // Service Requests (admin/moderator management)
    Route::middleware('can:view requests')->group(function () {
        Route::get('/requests', [ServiceRequestController::class, 'index'])->name('requests.index');
        Route::get('/requests/{service_request}', [ServiceRequestController::class, 'show'])->name('requests.show');
        Route::post('/requests/{service_request}/assign', [ServiceRequestController::class, 'assign'])->name('requests.assign')->middleware('can:process requests');
        Route::post('/requests/{service_request}/process', [ServiceRequestController::class, 'process'])->name('requests.process')->middleware('can:process requests');
        Route::post('/requests/{service_request}/approve', [ServiceRequestController::class, 'approve'])->name('requests.approve')->middleware('can:approve requests');
        Route::post('/requests/{service_request}/reject', [ServiceRequestController::class, 'reject'])->name('requests.reject')->middleware('can:reject requests');
        Route::delete('/requests/{service_request}', [ServiceRequestController::class, 'destroy'])->name('requests.destroy')->middleware('can:delete requests');
    });

    // Member's own requests
    Route::middleware('can:create requests')->group(function () {
        Route::get('/my-requests', [ServiceRequestController::class, 'myRequests'])->name('my-requests');
        Route::get('/my-requests/create', [ServiceRequestController::class, 'create'])->name('my-requests.create');
        Route::post('/my-requests', [ServiceRequestController::class, 'store'])->name('my-requests.store');
    });

    // Complaints (admin/moderator management)
    Route::middleware('can:view complaints')->group(function () {
        Route::get('/complaints', [ComplaintController::class, 'index'])->name('complaints.index');
        Route::get('/complaints/{complaint}', [ComplaintController::class, 'show'])->name('complaints.show');
        Route::post('/complaints/{complaint}/assign', [ComplaintController::class, 'assign'])->name('complaints.assign')->middleware('can:assign complaints');
        Route::post('/complaints/{complaint}/process', [ComplaintController::class, 'process'])->name('complaints.process')->middleware('can:process complaints');
        Route::post('/complaints/{complaint}/resolve', [ComplaintController::class, 'resolve'])->name('complaints.resolve')->middleware('can:resolve complaints');
        Route::delete('/complaints/{complaint}', [ComplaintController::class, 'destroy'])->name('complaints.destroy')->middleware('can:delete complaints');
    });

    // Member's own complaints
    Route::middleware('can:create complaints')->group(function () {
        Route::get('/my-complaints', [ComplaintController::class, 'myComplaints'])->name('my-complaints');
        Route::get('/my-complaints/create', [ComplaintController::class, 'create'])->name('my-complaints.create');
        Route::post('/my-complaints', [ComplaintController::class, 'store'])->name('my-complaints.store');
    });
});