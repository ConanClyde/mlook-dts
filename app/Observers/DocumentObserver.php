<?php

namespace App\Observers;

use App\Models\Document;
use App\Services\CacheService;

class DocumentObserver
{
    protected CacheService $cacheService;

    public function __construct(CacheService $cacheService)
    {
        $this->cacheService = $cacheService;
    }

    /**
     * Handle the Document "created" event.
     */
    public function created(Document $document): void
    {
        $this->clearRelatedCaches($document);
    }

    /**
     * Handle the Document "updated" event.
     */
    public function updated(Document $document): void
    {
        $this->clearRelatedCaches($document);
    }

    /**
     * Handle the Document "deleted" event.
     */
    public function deleted(Document $document): void
    {
        $this->clearRelatedCaches($document);
    }

    /**
     * Clear caches related to the document.
     */
    protected function clearRelatedCaches(Document $document): void
    {
        // Clear creator's cache
        if ($document->creator) {
            $this->cacheService->clearUserCache($document->creator);
        }

        // Clear current holder's cache
        if ($document->currentHolder) {
            $this->cacheService->clearUserCache($document->currentHolder);
        }

        // Clear department-related caches if applicable
        if ($document->origin_department_id) {
            \Cache::forget("department_stats_{$document->origin_department_id}");
        }
    }
}
