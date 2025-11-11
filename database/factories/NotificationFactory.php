<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Notification>
 */
class NotificationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $types = ['document_forwarded', 'document_received', 'document_approved', 'document_rejected', 'comment_added', 'deadline_approaching'];
        $type = fake()->randomElement($types);
        $isRead = fake()->boolean(40);

        return [
            'user_id' => \App\Models\User::factory(),
            'type' => $type,
            'title' => fake()->sentence(4),
            'message' => fake()->sentence(),
            'link' => '/documents/'.fake()->numberBetween(1, 100),
            'data' => json_encode([
                'document_id' => fake()->numberBetween(1, 100),
                'tracking_number' => 'DMMMSU-'.date('Ym').'-'.strtoupper(substr(uniqid(), -6)),
            ]),
            'read' => $isRead,
            'read_at' => $isRead ? now() : null,
        ];
    }

    /**
     * Indicate that the notification is unread.
     */
    public function unread(): static
    {
        return $this->state(fn (array $attributes) => [
            'read' => false,
            'read_at' => null,
        ]);
    }

    /**
     * Indicate that the notification is read.
     */
    public function read(): static
    {
        return $this->state(fn (array $attributes) => [
            'read' => true,
            'read_at' => now(),
        ]);
    }
}
