<?php

namespace App\Http\Controllers\Admin;

use App\Events\ProgramCreated;
use App\Events\ProgramDeleted;
use App\Events\ProgramUpdated;
use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Program;
use Illuminate\Http\Request;

class ProgramController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Program::with('college');

        // Search
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            });
        }

        // College filter
        if ($request->has('college') && $request->college !== '') {
            $query->where('college_id', $request->college);
        }

        $perPage = $request->get('per_page', 10);
        $perPage = in_array($perPage, [10, 25, 50, 100]) ? $perPage : 10;

        $programs = $query->orderBy('created_at', 'desc')
            ->paginate($perPage)
            ->withQueryString();

        $colleges = Department::where('type', 'college')->get();

        return view('admin.programs.index', compact('programs', 'colleges'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:20|unique:programs,code',
            'college_id' => 'required|exists:departments,id',
        ]);

        $program = Program::create($validated);

        // Broadcast event
        broadcast(new ProgramCreated($program))->toOthers();

        return response()->json([
            'success' => true,
            'message' => 'Program created successfully!',
            'program' => $program->load('college'),
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Program $program)
    {
        return response()->json([
            'program' => $program->load('college'),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Program $program)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:20|unique:programs,code,'.$program->id,
            'college_id' => 'required|exists:departments,id',
        ]);

        $program->update($validated);

        // Broadcast event
        broadcast(new ProgramUpdated($program))->toOthers();

        return response()->json([
            'success' => true,
            'message' => 'Program updated successfully!',
            'program' => $program->load('college'),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Program $program)
    {
        // Check if program is being used
        if ($program->users()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete program that has users assigned to it.',
                'error' => 'Cannot delete program that has users assigned to it.',
            ], 422);
        }

        $programId = $program->id;
        $program->delete();

        // Broadcast event
        broadcast(new ProgramDeleted($programId))->toOthers();

        return response()->json([
            'success' => true,
            'message' => 'Program deleted successfully!',
        ]);
    }
}
