<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\DocumentType;
use App\Models\Program;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    /**
     * Display settings page with all tabs
     */
    public function index(Request $request)
    {
        $tab = $request->get('tab', 'appearance');

        // Document Types
        $documentTypesQuery = DocumentType::query();
        if ($request->has('dt_search') && $request->dt_search !== '') {
            $search = $request->dt_search;
            $documentTypesQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            });
        }
        $dtPerPage = $request->get('dt_per_page', 10);
        $dtPerPage = in_array($dtPerPage, [10, 25, 50, 100]) ? $dtPerPage : 10;
        $documentTypes = $documentTypesQuery->orderBy('created_at', 'desc')
            ->paginate($dtPerPage, ['*'], 'dt_page')
            ->withQueryString();

        // Departments
        $departmentsQuery = Department::query();
        if ($request->has('dept_search') && $request->dept_search !== '') {
            $search = $request->dept_search;
            $departmentsQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            });
        }
        if ($request->has('dept_type') && $request->dept_type !== '') {
            $departmentsQuery->where('type', $request->dept_type);
        }
        $deptPerPage = $request->get('dept_per_page', 10);
        $deptPerPage = in_array($deptPerPage, [10, 25, 50, 100]) ? $deptPerPage : 10;
        $departments = $departmentsQuery->orderBy('created_at', 'desc')
            ->paginate($deptPerPage, ['*'], 'dept_page')
            ->withQueryString();

        // Programs
        $programsQuery = Program::with('college');
        if ($request->has('prog_search') && $request->prog_search !== '') {
            $search = $request->prog_search;
            $programsQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            });
        }
        if ($request->has('prog_college') && $request->prog_college !== '') {
            $programsQuery->where('college_id', $request->prog_college);
        }
        $progPerPage = $request->get('prog_per_page', 10);
        $progPerPage = in_array($progPerPage, [10, 25, 50, 100]) ? $progPerPage : 10;
        $programs = $programsQuery->orderBy('created_at', 'desc')
            ->paginate($progPerPage, ['*'], 'prog_page')
            ->withQueryString();

        $colleges = Department::where('type', 'college')->get();
        $users = User::orderBy('first_name')->get();

        // Tags
        $tagsQuery = Tag::with('creator');
        if ($request->has('tag_search') && $request->tag_search !== '') {
            $search = $request->tag_search;
            $tagsQuery->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }
        $tagPerPage = $request->get('tag_per_page', 10);
        $tagPerPage = in_array($tagPerPage, [10, 25, 50, 100]) ? $tagPerPage : 10;
        $tags = $tagsQuery->orderBy('name')
            ->paginate($tagPerPage, ['*'], 'tag_page')
            ->withQueryString();

        return view('admin.settings.index', compact(
            'tab',
            'documentTypes',
            'departments',
            'programs',
            'colleges',
            'users',
            'tags'
        ));
    }
}
