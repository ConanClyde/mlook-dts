<?php

namespace App\Http\Controllers;

use App\Events\UserDeleted;
use App\Events\UserUpdated;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    public function update(Request $request)
    {
        $user = Auth::user();

        // Handle avatar removal first (before validation)
        $removeAvatar = $request->has('remove_avatar') && ($request->remove_avatar == '1' || $request->remove_avatar === 1);

        $rules = [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
        ];

        // Only validate avatar if we're uploading a new one (not removing)
        if ($request->hasFile('avatar') && ! $removeAvatar) {
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

        $user->update($validated);

        // Reload user with relationships for broadcasting
        $user->refresh();
        $user->load(['program', 'department']);

        // Broadcast user update event
        \Log::info('ProfileController: Broadcasting UserUpdated event', [
            'user_id' => $user->id,
            'user_name' => $user->full_name,
        ]);
        event(new UserUpdated($user));
        \Log::info('ProfileController: UserUpdated event dispatched');

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Profile updated successfully!',
                'user' => $user,
            ]);
        }

        return redirect()->route('profile')->with('status', 'Profile updated successfully!');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $user = Auth::user();
        $user->update([
            'password' => Hash::make($request->password),
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Password updated successfully!',
            ]);
        }

        return redirect()->route('profile')->with('status', 'Password updated successfully!');
    }

    public function destroy(Request $request)
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = Auth::user();

        // Delete avatar if exists
        if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
            Storage::disk('public')->delete($user->avatar);
        }

        $userId = $user->id;
        Auth::logout();
        $user->delete();

        // Broadcast user deleted event
        broadcast(new UserDeleted($userId));

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Account deleted successfully!',
            ]);
        }

        return redirect()->route('home')->with('status', 'Account deleted successfully!');
    }

    public function updateNotificationPreferences(Request $request)
    {
        $request->validate([
            'preferences' => ['required', 'array'],
            'preferences.in_app' => ['boolean'],
            'preferences.email' => ['boolean'],
            'preferences.browser' => ['boolean'],
        ]);

        $user = Auth::user();
        $user->updateNotificationPreferences($request->preferences);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Notification preferences updated successfully!',
            ]);
        }

        return redirect()->route('profile')->with('status', 'Notification preferences updated successfully!');
    }

    public function activity()
    {
        $user = Auth::user();

        // For now, return basic activity info
        // In the future, you can add activity logs
        $activities = [
            [
                'type' => 'registration',
                'description' => 'Account created',
                'date' => $user->created_at,
            ],
            [
                'type' => 'login',
                'description' => 'Last login',
                'date' => $user->updated_at,
            ],
        ];

        return view('profile.activity', compact('activities'));
    }
}
