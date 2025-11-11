<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('document_types', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Memo, Letter, Request, Report, etc.
            $table->string('code')->unique(); // MEMO, LTR, REQ, RPT
            $table->text('description')->nullable();
            $table->json('allowed_roles')->nullable();
            $table->json('allowed_receive')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('auto_assign_enabled')->default(false);
            $table->enum('routing_logic', ['role', 'department', 'specific_user', 'routing_rules'])->nullable();
            $table->string('default_receiver_role')->nullable();
            $table->foreignId('default_receiver_department_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->foreignId('default_receiver_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_types');
    }
};
