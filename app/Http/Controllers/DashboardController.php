<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\AssistanceRequest;
use App\Models\Calamity;
use App\Models\Complaint;
use App\Models\EvacuationRegistration;
use App\Models\Household;
use App\Models\Incident;
use App\Models\InventoryItem;
use App\Models\MemberProfile;
use App\Models\ServiceRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();

        if ($user->hasRole('admin')) {
            return $this->adminDashboard();
        }

        if ($user->hasRole('moderator')) {
            return $this->moderatorDashboard($user);
        }

        return $this->memberDashboard($user);
    }

    private function adminDashboard(): Response
    {
        $stats = [
            'total_residents' => MemberProfile::count(),
            'total_households' => Household::count(),
            'pending_verifications' => MemberProfile::where('verification_status', 'pending')->count(),
            'open_complaints' => Complaint::whereNotIn('status', ['resolved', 'closed', 'rejected'])->count(),
            'pending_requests' => ServiceRequest::whereNotIn('status', ['released', 'rejected', 'cancelled'])->count(),
            'active_calamities' => Calamity::whereIn('status', ['reported', 'active', 'under_response'])->count(),
            'active_incidents' => Incident::whereNotIn('status', ['resolved', 'closed'])->count(),
            'current_evacuees' => EvacuationRegistration::whereNull('time_out')->count(),
            'low_stock_items' => InventoryItem::whereColumn('current_stock', '<=', 'reorder_level')->count(),
            'pending_assistance' => AssistanceRequest::whereIn('status', ['submitted', 'for_verification', 'assessment', 'approved', 'for_release'])->count(),
        ];

        // Charts data
        $complaintsByCategory = Complaint::selectRaw('complaint_categories.name as label, COUNT(*) as value')
            ->join('complaint_categories', 'complaints.category_id', '=', 'complaint_categories.id')
            ->groupBy('complaint_categories.name')
            ->get();

        $requestsByType = ServiceRequest::selectRaw('request_types.name as label, COUNT(*) as value')
            ->join('request_types', 'service_requests.request_type_id', '=', 'request_types.id')
            ->groupBy('request_types.name')
            ->get();

        $requestsByStatus = ServiceRequest::selectRaw('status as label, COUNT(*) as value')
            ->groupBy('status')
            ->get();

        $residentsByPurok = MemberProfile::selectRaw('puroks.name as label, COUNT(*) as value')
            ->join('puroks', 'member_profiles.purok_id', '=', 'puroks.id')
            ->groupBy('puroks.name')
            ->get();

        $incidentsByType = Incident::selectRaw('type as label, COUNT(*) as value')
            ->groupBy('type')
            ->get();

        $monthlyComplaints = Complaint::selectRaw("DATE_FORMAT(created_at, '%Y-%m') as month, COUNT(*) as value")
            ->where('created_at', '>=', now()->subMonths(6))
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $monthlyRequests = ServiceRequest::selectRaw("DATE_FORMAT(submitted_at, '%Y-%m') as month, COUNT(*) as value")
            ->where('submitted_at', '>=', now()->subMonths(6))
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $assistanceByCategory = AssistanceRequest::selectRaw('assistance_types.name as label, COUNT(*) as value')
            ->join('assistance_types', 'assistance_requests.assistance_type_id', '=', 'assistance_types.id')
            ->groupBy('assistance_types.name')
            ->get();

        return Inertia::render('Dashboard/Admin', [
            'stats' => $stats,
            'charts' => [
                'complaintsByCategory' => $complaintsByCategory,
                'requestsByType' => $requestsByType,
                'requestsByStatus' => $requestsByStatus,
                'residentsByPurok' => $residentsByPurok,
                'incidentsByType' => $incidentsByType,
                'monthlyComplaints' => $monthlyComplaints,
                'monthlyRequests' => $monthlyRequests,
                'assistanceByCategory' => $assistanceByCategory,
            ],
        ]);
    }

    private function moderatorDashboard($user): Response
    {
        $stats = [
            'assigned_complaints' => Complaint::where('assigned_to', $user->id)
                ->whereNotIn('status', ['resolved', 'closed'])
                ->count(),
            'pending_requests' => ServiceRequest::whereIn('status', ['submitted', 'for_verification'])
                ->count(),
            'unverified_residents' => MemberProfile::where('verification_status', 'pending')->count(),
            'active_incidents' => Incident::whereNotIn('status', ['resolved', 'closed'])->count(),
            'active_calamities' => Calamity::whereIn('status', ['reported', 'active', 'under_response'])->count(),
            'current_evacuees' => EvacuationRegistration::whereNull('time_out')->count(),
            'pending_assistance' => AssistanceRequest::whereIn('status', ['submitted', 'for_verification', 'assessment'])->count(),
            'recent_distributions' => \App\Models\ReliefDistributionEvent::whereMonth('distribution_date', now()->month)->count(),
        ];

        $urgentComplaints = Complaint::with(['category', 'complainant.memberProfile'])
            ->whereIn('priority', ['high', 'urgent'])
            ->whereNotIn('status', ['resolved', 'closed'])
            ->latest()
            ->take(5)
            ->get();

        $recentRequests = ServiceRequest::with(['requester.memberProfile', 'resident', 'requestType'])
            ->whereIn('status', ['submitted', 'for_verification'])
            ->latest()
            ->take(5)
            ->get();

        return Inertia::render('Dashboard/Moderator', [
            'stats' => $stats,
            'urgentComplaints' => $urgentComplaints,
            'recentRequests' => $recentRequests,
        ]);
    }

    private function memberDashboard($user): Response
    {
        $myRequests = ServiceRequest::with('requestType')
            ->where('requester_id', $user->id)
            ->latest()
            ->take(5)
            ->get();

        $myComplaints = Complaint::with('category')
            ->where('complainant_id', $user->id)
            ->latest()
            ->take(5)
            ->get();

        $myAssistance = AssistanceRequest::with('assistanceType')
            ->where('applicant_id', $user->id)
            ->latest()
            ->take(5)
            ->get();

        $announcements = Announcement::where('status', 'published')
            ->orderByRaw("FIELD(priority, 'emergency', 'important', 'normal')")
            ->latest('published_at')
            ->take(5)
            ->get();

        $notifications = $user->notifications()->take(5)->get();
        $unreadNotifications = $user->unreadNotifications()->count();

        return Inertia::render('Dashboard/Member', [
            'myRequests' => $myRequests,
            'myComplaints' => $myComplaints,
            'myAssistance' => $myAssistance,
            'announcements' => $announcements,
            'notifications' => $notifications,
            'unreadCount' => $unreadNotifications,
        ]);
    }
}