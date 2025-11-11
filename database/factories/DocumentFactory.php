<?php

namespace Database\Factories;

use App\Models\Department;
use App\Models\Document;
use App\Models\DocumentType;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Document>
 */
class DocumentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $status = fake()->randomElement([
            Document::STATUS_DRAFT,
            Document::STATUS_ROUTING,
            Document::STATUS_RECEIVED,
            Document::STATUS_IN_REVIEW,
            Document::STATUS_FOR_APPROVAL,
            Document::STATUS_COMPLETED,
        ]);

        $urgency = fake()->randomElement(['low', 'normal', 'high', 'urgent']);

        return [
            'tracking_number' => Document::generateTrackingNumber(),
            'document_type_id' => DocumentType::factory(),
            'title' => fake()->sentence(),
            'description' => fake()->paragraph(),
            'file_path' => null,
            'file_name' => null,
            'created_by' => User::factory(),
            'current_holder_id' => User::factory(),
            'origin_department_id' => Department::factory(),
            'status' => $status,
            'approval_status' => 'pending',
            'urgency_level' => $urgency,
            'deadline' => fake()->optional(0.7)->dateTimeBetween('now', '+30 days'),
            'is_overdue' => false,
            'remarks' => fake()->optional()->sentence(),
            'completed_at' => $status === Document::STATUS_COMPLETED ? now() : null,
            'archived_at' => null,
        ];
    }

    /**
     * Indicate that the document is a draft.
     */
    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Document::STATUS_DRAFT,
            'completed_at' => null,
        ]);
    }

    /**
     * Indicate that the document is routing.
     */
    public function routing(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Document::STATUS_ROUTING,
            'completed_at' => null,
        ]);
    }

    /**
     * Indicate that the document is completed.
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Document::STATUS_COMPLETED,
            'completed_at' => now(),
        ]);
    }

    /**
     * Indicate that the document is archived.
     */
    public function archived(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Document::STATUS_ARCHIVED,
            'archived_at' => now(),
        ]);
    }

    /**
     * Indicate that the document is urgent.
     */
    public function urgent(): static
    {
        return $this->state(fn (array $attributes) => [
            'urgency_level' => 'urgent',
        ]);
    }

    /**
     * Indicate that the document is approved.
     */
    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'approval_status' => 'approved',
            'approved_by' => User::factory(),
            'approved_at' => now(),
        ]);
    }

    /**
     * Indicate that the document is rejected.
     */
    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Document::STATUS_REJECTED,
            'approval_status' => 'rejected',
            'rejected_by' => User::factory(),
            'rejected_at' => now(),
            'rejection_reason' => fake()->sentence(),
        ]);
    }
}
