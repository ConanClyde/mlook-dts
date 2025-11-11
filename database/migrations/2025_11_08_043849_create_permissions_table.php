<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->string('description')->nullable();
            $table->string('category')->default('general'); // general, document, admin, system
            $table->timestamps();
        });

        Schema::create('role_permissions', function (Blueprint $table) {
            $table->id();
            $table->string('role'); // admin, dean, department_head, user
            $table->foreignId('permission_id')->constrained()->onDelete('cascade');
            $table->timestamps();
            
            $table->unique(['role', 'permission_id']);
        });

        Schema::create('user_permissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('permission_id')->constrained()->onDelete('cascade');
            $table->boolean('granted')->default(true); // true = grant, false = revoke
            $table->timestamps();
            
            $table->unique(['user_id', 'permission_id']);
        });

        // Insert default permissions
        DB::table('permissions')->insert([
            // Document permissions
            ['name' => 'Create Documents', 'slug' => 'document.create', 'category' => 'document', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'View All Documents', 'slug' => 'document.view.all', 'category' => 'document', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Edit Any Document', 'slug' => 'document.edit.any', 'category' => 'document', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Delete Any Document', 'slug' => 'document.delete.any', 'category' => 'document', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Archive Documents', 'slug' => 'document.archive', 'category' => 'document', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Approve Documents', 'slug' => 'document.approve', 'category' => 'document', 'created_at' => now(), 'updated_at' => now()],
            
            // Tag permissions
            ['name' => 'Manage Tags', 'slug' => 'tag.manage', 'category' => 'admin', 'created_at' => now(), 'updated_at' => now()],
            
            // Template permissions
            ['name' => 'Create Templates', 'slug' => 'template.create', 'category' => 'admin', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Edit Any Template', 'slug' => 'template.edit.any', 'category' => 'admin', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Delete Any Template', 'slug' => 'template.delete.any', 'category' => 'admin', 'created_at' => now(), 'updated_at' => now()],
            
            // Admin permissions
            ['name' => 'Manage Users', 'slug' => 'user.manage', 'category' => 'admin', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'View Audit Logs', 'slug' => 'audit.view', 'category' => 'admin', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Manage Settings', 'slug' => 'settings.manage', 'category' => 'admin', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Manage Permissions', 'slug' => 'permission.manage', 'category' => 'system', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_permissions');
        Schema::dropIfExists('role_permissions');
        Schema::dropIfExists('permissions');
    }
};
