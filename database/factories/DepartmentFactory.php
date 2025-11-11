<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Department>
 */
class DepartmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $type = fake()->randomElement(['department', 'college']);

        $departments = [
            'department' => [
                'Registrar Office', 'Human Resources', 'Finance Office',
                'IT Department', 'Library', 'Research Office',
                'Extension Office', 'Planning Office', 'Budget Office',
            ],
            'college' => [
                'College of Engineering', 'College of Education',
                'College of Arts and Sciences', 'College of Business Administration',
                'College of Agricultural Sciences', 'College of Industrial Technology',
                'College of Aquatic and Marine Sciences',
            ],
        ];

        $name = fake()->randomElement($departments[$type]);

        return [
            'name' => $name,
            'code' => strtoupper(substr(md5($name), 0, 6)),
            'description' => fake()->optional()->sentence(),
            'type' => $type,
            'head_user_id' => null,
        ];
    }

    /**
     * Indicate that this is a college.
     */
    public function college(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'college',
        ]);
    }

    /**
     * Indicate that this is a department.
     */
    public function department(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'department',
        ]);
    }
}
