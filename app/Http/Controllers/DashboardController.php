<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __construct(private DashboardService $dashboards)
    {
    }

    public function index(Request $request): Response
    {
        $user = $request->user();

        if ($user->hasRole('admin')) {
            return Inertia::render('Dashboard/Admin', $this->dashboards->adminDashboard());
        }

        if ($user->hasRole('moderator')) {
            return Inertia::render('Dashboard/Moderator', $this->dashboards->moderatorDashboard($user));
        }

        return Inertia::render('Dashboard/Member', $this->dashboards->memberDashboard($user));
    }
}