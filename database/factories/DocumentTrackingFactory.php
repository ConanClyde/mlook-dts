<?php

namespace Database\Factories;

use App\Models\Department;
use App\Models\Document;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\DocumentTracking>
 */
class DocumentTrackingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $action = fake()->randomElement(['forwarded', 'received', 'completed', 'returned', 'approved', 'rejected']);
        $sentAt = fake()->dateTimeBetween('-30 days', 'now');
        $receivedAt = fake()->boolean(70) ? fake()->dateTimeBetween($sentAt, 'now') : null;

        return [
            'document_id' => Document::factory(),
            'from_user_id' => User::factory(),
            'to_user_id' => User::factory(),
            'from_department_id' => Department::factory(),
            'to_department_id' => Department::factory(),
            'action' => $action,
            'remarks' => fake()->optional()->sentence(),
            'instructions' => fake()->optional()->paragraph(),
            'sent_at' => $sentAt,
            'received_at' => $receivedAt,
            'is_read' => $receivedAt !== null,
        ];
    }

    /**
     * Indicate that the tracking is received.
     */
    public function received(): static
    {
        return $this->state(fn (array $attributes) => [
            'action' => 'received',
            'received_at' => now(),
            'is_read' => true,
        ]);
    }

    /**
     * Indicate that the tracking is not yet received.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'action' => 'forwarded',
            'received_at' => null,
            'is_read' => false,
        ]);
    }
}
