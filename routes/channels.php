<?php

use Illuminate\Support\Facades\Broadcast;

// Register broadcasting authentication routes
Broadcast::routes();

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Admin notifications channel - accessible by users with admin privileges
Broadcast::channel('admin.notifications', function ($user) {
    return $user && $user->hasAdminPrivileges() ? ['id' => $user->id, 'name' => $user->full_name, 'role' => $user->usertype] : false;
});

// Admin users channel - accessible by users with admin privileges (for real-time user table updates)
Broadcast::channel('admin.users', function ($user) {
    return $user && $user->hasAdminPrivileges() ? ['id' => $user->id, 'name' => $user->full_name, 'role' => $user->usertype] : false;
});

// Admin settings channel - accessible by users with admin privileges (for real-time settings updates)
Broadcast::channel('admin.settings', function ($user) {
    return $user && $user->hasAdminPrivileges() ? ['id' => $user->id, 'name' => $user->full_name, 'role' => $user->usertype] : false;
});

// Documents channel - accessible by all authenticated users (for real-time document table updates)
Broadcast::channel('documents', function ($user) {
    return $user ? ['id' => $user->id, 'name' => $user->full_name] : false;
});

// Tags channel - accessible by all authenticated users
Broadcast::channel('tags', function ($user) {
    return $user ? ['id' => $user->id, 'name' => $user->full_name] : false;
});

// Categories channel - accessible by all authenticated users
Broadcast::channel('categories', function ($user) {
    return $user ? ['id' => $user->id, 'name' => $user->full_name] : false;
});

// Templates channel - accessible by all authenticated users
Broadcast::channel('templates', function ($user) {
    return $user ? ['id' => $user->id, 'name' => $user->full_name] : false;
});

// Document-specific channel - accessible by users who can view the document
Broadcast::channel('document.{documentId}', function ($user, $documentId) {
    if (!$user) {
        \Log::warning('Channel authorization failed: No user', ['document_id' => $documentId]);
        return false;
    }
    
    $document = \App\Models\Document::find($documentId);
    if (!$document) {
        \Log::warning('Channel authorization failed: Document not found', ['document_id' => $documentId, 'user_id' => $user->id]);
        return false;
    }
    
    // Check each access condition
    $isAdmin = $user->hasAdminPrivileges();
    $isCreator = $document->created_by === $user->id;
    $isCurrentHolder = $document->current_holder_id === $user->id;
    $isInTracking = $document->tracking()->where('from_user_id', $user->id)->exists() ||
                    $document->tracking()->where('to_user_id', $user->id)->exists();
    $isReceiver = $document->receivers()->where('receiver_id', $user->id)->exists();
    
    // User can access if they:
    // - Have admin privileges
    // - Are the creator
    // - Are the current holder
    // - Are in the tracking history (received or forwarded the document)
    // - Are a receiver of the document
    $canAccess = $isAdmin || $isCreator || $isCurrentHolder || $isInTracking || $isReceiver;
    
    // Log authorization attempt for debugging
    \Log::info('Channel authorization check', [
        'document_id' => $documentId,
        'user_id' => $user->id,
        'user_name' => $user->full_name,
        'is_admin' => $isAdmin,
        'is_creator' => $isCreator,
        'is_current_holder' => $isCurrentHolder,
        'is_in_tracking' => $isInTracking,
        'is_receiver' => $isReceiver,
        'can_access' => $canAccess,
    ]);
    
    return $canAccess ? ['id' => $user->id, 'name' => $user->full_name, 'role' => $user->usertype] : false;
});

// Permissions channel - accessible by users with admin privileges
Broadcast::channel('permissions', function ($user) {
    return $user && $user->hasAdminPrivileges() ? ['id' => $user->id, 'name' => $user->full_name, 'role' => $user->usertype] : false;
});

// Audit logs channel - accessible by users with admin privileges
Broadcast::channel('admin.audit-logs', function ($user) {
    return $user && $user->hasAdminPrivileges() ? ['id' => $user->id, 'name' => $user->full_name, 'role' => $user->usertype] : false;
});

// Backups channel - accessible by users with admin privileges
Broadcast::channel('admin.backups', function ($user) {
    return $user && $user->hasAdminPrivileges() ? ['id' => $user->id, 'name' => $user->full_name, 'role' => $user->usertype] : false;
});
