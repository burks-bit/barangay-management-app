<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\AssistanceController;
use App\Http\Controllers\BarangayProfileController;
use App\Http\Controllers\CalamityController;
use App\Http\Controllers\ComplaintController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HouseholdController;
use App\Http\Controllers\DisasterController;
use App\Http\Controllers\EvacuationCenterController;
use App\Http\Controllers\ReliefController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ResidentController;
use App\Http\Controllers\ServiceRequestController;
use App\Http\Controllers\RequestTypeController;
use App\Http\Controllers\UserController;
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
    Route::get('/requests/{service_request}/download', [ServiceRequestController::class, 'download'])->name('requests.download');
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'read'])->name('notifications.read');

    Route::post('/evacuation-centers/select', [DisasterController::class, 'selectEvacuationCenter'])->name('evacuation-centers.select');
    Route::post('/evacuation-centers/return', [DisasterController::class, 'returnHome'])->name('evacuation-centers.return');
    Route::get('/evacuations', [DisasterController::class, 'evacuations'])->name('evacuations.index');
    Route::get('/relief-inventory', [ReliefController::class, 'inventory'])->name('relief-inventory.index');
    Route::get('/relief-distributions', [ReliefController::class, 'distributions'])->name('relief-distributions.index');

    // Barangay Profile (admin)
    Route::middleware('can:manage settings')->group(function () {
        Route::get('/barangay', [BarangayProfileController::class, 'index'])->name('barangay.index');
        Route::get('/barangay/create', [BarangayProfileController::class, 'create'])->name('barangay.create');
        Route::post('/barangay', [BarangayProfileController::class, 'store'])->name('barangay.store');
        Route::get('/barangay/{barangay}', [BarangayProfileController::class, 'show'])->name('barangay.show');
        Route::get('/barangay/{barangay}/edit', [BarangayProfileController::class, 'edit'])->name('barangay.edit');
        Route::put('/barangay/{barangay}', [BarangayProfileController::class, 'update'])->name('barangay.update');
        Route::delete('/barangay/{barangay}', [BarangayProfileController::class, 'destroy'])->name('barangay.destroy');

        // Officials
        Route::post('/barangay/{barangay}/officials', [BarangayProfileController::class, 'storeOfficial'])->name('barangay.officials.store');
        Route::put('/barangay/{barangay}/officials/{official}', [BarangayProfileController::class, 'updateOfficial'])->name('barangay.officials.update');
        Route::delete('/barangay/{barangay}/officials/{official}', [BarangayProfileController::class, 'destroyOfficial'])->name('barangay.officials.destroy');
    });

    Route::middleware('can:manage users')->group(function () {
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::put('/users/{user}/role', [UserController::class, 'updateRole'])->name('users.role');
    });

    // Households (admin management)
    Route::middleware('can:view households')->group(function () {
        Route::get('/households', [HouseholdController::class, 'index'])->name('households.index');
    });
    Route::middleware('can:create households')->group(function () {
        Route::get('/households/create', [HouseholdController::class, 'create'])->name('households.create');
        Route::post('/households', [HouseholdController::class, 'store'])->name('households.store');
    });
    Route::middleware('can:update households')->group(function () {
        Route::get('/households/{household}/edit', [HouseholdController::class, 'edit'])->name('households.edit');
        Route::put('/households/{household}', [HouseholdController::class, 'update'])->name('households.update');
        Route::post('/households/{household}/evacuate', [HouseholdController::class, 'evacuate'])->name('households.evacuate');
        Route::post('/households/{household}/return-home', [HouseholdController::class, 'returnHome'])->name('households.return-home');
    });
    Route::middleware('can:delete households')->group(function () {
        Route::delete('/households/{household}', [HouseholdController::class, 'destroy'])->name('households.destroy');
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

    // Calamities (admin/moderator CRUD)
    Route::middleware('can:view calamities')->group(function () {
        Route::get('/calamities', [CalamityController::class, 'index'])->name('calamities.index');
    });
    Route::middleware('can:create calamities')->group(function () {
        Route::get('/calamities/create', [CalamityController::class, 'create'])->name('calamities.create');
        Route::post('/calamities', [CalamityController::class, 'store'])->name('calamities.store');
    });
    Route::middleware('can:update calamities')->group(function () {
        Route::get('/calamities/{calamity}/edit', [CalamityController::class, 'edit'])->name('calamities.edit');
        Route::put('/calamities/{calamity}', [CalamityController::class, 'update'])->name('calamities.update');
    });
    Route::middleware('can:delete calamities')->group(function () {
        Route::delete('/calamities/{calamity}', [CalamityController::class, 'destroy'])->name('calamities.destroy');
    });

    // Evacuation Centers (admin/moderator CRUD)
    Route::middleware('can:view evacuation centers')->group(function () {
        Route::get('/evacuation-centers', [EvacuationCenterController::class, 'index'])->name('evacuation-centers.index');
        Route::get('/evacuation-center', [EvacuationCenterController::class, 'index'])->name('evacuation-center.index');
    });
    Route::middleware('can:create evacuation centers')->group(function () {
        Route::get('/evacuation-centers/create', [EvacuationCenterController::class, 'create'])->name('evacuation-centers.create');
        Route::post('/evacuation-centers', [EvacuationCenterController::class, 'store'])->name('evacuation-centers.store');
    });
    Route::middleware('can:update evacuation centers')->group(function () {
        Route::get('/evacuation-centers/{evacuation_center}/edit', [EvacuationCenterController::class, 'edit'])->name('evacuation-centers.edit');
        Route::put('/evacuation-centers/{evacuation_center}', [EvacuationCenterController::class, 'update'])->name('evacuation-centers.update');
    });
    Route::middleware('can:delete evacuation centers')->group(function () {
        Route::delete('/evacuation-centers/{evacuation_center}', [EvacuationCenterController::class, 'destroy'])->name('evacuation-centers.destroy');
    });

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
        // Walk-in: staff encodes a request on behalf of a resident (must be
        // registered before the {service_request} wildcard route).
        Route::get('/requests/create', [ServiceRequestController::class, 'createWalkIn'])->name('requests.create')->middleware('can:process requests');
        Route::post('/requests', [ServiceRequestController::class, 'storeWalkIn'])->name('requests.store')->middleware('can:process requests');
        Route::get('/requests/{service_request}', [ServiceRequestController::class, 'show'])->name('requests.show');
        Route::get('/requests/{service_request}/preview', [ServiceRequestController::class, 'download'])->name('requests.preview');
        Route::post('/requests/{service_request}/assign', [ServiceRequestController::class, 'assign'])->name('requests.assign')->middleware('can:process requests');
        Route::post('/requests/{service_request}/process', [ServiceRequestController::class, 'process'])->name('requests.process')->middleware('can:process requests');
        Route::post('/requests/{service_request}/approve', [ServiceRequestController::class, 'approve'])->name('requests.approve')->middleware('can:approve requests');
        Route::post('/requests/{service_request}/reject', [ServiceRequestController::class, 'reject'])->name('requests.reject')->middleware('can:reject requests');
        Route::post('/requests/{service_request}/encode', [ServiceRequestController::class, 'encode'])->name('requests.encode')->middleware('can:process requests');
        Route::post('/requests/{service_request}/release', [ServiceRequestController::class, 'release'])->name('requests.release')->middleware('can:approve requests');
        Route::delete('/requests/{service_request}', [ServiceRequestController::class, 'destroy'])->name('requests.destroy')->middleware('can:delete requests');
    });

    Route::middleware('role:admin|moderator')->group(function () {
        Route::get('/request-types', [RequestTypeController::class, 'index'])->name('request-types.index');
        Route::post('/request-types', [RequestTypeController::class, 'store'])->name('request-types.store');
        Route::put('/request-types/{request_type}', [RequestTypeController::class, 'update'])->name('request-types.update');
        Route::delete('/request-types/{request_type}', [RequestTypeController::class, 'destroy'])->name('request-types.destroy');
    });

    // Member's own requests
    Route::middleware('can:create requests')->group(function () {
        Route::get('/my-requests', [ServiceRequestController::class, 'myRequests'])->name('my-requests');
        Route::get('/my-requests/create', [ServiceRequestController::class, 'create'])->name('my-requests.create');
        Route::post('/my-requests', [ServiceRequestController::class, 'store'])->name('my-requests.store');
        Route::get('/my-requests/{service_request}', [ServiceRequestController::class, 'myShow'])->name('my-requests.show');
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
        Route::get('/my-complaints/{complaint}', [ComplaintController::class, 'myShow'])->name('my-complaints.show');
    });
});