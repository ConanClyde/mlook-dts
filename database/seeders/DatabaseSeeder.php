<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed departments, programs, document types, and users
        $this->call([
            DepartmentSeeder::class,
            ProgramSeeder::class,
            DocumentTypeSeeder::class,
            ManyUsersSeeder::class,
            TagSeeder::class,
        ]);
    }
}
