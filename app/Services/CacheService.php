<?php

namespace App\Services;

use App\Models\Document;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

class CacheService
{
    /**
     * Cache duration in seconds (1 hour).
     */
    protected int $cacheDuration = 3600;

    /**
     * Get or cache user document statistics.
     */
    public function getUserDocumentStats(User $user): array
    {
        $cacheKey = "user_document_stats_{$user->id}";

        return Cache::remember($cacheKey, $this->cacheDuration, function () use ($user) {
            return [
                'total' => $this->getUserTotalDocuments($user),
                'pending' => $this->getUserPendingDocuments($user),
                'in_transit' => $this->getUserInTransitDocuments($user),
                'completed' => $this->getUserCompletedDocuments($user),
                'incoming' => $this->getUserIncomingDocuments($user),
                'outgoing' => $this->getUserOutgoingDocuments($user),
            ];
        });
    }

    /**
     * Get or cache document type distribution.
     */
    public function getDocumentTypeDistribution(User $user): mixed
    {
        $cacheKey = "document_type_distribution_{$user->id}";

        return Cache::remember($cacheKey, $this->cacheDuration, function () use ($user) {
            $query = Document::select('document_type_id', \DB::raw('count(*) as count'))
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
        });
    }

    /**
     * Get or cache monthly activity.
     */
    public function getMonthlyActivity(User $user): mixed
    {
        $cacheKey = "monthly_activity_{$user->id}";

        return Cache::remember($cacheKey, $this->cacheDuration, function () use ($user) {
            $sixMonthsAgo = now()->subMonths(6);

            $query = Document::select(
                \DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'),
                \DB::raw('count(*) as count')
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
        });
    }

    /**
     * Clear user-specific caches.
     */
    public function clearUserCache(User $user): void
    {
        $keys = [
            "user_document_stats_{$user->id}",
            "document_type_distribution_{$user->id}",
            "monthly_activity_{$user->id}",
            "status_distribution_{$user->id}",
            "urgency_distribution_{$user->id}",
            "weekly_activity_{$user->id}",
        ];

        foreach ($keys as $key) {
            Cache::forget($key);
        }
    }

    /**
     * Clear all document-related caches.
     */
    public function clearAllDocumentCaches(): void
    {
        Cache::tags(['documents'])->flush();
    }

    /**
     * Helper methods for statistics.
     */
    private function getUserTotalDocuments(User $user): int
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

    private function getUserPendingDocuments(User $user): int
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

    private function getUserInTransitDocuments(User $user): int
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

    private function getUserCompletedDocuments(User $user): int
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

    private function getUserIncomingDocuments(User $user): int
    {
        return Document::where('current_holder_id', $user->id)
            ->whereNotIn('status', [Document::STATUS_ARCHIVED])
            ->count();
    }

    private function getUserOutgoingDocuments(User $user): int
    {
        return Document::where('created_by', $user->id)->count();
    }
}
