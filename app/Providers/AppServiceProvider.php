<?php

namespace App\Providers;

use App\Models\AuditLog;
use App\Models\Document;
use App\Observers\AuditLogObserver;
use App\Observers\DocumentObserver;
use App\Policies\DocumentPolicy;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     */
    protected $policies = [
        Document::class => DocumentPolicy::class,
    ];

    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register policies
        Gate::policy(Document::class, DocumentPolicy::class);

        // Register observers
        Document::observe(DocumentObserver::class);
        AuditLog::observe(AuditLogObserver::class);

        // Use minimal pagination view globally
        Paginator::defaultView('pagination::minimal');
        Paginator::defaultSimpleView('pagination::simple-default');

        // Sidebar counts are provided elsewhere; avoid duplicating composers
    }
}
