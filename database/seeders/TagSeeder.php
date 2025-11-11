<?php

namespace Database\Seeders;

use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;

class TagSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get first admin user or create a default one
        $admin = User::where('usertype', 'admin')->first();
        $createdBy = $admin?->id ?? 1;

        $tags = [
            [
                'name' => 'Urgent',
                'description' => 'Documents requiring immediate attention',
                'is_active' => true,
                'usage_count' => 0,
                'created_by' => $createdBy,
            ],
            [
                'name' => 'Important',
                'description' => 'Important documents that need priority',
                'is_active' => true,
                'usage_count' => 0,
                'created_by' => $createdBy,
            ],
            [
                'name' => 'Confidential',
                'description' => 'Confidential documents requiring special handling',
                'is_active' => true,
                'usage_count' => 0,
                'created_by' => $createdBy,
            ],
            [
                'name' => 'Draft',
                'description' => 'Draft documents in progress',
                'is_active' => true,
                'usage_count' => 0,
                'created_by' => $createdBy,
            ],
            [
                'name' => 'Approved',
                'description' => 'Documents that have been approved',
                'is_active' => true,
                'usage_count' => 0,
                'created_by' => $createdBy,
            ],
            [
                'name' => 'Pending Review',
                'description' => 'Documents awaiting review',
                'is_active' => true,
                'usage_count' => 0,
                'created_by' => $createdBy,
            ],
            [
                'name' => 'Rejected',
                'description' => 'Documents that have been rejected',
                'is_active' => true,
                'usage_count' => 0,
                'created_by' => $createdBy,
            ],
            [
                'name' => 'Completed',
                'description' => 'Completed documents',
                'is_active' => true,
                'usage_count' => 0,
                'created_by' => $createdBy,
            ],
            [
                'name' => 'Archived',
                'description' => 'Archived documents',
                'is_active' => true,
                'usage_count' => 0,
                'created_by' => $createdBy,
            ],
            [
                'name' => 'Student Related',
                'description' => 'Documents related to students',
                'is_active' => true,
                'usage_count' => 0,
                'created_by' => $createdBy,
            ],
            [
                'name' => 'Faculty Related',
                'description' => 'Documents related to faculty',
                'is_active' => true,
                'usage_count' => 0,
                'created_by' => $createdBy,
            ],
            [
                'name' => 'Administrative',
                'description' => 'Administrative documents',
                'is_active' => true,
                'usage_count' => 0,
                'created_by' => $createdBy,
            ],
            [
                'name' => 'Academic',
                'description' => 'Academic documents',
                'is_active' => true,
                'usage_count' => 0,
                'created_by' => $createdBy,
            ],
            [
                'name' => 'Financial',
                'description' => 'Financial documents',
                'is_active' => true,
                'usage_count' => 0,
                'created_by' => $createdBy,
            ],
            [
                'name' => 'HR Related',
                'description' => 'Human resources related documents',
                'is_active' => true,
                'usage_count' => 0,
                'created_by' => $createdBy,
            ],
        ];

        foreach ($tags as $tag) {
            Tag::updateOrCreate(
                ['name' => $tag['name']],
                $tag
            );
        }

        $this->command->info('Tags seeded successfully!');
    }
}
