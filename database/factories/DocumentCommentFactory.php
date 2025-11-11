<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\DocumentComment>
 */
class DocumentCommentFactory extends Factory
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
            'user_id' => \App\Models\User::factory(),
            'parent_id' => null,
            'comment' => fake()->paragraph(),
            'is_internal' => fake()->boolean(30),
        ];
    }

    /**
     * Indicate that this is an internal comment.
     */
    public function internal(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_internal' => true,
        ]);
    }

    /**
     * Indicate that this is a reply to another comment.
     */
    public function reply(): static
    {
        return $this->state(fn (array $attributes) => [
            'parent_id' => \App\Models\DocumentComment::factory(),
        ]);
    }
}
