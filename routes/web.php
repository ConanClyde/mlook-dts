<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ForgotPasswordController;
use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    
    // Registration Routes (3-step process)
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register/send-code', [AuthController::class, 'sendVerificationCode'])->name('register.send-code');
    Route::get('/register/verify', [AuthController::class, 'showVerifyEmail'])->name('verify.show');
    Route::post('/register/verify', [AuthController::class, 'verifyCode'])->name('verify.code');
    Route::post('/register/resend', [AuthController::class, 'resendVerificationCode'])->name('verify.resend');
    Route::get('/register/complete', [AuthController::class, 'showCompleteRegistration'])->name('register.complete');
    Route::post('/register', [AuthController::class, 'register']);

    // Forgot Password Routes
    Route::get('/forgot-password', [ForgotPasswordController::class, 'showEmailForm'])->name('password.request');
    Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetCode']);
    Route::get('/password/code', [ForgotPasswordController::class, 'showCodeForm'])->name('password.code');
    Route::post('/password/code', [ForgotPasswordController::class, 'verifyCode']);
    Route::post('/password/resend', [ForgotPasswordController::class, 'resendCode'])->name('password.resend');
    Route::get('/password/reset', [ForgotPasswordController::class, 'showResetForm'])->name('password.reset');
    Route::post('/password/reset', [ForgotPasswordController::class, 'resetPassword']);
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/api/tag-analytics', [\App\Http\Controllers\DashboardController::class, 'getTagAnalytics']);

    // Dashboard Analytics API Endpoints
    Route::prefix('dashboard/api')->name('dashboard.api.')->group(function () {
        Route::get('/flow-timeline', [\App\Http\Controllers\DashboardController::class, 'getDocumentFlowTimeline'])->name('flow-timeline');
        Route::get('/department-stats', [\App\Http\Controllers\DashboardController::class, 'getDepartmentWiseStatistics'])->name('department-stats');
        Route::get('/user-activity', [\App\Http\Controllers\DashboardController::class, 'getUserActivityMetrics'])->name('user-activity');
        Route::get('/pending-actions', [\App\Http\Controllers\DashboardController::class, 'getPendingActionsWidget'])->name('pending-actions');
        Route::get('/completion-rate', [\App\Http\Controllers\DashboardController::class, 'getCompletionRateOverTime'])->name('completion-rate');
        Route::get('/realtime-updates', [\App\Http\Controllers\DashboardController::class, 'getRealtimeUpdates'])->name('realtime-updates');
        Route::get('/tag-analytics', [\App\Http\Controllers\DashboardController::class, 'getTagAnalytics'])->name('tag-analytics');
        Route::get('/version-analytics', [\App\Http\Controllers\DashboardController::class, 'getVersionAnalytics'])->name('version-analytics');
        Route::get('/expiration-analytics', [\App\Http\Controllers\DashboardController::class, 'getExpirationAnalytics'])->name('expiration-analytics');
    });

    // User Settings
    Route::get('/settings', [\App\Http\Controllers\SettingsController::class, 'index'])->name('settings.index');

    Route::get('/profile', function () {
        return view('profile');
    })->name('profile');
    Route::put('/profile', [\App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/password', [\App\Http\Controllers\ProfileController::class, 'updatePassword'])->name('profile.password.update');
    Route::post('/profile/notifications', [\App\Http\Controllers\ProfileController::class, 'updateNotificationPreferences'])->name('profile.notifications.update');
    Route::delete('/profile', [\App\Http\Controllers\ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/profile/activity', [\App\Http\Controllers\ProfileController::class, 'activity'])->name('profile.activity');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Two-Factor Authentication Routes
    Route::prefix('two-factor')->name('two-factor.')->group(function () {
        Route::get('/', [\App\Http\Controllers\TwoFactorController::class, 'index'])->name('index');
        Route::post('/enable', [\App\Http\Controllers\TwoFactorController::class, 'enable'])->name('enable');
        Route::post('/confirm', [\App\Http\Controllers\TwoFactorController::class, 'confirm'])->name('confirm');
        Route::post('/cancel', [\App\Http\Controllers\TwoFactorController::class, 'cancel'])->name('cancel');
        Route::delete('/disable', [\App\Http\Controllers\TwoFactorController::class, 'disable'])->name('disable');
        Route::get('/challenge', [\App\Http\Controllers\TwoFactorController::class, 'challenge'])->name('challenge');
        Route::post('/verify', [\App\Http\Controllers\TwoFactorController::class, 'verify'])->name('verify');
        Route::post('/recovery-codes/regenerate', [\App\Http\Controllers\TwoFactorController::class, 'regenerateRecoveryCodes'])->name('recovery-codes.regenerate');
    });

    // Notification Routes
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/count', [\App\Http\Controllers\NotificationController::class, 'unreadCount'])->name('count');
        Route::get('/', [\App\Http\Controllers\NotificationController::class, 'index'])->name('index');
        Route::post('/{notification}/read', [\App\Http\Controllers\NotificationController::class, 'markAsRead'])->name('read');
        Route::post('/read-all', [\App\Http\Controllers\NotificationController::class, 'markAllAsRead'])->name('read-all');
        Route::delete('/{notification}', [\App\Http\Controllers\NotificationController::class, 'destroy'])->name('destroy');
        Route::post('/clear-all', [\App\Http\Controllers\NotificationController::class, 'clearAll'])->name('clear-all');
    });
});

// Document Routes
Route::middleware(['auth'])->group(function () {
    // Clean workflow-based pages (no /documents prefix)
    Route::get('inbox', [\App\Http\Controllers\DocumentController::class, 'inbox'])->name('documents.inbox');

    // Documents - shows draft documents (not forwarded yet)
    Route::get('documents', [\App\Http\Controllers\DocumentController::class, 'myDocuments'])->name('documents.index');

    // My Documents - shows draft and returned documents with status filter
    Route::get('my-documents', [\App\Http\Controllers\DocumentController::class, 'myDocuments'])->name('documents.my-documents');

    // Sent - shows documents I created that have been forwarded
    Route::get('sent', [\App\Http\Controllers\DocumentController::class, 'myDocuments'])->name('documents.sent');

    // Legacy forwarded route (documents I forwarded) - uses old sent method
    Route::get('forwarded', [\App\Http\Controllers\DocumentController::class, 'sent'])->name('documents.forwarded');

    Route::get('completed', [\App\Http\Controllers\DocumentController::class, 'completed'])->name('documents.completed');
    Route::get('archive', [\App\Http\Controllers\DocumentController::class, 'archive'])->name('documents.archive');

    // Standard CRUD (for view/edit/delete specific documents)
    Route::resource('documents', \App\Http\Controllers\DocumentController::class)->except(['index', 'create']);
    Route::get('documents/{document}/download', [\App\Http\Controllers\DocumentController::class, 'download'])->name('documents.download');
    Route::get('documents/{document}/qr', [\App\Http\Controllers\DocumentController::class, 'generateQR'])->name('documents.qr');

    // Document actions
    Route::post('documents/{document}/forward', [\App\Http\Controllers\DocumentTrackingController::class, 'forward'])->name('documents.forward');
    Route::post('tracking/{tracking}/receive', [\App\Http\Controllers\DocumentTrackingController::class, 'receive'])->name('tracking.receive');
    Route::post('documents/{document}/complete', [\App\Http\Controllers\DocumentTrackingController::class, 'complete'])->name('documents.complete');
    Route::post('tracking/{tracking}/return', [\App\Http\Controllers\DocumentTrackingController::class, 'return'])->name('tracking.return');
    Route::post('documents/{document}/approve', [\App\Http\Controllers\DocumentTrackingController::class, 'approve'])->name('documents.approve');
    Route::post('documents/{document}/reject', [\App\Http\Controllers\DocumentTrackingController::class, 'reject'])->name('documents.reject');
    Route::post('documents/{document}/archive', [\App\Http\Controllers\DocumentController::class, 'archiveDocument'])->name('documents.archive.store');
    Route::post('documents/{document}/unarchive', [\App\Http\Controllers\DocumentController::class, 'unarchiveDocument'])->name('documents.unarchive.store');
    
    // Admin: Change document status manually
    Route::post('documents/{document}/change-status', [\App\Http\Controllers\DocumentController::class, 'changeStatus'])
        ->middleware('admin')
        ->name('documents.change-status');

    // Document Comments
    Route::post('documents/{document}/comments', [\App\Http\Controllers\DocumentCommentController::class, 'store'])->name('documents.comments.store');
    Route::delete('comments/{comment}', [\App\Http\Controllers\DocumentCommentController::class, 'destroy'])->name('comments.destroy');

    // Document Attachments
    Route::post('documents/{document}/attachments', [\App\Http\Controllers\DocumentController::class, 'addAttachment'])->name('documents.attachments.store');
    Route::delete('attachments/{attachment}', [\App\Http\Controllers\DocumentController::class, 'deleteAttachment'])->name('attachments.destroy');

    // Document Signatures
    Route::prefix('documents/{document}/signatures')->name('documents.signatures.')->group(function () {
        Route::get('/', [\App\Http\Controllers\DocumentSignatureController::class, 'index'])->name('index');
        Route::post('/', [\App\Http\Controllers\DocumentSignatureController::class, 'sign'])->name('sign');
    });
    Route::post('signatures/{signature}/verify', [\App\Http\Controllers\DocumentSignatureController::class, 'verify'])->name('signatures.verify');
    Route::delete('signatures/{signature}', [\App\Http\Controllers\DocumentSignatureController::class, 'destroy'])->name('signatures.destroy');

    // Document Tags
    Route::prefix('tags')->name('tags.')->group(function () {
        Route::get('/', [\App\Http\Controllers\DocumentTagController::class, 'index'])->name('index');
        Route::get('/statistics', [\App\Http\Controllers\DocumentTagController::class, 'statistics'])->name('statistics');
        Route::get('/{tag}/documents', [\App\Http\Controllers\DocumentTagController::class, 'getByTag'])->name('documents');
    });
    // New tag system (using Tag model)
    Route::post('documents/{document}/tags', [\App\Http\Controllers\DocumentController::class, 'addTags'])->name('documents.tags.add');
    Route::delete('documents/{document}/tags', [\App\Http\Controllers\DocumentController::class, 'removeTag'])->name('documents.tags.remove');
	
    // API: Inbox Count
    Route::get('/api/inbox-count', function() {
        $count = \App\Models\Document::where('current_holder_id', auth()->id())
            ->whereNotIn('status', [
                \App\Models\Document::STATUS_COMPLETED,
                \App\Models\Document::STATUS_ARCHIVED,
                \App\Models\Document::STATUS_DRAFT
            ])
            ->where('created_by', '!=', auth()->id())
            ->count();
        return response()->json(['count' => $count]);
    })->name('api.inbox.count');

    // API: Documents Count (unread draft + returned)
    Route::get('/api/documents-count', function() {
        $user = auth()->user();
		$count = \App\Models\Document::where('created_by', $user->id)
			->whereIn('status', [
				\App\Models\Document::STATUS_DRAFT,
				\App\Models\Document::STATUS_RETURNED
			])
			->count();
        return response()->json(['count' => $count]);
    })->name('api.documents.count');

    // API: Sent Count (unread routing, received, in_review, for_approval)
    Route::get('/api/sent-count', function() {
        $user = auth()->user();
		$count = \App\Models\Document::whereHas('tracking', function ($q) use ($user) {
				$q->where('from_user_id', $user->id);
			})
			->whereIn('status', [
				\App\Models\Document::STATUS_ROUTING,
				\App\Models\Document::STATUS_RECEIVED,
				\App\Models\Document::STATUS_IN_REVIEW,
				\App\Models\Document::STATUS_FOR_APPROVAL
			])
			->count();
        return response()->json(['count' => $count]);
    })->name('api.sent.count');

    // API: Completed Count (unread)
    Route::get('/api/completed-count', function() {
        $user = auth()->user();
        $query = \App\Models\Document::whereIn('status', [
            \App\Models\Document::STATUS_COMPLETED,
            \App\Models\Document::STATUS_APPROVED,
            \App\Models\Document::STATUS_REJECTED
        ]);

        // Non-admins see only their completed documents
        if (! $user->isAdmin()) {
            $query->where(function ($q) use ($user) {
                $q->where('created_by', $user->id)
                    ->orWhere('current_holder_id', $user->id)
                    ->orWhereHas('tracking', function ($tq) use ($user) {
                        $tq->where('from_user_id', $user->id)
                            ->orWhere('to_user_id', $user->id);
                    });
            });
        }

        $count = $query->count();
        return response()->json(['count' => $count]);
    })->name('api.completed.count');

    // API: Get Active Tags (for document tagging)
    Route::get('/api/tags', function() {
        $tags = \App\Models\Tag::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'usage_count']);
        
        return response()->json($tags);
    });
});

// Admin Routes (clean URLs)
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('users/export', [\App\Http\Controllers\Admin\UserController::class, 'export'])->name('users.export');
    Route::resource('users', \App\Http\Controllers\Admin\UserController::class);

    // Settings Page (with tabs)
    Route::get('settings', [\App\Http\Controllers\Admin\SettingsController::class, 'index'])->name('settings.index');

    // Settings Management APIs (for CRUD operations)
    Route::resource('document-types', \App\Http\Controllers\Admin\DocumentTypeController::class)->except(['index']);
    Route::resource('departments', \App\Http\Controllers\Admin\DepartmentController::class)->except(['index']);
    Route::resource('programs', \App\Http\Controllers\Admin\ProgramController::class)->except(['index']);

    // Tags Routes
    Route::get('tags', [\App\Http\Controllers\Admin\TagController::class, 'index'])->name('tags.index');
    Route::get('tags/{tag}', [\App\Http\Controllers\Admin\TagController::class, 'show'])->name('tags.show');
    Route::post('tags', [\App\Http\Controllers\Admin\TagController::class, 'store'])->name('tags.store');
    Route::put('tags/{tag}', [\App\Http\Controllers\Admin\TagController::class, 'update'])->name('tags.update');
    Route::delete('tags/{tag}', [\App\Http\Controllers\Admin\TagController::class, 'destroy'])->name('tags.destroy');

    // Backup Management Routes
    Route::prefix('backups')->name('backups.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\BackupController::class, 'index'])->name('index');
        Route::post('/', [\App\Http\Controllers\Admin\BackupController::class, 'store'])->name('store');
        Route::get('/{filename}/download', [\App\Http\Controllers\Admin\BackupController::class, 'download'])->name('download');
        Route::delete('/{filename}', [\App\Http\Controllers\Admin\BackupController::class, 'destroy'])->name('destroy');
        Route::post('/clean', [\App\Http\Controllers\Admin\BackupController::class, 'clean'])->name('clean');
        Route::post('/bulk-delete', [\App\Http\Controllers\Admin\BackupController::class, 'bulkDelete'])->name('bulk-delete');
    });

    // Audit Logs
    Route::get('audit-logs', [\App\Http\Controllers\Admin\AuditLogController::class, 'index'])->name('audit-logs.index');

    // Performance Monitoring
    Route::prefix('performance')->name('performance.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\PerformanceController::class, 'index'])->name('index');
        Route::post('/clean', [\App\Http\Controllers\Admin\PerformanceController::class, 'clean'])->name('clean');
        Route::get('/metrics', [\App\Http\Controllers\Admin\PerformanceController::class, 'metrics'])->name('metrics');
    });

    // Routing Rules
    Route::resource('routing-rules', \App\Http\Controllers\Admin\RoutingRuleController::class)->except(['show', 'create', 'edit']);
    Route::post('routing-rules/{routingRule}/toggle-status', [\App\Http\Controllers\Admin\RoutingRuleController::class, 'toggleStatus'])->name('routing-rules.toggle-status');

    // Compliance & GDPR
    Route::prefix('compliance')->name('compliance.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\ComplianceController::class, 'index'])->name('index');
        Route::get('users/{user}/export', [\App\Http\Controllers\Admin\ComplianceController::class, 'exportUserData'])->name('users.export');
        Route::post('users/{user}/anonymize', [\App\Http\Controllers\Admin\ComplianceController::class, 'anonymizeUser'])->name('users.anonymize');
        Route::delete('users/{user}/data', [\App\Http\Controllers\Admin\ComplianceController::class, 'deleteUserData'])->name('users.delete');
        Route::post('retention/apply', [\App\Http\Controllers\Admin\ComplianceController::class, 'applyRetention'])->name('retention.apply');
    });
});
