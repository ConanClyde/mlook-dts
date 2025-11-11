<?php

namespace App\Http\Requests;

use App\Rules\DmmmsuEmailRule;
use App\Rules\UniversityIdRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        // Get email from session (Step 3) or from input (if validating Step 1)
        $email = session('verification_email', $this->input('email', ''));
        $isStudent = str_ends_with($email, '@student.dmmmsu.edu.ph');
        $isStaff = str_ends_with($email, '@dmmmsu.edu.ph') && ! $isStudent;

        return [
            // These fields are optional in Step 3 (they come from session)
            'first_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'string', 'email', 'max:255', 'unique:users,email', new DmmmsuEmailRule],
            
            // These fields are required in Step 3
            'university_id' => ['required', 'string', 'max:255', 'unique:users,university_id', new UniversityIdRule],
            'program_id' => [$isStudent ? 'required' : 'nullable', 'exists:programs,id'],
            'department_id' => [$isStaff ? 'required' : 'nullable', 'exists:departments,id'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ];
    }

    /**
     * Get custom error messages.
     */
    public function messages(): array
    {
        return [
            'university_id.unique' => 'This University ID is already registered.',
            'email.unique' => 'This email address is already registered.',
        ];
    }

    /**
     * Get custom attribute names.
     */
    public function attributes(): array
    {
        $email = session('verification_email', $this->input('email', ''));
        $isStudent = str_ends_with($email, '@student.dmmmsu.edu.ph');
        
        return [
            'first_name' => 'first name',
            'last_name' => 'last name',
            'university_id' => $isStudent ? 'Student ID' : 'Staff ID',
            'program_id' => 'program',
            'department_id' => 'department',
        ];
    }
}
