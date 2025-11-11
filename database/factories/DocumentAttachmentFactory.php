<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\DocumentAttachment>
 */
class DocumentAttachmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $fileTypes = ['pdf', 'docx', 'xlsx', 'jpg', 'png'];
        $fileType = fake()->randomElement($fileTypes);
        $fileName = fake()->word().'.'.$fileType;

        return [
            'document_id' => \App\Models\Document::factory(),
            'file_name' => $fileName,
            'file_path' => 'attachments/'.fake()->uuid().'.'.$fileType,
            'file_type' => $fileType,
            'file_size' => fake()->numberBetween(1024, 5242880),
            'description' => fake()->optional()->sentence(),
            'uploaded_by' => \App\Models\User::factory(),
        ];
    }
}
