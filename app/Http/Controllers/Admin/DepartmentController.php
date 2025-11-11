<?php

namespace App\Http\Controllers\Admin;

use App\Events\DepartmentCreated;
use App\Events\DepartmentDeleted;
use App\Events\DepartmentUpdated;
use App\Http\Controllers\Controller;
use App\Models\Department;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Department::query();

        // Search
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            });
        }

        // Type filter
        if ($request->has('type') && $request->type !== '') {
            $query->where('type', $request->type);
        }

        $perPage = $request->get('per_page', 10);
        $perPage = in_array($perPage, [10, 25, 50, 100]) ? $perPage : 10;

        $departments = $query->orderBy('created_at', 'desc')
            ->paginate($perPage)
            ->withQueryString();

        return view('admin.departments.index', compact('departments'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:20|unique:departments,code',
            'type' => 'required|in:department,college',
        ]);

        $department = Department::create($validated);

        // Broadcast event
        broadcast(new DepartmentCreated($department))->toOthers();

        return response()->json([
            'success' => true,
            'message' => 'Department created successfully!',
            'department' => $department,
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Department $department)
    {
        return response()->json([
            'department' => $department,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Department $department)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:20|unique:departments,code,'.$department->id,
            'type' => 'required|in:department,college',
        ]);

        $department->update($validated);

        // Broadcast event
        broadcast(new DepartmentUpdated($department))->toOthers();

        return response()->json([
            'success' => true,
            'message' => 'Department updated successfully!',
            'department' => $department,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Department $department)
    {
        // Check if department is being used
		if ($department->assignedUsers()->count() > 0 || $department->programs()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete department that has users or programs assigned to it.',
                'error' => 'Cannot delete department that has users or programs assigned to it.',
            ], 422);
        }

        $departmentId = $department->id;
        $department->delete();

        // Broadcast event
        broadcast(new DepartmentDeleted($departmentId))->toOthers();

        return response()->json([
            'success' => true,
            'message' => 'Department deleted successfully!',
        ]);
    }
}
