<?php

namespace App\Http\Controllers\Admin;

use App\Events\RoutingRuleCreated;
use App\Events\RoutingRuleDeleted;
use App\Events\RoutingRuleUpdated;
use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\DocumentType;
use App\Models\RoutingRule;
use App\Models\User;
use Illuminate\Http\Request;

class RoutingRuleController extends Controller
{
    /**
     * Display a listing of routing rules.
     */
    public function index(Request $request)
    {
        $query = RoutingRule::with(['documentType', 'department', 'user'])
            ->orderBy('priority', 'desc');

        if ($request->filled('document_type')) {
            $query->where('document_type_id', $request->document_type);
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->is_active);
        }

        $rules = $query->paginate(20);
        $documentTypes = DocumentType::orderBy('name')->get();
        $departments = Department::orderBy('name')->get();
        $users = User::where('usertype', '!=', 'student')
            ->orderBy('name')
            ->get();

        return view('admin.routing-rules.index', compact('rules', 'documentTypes', 'departments', 'users'));
    }

    /**
     * Store a newly created routing rule.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'document_type_id' => 'required|exists:document_types,id',
            'department_id' => 'nullable|exists:departments,id',
            'user_id' => 'nullable|exists:users,id',
            'priority' => 'required|integer|min:0|max:100',
            'condition_type' => 'required|in:always,urgency,department',
            'condition_value' => 'nullable|string',
            'is_active' => 'boolean',
            'description' => 'nullable|string|max:500',
        ]);

        $validated['is_active'] = $request->has('is_active');

        $rule = RoutingRule::create($validated);

        // Broadcast routing rule created event
        broadcast(new RoutingRuleCreated($rule));

        return response()->json([
            'success' => true,
            'message' => 'Routing rule created successfully',
            'rule' => $rule->load(['documentType', 'department', 'user']),
        ]);
    }

    /**
     * Update the specified routing rule.
     */
    public function update(Request $request, RoutingRule $routingRule)
    {
        $validated = $request->validate([
            'document_type_id' => 'required|exists:document_types,id',
            'department_id' => 'nullable|exists:departments,id',
            'user_id' => 'nullable|exists:users,id',
            'priority' => 'required|integer|min:0|max:100',
            'condition_type' => 'required|in:always,urgency,department',
            'condition_value' => 'nullable|string',
            'is_active' => 'boolean',
            'description' => 'nullable|string|max:500',
        ]);

        $validated['is_active'] = $request->has('is_active');

        $routingRule->update($validated);

        // Broadcast routing rule updated event
        broadcast(new RoutingRuleUpdated($routingRule->fresh()));

        return response()->json([
            'success' => true,
            'message' => 'Routing rule updated successfully',
            'rule' => $routingRule->load(['documentType', 'department', 'user']),
        ]);
    }

    /**
     * Remove the specified routing rule.
     */
    public function destroy(RoutingRule $routingRule)
    {
        $ruleId = $routingRule->id;
        $routingRule->delete();

        // Broadcast routing rule deleted event
        broadcast(new RoutingRuleDeleted($ruleId));

        return response()->json([
            'success' => true,
            'message' => 'Routing rule deleted successfully',
        ]);
    }

    /**
     * Toggle active status of routing rule.
     */
    public function toggleStatus(RoutingRule $routingRule)
    {
        $routingRule->update([
            'is_active' => ! $routingRule->is_active,
        ]);

        // Broadcast routing rule updated event
        broadcast(new RoutingRuleUpdated($routingRule->fresh()));

        return response()->json([
            'success' => true,
            'message' => 'Routing rule status updated',
            'is_active' => $routingRule->is_active,
        ]);
    }
}
