<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Program;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ManyUsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $colleges = Department::where('type', 'college')->get();
        $adminDepts = Department::where('type', 'department')->get();
        $programs = Program::all();

        $usersCreated = 0;

        // Create admins (Staff ID format: 6+ digits, e.g., 100001, 100002, etc.)
        $adminCounter = User::where('usertype', 'admin')->count();
        for ($i = $adminCounter + 1; $i <= $adminCounter + 2; $i++) {
            User::create([
                'first_name' => fake()->firstName(),
                'last_name' => fake()->lastName(),
                'university_id' => str_pad(100000 + $i, 6, '0', STR_PAD_LEFT),
                'usertype' => 'admin',
                'email' => 'admin'.$i.'@dmmmsu.edu.ph',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]);
            $usersCreated++;
        }

        // Create registrars (Staff ID format: 6+ digits, e.g., 200001, 200002, etc.)
        $registrarCount = User::where('usertype', 'registrar')->count();
        $registrarCounter = 200000 + $registrarCount;
        for ($i = $registrarCount + 1; $i <= $registrarCount + 2; $i++) {
            $dept = $adminDepts->where('code', 'REG')->first();
            if ($dept) {
                User::create([
                    'first_name' => fake()->firstName(),
                    'last_name' => fake()->lastName(),
                    'university_id' => (string) ($registrarCounter + $i),
                    'usertype' => 'registrar',
                    'department_id' => $dept->id,
                    'email' => 'registrar'.$i.'@dmmmsu.edu.ph',
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                ]);
                $usersCreated++;
            }
        }

        // Create deans for each college (Staff ID format: 6+ digits, e.g., 300001, 300002, etc.)
        $deanCounter = 300000;
        foreach ($colleges as $college) {
            $existingDean = User::where('department_id', $college->id)
                ->where('usertype', 'dean')
                ->first();

            if (! $existingDean) {
                $deanCounter++;
                $dean = User::create([
                    'first_name' => fake()->firstName(),
                    'last_name' => fake()->lastName(),
                    'university_id' => (string) $deanCounter,
                    'usertype' => 'dean',
                    'department_id' => $college->id,
                    'email' => strtolower(str_replace(' ', '', $college->code)).'.dean@dmmmsu.edu.ph',
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                ]);
                $college->update(['head_user_id' => $dean->id]);
                $usersCreated++;
            }
        }

        // Create department heads for admin departments (Staff ID format: 6+ digits, e.g., 400001, 400002, etc.)
        $deptHeadCounter = 400000;
        foreach ($adminDepts as $dept) {
            if ($dept->code !== 'REG' && ! $dept->head_user_id) {
                $deptHeadCounter++;
                $deptHead = User::create([
                    'first_name' => fake()->firstName(),
                    'last_name' => fake()->lastName(),
                    'university_id' => (string) $deptHeadCounter,
                    'usertype' => 'department_head',
                    'department_id' => $dept->id,
                    'email' => strtolower($dept->code).'.head@dmmmsu.edu.ph',
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                ]);
                $dept->update(['head_user_id' => $deptHead->id]);
                $usersCreated++;
            }
        }

        // Create faculty members (Staff ID format: 6+ digits, e.g., 500001, 500002, etc.)
        $facultyCounter = 500000 + User::where('usertype', 'faculty')->count();
        foreach ($colleges as $college) {
            $facultyPerCollege = rand(2, 3);
            for ($i = 0; $i < $facultyPerCollege; $i++) {
                $facultyCounter++;
                $firstName = fake()->firstName();
                $lastName = fake()->lastName();
                $username = strtolower($firstName.'.'.$lastName.rand(1, 99));

                User::create([
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'university_id' => (string) $facultyCounter,
                    'usertype' => 'faculty',
                    'department_id' => $college->id,
                    'email' => $username.'@dmmmsu.edu.ph',
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                ]);
                $usersCreated++;
            }
        }

        // Create staff members (Staff ID format: 6+ digits, e.g., 600001, 600002, etc.)
        $staffCounter = 600000 + User::where('usertype', 'staff')->count();
        foreach ($adminDepts as $dept) {
            $staffPerDept = rand(1, 2);
            for ($i = 0; $i < $staffPerDept; $i++) {
                $staffCounter++;
                $firstName = fake()->firstName();
                $lastName = fake()->lastName();
                $username = strtolower($firstName.'.'.$lastName.rand(1, 99));

                User::create([
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'university_id' => (string) $staffCounter,
                    'usertype' => 'staff',
                    'department_id' => $dept->id,
                    'email' => $username.'@dmmmsu.edu.ph',
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                ]);
                $usersCreated++;
            }
        }

        // Create students (Student ID format: 2XX-XXXX-2, e.g., 221-0238-2, 222-1234-2)
        $studentYear = 221; // Starting year code (221 = 2021, 222 = 2022, etc.)
        $studentSequence = 1;
        foreach ($programs as $program) {
            $studentsPerProgram = rand(3, 5);
            for ($i = 0; $i < $studentsPerProgram; $i++) {
                $firstName = fake()->firstName();
                $lastName = fake()->lastName();
                $username = strtolower($firstName.'.'.$lastName.rand(1, 999));

                // Generate student ID: 2XX-XXXX-2 format
                // Format: 2 + year code (2 digits) + - + sequence (4 digits) + - + 2
                $yearCode = $studentYear + (int) floor($studentSequence / 10000); // Increment year every 10000 students
                $sequence = ($studentSequence % 10000);
                $studentId = '2'.str_pad($yearCode % 100, 2, '0', STR_PAD_LEFT).'-'.str_pad($sequence, 4, '0', STR_PAD_LEFT).'-2';

                User::create([
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'university_id' => $studentId,
                    'usertype' => 'student',
                    'program_id' => $program->id,
                    'department_id' => $program->college_id,
                    'email' => $username.'@student.dmmmsu.edu.ph',
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                ]);
                $usersCreated++;
                $studentSequence++;
            }
        }

        $this->command->info("✓ Successfully created {$usersCreated} users with proper ID formats!");
        $this->command->info('  - Admin: 6+ digits (e.g., 100001, 100002)');
        $this->command->info('  - Registrar: 6+ digits (e.g., 200001, 200002)');
        $this->command->info('  - Deans: 6+ digits (e.g., 300001, 300002)');
        $this->command->info('  - Department Heads: 6+ digits (e.g., 400001, 400002)');
        $this->command->info('  - Faculty: 6+ digits (e.g., 500001, 500002)');
        $this->command->info('  - Staff: 6+ digits (e.g., 600001, 600002)');
        $this->command->info('  - Students: 2XX-XXXX-2 format (e.g., 221-0238-2, 222-1234-2)');
    }
}
