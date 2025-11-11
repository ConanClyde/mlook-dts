<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\DocumentTracking;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Display the dashboard with analytics.
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // Basic document counts
        $stats = [
            'total_documents' => $this->getUserDocumentCount($user),
            'pending' => $this->getPendingDocumentCount($user),
            'in_transit' => $this->getInTransitDocumentCount($user),
            'completed' => $this->getCompletedDocumentCount($user),
            'incoming' => Document::where('current_holder_id', $user->id)
                ->whereNotIn('status', [Document::STATUS_ARCHIVED])
                ->count(),
            'outgoing' => Document::where('created_by', $user->id)->count(),
        ];

        // Recent documents
        $recentDocuments = $this->getRecentDocuments($user)->take(5);

        // Processing time statistics
        $avgProcessingTime = $this->getAverageProcessingTime($user);

        // Department statistics (for Deans and Dept Heads)
        $departmentStats = null;
        if ($user->isDean() || $user->isDepartmentHead()) {
            $departmentStats = $this->getDepartmentStatistics($user);
        }

        // Document type distribution
        $documentTypeDistribution = $this->getDocumentTypeDistribution($user);

        // Monthly activity (last 6 months)
        $monthlyActivity = $this->getMonthlyActivity($user);

        // Status distribution for pie chart
        $statusDistribution = $this->getStatusDistribution($user);

        // Urgency level distribution
        $urgencyDistribution = $this->getUrgencyDistribution($user);

        // Weekly activity (last 7 days)
        $weeklyActivity = $this->getWeeklyActivity($user);

        // Overdue documents count
        $overdueCount = $this->getOverdueCount($user);

        // Document stats for existing dashboard UI
        $documentStats = $this->getDocumentStats($user);

        // User stats for existing dashboard UI
        $userStats = $this->getUserStats($user);

        return view('dashboard', compact(
            'stats',
            'recentDocuments',
            'avgProcessingTime',
            'departmentStats',
            'documentTypeDistribution',
            'monthlyActivity',
            'statusDistribution',
            'urgencyDistribution',
            'weeklyActivity',
            'overdueCount',
            'documentStats',
            'userStats'
        ));
    }

    /**
     * Get user's document count based on role.
     */
    private function getUserDocumentCount($user)
    {
        if ($user->isAdmin()) {
            return Document::count();
        }

        if ($user->isDean() || $user->isDepartmentHead()) {
            return Document::where('origin_department_id', $user->department_id)->count();
        }

        return Document::where(function ($q) use ($user) {
            $q->where('created_by', $user->id)
                ->orWhere('current_holder_id', $user->id);
        })->count();
    }

    /**
     * Get pending document count.
     */
    private function getPendingDocumentCount($user)
    {
        $pendingStatuses = [
            Document::STATUS_DRAFT,
            Document::STATUS_ROUTING,
            Document::STATUS_RECEIVED,
            Document::STATUS_IN_REVIEW,
            Document::STATUS_FOR_APPROVAL,
            Document::STATUS_RETURNED,
        ];

        return Document::where('current_holder_id', $user->id)
            ->whereIn('status', $pendingStatuses)
            ->count();
    }

    /**
     * Get in-transit document count.
     */
    private function getInTransitDocumentCount($user)
    {
        $query = Document::where('status', Document::STATUS_ROUTING);

        if (! $user->isAdmin()) {
            $query->where(function ($q) use ($user) {
                $q->where('created_by', $user->id)
                    ->orWhere('current_holder_id', $user->id);
            });
        }

        return $query->count();
    }

    /**
     * Get completed document count.
     */
    private function getCompletedDocumentCount($user)
    {
        $query = Document::query();

        if (! $user->isAdmin()) {
            $query->where(function ($q) use ($user) {
                $q->where('created_by', $user->id)
                    ->orWhere('current_holder_id', $user->id);
            });
        }

        return $query->whereIn('status', [
            Document::STATUS_COMPLETED,
            Document::STATUS_APPROVED,
        ])->count();
    }

    /**
     * Get recent documents for user.
     */
    private function getRecentDocuments($user)
    {
        $query = Document::with(['documentType', 'creator', 'currentHolder'])
            ->orderBy('created_at', 'desc');

        if (! $user->isAdmin()) {
            $query->where(function ($q) use ($user) {
                $q->where('created_by', $user->id)
                    ->orWhere('current_holder_id', $user->id);
            });
        }

        return $query->get();
    }

    /**
     * Calculate average processing time.
     */
    private function getAverageProcessingTime($user)
    {
        $query = Document::whereNotNull('completed_at');

        if (! $user->isAdmin()) {
            $query->where(function ($q) use ($user) {
                $q->where('created_by', $user->id)
                    ->orWhere('current_holder_id', $user->id);
            });
        }

        $documents = $query->get();

        if ($documents->isEmpty()) {
            return 0;
        }

        $totalHours = 0;
        foreach ($documents as $document) {
            $totalHours += $document->created_at->diffInHours($document->completed_at);
        }

        return round($totalHours / $documents->count(), 1);
    }

    /**
     * Get department statistics.
     */
    private function getDepartmentStatistics($user)
    {
        return [
            'total' => Document::where('origin_department_id', $user->department_id)->count(),
            'pending' => Document::where('origin_department_id', $user->department_id)
                ->whereIn('status', [
                    Document::STATUS_DRAFT,
                    Document::STATUS_ROUTING,
                    Document::STATUS_RECEIVED,
                    Document::STATUS_IN_REVIEW,
                    Document::STATUS_FOR_APPROVAL,
                    Document::STATUS_RETURNED,
                ])->count(),
            'completed' => Document::where('origin_department_id', $user->department_id)
                ->whereIn('status', [Document::STATUS_COMPLETED, Document::STATUS_APPROVED])->count(),
            'avg_time' => $this->getDepartmentAvgProcessingTime($user),
        ];
    }

    /**
     * Get department average processing time.
     */
    private function getDepartmentAvgProcessingTime($user)
    {
        $documents = Document::where('origin_department_id', $user->department_id)
            ->whereNotNull('completed_at')
            ->get();

        if ($documents->isEmpty()) {
            return 0;
        }

        $totalHours = 0;
        foreach ($documents as $document) {
            $totalHours += $document->created_at->diffInHours($document->completed_at);
        }

        return round($totalHours / $documents->count(), 1);
    }

    /**
     * Get document type distribution.
     */
    private function getDocumentTypeDistribution($user)
    {
        $query = Document::select('document_type_id', DB::raw('count(*) as count'))
            ->with('documentType')
            ->groupBy('document_type_id');

        if (! $user->isAdmin()) {
            if ($user->isDean() || $user->isDepartmentHead()) {
                $query->where('origin_department_id', $user->department_id);
            } else {
                $query->where(function ($q) use ($user) {
                    $q->where('created_by', $user->id)
                        ->orWhere('current_holder_id', $user->id);
                });
            }
        }

        return $query->get();
    }

    /**
     * Get monthly activity for last 6 months.
     */
    private function getMonthlyActivity($user)
    {
        $sixMonthsAgo = now()->subMonths(6);

        $query = Document::select(
            DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'),
            DB::raw('count(*) as count')
        )
            ->where('created_at', '>=', $sixMonthsAgo)
            ->groupBy('month')
            ->orderBy('month');

        if (! $user->isAdmin()) {
            if ($user->isDean() || $user->isDepartmentHead()) {
                $query->where('origin_department_id', $user->department_id);
            } else {
                $query->where(function ($q) use ($user) {
                    $q->where('created_by', $user->id)
                        ->orWhere('current_holder_id', $user->id);
                });
            }
        }

        return $query->get();
    }

    /**
     * Get status distribution.
     */
    private function getStatusDistribution($user)
    {
        $query = Document::select('status', DB::raw('count(*) as count'))
            ->groupBy('status');

        if (! $user->isAdmin()) {
            if ($user->isDean() || $user->isDepartmentHead()) {
                $query->where('origin_department_id', $user->department_id);
            } else {
                $query->where(function ($q) use ($user) {
                    $q->where('created_by', $user->id)
                        ->orWhere('current_holder_id', $user->id);
                });
            }
        }

        return $query->get();
    }

    /**
     * Get urgency level distribution.
     */
    private function getUrgencyDistribution($user)
    {
        $query = Document::select('urgency_level', DB::raw('count(*) as count'))
            ->groupBy('urgency_level');

        if (! $user->isAdmin()) {
            if ($user->isDean() || $user->isDepartmentHead()) {
                $query->where('origin_department_id', $user->department_id);
            } else {
                $query->where(function ($q) use ($user) {
                    $q->where('created_by', $user->id)
                        ->orWhere('current_holder_id', $user->id);
                });
            }
        }

        return $query->get();
    }

    /**
     * Get weekly activity for last 7 days.
     */
    private function getWeeklyActivity($user)
    {
        $sevenDaysAgo = now()->subDays(7);

        $query = Document::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('count(*) as count')
        )
            ->where('created_at', '>=', $sevenDaysAgo)
            ->groupBy('date')
            ->orderBy('date');

        if (! $user->isAdmin()) {
            if ($user->isDean() || $user->isDepartmentHead()) {
                $query->where('origin_department_id', $user->department_id);
            } else {
                $query->where(function ($q) use ($user) {
                    $q->where('created_by', $user->id)
                        ->orWhere('current_holder_id', $user->id);
                });
            }
        }

        return $query->get();
    }

    /**
     * Get overdue documents count.
     */
    private function getOverdueCount($user)
    {
        $query = Document::where('is_overdue', true)
            ->whereNotIn('status', [Document::STATUS_COMPLETED, Document::STATUS_ARCHIVED]);

        if (! $user->isAdmin()) {
            if ($user->isDean() || $user->isDepartmentHead()) {
                $query->where('origin_department_id', $user->department_id);
            } else {
                $query->where(function ($q) use ($user) {
                    $q->where('created_by', $user->id)
                        ->orWhere('current_holder_id', $user->id);
                });
            }
        }

        return $query->count();
    }

    /**
     * Get document statistics for existing dashboard UI.
     */
    private function getDocumentStats($user)
    {
        if ($user->isAdmin()) {
            $pendingStatuses = [
                Document::STATUS_DRAFT,
                Document::STATUS_ROUTING,
                Document::STATUS_RECEIVED,
                Document::STATUS_IN_REVIEW,
                Document::STATUS_FOR_APPROVAL,
                Document::STATUS_RETURNED,
            ];

            return [
                // System-wide stats
                'total_documents' => Document::count(),
                'pending_documents' => Document::whereIn('status', $pendingStatuses)->count(),
                'completed_documents' => Document::whereIn('status', [Document::STATUS_COMPLETED, Document::STATUS_APPROVED])->count(),
                'urgent_documents' => Document::where('urgency_level', 'urgent')->count(),
                'documents_today' => Document::whereDate('created_at', today())->count(),

                // Admin personal stats
                'my_documents' => Document::where('created_by', $user->id)
                    ->where('status', Document::STATUS_DRAFT)
                    ->whereDoesntHave('tracking', function ($q) {
                        $q->where('action', '!=', DocumentTracking::ACTION_CREATED);
                    })
                    ->count(),
                'incoming_documents' => Document::where('current_holder_id', $user->id)
                    ->where('created_by', '!=', $user->id)
                    ->whereNotIn('status', [Document::STATUS_ARCHIVED, Document::STATUS_COMPLETED, Document::STATUS_APPROVED, Document::STATUS_DRAFT])
                    ->count(),
                'pending_actions' => Document::where('created_by', $user->id)
                    ->whereHas('tracking', function ($q) {
                        $q->where('action', '!=', DocumentTracking::ACTION_CREATED);
                    })
                    ->where('status', '!=', Document::STATUS_DRAFT)
                    ->whereNotIn('status', [Document::STATUS_COMPLETED, Document::STATUS_ARCHIVED])
                    ->count(),
                'my_completed' => Document::where(function ($q) use ($user) {
                    $q->where('created_by', $user->id)
                        ->orWhere('current_holder_id', $user->id)
                        ->orWhereHas('tracking', function ($tq) use ($user) {
                            $tq->where('from_user_id', $user->id)
                                ->orWhere('to_user_id', $user->id);
                        });
                })
                    ->whereIn('status', [Document::STATUS_COMPLETED, Document::STATUS_APPROVED])
                    ->count(),
                'archived_documents' => Document::where(function ($q) use ($user) {
                    $q->where('created_by', $user->id)
                        ->orWhere('current_holder_id', $user->id)
                        ->orWhereHas('tracking', function ($tq) use ($user) {
                            $tq->where('from_user_id', $user->id)
                                ->orWhere('to_user_id', $user->id);
                        });
                })
                    ->where('status', Document::STATUS_ARCHIVED)
                    ->count(),
            ];
        }

        return [
            // My Documents = Draft documents created by user (not forwarded yet)
            // Matches /documents page
            'my_documents' => Document::where('created_by', $user->id)
                ->where('status', Document::STATUS_DRAFT)
                ->whereDoesntHave('tracking', function ($q) {
                    $q->where('action', '!=', DocumentTracking::ACTION_CREATED);
                })
                ->count(),

            // Inbox = Documents sent TO user (incoming, excluding own documents)
            // Matches /inbox page
            'incoming_documents' => Document::where('current_holder_id', $user->id)
                ->where('created_by', '!=', $user->id)
                ->whereNotIn('status', [Document::STATUS_ARCHIVED, Document::STATUS_COMPLETED, Document::STATUS_APPROVED, Document::STATUS_DRAFT])
                ->count(),

            // Sent = Documents created by user that have been forwarded
            // Matches /sent page
            'pending_actions' => Document::where('created_by', $user->id)
                ->whereHas('tracking', function ($q) {
                    $q->where('action', '!=', DocumentTracking::ACTION_CREATED);
                })
                ->where('status', '!=', Document::STATUS_DRAFT)
                ->whereNotIn('status', [Document::STATUS_COMPLETED, Document::STATUS_ARCHIVED])
                ->count(),

            // Completed = Documents user was involved in that are completed
            // Matches /completed page
            'completed_documents' => Document::where(function ($q) use ($user) {
                $q->where('created_by', $user->id)
                    ->orWhere('current_holder_id', $user->id)
                    ->orWhereHas('tracking', function ($tq) use ($user) {
                        $tq->where('from_user_id', $user->id)
                            ->orWhere('to_user_id', $user->id);
                    });
            })
                ->whereIn('status', [Document::STATUS_COMPLETED, Document::STATUS_APPROVED])
                ->count(),

            // Archived = Documents user was involved in that are archived
            // Matches /archive page
            'archived_documents' => Document::where(function ($q) use ($user) {
                $q->where('created_by', $user->id)
                    ->orWhere('current_holder_id', $user->id)
                    ->orWhereHas('tracking', function ($tq) use ($user) {
                        $tq->where('from_user_id', $user->id)
                            ->orWhere('to_user_id', $user->id);
                    });
            })
                ->where('status', Document::STATUS_ARCHIVED)
                ->count(),
        ];
    }

    /**
     * Get user statistics for existing dashboard UI.
     */
    private function getUserStats($user)
    {
        if ($user->isAdmin() || $user->isRegistrar()) {
            return [
                'total_users' => User::count(),
                'total_students' => User::where('usertype', 'student')->count(),
                'total_faculty' => User::where('usertype', 'faculty')->count(),
                'total_staff' => User::where('usertype', 'staff')->count(),
                'total_admins' => User::where('usertype', 'admin')->count(),
                'total_registrars' => User::where('usertype', 'registrar')->count(),
                'total_deans' => User::where('usertype', 'dean')->count(),
                'total_department_heads' => User::where('usertype', 'department_head')->count(),
            ];
        }

        if ($user->isDean() && $user->department) {
            return [
                'college_name' => $user->department->name,
                'total_faculty' => User::where('usertype', 'faculty')
                    ->where('department_id', $user->department_id)->count(),
                'total_students' => User::where('usertype', 'student')
                    ->whereHas('program', function ($q) use ($user) {
                        $q->where('college_id', $user->department_id);
                    })->count(),
            ];
        }

        if ($user->isDepartmentHead() && $user->department) {
            return [
                'department_name' => $user->department->name,
                'total_staff' => User::where('usertype', 'staff')
                    ->where('department_id', $user->department_id)->count(),
            ];
        }

        // For regular users (students, faculty, staff without special roles)
        // Return basic stats about their own activity
        return [
            'user_type' => ucfirst(str_replace('_', ' ', $user->usertype)),
            'department_name' => $user->department ? $user->department->name : 'N/A',
            'program_name' => $user->program ? $user->program->name : 'N/A',
        ];
    }

    /**
     * Get document flow timeline for visualization.
     */
    public function getDocumentFlowTimeline(Request $request)
    {
        $user = Auth::user();
        $days = $request->input('days', 30);

        $query = DocumentTracking::selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->where('created_at', '>=', now()->subDays($days))
            ->groupBy('date')
            ->orderBy('date');

        if (! $user->isAdmin()) {
            $query->where(function ($q) use ($user) {
                $q->where('from_user_id', $user->id)
                    ->orWhere('to_user_id', $user->id);
            });
        }

        return response()->json($query->get());
    }

    /**
     * Get department-wise document statistics.
     */
    public function getDepartmentWiseStatistics(Request $request)
    {
        $user = Auth::user();

        $query = Document::join('departments', 'documents.origin_department_id', '=', 'departments.id')
            ->selectRaw('departments.name as department_name, 
                         COUNT(*) as total,
                         SUM(CASE WHEN documents.status = "completed" THEN 1 ELSE 0 END) as completed,
                         SUM(CASE WHEN documents.status = "approved" THEN 1 ELSE 0 END) as approved,
                         SUM(CASE WHEN documents.status IN ("draft", "routing", "received", "in_review", "for_approval", "returned") THEN 1 ELSE 0 END) as pending')
            ->groupBy('departments.id', 'departments.name')
            ->orderBy('total', 'desc');

        if (! $user->isAdmin()) {
            $query->where('departments.id', $user->department_id);
        }

        return response()->json($query->get());
    }

    /**
     * Get user activity metrics.
     */
    public function getUserActivityMetrics(Request $request)
    {
        $user = Auth::user();
        $days = $request->input('days', 30);

        $metrics = [
            'documents_created' => Document::where('created_by', $user->id)
                ->where('created_at', '>=', now()->subDays($days))
                ->count(),
            'documents_forwarded' => DocumentTracking::where('from_user_id', $user->id)
                ->where('action', DocumentTracking::ACTION_FORWARDED)
                ->where('created_at', '>=', now()->subDays($days))
                ->count(),
            'documents_received' => DocumentTracking::where('to_user_id', $user->id)
                ->where('action', DocumentTracking::ACTION_ACKNOWLEDGED)
                ->where('created_at', '>=', now()->subDays($days))
                ->count(),
            'documents_approved' => Document::where('approved_by', $user->id)
                ->where('approved_at', '>=', now()->subDays($days))
                ->count(),
            'documents_rejected' => Document::where('rejected_by', $user->id)
                ->where('rejected_at', '>=', now()->subDays($days))
                ->count(),
            'avg_response_time' => $this->getUserAvgResponseTime($user, $days),
        ];

        return response()->json($metrics);
    }

    /**
     * Get user average response time.
     */
    private function getUserAvgResponseTime($user, $days)
    {
        $trackings = DocumentTracking::where('to_user_id', $user->id)
            ->whereNotNull('received_at')
            ->where('created_at', '>=', now()->subDays($days))
            ->get();

        if ($trackings->isEmpty()) {
            return 0;
        }

        $totalHours = 0;
        foreach ($trackings as $tracking) {
            $totalHours += $tracking->sent_at->diffInHours($tracking->received_at);
        }

        return round($totalHours / $trackings->count(), 1);
    }

    /**
     * Get pending actions alert widget data.
     */
    public function getPendingActionsWidget(Request $request)
    {
        $user = Auth::user();

        $pendingActions = [
            'for_approval' => Document::where('current_holder_id', $user->id)
                ->where('status', Document::STATUS_FOR_APPROVAL)
                ->with(['documentType', 'creator'])
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get(),
            'overdue' => Document::where('current_holder_id', $user->id)
                ->where('is_overdue', true)
                ->whereNotIn('status', [Document::STATUS_COMPLETED, Document::STATUS_ARCHIVED])
                ->with(['documentType', 'creator'])
                ->orderBy('deadline', 'asc')
                ->limit(5)
                ->get(),
            'urgent' => Document::where('current_holder_id', $user->id)
                ->where('urgency_level', 'urgent')
                ->whereNotIn('status', [Document::STATUS_COMPLETED, Document::STATUS_ARCHIVED])
                ->with(['documentType', 'creator'])
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get(),
        ];

        return response()->json($pendingActions);
    }

    /**
     * Get document completion rate over time.
     */
    public function getCompletionRateOverTime(Request $request)
    {
        $user = Auth::user();
        $months = $request->input('months', 6);

        $query = Document::selectRaw('
                DATE_FORMAT(created_at, "%Y-%m") as month,
                COUNT(*) as total,
                SUM(CASE WHEN status IN ("completed", "approved") THEN 1 ELSE 0 END) as completed,
                ROUND((SUM(CASE WHEN status IN ("completed", "approved") THEN 1 ELSE 0 END) / COUNT(*)) * 100, 2) as completion_rate
            ')
            ->where('created_at', '>=', now()->subMonths($months))
            ->groupBy('month')
            ->orderBy('month');

        if (! $user->isAdmin()) {
            if ($user->isDean() || $user->isDepartmentHead()) {
                $query->where('origin_department_id', $user->department_id);
            } else {
                $query->where(function ($q) use ($user) {
                    $q->where('created_by', $user->id)
                        ->orWhere('current_holder_id', $user->id);
                });
            }
        }

        return response()->json($query->get());
    }

    /**
     * Get real-time dashboard updates.
     */
    public function getRealtimeUpdates(Request $request)
    {
        $user = Auth::user();
        $lastUpdate = $request->input('last_update', now()->subMinutes(5));

        $updates = [
            'new_documents' => Document::where('current_holder_id', $user->id)
                ->where('created_at', '>=', $lastUpdate)
                ->count(),
            'new_actions' => DocumentTracking::where('to_user_id', $user->id)
                ->where('created_at', '>=', $lastUpdate)
                ->count(),
            'recent_activity' => DocumentTracking::where(function ($q) use ($user) {
                $q->where('from_user_id', $user->id)
                    ->orWhere('to_user_id', $user->id);
            })
                ->where('created_at', '>=', $lastUpdate)
                ->with(['document', 'fromUser', 'toUser'])
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get(),
        ];

        return response()->json($updates);
    }

    /**
     * Get tag usage analytics
     */
    public function getTagAnalytics(Request $request)
    {
        try {
            $user = Auth::user();
            $limit = $request->input('limit', 10);

            $tags = \App\Models\Tag::where('is_active', true)
                ->orderBy('usage_count', 'desc')
                ->limit($limit)
                ->get(['id', 'name', 'usage_count']);

            return response()->json([
                'success' => true,
                'tags' => $tags,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'tags' => []
            ], 500);
        }
    }

    /**
     * Get template usage statistics
     */
    public function getTemplateAnalytics(Request $request)
    {
        $user = Auth::user();

        $templates = \App\Models\DocumentTemplate::where('is_active', true)
            ->withCount('documents')
            ->orderBy('documents_count', 'desc')
            ->limit(10)
            ->get(['id', 'name', 'documents_count']);

        return response()->json([
            'success' => true,
            'templates' => $templates,
        ]);
    }

    /**
     * Get document versioning statistics
     */
    public function getVersionAnalytics(Request $request)
    {
        $user = Auth::user();

        $stats = [
            'total_versions' => \App\Models\DocumentVersion::count(),
            'documents_with_versions' => \App\Models\DocumentVersion::distinct('document_id')->count('document_id'),
            'avg_versions_per_document' => \App\Models\DocumentVersion::selectRaw('AVG(version_count) as avg')
                ->from(DB::raw('(SELECT document_id, COUNT(*) as version_count FROM document_versions GROUP BY document_id) as subquery'))
                ->value('avg'),
            'recent_versions' => \App\Models\DocumentVersion::with(['document', 'createdBy'])
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get(),
        ];

        return response()->json([
            'success' => true,
            'stats' => $stats,
        ]);
    }

    /**
     * Get expiration analytics
     */
    public function getExpirationAnalytics(Request $request)
    {
        $user = Auth::user();

        $query = Document::whereNotNull('expiration_date');

        if (! $user->isAdmin()) {
            $query->where(function ($q) use ($user) {
                $q->where('created_by', $user->id)
                    ->orWhere('current_holder_id', $user->id);
            });
        }

        $stats = [
            'expired' => (clone $query)->where('is_expired', true)->count(),
            'expiring_soon' => (clone $query)
                ->where('is_expired', false)
                ->whereDate('expiration_date', '<=', now()->addDays(7))
                ->count(),
            'expiring_this_month' => (clone $query)
                ->where('is_expired', false)
                ->whereMonth('expiration_date', now()->month)
                ->whereYear('expiration_date', now()->year)
                ->count(),
            'total_with_expiration' => $query->count(),
        ];

        return response()->json([
            'success' => true,
            'stats' => $stats,
        ]);
    }
}
