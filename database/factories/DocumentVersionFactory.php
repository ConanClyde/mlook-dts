<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\DocumentVersion>
 */
class DocumentVersionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'document_id' => \App\Models\Document::factory(),
            'version_number' => 1,
            'title' => fake()->sentence(),
            'description' => fake()->paragraph(),
            'file_path' => 'documents/'.fake()->uuid().'.pdf',
            'file_name' => fake()->word().'.pdf',
            'changes' => json_encode([
                'changed_fields' => ['title', 'description'],
                'old_values' => ['title' => fake()->sentence()],
                'new_values' => ['title' => fake()->sentence()],
            ]),
            'change_summary' => fake()->sentence(),
            'created_by' => \App\Models\User::factory(),
        ];
    }
}
