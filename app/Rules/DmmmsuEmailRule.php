<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class DmmmsuEmailRule implements ValidationRule
{
    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $allowedDomains = [
            'student.dmmmsu.edu.ph',
            'dmmmsu.edu.ph', // For staff, faculty, admin
        ];

        $domain = substr(strrchr($value, '@'), 1);

        if (! in_array($domain, $allowedDomains)) {
            $fail('Only DMMMSU email addresses (@student.dmmmsu.edu.ph or @dmmmsu.edu.ph) are allowed to register.');
        }
    }

    /**
     * Check if email is a student email.
     */
    public static function isStudentEmail(string $email): bool
    {
        return str_ends_with($email, '@student.dmmmsu.edu.ph');
    }

    /**
     * Check if email is a staff/faculty email.
     */
    public static function isStaffEmail(string $email): bool
    {
        return str_ends_with($email, '@dmmmsu.edu.ph') &&
               ! str_ends_with($email, '@student.dmmmsu.edu.ph');
    }
}
