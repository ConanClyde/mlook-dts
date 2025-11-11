<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\DocumentAction>
 */
class DocumentActionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $actionTypes = ['created', 'updated', 'forwarded', 'received', 'approved', 'rejected', 'completed', 'archived'];

        return [
            'document_id' => \App\Models\Document::factory(),
            'user_id' => \App\Models\User::factory(),
            'action_type' => fake()->randomElement($actionTypes),
            'remarks' => fake()->optional()->sentence(),
            'metadata' => json_encode([
                'ip_address' => fake()->ipv4(),
                'user_agent' => fake()->userAgent(),
            ]),
        ];
    }
}
