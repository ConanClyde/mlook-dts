<?php

namespace App\Http\Controllers\Admin;

use App\Events\DocumentTypeCreated;
use App\Events\DocumentTypeDeleted;
use App\Events\DocumentTypeUpdated;
use App\Http\Controllers\Controller;
use App\Models\DocumentType;
use Illuminate\Http\Request;

class DocumentTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = DocumentType::query();

        // Search
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Status filter
        if ($request->has('status') && $request->status !== '') {
            $query->where('is_active', $request->status === '1');
        }

        $perPage = $request->get('per_page', 10);
        $perPage = in_array($perPage, [10, 25, 50, 100]) ? $perPage : 10;

        $documentTypes = $query->orderBy('created_at', 'desc')
            ->paginate($perPage)
            ->withQueryString();

        return view('admin.document-types.index', compact('documentTypes'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:document_types,name',
            'code' => 'required|string|max:20|unique:document_types,code',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'allowed_roles' => 'required|array|min:1',
            'allowed_roles.*' => 'required|string|in:admin,registrar,dean,department_head,faculty,staff,student',
            'allowed_receive' => 'nullable|array',
            'allowed_receive.*' => 'required|string|in:admin,registrar,dean,department_head,faculty,staff,student',
            'auto_assign_enabled' => 'boolean',
            'routing_logic' => 'nullable|string|in:role,department,specific_user,routing_rules',
            'default_receiver_role' => 'nullable|string|in:registrar,dean,department_head,faculty,staff',
            'default_receiver_department_id' => 'nullable|exists:departments,id',
            'default_receiver_user_id' => 'nullable|exists:users,id',
        ]);

        $validated['is_active'] = $request->has('is_active');
        $validated['auto_assign_enabled'] = $request->has('auto_assign_enabled');

        $documentType = DocumentType::create($validated);

        // Broadcast event
        broadcast(new DocumentTypeCreated($documentType))->toOthers();

        return response()->json([
            'success' => true,
            'message' => 'Document type created successfully!',
            'documentType' => $documentType,
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(DocumentType $documentType)
    {
        $documentType->load(['defaultReceiverDepartment', 'defaultReceiverUser']);

        return response()->json([
            'documentType' => $documentType,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, DocumentType $documentType)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:document_types,name,'.$documentType->id,
            'code' => 'required|string|max:20|unique:document_types,code,'.$documentType->id,
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'allowed_roles' => 'required|array|min:1',
            'allowed_roles.*' => 'required|string|in:admin,registrar,dean,department_head,faculty,staff,student',
            'allowed_receive' => 'nullable|array',
            'allowed_receive.*' => 'required|string|in:admin,registrar,dean,department_head,faculty,staff,student',
            'auto_assign_enabled' => 'boolean',
            'routing_logic' => 'nullable|string|in:role,department,specific_user,routing_rules',
            'default_receiver_role' => 'nullable|string|in:registrar,dean,department_head,faculty,staff',
            'default_receiver_department_id' => 'nullable|exists:departments,id',
            'default_receiver_user_id' => 'nullable|exists:users,id',
        ]);

        $validated['is_active'] = $request->has('is_active');
        $validated['auto_assign_enabled'] = $request->has('auto_assign_enabled');

        $documentType->update($validated);

        // Broadcast event
        broadcast(new DocumentTypeUpdated($documentType))->toOthers();

        return response()->json([
            'success' => true,
            'message' => 'Document type updated successfully!',
            'documentType' => $documentType,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DocumentType $documentType)
    {
        // Check if document type is being used
        if ($documentType->documents()->count() > 0) {
            return response()->json([
                'error' => 'Cannot delete document type that is being used by documents.',
            ], 422);
        }

        $documentTypeId = $documentType->id;
        $documentType->delete();

        // Broadcast event
        broadcast(new DocumentTypeDeleted($documentTypeId))->toOthers();

        return response()->json([
            'success' => true,
            'message' => 'Document type deleted successfully!',
        ]);
    }
}
