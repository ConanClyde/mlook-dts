<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $usertype = fake()->randomElement(['student', 'faculty', 'staff', 'registrar', 'department_head', 'dean', 'admin']);
        $firstName = fake()->firstName();
        $lastName = fake()->lastName();

        // Generate appropriate email based on usertype
        $email = $this->generateDmmmsuEmail($firstName, $lastName, $usertype);

        return [
            'first_name' => $firstName,
            'last_name' => $lastName,
            'university_id' => fake()->unique()->numerify('####-#####'),
            'usertype' => $usertype,
            'email' => $email,
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Generate a DMMMSU email address based on name and usertype.
     */
    protected function generateDmmmsuEmail(string $firstName, string $lastName, string $usertype): string
    {
        $username = strtolower($firstName.'.'.$lastName.fake()->unique()->numberBetween(1, 999));
        $username = str_replace(' ', '', $username);

        // Students get @student.dmmmsu.edu.ph
        if ($usertype === 'student') {
            return $username.'@student.dmmmsu.edu.ph';
        }

        // Staff, faculty, admin get @dmmmsu.edu.ph
        return $username.'@dmmmsu.edu.ph';
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * Indicate that the user is an admin.
     */
    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'usertype' => 'admin',
        ]);
    }

    /**
     * Indicate that the user is a registrar.
     */
    public function registrar(): static
    {
        return $this->state(fn (array $attributes) => [
            'usertype' => 'registrar',
        ]);
    }

    /**
     * Indicate that the user is a dean.
     */
    public function dean(): static
    {
        return $this->state(fn (array $attributes) => [
            'usertype' => 'dean',
        ]);
    }

    /**
     * Indicate that the user is a department head.
     */
    public function departmentHead(): static
    {
        return $this->state(fn (array $attributes) => [
            'usertype' => 'department_head',
        ]);
    }

    /**
     * Indicate that the user is faculty.
     */
    public function faculty(): static
    {
        return $this->state(fn (array $attributes) => [
            'usertype' => 'faculty',
        ]);
    }

    /**
     * Indicate that the user is staff.
     */
    public function staff(): static
    {
        return $this->state(fn (array $attributes) => [
            'usertype' => 'staff',
        ]);
    }

    /**
     * Indicate that the user is a student.
     */
    public function student(): static
    {
        return $this->state(fn (array $attributes) => [
            'usertype' => 'student',
        ]);
    }
}
