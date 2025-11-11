<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class UniversityIdRule implements ValidationRule
{
    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Determine if user is a student or staff based on email
        // Check session first (Step 3), then request (Step 1)
        $email = session('verification_email', request()->input('email', ''));
        $isStudent = str_ends_with($email, '@student.dmmmsu.edu.ph');

        if ($isStudent) {
            // Students: Format 2XX-XXXX-2 (e.g., 221-0238-2)
            if (! preg_match('/^2\d{2}-\d{4}-2$/', $value)) {
                $fail('The Student ID must follow the format: 2XX-XXXX-2 (e.g., 221-0238-2)');
            }
        } else {
            // Staff: Minimum 6 digits (e.g., 123456)
            if (! preg_match('/^\d{6,}$/', $value)) {
                $fail('The Staff ID must be at least 6 digits (e.g., 123456)');
            }
        }
    }
}
