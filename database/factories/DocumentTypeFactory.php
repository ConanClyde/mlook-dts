<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\DocumentType>
 */
class DocumentTypeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $documentTypes = [
            ['name' => 'Memorandum', 'code' => 'MEMO'],
            ['name' => 'Letter', 'code' => 'LTR'],
            ['name' => 'Resolution', 'code' => 'RES'],
            ['name' => 'Request Form', 'code' => 'REQ'],
            ['name' => 'Report', 'code' => 'RPT'],
            ['name' => 'Certificate', 'code' => 'CERT'],
            ['name' => 'Application', 'code' => 'APP'],
            ['name' => 'Contract', 'code' => 'CNTR'],
            ['name' => 'Invoice', 'code' => 'INV'],
            ['name' => 'Purchase Order', 'code' => 'PO'],
        ];

        $type = fake()->randomElement($documentTypes);

        return [
            'name' => $type['name'],
            'code' => $type['code'].'-'.fake()->unique()->numberBetween(1, 9999),
            'description' => fake()->optional()->sentence(),
            'is_active' => fake()->boolean(90),
        ];
    }

    /**
     * Indicate that the document type is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
        ]);
    }

    /**
     * Indicate that the document type is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
