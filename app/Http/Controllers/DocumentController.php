<?php

namespace App\Http\Controllers;

use App\Events\DocumentCreated;
use App\Events\DocumentForwarded;
use App\Events\DocumentUpdated;
use App\Events\NotificationCreated;
use App\Models\Department;
use App\Models\Document;
use App\Models\DocumentAction;
use App\Models\DocumentAttachment;
use App\Models\DocumentTracking;
use App\Models\DocumentType;
use App\Models\Notification;
use App\Models\Tag;
use App\Models\User;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class DocumentController extends Controller
{
    /**
     * Get filtered document types based on user role.
     */
    protected function getFilteredDocumentTypes(?User $user = null): \Illuminate\Support\Collection
    {
        $user = $user ?? auth()->user();
        $userRole = $user->getUserRole();

        if ($user->isAdmin()) {
            return DocumentType::where('is_active', true)->get();
        }

        $allTypes = DocumentType::where('is_active', true)->get();

        return $allTypes->filter(function ($type) use ($userRole) {
            if (is_null($type->allowed_roles)) {
                return false;
            }
            if (empty($type->allowed_roles) || ! is_array($type->allowed_roles)) {
                return false;
            }

            return in_array($userRole, $type->allowed_roles, true);
        });
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $tab = $request->get('tab', 'all');

        // Base query with common relations
        $query = Document::withCommonRelations()->orderBy('created_at', 'desc');

        // Apply search filters using scopes
        $query->search($request->get('search'));
        $query->ofType($request->get('document_type') ?: $request->get('type'));
        $query->withUrgency($request->get('urgency'));
        $query->withStatus($request->get('status_filter'));
        $query->dateRange($request->get('date_from'), $request->get('date_to'));
        $query->withTags($request->get('tags', []));

        $pendingStatuses = [
            Document::STATUS_DRAFT,
            Document::STATUS_ROUTING,
            Document::STATUS_RECEIVED,
            Document::STATUS_IN_REVIEW,
            Document::STATUS_FOR_APPROVAL,
            Document::STATUS_RETURNED,
        ];
        $receivedStatuses = [
            Document::STATUS_RECEIVED,
            Document::STATUS_IN_REVIEW,
        ];

        // Filter based on tab and user role
        switch ($tab) {
            case 'incoming':
                $query->incomingFor($user->id)
                    ->excludeStatuses([Document::STATUS_ARCHIVED, Document::STATUS_DRAFT]);
                break;
            case 'outgoing':
                $query->createdBy($user->id);
                break;
            case 'pending':
                $query->heldBy($user->id)
                    ->withStatuses($pendingStatuses);
                break;
            case 'completed':
                $query->where(function ($q) {
                    $q->whereIn('status', [Document::STATUS_COMPLETED, Document::STATUS_APPROVED])
                        ->orWhereNotNull('completed_at');
                });
                break;
            case 'received':
                $query->withStatuses($receivedStatuses);
                break;
            case 'returned':
                $query->withStatus(Document::STATUS_RETURNED);
                break;
            case 'archived':
                $query->withStatus(Document::STATUS_ARCHIVED);
                break;
            case 'all':
            default:
                // Default view: Show Draft and Returned documents created by the user
                $query->createdBy($user->id)
                    ->whereIn('status', [Document::STATUS_DRAFT, Document::STATUS_RETURNED]);
                break;
        }

        $perPage = $request->get('per_page', 10);
        $perPage = in_array($perPage, [10, 25, 50, 100]) ? $perPage : 10;
        $documents = $query->paginate($perPage)->withQueryString();
        $documentTypes = $this->getFilteredDocumentTypes($user);

        // Get active tags for filtering
        $tags = Tag::active()->orderBy('name')->get();

        // Get potential recipients for bulk forwarding (exclude students and current user)
        $users = User::where('id', '!=', $user->id)
            ->where('usertype', '!=', 'student')
            ->with('department')
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();

        // Get counts for tabs
        $counts = [
            'all' => Document::count(),
            'incoming' => Document::where('current_holder_id', $user->id)
                ->whereNotIn('status', [Document::STATUS_ARCHIVED])
                ->count(),
            'outgoing' => Document::where('created_by', $user->id)->count(),
            'pending' => Document::where('current_holder_id', $user->id)
                ->whereIn('status', $pendingStatuses)
                ->count(),
            'completed' => Document::where(function ($q) {
                $q->whereIn('status', [Document::STATUS_COMPLETED, Document::STATUS_APPROVED])
                    ->orWhereNotNull('completed_at');
            })->count(),
            'received' => Document::whereIn('status', $receivedStatuses)->count(),
            'returned' => Document::where('status', Document::STATUS_RETURNED)->count(),
            'archived' => Document::where('status', Document::STATUS_ARCHIVED)->count(),
        ];

        return view('documents.index', compact('documents', 'documentTypes', 'counts', 'tab', 'users', 'tags'));
    }

    /**
     * Display inbox (incoming documents).
     */
    public function inbox(Request $request)
    {
        $user = Auth::user();

        $query = Document::with(['documentType', 'creator', 'currentHolder', 'originDepartment', 'tags'])
            ->where('current_holder_id', $user->id)
            ->whereNotIn('status', [Document::STATUS_COMPLETED, Document::STATUS_ARCHIVED, Document::STATUS_DRAFT])
            ->where('created_by', '!=', $user->id) // Exclude documents created by the user (they should be in "My Documents")
            ->orderBy('created_at', 'desc');

        // Additional filters
        if ($request->has('type') && $request->type != '') {
            $query->where('document_type_id', $request->type);
        }

        if ($request->has('urgency') && $request->urgency != '') {
            $query->where('urgency_level', $request->urgency);
        }

        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('tracking_number', 'like', "%{$search}%")
                    ->orWhere('title', 'like', "%{$search}%");
            });
        }

        // If count_only is requested, return just the count
        if ($request->has('count_only') && $request->count_only == '1') {
            return response()->json(['count' => $query->count()]);
        }

        $perPage = $request->get('per_page', 10);
        $perPage = in_array($perPage, [10, 25, 50, 100]) ? $perPage : 10;
        $documents = $query->paginate($perPage)->withQueryString();
        $documentTypes = $this->getFilteredDocumentTypes($user);

        // Get potential recipients for document creation (exclude students and current user)
        $users = User::where('id', '!=', $user->id)
            ->where('usertype', '!=', 'student')
            ->with('department')
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();

        $pageTitle = 'Inbox';

        // Get active tags for create modal
        $tags = Tag::active()->orderBy('name')->get();

        return view('documents.list', compact('documents', 'documentTypes', 'pageTitle', 'users', 'tags'));
    }

    /**
     * Display my documents (created by me).
     */
    public function myDocuments(Request $request)
    {
        $user = Auth::user();

        // Determine section from route or query parameter
        $section = $request->get('section');
        if (! $section && $request->route()) {
            // Check route name to determine section
            $routeName = $request->route()->getName();
            if ($routeName === 'documents.index') {
                $section = 'draft';
            } elseif ($routeName === 'documents.sent') {
                $section = 'sent';
            } else {
                $section = 'draft'; // Default
            }
        } elseif (! $section) {
            $section = 'draft'; // Default if no route
        }

        $query = Document::withCommonRelations()
            ->createdBy($user->id);

        // Status filter
        $hasStatusFilter = $request->has('status') && $request->status != '';
        
        if ($hasStatusFilter) {
            // If status filter is explicitly set, use it
            $query->withStatus($request->status);
        } elseif ($section === 'draft') {
            // My Documents: Show Draft and Returned statuses by default
            $query->whereIn('status', [Document::STATUS_DRAFT, Document::STATUS_RETURNED]);
        } else {
            // Sent: Show Routing, Received, In Review, For Approval (NOT Returned)
            $query->whereIn('status', [
                Document::STATUS_ROUTING,
                Document::STATUS_RECEIVED,
                Document::STATUS_IN_REVIEW,
                Document::STATUS_FOR_APPROVAL
            ])
            // Must have been forwarded at least once
            ->whereHas('tracking', function ($q) {
                $q->where('action', '!=', DocumentTracking::ACTION_CREATED);
            });
        }

        $query->orderBy('created_at', 'desc');

        // Apply filters using scopes
        $query->ofType($request->get('type'));
        $query->withUrgency($request->get('urgency'));
        $query->search($request->get('search'));

        $perPage = $request->get('per_page', 10);
        $perPage = in_array($perPage, [10, 25, 50, 100]) ? $perPage : 10;
        $documents = $query->paginate($perPage)->withQueryString();
        $documentTypes = $this->getFilteredDocumentTypes($user);

        // Get potential recipients for document creation (exclude students and current user)
        $users = User::where('id', '!=', $user->id)
            ->where('usertype', '!=', 'student')
            ->with('department')
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();

        $pageTitle = $section === 'draft' ? 'My Documents' : 'Sent Documents';

        // Get active tags for create modal
        $tags = Tag::active()->orderBy('name')->get();

        return view('documents.list', compact('documents', 'documentTypes', 'pageTitle', 'users', 'section', 'tags'));
    }

    /**
     * Display sent documents (forwarded by me).
     */
    public function sent(Request $request)
    {
        $user = Auth::user();

        // Students cannot forward documents, so they shouldn't access sent
        if ($user->isStudent()) {
            abort(403, 'Students cannot forward documents. You can only view documents you created.');
        }

        // Show documents where user forwarded them (from_user_id)
        // Exclude returned and draft - those go to Documents page
        $query = Document::with(['documentType', 'creator', 'currentHolder', 'originDepartment', 'tags'])
            ->whereHas('tracking', function ($q) use ($user) {
                $q->where('from_user_id', $user->id);
            })
            ->whereIn('status', [
                Document::STATUS_ROUTING,
                Document::STATUS_RECEIVED,
                Document::STATUS_IN_REVIEW,
                Document::STATUS_FOR_APPROVAL
            ])
            ->orderBy('created_at', 'desc');

        // Filters
        if ($request->has('type') && $request->type != '') {
            $query->where('document_type_id', $request->type);
        }

        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('tracking_number', 'like', "%{$search}%")
                    ->orWhere('title', 'like', "%{$search}%");
            });
        }

        $perPage = $request->get('per_page', 10);
        $perPage = in_array($perPage, [10, 25, 50, 100]) ? $perPage : 10;
        $documents = $query->paginate($perPage)->withQueryString();
        $documentTypes = $this->getFilteredDocumentTypes($user);

        // Get potential recipients for document creation (exclude students and current user)
        $users = User::where('id', '!=', $user->id)
            ->where('usertype', '!=', 'student')
            ->with('department')
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();

        $pageTitle = 'Sent Documents';

        // Get active tags for create modal
        $tags = Tag::active()->orderBy('name')->get();

        return view('documents.list', compact('documents', 'documentTypes', 'pageTitle', 'users', 'tags'));
    }

    /**
     * Display completed documents.
     */
    public function completed(Request $request)
    {
        $user = Auth::user();

        $query = Document::with(['documentType', 'creator', 'currentHolder', 'originDepartment', 'tags']);
        
        // Status filter
        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        } else {
            // Default: show completed, approved, and rejected
            $query->whereIn('status', [
                Document::STATUS_COMPLETED,
                Document::STATUS_APPROVED,
                Document::STATUS_REJECTED,
            ]);
        }
        
        $query->orderByDesc('completed_at');

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

        // Filters
        if ($request->has('type') && $request->type != '') {
            $query->where('document_type_id', $request->type);
        }

        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('tracking_number', 'like', "%{$search}%")
                    ->orWhere('title', 'like', "%{$search}%");
            });
        }

        $perPage = $request->get('per_page', 10);
        $perPage = in_array($perPage, [10, 25, 50, 100]) ? $perPage : 10;
        $documents = $query->paginate($perPage)->withQueryString();
        $documentTypes = $this->getFilteredDocumentTypes($user);

        // Get potential recipients for document creation (exclude students and current user)
        $users = User::where('id', '!=', $user->id)
            ->where('usertype', '!=', 'student')
            ->with('department')
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();

        $pageTitle = 'Completed Documents';

        // Get active tags for create modal
        $tags = Tag::active()->orderBy('name')->get();

        return view('documents.list', compact('documents', 'documentTypes', 'pageTitle', 'users', 'tags'));
    }

    /**
     * Display archived documents.
     */
    public function archive(Request $request)
    {
        $user = Auth::user();

        $query = Document::with(['documentType', 'creator', 'currentHolder', 'originDepartment', 'tags']);
        
        // Status filter - default to archived only
        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        } else {
            $query->where('status', Document::STATUS_ARCHIVED);
        }
        
        $query->orderByDesc('archived_at');

        // Filters
        if ($request->has('type') && $request->type != '') {
            $query->where('document_type_id', $request->type);
        }

        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('tracking_number', 'like', "%{$search}%")
                    ->orWhere('title', 'like', "%{$search}%");
            });
        }

        $perPage = $request->get('per_page', 10);
        $perPage = in_array($perPage, [10, 25, 50, 100]) ? $perPage : 10;
        $documents = $query->paginate($perPage)->withQueryString();
        $documentTypes = $this->getFilteredDocumentTypes($user);

        // Get potential recipients for document creation (exclude students and current user)
        $users = User::where('id', '!=', $user->id)
            ->where('usertype', '!=', 'student')
            ->with('department')
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();

        $pageTitle = 'Archive';

        // Get active tags for create modal
        $tags = Tag::active()->orderBy('name')->get();

        return view('documents.list', compact('documents', 'documentTypes', 'pageTitle', 'users', 'tags'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @deprecated Document creation is now handled via modal on documents index page.
     * This method redirects to the documents index page.
     */
    public function create()
    {
        return redirect()->route('documents.index');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'document_type_id' => 'required|exists:document_types,id',
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'urgency_level' => 'required|in:low,normal,high,urgent',
                'files.*' => 'required|file|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png|max:20480', // 20MB per file
                'files' => 'required|array|min:1', // At least 1 file required
                'remarks' => 'nullable|string',
                'to_user_id' => 'nullable|exists:users,id',
                'forwarding_instructions' => 'nullable|string',
                'tags' => 'nullable|array',
                'tags.*' => 'exists:tags,id',
                'custom_tags' => 'nullable|array',
                'custom_tags.*' => 'string|max:50',
                'expiration_date' => 'nullable|date|after:today',
                'auto_archive_on_expiration' => 'nullable|boolean',
            ], [
                'files.required' => 'At least one file must be uploaded.',
                'files.min' => 'At least one file must be uploaded.',
                'files.*.required' => 'Each file is required.',
                'files.*.max' => 'Each file must not exceed 20MB.',
                'files.*.mimes' => 'Invalid file type. Allowed: PDF, DOC, DOCX, XLS, XLSX, JPG, PNG.',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Return JSON response for validation errors
            if ($this->expectsJson($request)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $e->errors(),
                ], 422);
            }
            throw $e;
        }

        $user = Auth::user();

        // Check if user is allowed to create this document type (admins can create all)
        if (! $user->isAdmin()) {
            $documentType = DocumentType::find($validated['document_type_id']);
            $userRole = $user->getUserRole();

            // If no roles specified or user role not in allowed roles, deny
            if (empty($documentType->allowed_roles) || ! in_array($userRole, $documentType->allowed_roles)) {
                return back()->with('error', 'You are not authorized to create this type of document.');
            }
        }

        // Generate tracking number
        $trackingNumber = Document::generateTrackingNumber();

        // Handle multiple file uploads
        $filePath = null;
        $fileName = null;
        $files = $request->file('files');

        // First file becomes the main document file (for backward compatibility)
        if ($files && is_array($files) && count($files) > 0 && isset($files[0]) && $files[0]) {
            $firstFile = $files[0];
            $fileName = $firstFile->getClientOriginalName();
            $filePath = $firstFile->store('documents', 'public');
        }

        // Get origin department ID
        // Origin department is optional - only set if user has a department
        $originDepartmentId = $user->department_id ?? null;
        if (! $originDepartmentId && $user->program_id) {
            $user->load('program');
            $originDepartmentId = $user->program?->college_id ?? null;
        }

        // Determine initial holder - if forwarding, recipient becomes holder; otherwise creator
        $toUserId = $validated['to_user_id'] ?? null;
        $initialHolderId = $toUserId ?: $user->id;
        $initialStatus = $toUserId
            ? Document::STATUS_ROUTING
            : Document::STATUS_DRAFT;

        // If forwarding, validate recipient is not a student
        if ($toUserId) {
            $toUser = User::findOrFail($toUserId);
            if ($toUser->isStudent()) {
                return response()->json([
                    'success' => false,
                    'error' => 'Documents cannot be forwarded to students.',
                    'message' => 'Invalid recipient selected.',
                ], 422);
            }
        }

        // Create document
        $document = Document::create([
            'tracking_number' => $trackingNumber,
            'document_type_id' => $validated['document_type_id'],
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'file_path' => $filePath,
            'file_name' => $fileName,
            'created_by' => $user->id,
            'current_holder_id' => $initialHolderId,
            'origin_department_id' => $originDepartmentId,
            'status' => $initialStatus,
            'urgency_level' => $validated['urgency_level'],
            'remarks' => $validated['remarks'] ?? null,
            'expiration_date' => $validated['expiration_date'] ?? null,
            'auto_archive_on_expiration' => $request->has('auto_archive_on_expiration'),
        ]);

        // Attach tags if provided
        $allTagIds = [];

        // Handle existing tags
        if (! empty($validated['tags']) && is_array($validated['tags'])) {
            $allTagIds = array_filter($validated['tags']);
        }

        // Handle custom tags - create them first
        if (! empty($validated['custom_tags']) && is_array($validated['custom_tags'])) {
            foreach ($validated['custom_tags'] as $customTagName) {
                $customTagName = trim($customTagName);
                if ($customTagName) {
                    // Find or create the tag
                    $tag = Tag::firstOrCreate(
                        ['name' => $customTagName],
                        ['created_by' => $user->id, 'is_active' => true]
                    );
                    $allTagIds[] = $tag->id;
                }
            }
        }

        // Sync all tags
        if (! empty($allTagIds)) {
            $document->tags()->sync($allTagIds);

            // Broadcast tags updated event
            broadcast(new \App\Events\DocumentTagsUpdated($document, $allTagIds, 'added'));

            // Update usage count for tags
            foreach ($allTagIds as $tagId) {
                $tag = Tag::find($tagId);
                if ($tag) {
                    $tag->incrementUsage();
                }
            }
        }

        // Log action
        DocumentAction::create([
            'document_id' => $document->id,
            'user_id' => $user->id,
            'action_type' => 'created',
            'remarks' => "Document created: {$document->title}",
        ]);

        // Always record the creation event
        $creationTracking = DocumentTracking::create([
            'document_id' => $document->id,
            'from_user_id' => $user->id,
            'to_user_id' => $user->id,
            'from_department_id' => $originDepartmentId,
            'to_department_id' => $originDepartmentId,
            'action' => DocumentTracking::ACTION_CREATED,
            'remarks' => 'Document created',
            'sent_at' => now(),
            'received_at' => now(),
            'is_read' => true,
        ]);

        // Save all uploaded files as attachments (first file is already in file_path for backward compatibility)
        if ($files && is_array($files) && count($files) > 0) {
            foreach ($files as $index => $file) {
                if (! $file) {
                    continue; // Skip null/empty files
                }

                // First file uses existing file_path, others need to be stored
                if ($index === 0 && $filePath) {
                    // Save first file as attachment too for consistency
                    DocumentAttachment::create([
                        'document_id' => $document->id,
                        'file_name' => $file->getClientOriginalName(),
                        'file_path' => $filePath, // Use already stored path
                        'file_type' => $file->getMimeType(),
                        'file_size' => $file->getSize(),
                        'uploaded_by' => $user->id,
                    ]);
                } else {
                    // Save additional files as attachments
                    $attachmentPath = $file->store('documents', 'public');
                    DocumentAttachment::create([
                        'document_id' => $document->id,
                        'file_name' => $file->getClientOriginalName(),
                        'file_path' => $attachmentPath,
                        'file_type' => $file->getMimeType(),
                        'file_size' => $file->getSize(),
                        'uploaded_by' => $user->id,
                    ]);
                }
            }
        }

        // Note: Auto-assignment now works as a role filter in the forward modal
        // Document stays in draft status until manually forwarded by owner
        // If document type has auto_assign_enabled, owner can only forward to users with default_receiver_role

        // Create initial tracking record when forwarding upon creation (manual or auto-assigned)
        if ($toUserId && $toUserId !== $user->id) {
            $toUser = User::findOrFail($toUserId);
            $toDepartmentId = $toUser->department_id ?? $originDepartmentId;
            
            // For now, all forwards during creation are manual (not auto-assigned)
            $autoAssigned = false;

            $tracking = DocumentTracking::create([
                'document_id' => $document->id,
                'from_user_id' => $user->id,
                'to_user_id' => $toUserId,
                'from_department_id' => $originDepartmentId,
                'to_department_id' => $toDepartmentId,
                'action' => DocumentTracking::ACTION_FORWARDED,
                'remarks' => $autoAssigned 
                    ? 'Auto-assigned based on document type configuration' 
                    : ($validated['forwarding_instructions'] ?? 'Document forwarded upon creation'),
                'instructions' => $validated['forwarding_instructions'] ?? null,
                'sent_at' => now(),
                'is_read' => false,
            ]);

            // Log forwarding action
            DocumentAction::create([
                'document_id' => $document->id,
                'user_id' => $user->id,
                'action_type' => 'forwarded',
                'remarks' => $autoAssigned 
                    ? "Document auto-assigned to {$toUser->full_name} based on document type settings"
                    : "Document forwarded to {$toUser->full_name} upon creation",
                'metadata' => [
                    'tracking_id' => $tracking->id,
                ],
            ]);

            // Notify recipient
            try {
                $notification = Notification::create([
                    'user_id' => $toUserId,
                    'type' => 'document_forwarded',
                    'title' => 'New Document Received',
                    'message' => "{$user->full_name} sent you a document: {$document->title}",
                    'link' => route('documents.show', $document->id),
                    'data' => [
                        'document_id' => $document->id,
                        'tracking_number' => $document->tracking_number,
                        'from_user_id' => $user->id,
                        'tracking_id' => $tracking->id,
                    ],
                    'read' => false,
                ]);

                broadcast(new DocumentForwarded($document, $tracking));
                broadcast(new NotificationCreated($notification, $toUserId));
            } catch (\Exception $e) {
                \Log::error('Failed to create notification', ['error' => $e->getMessage()]);
            }
        } else {
            // Log that the document remains with the creator
            DocumentAction::create([
                'document_id' => $document->id,
                'user_id' => $user->id,
                'action_type' => 'created',
                'remarks' => 'Document retained by creator',
                'metadata' => [
                    'tracking_id' => $creationTracking->id,
                ],
            ]);
        }

        // Notify admins
        if (! $user->isAdmin()) {
            $admins = User::where('usertype', 'admin')->get();
            foreach ($admins as $admin) {
                try {
                    $notification = Notification::create([
                        'user_id' => $admin->id,
                        'type' => 'document_created',
                        'title' => 'New Document Created',
                        'message' => "{$user->full_name} created a new document: {$document->title}",
                        'link' => route('documents.show', $document->id),
                        'data' => [
                            'document_id' => $document->id,
                            'tracking_number' => $document->tracking_number,
                            'creator_id' => $user->id,
                        ],
                        'read' => false,
                    ]);

                    broadcast(new NotificationCreated($notification));
                } catch (\Exception $e) {
                    // Log error but don't fail document creation
                    \Log::error('Failed to create notification', ['error' => $e->getMessage()]);
                }
            }
        }

        // Broadcast document created event
        broadcast(new DocumentCreated($document));

        return response()->json([
            'success' => true,
            'message' => 'Document created successfully!',
            'document' => $document->load(['documentType', 'creator', 'currentHolder']),
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Document $document)
    {
        // Authorization check
        $this->authorize('view', $document);

        $document->load([
            'documentType',
            'creator',
            'currentHolder',
            'originDepartment',
            'tags',
            'tracking.fromUser',
            'tracking.toUser',
            'tracking.fromDepartment',
            'tracking.toDepartment',
            'actions.user',
            'attachments.uploader',
        ]);

        $user = Auth::user();

        // Log view action
        DocumentAction::create([
            'document_id' => $document->id,
            'user_id' => $user->id,
            'action_type' => 'viewed',
            'remarks' => "Document viewed by {$user->full_name}",
        ]);

        // Get potential recipients for forwarding (exclude students)
        $usersQuery = User::where('id', '!=', $user->id)
            ->where('usertype', '!=', 'student');
        
        // ✅ Apply role filter based on document type configuration
        if ($document->created_by === $user->id) {
            $documentType = $document->documentType;
            
            // If auto-assignment is enabled, use default_receiver_role for filtering
            if ($documentType && $documentType->auto_assign_enabled && $documentType->default_receiver_role) {
                $usersQuery->where('usertype', $documentType->default_receiver_role);
            }
            // If auto-assignment is disabled but allowed_receive is set, use that for filtering
            elseif ($documentType && !empty($documentType->allowed_receive) && is_array($documentType->allowed_receive)) {
                $usersQuery->whereIn('usertype', $documentType->allowed_receive);
            }
        }
        // If not document owner, no restriction - can forward to anyone
        
        $users = $usersQuery->get();

        // Mark document as viewed by current user
        $document->markAsViewedBy($user->id);

        return view('documents.show', compact('document', 'users'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Document $document)
    {
        $user = Auth::user();

        try {
            $this->authorize('update', $document);
        } catch (AuthorizationException $e) {
            return response()->json([
                'success' => false,
                'error' => 'You are not authorized to edit this document.',
                'message' => 'Authorization failed',
            ], 403);
        }

        try {
            // Load document relationships safely
            $document->load(['documentType']);

            $documentTypes = $this->getFilteredDocumentTypes($user) ?? collect();
            $departments = Department::all() ?? collect();

            // Load active tags for selection
            $tags = Tag::active()->orderBy('name')->get() ?? collect();

            // Get document tags using the relationship query builder to ensure we always get a collection
            $documentTags = $document->tags()->get();

            return response()->json([
                'success' => true,
                'document' => [
                    'id' => $document->id,
                    'document_type_id' => $document->document_type_id,
                    'title' => $document->title,
                    'description' => $document->description,
                    'urgency_level' => $document->urgency_level,
                    'remarks' => $document->remarks,
                    'expiration_date' => $document->expiration_date,
                    'auto_archive_on_expiration' => $document->auto_archive_on_expiration,
                    'file_name' => $document->file_name,
                    'file_path' => $document->file_path,
                    'tags' => $documentTags->map(function ($tag) {
                        return [
                            'id' => $tag->id,
                            'name' => $tag->name,
                            'color' => $tag->color,
                        ];
                    })->values(),
                ],
                'documentTypes' => $documentTypes->map(function ($type) {
                    return [
                        'id' => $type->id,
                        'name' => $type->name,
                    ];
                })->values(),
                'departments' => $departments->map(function ($dept) {
                    return [
                        'id' => $dept->id,
                        'name' => $dept->name,
                    ];
                })->values(),
                'tags' => $tags->map(function ($tag) {
                    return [
                        'id' => $tag->id,
                        'name' => $tag->name,
                    ];
                })->values(),
            ]);
        } catch (\Exception $e) {
            \Log::error('Error in DocumentController@edit', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while fetching document details.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Document $document)
    {
        $this->handleAuthorization(
            fn () => $this->authorize('update', $document),
            $request,
            'You are not authorized to update this document.'
        );

        $validated = $request->validate([
            'document_type_id' => 'required|exists:document_types,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'urgency_level' => 'required|in:low,normal,high,urgent',
            'file' => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png|max:10240',
            'remarks' => 'nullable|string',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:tags,id',
            'expiration_date' => 'nullable|date|after_or_equal:today',
            'auto_archive_on_expiration' => 'nullable|boolean',
        ]);

        $user = Auth::user();

        // Handle file upload
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            if ($file) {
                // Delete old file if exists
                if ($document->file_path) {
                    Storage::disk('public')->delete($document->file_path);
                }

                $fileName = $file->getClientOriginalName();
                $filePath = $file->store('documents', 'public');

                $validated['file_path'] = $filePath;
                $validated['file_name'] = $fileName;
            }
        }

        // Get old tag IDs for usage count update
        $document->load('tags');
        $oldTagIds = $document->tags ? $document->tags->pluck('id')->toArray() : [];

        // Update document
        $document->update($validated);

        // Handle tags sync
        // Always sync tags if the request includes them
        $tagIds = [];
        if ($request->has('tags')) {
            $rawTags = $request->input('tags');
            if (is_array($rawTags)) {
                $tagIds = array_filter($rawTags);
            }
        }

        \Log::info('Tags to sync', ['tag_ids' => $tagIds, 'old_tags' => $oldTagIds]);

        // Get tags to increment (new tags)
        $tagsToIncrement = array_diff($tagIds, $oldTagIds);
        // Get tags to decrement (removed tags)
        $tagsToDecrement = array_diff($oldTagIds, $tagIds);

        // Sync tags (empty array will remove all tags)
        $document->tags()->sync($tagIds);

        // Broadcast tags updated event
        broadcast(new \App\Events\DocumentTagsUpdated($document, $tagIds, 'updated'));

        // Update usage counts
        foreach ($tagsToIncrement as $tagId) {
            $tag = Tag::find($tagId);
            if ($tag) {
                $tag->incrementUsage();
            }
        }

        foreach ($tagsToDecrement as $tagId) {
            $tag = Tag::find($tagId);
            if ($tag && $tag->usage_count > 0) {
                $tag->decrementUsage();
            }
        }

        // Broadcast document updated event
        broadcast(new DocumentUpdated($document->fresh(['tags'])));

        // Log action
        DocumentAction::create([
            'document_id' => $document->id,
            'user_id' => $user->id,
            'action_type' => 'updated',
            'remarks' => "Document updated by {$user->full_name}",
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Document updated successfully!',
            'document' => $document->load(['documentType', 'creator', 'currentHolder', 'tags']),
        ]);
    }

    /**
     * Add tags to document (using Tag model)
     */
    public function addTags(Request $request, Document $document)
    {
        $this->authorize('update', $document);

        $validated = $request->validate([
            'tags' => 'required|array|min:1',
            'tags.*' => 'string|max:50',
        ]);

        // Find or create tags by name
        $tagIds = [];
        foreach ($validated['tags'] as $tagName) {
            $tagName = trim($tagName);
            if (empty($tagName)) {
                continue; // Skip empty tag names
            }
            
            $tag = \App\Models\Tag::firstOrCreate(
                ['name' => $tagName],
                [
                    'created_by' => auth()->id(),
                    'is_active' => true,
                ]
            );
            $tagIds[] = $tag->id;
        }

        // If no valid tags were found, return error
        if (empty($tagIds)) {
            return response()->json([
                'success' => false,
                'message' => 'No valid tags provided.',
            ], 422);
        }

        // Get current tag IDs (load relationship first, then pluck to avoid SQL ambiguity)
        $document->load('tags');
        $currentTagIds = $document->tags ? $document->tags->pluck('id')->toArray() : [];
        
        // Find new tags that weren't already attached
        $newTagIds = array_diff($tagIds, $currentTagIds);
        
        // If all tags are already attached, return success
        if (empty($newTagIds)) {
            $document->load('tags');
            $updatedTags = $document->tags ? $document->tags->map(function ($tag) {
                return [
                    'id' => $tag->id,
                    'name' => $tag->name,
                ];
            })->values() : collect([]);
            
            return response()->json([
                'success' => true,
                'message' => 'Tags already attached to document',
                'tags' => $updatedTags,
            ]);
        }
        
        // Sync tags (add new ones, keep existing)
        $allTagIds = array_unique(array_merge($currentTagIds, $tagIds));
        $document->tags()->sync($allTagIds);

        // Increment usage count only for newly attached tags
        foreach ($newTagIds as $tagId) {
            $tag = \App\Models\Tag::find($tagId);
            if ($tag) {
                $tag->incrementUsage();
            }
        }

        // Get updated tags with full info
        $document = $document->fresh(['tags']);
        $updatedTags = $document->tags ? $document->tags->map(function ($tag) {
            return [
                'id' => $tag->id,
                'name' => $tag->name,
            ];
        })->values() : collect([]);

        // Broadcast tags updated event with FULL tag objects (not just IDs) and current user ID
        // This allows frontend to check if it's the current user's action
        broadcast(new \App\Events\DocumentTagsUpdated(
            $document, 
            $updatedTags->toArray(), 
            'added',
            auth()->id() // Pass current user ID so frontend can ignore own actions
        ));

        // Log the sync operation
        \Log::info('Tags synced for document', [
            'document_id' => $document->id,
            'current_tags' => $currentTagIds,
            'new_tag_ids' => $newTagIds,
            'all_tag_ids' => $allTagIds,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Tags added successfully',
            'tags' => $updatedTags,
        ]);
    }

    /**
     * Remove tag from document (using Tag model)
     */
    public function removeTag(Request $request, Document $document)
    {
        $this->authorize('update', $document);

        $validated = $request->validate([
            'tag_id' => 'required|exists:tags,id',
        ]);

        // Detach the tag
        $document->tags()->detach($validated['tag_id']);

        // Broadcast tags updated event
        broadcast(new \App\Events\DocumentTagsUpdated($document, [$validated['tag_id']], 'removed'));

        // Decrement usage count
        $tag = \App\Models\Tag::find($validated['tag_id']);
        if ($tag && $tag->usage_count > 0) {
            $tag->decrementUsage();
        }

        // Return updated tags
        $updatedTags = $document->fresh()->tags->map(function ($tag) {
            return [
                'id' => $tag->id,
                'name' => $tag->name,
            ];
        });

        // Broadcast the tag update event
        broadcast(new \App\Events\DocumentTagsUpdated($document, $updatedTags->toArray(), 'removed'));

        return response()->json([
            'success' => true,
            'message' => 'Tag removed successfully',
            'tags' => $updatedTags,
        ]);
    }

    /**
     * Change document status (Admin only).
     */
    public function changeStatus(Request $request, Document $document)
    {
        // Only admins can manually change status
        if (!auth()->user()->isAdmin()) {
            return response()->json([
                'success' => false,
                'error' => 'Unauthorized. Only administrators can manually change document status.',
            ], 403);
        }

        $validated = $request->validate([
            'status' => 'required|in:draft,routing,received,in_review,for_approval,approved,rejected,completed,returned,archived',
            'reason' => 'required|string|max:500',
        ]);

        $oldStatus = $document->status;
        $newStatus = $validated['status'];

        // Prevent changing to the same status
        if ($oldStatus === $newStatus) {
            return response()->json([
                'success' => false,
                'error' => 'Document is already in this status.',
            ], 422);
        }

        // Update status
        $document->status = $newStatus;
        $document->save();

        // Log the manual status change
        DocumentAction::create([
            'document_id' => $document->id,
            'user_id' => auth()->id(),
            'action_type' => 'status_changed',
            'remarks' => "Status manually changed from " . str_replace('_', ' ', $oldStatus) . " to " . str_replace('_', ' ', $newStatus) . ". Reason: {$validated['reason']}",
        ]);

        // Create audit log
        $auditLog = app(\App\Services\AuditLogService::class);
        $auditLog->log(
            'document_status_changed',
            $document,
            ['old_status' => $oldStatus],
            ['new_status' => $newStatus, 'reason' => $validated['reason']],
            "Admin manually changed document status from {$oldStatus} to {$newStatus}"
        );

        try {
            // Broadcast document updated event
            broadcast(new DocumentUpdated($document->fresh()));

            // Notify involved users about status change
            $this->notifyStatusChange($document, $oldStatus, $newStatus, $validated['reason']);
        } catch (\Exception $e) {
            \Log::error('Failed to broadcast status change or send notifications', [
                'error' => $e->getMessage(),
                'document_id' => $document->id,
            ]);
            // Continue - status was changed successfully
        }

        return response()->json([
            'success' => true,
            'message' => 'Document status updated successfully.',
            'data' => [
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
            ],
        ]);
    }

    /**
     * Notify involved users about status change.
     */
    protected function notifyStatusChange(Document $document, string $oldStatus, string $newStatus, string $reason)
    {
        // Get all users involved in the document
        $userIds = collect();

        // Document creator
        if ($document->created_by) {
            $userIds->push($document->created_by);
        }

        // Current holder
        if ($document->current_holder_id) {
            $userIds->push($document->current_holder_id);
        }

        // Users in tracking history (optimized single query)
        $trackingUsers = $document->tracking()
            ->select('from_user_id', 'to_user_id')
            ->get()
            ->flatMap(function ($tracking) {
                return [$tracking->from_user_id, $tracking->to_user_id];
            })
            ->filter();

        $userIds = $userIds->merge($trackingUsers)->unique()->filter(function ($id) {
            return $id !== auth()->id(); // Don't notify the admin who made the change
        });

        // Create notifications
        foreach ($userIds as $userId) {
            try {
                $notification = Notification::create([
                    'user_id' => $userId,
                    'type' => 'status_changed',
                    'title' => 'Document Status Changed',
                    'message' => "Document \"{$document->title}\" status changed from " . str_replace('_', ' ', $oldStatus) . " to " . str_replace('_', ' ', $newStatus),
                    'link' => route('documents.show', $document->id),
                    'data' => [
                        'document_id' => $document->id,
                        'tracking_number' => $document->tracking_number,
                        'old_status' => $oldStatus,
                        'new_status' => $newStatus,
                        'reason' => $reason,
                        'changed_by' => auth()->id(),
                    ],
                    'read' => false,
                ]);

                // Broadcast notification
                broadcast(new \App\Events\NotificationCreated($notification, $userId));
            } catch (\Exception $e) {
                \Log::error('Failed to create status change notification', ['error' => $e->getMessage()]);
            }
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Document $document)
    {
        $this->handleAuthorization(
            fn () => $this->authorize('delete', $document),
            request(),
            'You are not authorized to delete this document.'
        );

        $user = Auth::user();
        $isCompleted = in_array($document->status, [Document::STATUS_COMPLETED, Document::STATUS_APPROVED]);

        // Log action before deletion
        DocumentAction::create([
            'document_id' => $document->id,
            'user_id' => $user->id,
            'action_type' => 'deleted',
            'remarks' => "Document deleted by {$user->full_name}",
        ]);

        // For completed documents: soft delete for everyone (just removed from view, still in database)
        // Admins can permanently delete if needed (using forceDelete elsewhere if needed)
        if ($isCompleted && ! $user->isAdmin()) {
            // Soft delete only - document is removed from view but not permanently deleted
            $document->delete(); // Soft delete

            return response()->json([
                'success' => true,
                'message' => 'Document removed from view. Only administrators can permanently delete documents.',
            ]);
        }

        // Default: soft delete (for non-completed or completed by admin)
        // This removes the document from view but keeps it in the database
        $deletedDocumentId = $document->id;
        $document->delete(); // Soft delete
        
        // Broadcast document deleted for realtime table updates
        broadcast(new \App\Events\DocumentDeleted($deletedDocumentId));

        return response()->json([
            'success' => true,
            'message' => 'Document removed from view successfully!',
        ]);
    }

    /**
     * Download document file.
     */
    public function download(Document $document)
    {
        // Authorization check
        $this->authorize('download', $document);

        if (! $document->file_path) {
            abort(404, 'File not found');
        }

        $user = Auth::user();

        // Log download action
        DocumentAction::create([
            'document_id' => $document->id,
            'user_id' => $user->id,
            'action_type' => 'downloaded',
            'remarks' => "Document downloaded by {$user->full_name}",
        ]);

        return Storage::disk('public')->download($document->file_path, $document->file_name);
    }

    /**
     * Generate QR code for document tracking.
     */
    public function generateQR(Document $document): \Symfony\Component\HttpFoundation\Response
    {
        // Authorization check
        $this->authorize('view', $document);

        // Generate tracking URL
        $trackingUrl = route('documents.show', $document);

        // Create QR code using chillerlan/php-qrcode (SVG - no GD required)
        $options = new QROptions([
            'version' => 5,
            'outputType' => 'svg',  // SVG output - no GD extension needed
            'outputBase64' => false, // Return raw SVG, not base64
            'eccLevel' => QRCode::ECC_H,
            'scale' => 10,
            'margin' => 2,  // Reduced padding (default is 4, minimum recommended is 2)
        ]);

        $qrcode = new QRCode($options);
        $qrSvg = $qrcode->render($trackingUrl);

        // Ensure SVG has XML declaration if missing
        if (! str_starts_with(trim($qrSvg), '<?xml')) {
            $qrSvg = '<?xml version="1.0" encoding="UTF-8"?>'."\n".$qrSvg;
        }

        // Don't log QR generation - it happens on every page view
        // Only the download action is logged when user actually downloads it

        return response($qrSvg, 200, [
            'Content-Type' => 'image/svg+xml; charset=utf-8',
            'Content-Disposition' => 'inline; filename="'.$document->tracking_number.'-qr.svg"',
        ]);
    }

    /**
     * Archive a document.
     */
    public function archiveDocument(Document $document): \Illuminate\Http\JsonResponse
    {
        $user = Auth::user();

        // Only admins can archive documents
        if (! $user->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Only administrators can archive documents.',
            ], 403);
        }

        // Can't archive if already archived
        if ($document->status === Document::STATUS_ARCHIVED) {
            return response()->json([
                'success' => false,
                'message' => 'Document is already archived.',
            ], 400);
        }

        // Only completed or approved documents can be archived
        if (! in_array($document->status, [Document::STATUS_COMPLETED, Document::STATUS_APPROVED])) {
            return response()->json([
                'success' => false,
                'message' => 'Only completed or approved documents can be archived.',
            ], 400);
        }

        // Update document status
        $document->update([
            'status' => Document::STATUS_ARCHIVED,
            'archived_at' => now(),
        ]);

        // Log action
        DocumentAction::create([
            'document_id' => $document->id,
            'user_id' => $user->id,
            'action_type' => 'archived',
            'remarks' => "Document archived by {$user->full_name}",
        ]);

        // Broadcast document updated event
        broadcast(new DocumentUpdated($document->fresh()));

        return response()->json([
            'success' => true,
            'message' => 'Document has been archived successfully.',
        ]);
    }

    /**
     * Unarchive a document.
     */
    public function unarchiveDocument(Document $document): \Illuminate\Http\JsonResponse
    {
        $user = Auth::user();

        // Only admins can unarchive documents
        if (! $user->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Only administrators can unarchive documents.',
            ], 403);
        }

        // Can't unarchive if not archived
        if ($document->status !== Document::STATUS_ARCHIVED) {
            return response()->json([
                'success' => false,
                'message' => 'Document is not archived.',
            ], 400);
        }

        // Restore document to completed status (archived documents were previously completed or approved)
        $document->update([
            'status' => Document::STATUS_COMPLETED,
            'archived_at' => null,
        ]);

        // Log action
        DocumentAction::create([
            'document_id' => $document->id,
            'user_id' => $user->id,
            'action_type' => 'unarchived',
            'remarks' => "Document unarchived by {$user->full_name}",
        ]);

        // Broadcast document updated event
        broadcast(new DocumentUpdated($document->fresh()));

        return response()->json([
            'success' => true,
            'message' => 'Document has been unarchived successfully.',
        ]);
    }

    /**
     * Find the appropriate receiver for auto-assignment based on document type routing logic.
     */
    protected function findAutoAssignReceiver(DocumentType $documentType, User $creator, ?int $originDepartmentId): ?User
    {
        $routingLogic = $documentType->routing_logic;

        switch ($routingLogic) {
            case 'role':
                // Find first active user with the specified role
                $role = $documentType->default_receiver_role;
                if (! $role) {
                    return null;
                }

                return User::where('usertype', $role)
                    ->where('id', '!=', $creator->id) // Don't assign to creator
                    ->first();

            case 'department':
                // Find department head or first active user in the specified department
                $departmentId = $documentType->default_receiver_department_id;
                if (! $departmentId) {
                    return null;
                }

                // Try to find department head first
                $deptHead = User::where('department_id', $departmentId)
                    ->where('usertype', 'department_head')
                    ->where('id', '!=', $creator->id)
                    ->first();

                if ($deptHead) {
                    return $deptHead;
                }

                // Fallback to dean if it's a college
                $dean = User::where('department_id', $departmentId)
                    ->where('usertype', 'dean')
                    ->where('id', '!=', $creator->id)
                    ->first();

                if ($dean) {
                    return $dean;
                }

                // Fallback to any user in that department
                return User::where('department_id', $departmentId)
                    ->where('id', '!=', $creator->id)
                    ->whereIn('usertype', ['registrar', 'staff', 'faculty'])
                    ->first();

            case 'specific_user':
                // Assign to specific user
                $userId = $documentType->default_receiver_user_id;
                if (! $userId) {
                    return null;
                }

                $user = User::where('id', $userId)
                    ->where('id', '!=', $creator->id)
                    ->first();

                return $user;

            case 'routing_rules':
                // Use existing routing rules (if implemented)
                // For now, fallback to registrar
                return User::where('usertype', 'registrar')
                    ->where('id', '!=', $creator->id)
                    ->first();

            default:
                return null;
        }
    }

    /**
     * Add attachment to an existing document.
     */
    public function addAttachment(Request $request, Document $document)
    {
        $user = Auth::user();

        // Permission check: Only current holder or admin can add attachments
        if (! $user->isAdmin() && $document->current_holder_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to add attachments to this document.',
            ], 403);
        }

        $validated = $request->validate([
            'files.*' => 'required|file|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png|max:20480', // 20MB per file
            'files' => 'required|array|min:1',
        ], [
            'files.required' => 'At least one file must be uploaded.',
            'files.min' => 'At least one file must be uploaded.',
            'files.*.required' => 'Each file is required.',
            'files.*.max' => 'Each file must not exceed 20MB.',
            'files.*.mimes' => 'Invalid file type. Allowed: PDF, DOC, DOCX, XLS, XLSX, JPG, PNG.',
        ]);

        $files = $request->file('files');
        $uploadedAttachments = [];

        if ($files && is_array($files)) {
            foreach ($files as $file) {
                if (! $file) {
                    continue; // Skip null/empty files
                }

                $filePath = $file->store('documents', 'public');

                $attachment = DocumentAttachment::create([
                    'document_id' => $document->id,
                    'file_name' => $file->getClientOriginalName(),
                    'file_path' => $filePath,
                    'file_type' => $file->getMimeType(),
                    'file_size' => $file->getSize(),
                    'uploaded_by' => $user->id,
                ]);

                $uploadedAttachments[] = $attachment;
                
                // Broadcast attachment uploaded event
                broadcast(new \App\Events\AttachmentUploaded($attachment));
            }
        }

        // Log action
        DocumentAction::create([
            'document_id' => $document->id,
            'user_id' => $user->id,
            'action_type' => 'attachment_added',
            'remarks' => $user->full_name.' added '.count($uploadedAttachments).' attachment(s)',
            'metadata' => [
                'attachment_count' => count($uploadedAttachments),
                'file_names' => array_map(fn ($a) => $a->file_name, $uploadedAttachments),
            ],
        ]);

        // Broadcast document updated event
        broadcast(new DocumentUpdated($document->fresh()));

        return response()->json([
            'success' => true,
            'message' => count($uploadedAttachments).' file(s) uploaded successfully!',
            'attachments' => $uploadedAttachments,
        ]);
    }

    /**
     * Delete an attachment from a document.
     */
    public function deleteAttachment(DocumentAttachment $attachment)
    {
        $user = Auth::user();
        $document = $attachment->document;

        // Permission check: Only uploader, current holder, or admin can delete
        if (! $user->isAdmin() &&
            $attachment->uploaded_by !== $user->id &&
            $document->current_holder_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to delete this attachment.',
            ], 403);
        }

        // Store file info before deletion
        $fileName = $attachment->file_name;
        $filePath = $attachment->file_path;
        $attachmentId = $attachment->id;
        $documentId = $document->id;

        // Delete the file from storage
        if (Storage::disk('public')->exists($filePath)) {
            Storage::disk('public')->delete($filePath);
        }

        // Delete the attachment record
        $attachment->delete();
        
        // Broadcast attachment deleted event
        broadcast(new \App\Events\AttachmentDeleted($attachmentId, $documentId));

        // Log action
        DocumentAction::create([
            'document_id' => $document->id,
            'user_id' => $user->id,
            'action_type' => 'attachment_deleted',
            'remarks' => $user->full_name.' deleted attachment: '.$fileName,
            'metadata' => [
                'file_name' => $fileName,
            ],
        ]);

        // Broadcast document updated event
        broadcast(new DocumentUpdated($document->fresh()));

        return response()->json([
            'success' => true,
            'message' => 'Attachment deleted successfully!',
        ]);
    }

}
