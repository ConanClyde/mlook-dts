<?php

namespace App\Services;

use App\Helpers\NotificationHelper;
use App\Models\User;
use App\Rules\DmmmsuEmailRule;
use Illuminate\Support\Facades\Hash;

class UserRegistrationService
{
    /**
     * Register a new user.
     */
    public function register(array $data): User
    {
        // Automatically determine usertype based on email domain
        $usertype = $this->determineUserType($data['email']);

        $user = User::create([
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'university_id' => $data['university_id'],
            'usertype' => $usertype,
            'email' => $data['email'],
            'program_id' => $data['program_id'] ?? null,
            'department_id' => $data['department_id'] ?? null,
            'password' => Hash::make($data['password']),
        ]);

        // Notify all admins about new registration
        NotificationHelper::notifyAdmins(
            type: 'user_registered',
            title: 'New User Registration',
            message: "A new {$usertype} has registered: {$user->first_name} {$user->last_name} ({$user->email})",
            link: route('admin.users.index'),
            data: ['user_id' => $user->id]
        );

        return $user;
    }

    /**
     * Determine user type based on email domain.
     */
    protected function determineUserType(string $email): string
    {
        // Students use @student.dmmmsu.edu.ph
        if (DmmmsuEmailRule::isStudentEmail($email)) {
            return 'student';
        }

        // Staff/Faculty use @dmmmsu.edu.ph
        // Default to 'staff' for non-student university emails
        // Admin can later upgrade specific accounts
        if (DmmmsuEmailRule::isStaffEmail($email)) {
            return 'staff';
        }

        // Fallback (shouldn't reach here if validation works)
        return 'student';
    }

    /**
     * Validate if registration is allowed for this email.
     */
    public function canRegister(string $email): bool
    {
        return DmmmsuEmailRule::isStudentEmail($email) ||
               DmmmsuEmailRule::isStaffEmail($email);
    }
}
