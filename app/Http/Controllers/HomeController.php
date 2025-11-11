<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\User;

class HomeController extends Controller
{
    public function index()
    {
        // Redirect authenticated users to dashboard
        if (auth()->check()) {
            return redirect()->route('dashboard');
        }

        // Get real document statistics for the landing page
        $pendingStatuses = [
            Document::STATUS_DRAFT,
            Document::STATUS_ROUTING,
            Document::STATUS_RECEIVED,
            Document::STATUS_IN_REVIEW,
            Document::STATUS_FOR_APPROVAL,
            Document::STATUS_RETURNED,
        ];

        $completedStatuses = [
            Document::STATUS_COMPLETED,
            Document::STATUS_APPROVED,
        ];

        $inProgressStatuses = [
            Document::STATUS_ROUTING,
            Document::STATUS_RECEIVED,
            Document::STATUS_IN_REVIEW,
            Document::STATUS_FOR_APPROVAL,
        ];

        // Current counts
        $totalDocuments = Document::count();
        $inProgress = Document::whereIn('status', $inProgressStatuses)->count();
        $completed = Document::whereIn('status', $completedStatuses)->count();
        $pending = Document::whereIn('status', $pendingStatuses)->count();

        // Calculate month-over-month changes (comparing last 30 days vs previous 30 days)
        $thirtyDaysAgo = now()->subDays(30);
        $sixtyDaysAgo = now()->subDays(60);

        // Documents created in last 30 days vs previous 30 days
        $documentsLast30Days = Document::where('created_at', '>=', $thirtyDaysAgo)->count();
        $documentsPrevious30Days = Document::whereBetween('created_at', [$sixtyDaysAgo, $thirtyDaysAgo])->count();
        $totalDocumentsChange = $documentsPrevious30Days > 0
            ? round((($documentsLast30Days - $documentsPrevious30Days) / $documentsPrevious30Days) * 100, 1)
            : ($documentsLast30Days > 0 ? 100 : 0);

        // In Progress: count updated in last 30 days vs previous 30 days
        $inProgressLast30Days = Document::whereIn('status', $inProgressStatuses)
            ->where('updated_at', '>=', $thirtyDaysAgo)
            ->count();
        $inProgressPrevious30Days = Document::whereIn('status', $inProgressStatuses)
            ->whereBetween('updated_at', [$sixtyDaysAgo, $thirtyDaysAgo])
            ->count();
        $inProgressChange = $inProgressPrevious30Days > 0
            ? round((($inProgressLast30Days - $inProgressPrevious30Days) / $inProgressPrevious30Days) * 100, 1)
            : ($inProgressLast30Days > 0 ? 100 : 0);

        // Completed: count updated in last 30 days vs previous 30 days
        $completedLast30Days = Document::whereIn('status', $completedStatuses)
            ->where('updated_at', '>=', $thirtyDaysAgo)
            ->count();
        $completedPrevious30Days = Document::whereIn('status', $completedStatuses)
            ->whereBetween('updated_at', [$sixtyDaysAgo, $thirtyDaysAgo])
            ->count();
        $completedChange = $completedPrevious30Days > 0
            ? round((($completedLast30Days - $completedPrevious30Days) / $completedPrevious30Days) * 100, 1)
            : ($completedLast30Days > 0 ? 100 : 0);

        // Pending: count updated in last 30 days vs previous 30 days
        $pendingLast30Days = Document::whereIn('status', $pendingStatuses)
            ->where('updated_at', '>=', $thirtyDaysAgo)
            ->count();
        $pendingPrevious30Days = Document::whereIn('status', $pendingStatuses)
            ->whereBetween('updated_at', [$sixtyDaysAgo, $thirtyDaysAgo])
            ->count();
        $pendingChange = $pendingPrevious30Days > 0
            ? round((($pendingLast30Days - $pendingPrevious30Days) / $pendingPrevious30Days) * 100, 1)
            : ($pendingLast30Days > 0 ? 100 : 0);

        $stats = [
            'total_documents' => $totalDocuments,
            'total_documents_change' => $totalDocumentsChange,
            'in_progress' => $inProgress,
            'in_progress_change' => $inProgressChange,
            'completed' => $completed,
            'completed_change' => $completedChange,
            'pending' => $pending,
            'pending_change' => $pendingChange,
        ];

        return view('welcome', compact('stats'));
    }

    public function dashboard()
    {
        $user = auth()->user();
        $userStats = [];
        $documentStats = [];

        $pendingStatuses = [
            Document::STATUS_DRAFT,
            Document::STATUS_ROUTING,
            Document::STATUS_RECEIVED,
            Document::STATUS_IN_REVIEW,
            Document::STATUS_FOR_APPROVAL,
            Document::STATUS_RETURNED,
        ];

        $completedStatuses = [
            Document::STATUS_COMPLETED,
            Document::STATUS_APPROVED,
        ];

        // Admin: All user statistics
        if ($user->isAdmin()) {
            $userStats = [
                'total_users' => User::count(),
                'total_students' => User::where('usertype', 'student')->count(),
                'total_faculty' => User::where('usertype', 'faculty')->count(),
                'total_staff' => User::where('usertype', 'staff')->count(),
                'total_admins' => User::where('usertype', 'admin')->count(),
                'total_registrars' => User::where('usertype', 'registrar')->count(),
                'total_deans' => User::where('usertype', 'dean')->count(),
                'total_department_heads' => User::where('usertype', 'department_head')->count(),
                'new_users_today' => User::whereDate('created_at', today())->count(),
                'new_users_this_month' => User::whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year)
                    ->count(),
            ];

            // Admin document stats
            $documentStats = [
                'total_documents' => Document::count(),
                'pending_documents' => Document::whereIn('status', $pendingStatuses)->count(),
                'in_transit_documents' => Document::where('status', Document::STATUS_ROUTING)->count(),
                'completed_documents' => Document::whereIn('status', $completedStatuses)->count(),
                'urgent_documents' => Document::where('urgency_level', 'urgent')
                    ->whereNotIn('status', [Document::STATUS_COMPLETED, Document::STATUS_ARCHIVED])
                    ->count(),
                'documents_today' => Document::whereDate('created_at', today())->count(),
            ];
        }
        // Registrar: See all user stats
        elseif ($user->isRegistrar()) {
            $userStats = [
                'total_users' => User::count(),
                'total_students' => User::where('usertype', 'student')->count(),
                'total_faculty' => User::where('usertype', 'faculty')->count(),
                'total_staff' => User::where('usertype', 'staff')->count(),
            ];

            // Registrar document stats
            $documentStats = [
                'my_documents' => Document::where('created_by', $user->id)->count(),
                'incoming_documents' => Document::where('current_holder_id', $user->id)
                    ->whereNotIn('status', [Document::STATUS_ARCHIVED])
                    ->count(),
                'pending_actions' => Document::where('current_holder_id', $user->id)
                    ->whereIn('status', $pendingStatuses)
                    ->count(),
                'completed_documents' => Document::where('created_by', $user->id)
                    ->whereIn('status', $completedStatuses)
                    ->count(),
            ];
        }
        // Dean: See stats for their college
        elseif ($user->isDean() && $user->department_id) {
            $college = $user->department;
            $userStats = [
                'college_name' => $college->name,
                'total_faculty' => User::where('department_id', $user->department_id)
                    ->where('usertype', 'faculty')->count(),
                'total_students' => User::whereHas('program', function ($q) use ($college) {
                    $q->where('college_id', $college->id);
                })->where('usertype', 'student')->count(),
            ];

            // Dean document stats
            $documentStats = [
                'my_documents' => Document::where('created_by', $user->id)->count(),
                'incoming_documents' => Document::where('current_holder_id', $user->id)
                    ->whereNotIn('status', [Document::STATUS_ARCHIVED])
                    ->count(),
                'pending_actions' => Document::where('current_holder_id', $user->id)
                    ->whereIn('status', $pendingStatuses)
                    ->count(),
                'college_documents' => Document::where('origin_department_id', $user->department_id)->count(),
            ];
        }
        // Department Head: See stats for their department
        elseif ($user->isDepartmentHead() && $user->department_id) {
            $department = $user->department;
            $userStats = [
                'department_name' => $department->name,
                'total_staff' => User::where('department_id', $user->department_id)
                    ->where('usertype', 'staff')->count(),
            ];

            // Department Head document stats
            $documentStats = [
                'my_documents' => Document::where('created_by', $user->id)->count(),
                'incoming_documents' => Document::where('current_holder_id', $user->id)
                    ->whereNotIn('status', [Document::STATUS_ARCHIVED])
                    ->count(),
                'pending_actions' => Document::where('current_holder_id', $user->id)
                    ->whereIn('status', $pendingStatuses)
                    ->count(),
                'department_documents' => Document::where('origin_department_id', $user->department_id)->count(),
            ];
        }
        // Other users (Faculty, Staff, Student)
        else {
            $documentStats = [
                'my_documents' => Document::where('created_by', $user->id)->count(),
                'incoming_documents' => Document::where('current_holder_id', $user->id)
                    ->whereNotIn('status', [Document::STATUS_ARCHIVED])
                    ->count(),
                'pending_actions' => Document::where('current_holder_id', $user->id)
                    ->whereIn('status', $pendingStatuses)
                    ->count(),
                'completed_documents' => Document::where('created_by', $user->id)
                    ->whereIn('status', $completedStatuses)
                    ->count(),
            ];
        }

        return view('dashboard', compact('userStats', 'documentStats'));
    }
}
