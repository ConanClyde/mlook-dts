<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Get unread notifications count.
     */
    public function unreadCount()
    {
        try {
            $user = Auth::user();

            // Get unread notifications, excluding ones where user forwarded documents to themselves
            // or notifications about documents they created (unless someone else acted on them)
            $count = $user->notifications()
                ->where('read', false)
                ->where(function ($query) use ($user) {
                    // Exclude notifications where user forwarded a document to themselves
                    $query->where(function ($q) use ($user) {
                        $q->where('type', '!=', 'document_forwarded')
                            ->orWhere(function ($subQ) use ($user) {
                                // Include document_forwarded only if from_user_id is NOT the current user
                                $subQ->where('type', 'document_forwarded')
                                    ->whereRaw('JSON_EXTRACT(data, "$.from_user_id") != ?', [$user->id]);
                            });
                    })
                    // Exclude notifications about documents the user created (document_created type)
                        ->where(function ($q) use ($user) {
                            $q->where('type', '!=', 'document_created')
                                ->orWhere(function ($subQ) use ($user) {
                                    // Include document_created only if creator_id is NOT the current user or is NULL
                                    $subQ->where('type', 'document_created')
                                        ->where(function ($subSubQ) use ($user) {
                                            $subSubQ->whereRaw('JSON_EXTRACT(data, "$.creator_id") IS NULL')
                                                ->orWhereRaw('JSON_EXTRACT(data, "$.creator_id") != ?', [$user->id]);
                                        });
                                });
                        });
                })
                ->count();

            return response()->json([
                'count' => $count,
            ]);
        } catch (\Exception $e) {
            // If notifications table doesn't exist yet, return 0
            return response()->json([
                'count' => 0,
            ]);
        }
    }

    /**
     * Get recent notifications.
     */
    public function index(Request $request)
    {
        try {
            $user = Auth::user();

            // Get notifications, excluding ones where user forwarded documents to themselves
            // or notifications about documents they created (unless someone else acted on them)
            $notifications = $user->notifications()
                ->where(function ($query) use ($user) {
                    // Exclude notifications where user forwarded a document to themselves
                    $query->where(function ($q) use ($user) {
                        $q->where('type', '!=', 'document_forwarded')
                            ->orWhere(function ($subQ) use ($user) {
                                // Include document_forwarded only if from_user_id is NOT the current user
                                $subQ->where('type', 'document_forwarded')
                                    ->whereRaw('JSON_EXTRACT(data, "$.from_user_id") != ?', [$user->id]);
                            });
                    })
                    // Exclude notifications about documents the user created (document_created type)
                        ->where(function ($q) use ($user) {
                            $q->where('type', '!=', 'document_created')
                                ->orWhere(function ($subQ) use ($user) {
                                    // Include document_created only if creator_id is NOT the current user or is NULL
                                    $subQ->where('type', 'document_created')
                                        ->where(function ($subSubQ) use ($user) {
                                            $subSubQ->whereRaw('JSON_EXTRACT(data, "$.creator_id") IS NULL')
                                                ->orWhereRaw('JSON_EXTRACT(data, "$.creator_id") != ?', [$user->id]);
                                        });
                                });
                        });
                })
                ->orderBy('created_at', 'desc')
                ->limit($request->get('limit', 10))
                ->get();

            return response()->json($notifications);
        } catch (\Exception $e) {
            // If notifications table doesn't exist yet, return empty array
            return response()->json([]);
        }
    }

    /**
     * Mark notification as read.
     */
    public function markAsRead(Notification $notification)
    {
        // Ensure the notification belongs to the authenticated user
        if ($notification->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $notification->markAsRead();
        
        // Broadcast notification read event
        broadcast(new \App\Events\NotificationRead($notification->id, $notification->user_id));

        return response()->json(['success' => true]);
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllAsRead()
    {
        Auth::user()
            ->notifications()
            ->where('read', false)
            ->update([
                'read' => true,
                'read_at' => now(),
            ]);

        return response()->json(['success' => true]);
    }

    /**
     * Delete a single notification.
     */
    public function destroy(Notification $notification)
    {
        // Ensure the notification belongs to the authenticated user
        if ($notification->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $notification->delete();

        return response()->json(['success' => true]);
    }

    /**
     * Clear all read notifications.
     */
    public function clearAll()
    {
        Auth::user()
            ->notifications()
            ->where('read', true)
            ->delete();

        return response()->json(['success' => true]);
    }
}
