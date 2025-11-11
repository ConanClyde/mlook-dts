<?php

namespace App\Http\Controllers\Admin;

use App\Events\UserCreated;
use App\Events\UserDeleted;
use App\Events\UserUpdated;
use App\Helpers\NotificationHelper;
use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Program;
use App\Models\User;
use App\Rules\DmmmsuEmailRule;
use App\Rules\UniversityIdRule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    /**
     * Export users to CSV.
     */
    public function export(Request $request)
    {
        $query = User::query();

        // Apply same filters as index
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('university_id', 'like', "%{$search}%");
            });
        }

        if ($request->filled('usertype')) {
            $query->where('usertype', $request->usertype);
        }

        if ($request->filled('program_id')) {
            $query->where('program_id', $request->program_id);
        }

        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        $users = $query->with(['program', 'department'])
            ->orderBy('created_at', 'desc')
            ->get();

        $filename = 'users_export_'.date('Y-m-d_His').'.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function () use ($users) {
            $file = fopen('php://output', 'w');

            // Add CSV headers
            fputcsv($file, [
                'First Name',
                'Last Name',
                'Email',
                'University ID',
                'User Type',
                'Program/Course',
                'Department/College',
                'Created At',
            ]);

            // Add data rows
            foreach ($users as $user) {
                $programName = $user->program ? $user->program->name : '-';
                $departmentName = $user->department ? $user->department->name : '-';

                fputcsv($file, [
                    $user->first_name,
                    $user->last_name,
                    $user->email,
                    $user->university_id,
                    ucfirst(str_replace('_', ' ', $user->usertype)),
                    $programName,
                    $departmentName,
                    $user->created_at->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = User::query();

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('university_id', 'like', "%{$search}%");
            });
        }

        // Filter by usertype
        if ($request->filled('usertype')) {
            $query->where('usertype', $request->usertype);
        }

        // Filter by program (for faculty and students)
        if ($request->filled('program_id')) {
            $query->where('program_id', $request->program_id);
        }

        // Filter by department (for deans, department heads, staff, registrar)
        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        // Pagination per page
        $perPage = $request->get('per_page', 10);
        $perPage = in_array($perPage, [10, 25, 50, 100]) ? $perPage : 10;

        $users = $query->with(['program', 'department'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage)
            ->withQueryString();

        // Get filter options
        $colleges = Department::where('type', 'college')->orderBy('name')->get();
        $departments = Department::where('type', 'department')->orderBy('name')->get();
        $programs = Program::with('college')->orderBy('name')->get();

        return view('admin.users.index', compact('users', 'colleges', 'departments', 'programs'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $colleges = Department::where('type', 'college')->orderBy('name')->get();
        $departments = Department::where('type', 'department')->orderBy('name')->get();
        $programs = Program::with('college')->orderBy('name')->get();

        return view('admin.users.create', compact('colleges', 'departments', 'programs'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'university_id' => ['required', 'string', 'max:255', 'unique:users,university_id', new UniversityIdRule],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email', new DmmmsuEmailRule],
            'usertype' => ['required', 'string', 'in:admin,registrar,dean,department_head,faculty,staff,student'],
            'program_id' => ['nullable', 'exists:programs,id'],
            'department_id' => ['nullable', 'exists:departments,id'],
            'college_id' => ['nullable', 'exists:departments,id'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        // Handle dean assignment - if dean is selected, use college_id as department_id
        $departmentId = $validated['department_id'] ?? null;
        if ($validated['usertype'] === 'dean' && ! empty($request->college_id)) {
            $departmentId = $request->college_id;
        }

        $user = User::create([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'university_id' => $validated['university_id'],
            'email' => $validated['email'],
            'usertype' => $validated['usertype'],
            'program_id' => $validated['program_id'] ?? null,
            'department_id' => $departmentId,
            'password' => Hash::make($validated['password']),
            'email_verified_at' => now(),
        ]);

        // Notify other admins (exclude the actor)
        NotificationHelper::notifyAdmins(
            type: 'user_registered',
            title: 'New User Registered',
            message: "A new {$validated['usertype']} has been registered: {$user->first_name} {$user->last_name} ({$user->email})",
            link: route('admin.users.index'),
            data: ['user_id' => $user->id],
            excludeUserId: auth()->id()
        );

        // Broadcast user created event
        broadcast(new UserCreated($user));

        // Check if AJAX request
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'User created successfully!',
                'user' => $user->load(['program.college', 'department']),
            ]);
        }

        return redirect()->route('admin.users.index')
            ->with('status', 'User created successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        return view('admin.users.show', compact('user'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        $colleges = Department::where('type', 'college')->orderBy('name')->get();
        $departments = Department::where('type', 'department')->orderBy('name')->get();
        $programs = Program::with('college')->orderBy('name')->get();

        return view('admin.users.edit', compact('user', 'colleges', 'departments', 'programs'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        // Handle avatar removal first (before validation)
        $removeAvatar = $request->has('remove_avatar') && ($request->remove_avatar == '1' || $request->remove_avatar === 1);

        $rules = [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'university_id' => ['required', 'string', 'max:255', 'unique:users,university_id,'.$user->id, new UniversityIdRule],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,'.$user->id, new DmmmsuEmailRule],
            'usertype' => ['required', 'string', 'in:admin,registrar,dean,department_head,faculty,staff,student'],
            'program_id' => ['nullable', 'exists:programs,id'],
            'department_id' => ['nullable', 'exists:departments,id'],
            'college_id' => ['nullable', 'exists:departments,id'],
            'password' => ['nullable', 'confirmed', Password::defaults()],
        ];

        // Only validate avatar if we're uploading a new one
        if ($request->hasFile('avatar')) {
            $rules['avatar'] = ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'];
        }

        $validated = $request->validate($rules);

        // Handle avatar removal
        if ($removeAvatar) {
            // Delete old avatar if exists
            if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                Storage::disk('public')->delete($user->avatar);
            }
            $validated['avatar'] = null;
        }
        // Handle avatar upload
        elseif ($request->hasFile('avatar')) {
            // Delete old avatar if exists
            if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                Storage::disk('public')->delete($user->avatar);
            }

            // Store new avatar
            $avatarFile = $request->file('avatar');
            if ($avatarFile) {
                $path = $avatarFile->store('avatars', 'public');
                $validated['avatar'] = $path;
            }
        } else {
            // Don't overwrite avatar if not uploading or removing
            unset($validated['avatar']);
        }

        // Handle dean assignment - if dean is selected, use college_id as department_id
        $departmentId = $validated['department_id'] ?? null;
        if ($validated['usertype'] === 'dean' && ! empty($request->college_id)) {
            $departmentId = $request->college_id;
        }

        $updateData = [
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'university_id' => $validated['university_id'],
            'email' => $validated['email'],
            'usertype' => $validated['usertype'],
            'program_id' => $validated['program_id'] ?? null,
            'department_id' => $departmentId,
        ];

        if (! empty($validated['password'])) {
            $updateData['password'] = Hash::make($validated['password']);
        }

        if (! empty($validated['avatar'])) {
            $updateData['avatar'] = $validated['avatar'];
        }

        $user->update($updateData);

        // Reload user with relationships for broadcasting
        $user->refresh();
        $user->load(['program', 'department']);

        // Broadcast user update event
        \Log::info('UserController: Broadcasting UserUpdated event', [
            'user_id' => $user->id,
            'user_name' => $user->full_name,
            'updated_by' => auth()->id(),
        ]);
        event(new UserUpdated($user));
        \Log::info('UserController: UserUpdated event dispatched');

        // Check if it's an AJAX request
        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'User updated successfully!',
                'user' => $user,
            ]);
        }

        return redirect()->route('admin.users.index')
            ->with('status', 'User updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, User $user)
    {
        // Prevent deleting yourself
        if ($user->id === auth()->id()) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You cannot delete your own account!',
                ], 403);
            }

            return back()->with('error', 'You cannot delete your own account!');
        }

        // Store user ID before deletion
        $userId = $user->id;

        // Delete user avatar if exists
        if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
            Storage::disk('public')->delete($user->avatar);
        }

        $user->delete();

        // Broadcast user deletion event (will logout the user automatically)
        \Log::info('UserController: Broadcasting UserDeleted event', [
            'user_id' => $userId,
            'deleted_by' => auth()->id(),
        ]);
        event(new UserDeleted($userId));
        \Log::info('UserController: UserDeleted event dispatched');

        // Check if it's an AJAX request
        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'User deleted successfully!',
            ]);
        }

        return redirect()->route('admin.users.index')
            ->with('status', 'User deleted successfully!');
    }
}
