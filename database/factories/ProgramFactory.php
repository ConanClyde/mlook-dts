<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Program>
 */
class ProgramFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $programs = [
            ['name' => 'Bachelor of Science in Computer Science', 'code' => 'BSCS'],
            ['name' => 'Bachelor of Science in Information Technology', 'code' => 'BSIT'],
            ['name' => 'Bachelor of Science in Civil Engineering', 'code' => 'BSCE'],
            ['name' => 'Bachelor of Science in Electrical Engineering', 'code' => 'BSEE'],
            ['name' => 'Bachelor of Science in Mechanical Engineering', 'code' => 'BSME'],
            ['name' => 'Bachelor of Elementary Education', 'code' => 'BEED'],
            ['name' => 'Bachelor of Secondary Education', 'code' => 'BSED'],
            ['name' => 'Bachelor of Science in Business Administration', 'code' => 'BSBA'],
            ['name' => 'Bachelor of Science in Accountancy', 'code' => 'BSA'],
            ['name' => 'Bachelor of Science in Agriculture', 'code' => 'BSAg'],
            ['name' => 'Bachelor of Science in Marine Biology', 'code' => 'BSMB'],
            ['name' => 'Bachelor of Arts in Communication', 'code' => 'ABComm'],
        ];

        $program = fake()->randomElement($programs);

        return [
            'name' => $program['name'],
            'code' => $program['code'],
            'description' => fake()->optional()->sentence(),
            'college_id' => null,
            'head_user_id' => null,
        ];
    }
}
